<!-- Cart related logic haru sabb handle grney -->
<?php
session_start();
require_once "../shared/dbconnect.php";

// yedi user login gareko chaina bhane login page ma redirect garne
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login to add items to cart.'); window.location.href='loginsignup/login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Get values (GET or POST)
$product_id = intval($_GET['id'] ?? $_POST['product_id'] ?? 0);
$jersey_size = trim($_GET['size'] ?? $_POST['size'] ?? '');
$quantity = intval($_GET['qty'] ?? $_POST['quantity'] ?? 1);

$quality = trim($_GET['quality'] ?? $_POST['quality'] ?? '');
$print_name = trim($_POST['print_name'] ?? '');
$print_number = trim($_POST['print_number'] ?? '');
$final_price = isset($_POST['final_price']) ? floatval($_POST['final_price']) : null;

// Validation
if (!$product_id || !$jersey_size) {
    $_SESSION['alert'] = "Please select a size.";
    $_SESSION['alert_type'] = "warning";
    header("Location: view_jersey.php?id=" . $product_id);
    exit();
}
if ($quantity < 1)
    $quantity = 1;

// Fetch product info
$stmt = $conn->prepare("SELECT j_name, price, discount, category, image, quality FROM products WHERE id=? LIMIT 1");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
$stmt->close();

$pname = $p['j_name'];
$base_price = floatval($p['price']); // original product price
$discount = floatval($p['discount']);
$category = $p['category'];
$image = $p['image'];

// If quality not provided by user, default to product's quality from products table
if ($quality === '') {
    $quality = $p['quality'] ?? '';
}

$adjusted_base_price = $base_price;

// Now apply discount on adjusted base price
$price_after_discount = $adjusted_base_price;
if ($discount > 0) {
    $price_after_discount -= ($adjusted_base_price * $discount / 100);
}

// Add print cost
$print_cost = 0;
if (!empty($print_name))
    $print_cost += 100;
if (!empty($print_number))
    $print_cost += 50;

$price_after_discount += $print_cost;
$price_after_discount = round($price_after_discount, 2);


// Override with modal price if provided
if ($final_price !== null) {
    $price_after_discount = round($final_price, 2);
}

// Check if same item exists in cart
$check = $conn->prepare("
    SELECT id, quantity FROM cart_items 
    WHERE user_id=? AND product_id=? AND jersey_size=? AND quality=? AND print_name=? AND print_number=? LIMIT 1
");
$check->bind_param("iissss", $user_id, $product_id, $jersey_size, $quality, $print_name, $print_number);
$check->execute();
$res = $check->get_result();

// Check available stock (soft check)
$stockStmt = $conn->prepare("
    SELECT stock FROM product_sizes 
    WHERE product_id=? AND size=? LIMIT 1
");
$stockStmt->bind_param("is", $product_id, $jersey_size);
$stockStmt->execute();
$stockRow = $stockStmt->get_result()->fetch_assoc();
$stockStmt->close();

$available_stock = intval($stockRow['stock'] ?? 0);

if ($available_stock <= 0) {
    $_SESSION['alert'] = "Selected size is out of stock.";
    $_SESSION['alert_type'] = "danger";
    header("Location: view_jersey.php?id=" . $product_id);
    exit();
}


if ($res->num_rows > 0) {
    // Update quantity
    $row = $res->fetch_assoc();
    $new_q = min($row['quantity'] + $quantity, $available_stock);

    $u = $conn->prepare("UPDATE cart_items SET quantity=? WHERE id=?");
    $u->bind_param("ii", $new_q, $row['id']);
    $u->execute();
    $u->close();
} else {
    // Insert new item
    $ins = $conn->prepare("
        INSERT INTO cart_items
        (user_id, product_id, pname, category, jersey_size, quality, base_price, print_name, print_number, print_cost, quantity, price_after_discount, discount, image, created_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())
    ");
    $ins->bind_param(
        "iissssdsssdids",
        $user_id,
        $product_id,
        $pname,
        $category,
        $jersey_size,
        $quality,
        $adjusted_base_price,  // store quality-adjusted base price
        $print_name,
        $print_number,
        $print_cost,
        $quantity,
        $price_after_discount,
        $discount,
        $image
    );
    if (!$ins->execute()) {
        $_SESSION['alert'] = "Database error: " . $ins->error;
        $_SESSION['alert_type'] = "danger";
        $ins->close();
        header("Location: view_jersey.php?id=" . $product_id);
        exit();
    }
    $ins->close();
}

$check->close();
$_SESSION['alert'] = "Item added to cart.";
$_SESSION['alert_type'] = "success";
header("Location: displaycart.php");
exit();
?>