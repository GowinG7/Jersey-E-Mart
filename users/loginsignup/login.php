<?php
session_start();

require_once("../../shared/dbconnect.php");
include_once("../../shared/commonlinks.php");

function mergeGuestCartIntoDb($conn, $userId, $guestItems)
{
    if (!is_array($guestItems) || empty($guestItems)) {
        return;
    }

    foreach ($guestItems as $item) {
        $productId = intval($item['product_id'] ?? 0);
        $jerseySize = strtoupper(trim($item['size'] ?? ''));
        $quantity = max(1, intval($item['quantity'] ?? 1));
        $printName = trim($item['print_name'] ?? '');
        $printNumber = trim((string) ($item['print_number'] ?? ''));
        $itemQuality = trim($item['quality'] ?? '');

        if ($productId <= 0 || $jerseySize === '') {
            continue;
        }

        $stmt = $conn->prepare("SELECT j_name, price, discount, category, image, quality FROM products WHERE id=? LIMIT 1");
        if (!$stmt) {
            continue;
        }
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$product) {
            continue;
        }

        $quality = $itemQuality !== '' ? $itemQuality : ($product['quality'] ?? '');

        $stockStmt = $conn->prepare("SELECT stock FROM product_sizes WHERE product_id=? AND size=? LIMIT 1");
        if (!$stockStmt) {
            continue;
        }
        $stockStmt->bind_param("is", $productId, $jerseySize);
        $stockStmt->execute();
        $stockRow = $stockStmt->get_result()->fetch_assoc();
        $stockStmt->close();

        $availableStock = intval($stockRow['stock'] ?? 0);
        if ($availableStock <= 0) {
            continue;
        }

        $quantity = min($quantity, $availableStock);

        $basePrice = floatval($product['price']);
        $discount = floatval($product['discount']);

        $priceAfterDiscount = $basePrice;
        if ($discount > 0) {
            $priceAfterDiscount -= ($basePrice * $discount / 100);
        }

        $printCost = 0;
        if ($printName !== '') {
            $printCost += 100;
        }
        if ($printNumber !== '') {
            $printCost += 50;
        }
        $priceAfterDiscount = round($priceAfterDiscount + $printCost, 2);

        $check = $conn->prepare("SELECT id, quantity FROM cart_items WHERE user_id=? AND product_id=? AND jersey_size=? AND quality=? AND print_name=? AND print_number=? LIMIT 1");
        if (!$check) {
            continue;
        }
        $check->bind_param("iissss", $userId, $productId, $jerseySize, $quality, $printName, $printNumber);
        $check->execute();
        $existing = $check->get_result();

        if ($existing && $existing->num_rows > 0) {
            $row = $existing->fetch_assoc();
            $newQty = min(intval($row['quantity']) + $quantity, $availableStock);

            $update = $conn->prepare("UPDATE cart_items SET quantity=?, price_after_discount=?, base_price=?, discount=?, image=?, category=?, pname=? WHERE id=?");
            if ($update) {
                $update->bind_param(
                    "idddsssi",
                    $newQty,
                    $priceAfterDiscount,
                    $basePrice,
                    $discount,
                    $product['image'],
                    $product['category'],
                    $product['j_name'],
                    $row['id']
                );
                $update->execute();
                $update->close();
            }
        } else {
            $insert = $conn->prepare("INSERT INTO cart_items (user_id, product_id, pname, category, jersey_size, quality, base_price, print_name, print_number, print_cost, quantity, price_after_discount, discount, image, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");
            if ($insert) {
                $insert->bind_param(
                    "iissssdsssdids",
                    $userId,
                    $productId,
                    $product['j_name'],
                    $product['category'],
                    $jerseySize,
                    $quality,
                    $basePrice,
                    $printName,
                    $printNumber,
                    $printCost,
                    $quantity,
                    $priceAfterDiscount,
                    $discount,
                    $product['image']
                );
                $insert->execute();
                $insert->close();
            }
        }

        $check->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    // Get username/email and password
    $unameOrEmail = trim($_POST["unameOrEmail"]);
    $pass = trim($_POST["password"]);

    if (!empty($unameOrEmail) && !empty($pass)) {
        // Prepare query to check for username OR email
        $stmt = $conn->prepare("SELECT id, name, username, password FROM user_creden WHERE username = ? OR email = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $unameOrEmail, $unameOrEmail);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // Verify hashed password
                if (password_verify($pass, $row["password"])) {
                    // Safe: only set session after password is correct
                    //aba login gareko user ko id ra username session ma store garna set grney
                    $_SESSION["user_id"] = $row["id"];
                    $_SESSION["user_name"] = $row["username"];
                    $_SESSION["nam"] = $row["name"];

                    $guestCartRaw = $_POST["guest_cart_json"] ?? "";
                    if (!empty($guestCartRaw)) {
                        $guestCart = json_decode($guestCartRaw, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($guestCart)) {
                            mergeGuestCartIntoDb($conn, intval($row["id"]), $guestCart);
                            $_SESSION["clear_guest_cart"] = 1;
                        }
                    }

                    header("Location: ../index.php");
                    exit();
                } else {
                    $_SESSION["errorMessage"] = "Incorrect password!";
                }
            } else {
                $_SESSION["errorMessage"] = "Username or email not found!";
            }

            $stmt->close();
        } else {
            $_SESSION["errorMessage"] = "Something went wrong with the query!";
        }
    } else {
        $_SESSION["errorMessage"] = "All fields are required!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="../css/login.css"> <!-- Link to your CSS file -->

</head>

<body>
    <div class="login-form">
        <form action="login.php" method="POST">
            <h4>Login</h4>
            <input type="hidden" name="guest_cart_json" id="guestCartJson" value="">

            <!-- Show messages -->
            <?php
            // if (isset($_SESSION['successMessage'])) {
            //     echo "<div class='success' id='successMessage'>" . $_SESSION['successMessage'] . "</div>";
            //     unset($_SESSION['successMessage']);
            // }
            if (isset($_SESSION['errorMessage'])) {
                echo "<div class='error' id='errorMessage'>" . $_SESSION['errorMessage'] . "</div>";
                unset($_SESSION['errorMessage']);
            }
            ?>

            <!-- Username or Email input -->
            <input type="text" name="unameOrEmail" id="unameOrEmail" class="form-control"
                placeholder="Username or Email" required>

            <!-- Password input -->
            <input name="password" type="password" class="form-control" id="password" placeholder="Password" required>

            <!-- Login button -->
            <button type="submit" name="submit" class="btn">Login</button>

            <!-- Footer links -->
            <div class="footer">
                <a href="forgot_pass.php">Forgot your password?</a>
                <hr>
                <div>
                    Don't have an account?
                    <a href="signup.php">Sign up</a>
                </div>
            </div>
        </form>
    </div>


    <!-- offline jquery v3.7.1 script file -->
    <script src="../../shared/jquery-3.7.1.min.js"></script>
    <script src="../js/guest_cart.js"></script>
    <script src="../js/hidemessage.js"></script>
    <script src="../js/login.js"></script>
    <script>
        (function () {
            const form = document.querySelector("form[action='login.php']");
            const hidden = document.getElementById("guestCartJson");

            if (!form || !hidden || !window.guestCart) {
                return;
            }

            form.addEventListener("submit", function () {
                hidden.value = JSON.stringify(window.guestCart.getCart());
            });
        })();
    </script>

</body>

</html>