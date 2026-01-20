<?php
session_start();
require_once "../shared/dbconnect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: loginsignup/login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $payment_option = $_POST['payment_option'];
    $shipping = 150;

    // Fetch cart items
    $cart_query = mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id = $user_id");
    if (mysqli_num_rows($cart_query) == 0) {
        header("Location: displaycart.php");
        exit();
    }

    // Build items array and calculate total
    $grand_total = 0;
    $items = [];

    while ($row = mysqli_fetch_assoc($cart_query)) {
        $items[] = [
            'pid' => $row['product_id'],
            'pname' => $row['pname'],
            'category' => $row['category'],
            'size' => $row['jersey_size'],
            'quality' => $row['quality'],
            'base_price' => $row['base_price'],
            'print_name' => $row['print_name'],
            'print_number' => $row['print_number'],
            'print_cost' => $row['print_cost'],
            'qty' => $row['quantity'],
            'final_price' => $row['price_after_discount'],
            'subtotal' => $row['price_after_discount'] * $row['quantity'],
            'image' => $row['image']
        ];
        $grand_total += $row['price_after_discount'] * $row['quantity'];
    }
    $grand_total += $shipping;

    // ========== eSewa Payment ==========
    if ($payment_option == 'esewa') {

        // Save to session for success.php
        $_SESSION['name'] = $name;
        $_SESSION['location'] = $location;
        $_SESSION['contact'] = $contact;

        $transaction_id = date("Ymd-His") . "-$user_id-" . uniqid();
        $_SESSION['transaction_id'] = $transaction_id;

        // eSewa gateway signature
        $product_code = 'EPAYTEST';
        $secret_key = '8gBm/:&EnhH.1/q';
        $signed_field_names = "total_amount,transaction_uuid,product_code";
        $signature_data = "total_amount=$grand_total,transaction_uuid=$transaction_id,product_code=$product_code";
        $signature = base64_encode(hash_hmac('sha256', $signature_data, $secret_key, true));
        ?>

        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Confirm Payment - eSewa</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    max-width: 600px;
                    margin: 50px auto;
                    padding: 20px;
                }

                .payment-summary {
                    background: #f5f5f5;
                    padding: 20px;
                    border-radius: 5px;
                    margin-bottom: 20px;
                }

                .payment-summary p {
                    margin: 10px 0;
                }

                button {
                    background: #60bb46;
                    color: white;
                    padding: 12px 30px;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 16px;
                }

                button:hover {
                    background: #4a9636;
                }
            </style>
        </head>

        <body>
            <h2>Confirm Your Payment</h2>
            <div class="payment-summary">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($location); ?></p>
                <p><strong>Contact:</strong> <?php echo htmlspecialchars($contact); ?></p>
                <p><strong>Total Amount:</strong> NPR <?php echo number_format($grand_total, 2); ?></p>
                <p><strong>Payment Method:</strong> eSewa</p>
            </div>

            <form action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">
                <input type="hidden" name="amount" value="<?= $grand_total ?>" />
                <input type="hidden" name="tax_amount" value="0" />
                <input type="hidden" name="total_amount" value="<?= $grand_total ?>" />
                <input type="hidden" name="transaction_uuid" value="<?= $transaction_id ?>" />
                <input type="hidden" name="product_code" value="<?= $product_code ?>" />
                <input type="hidden" name="product_service_charge" value="0" />
                <input type="hidden" name="product_delivery_charge" value="0" />
                <input type="hidden" name="success_url" value="http://localhost/Jersey-E-Mart/users/success.php" />
                <input type="hidden" name="failure_url" value="http://localhost/Jersey-E-Mart/users/failure.php" />
                <input type="hidden" name="signed_field_names" value="<?= $signed_field_names ?>" />
                <input type="hidden" name="signature" value="<?= $signature ?>" />
                <button type="submit">Proceed to eSewa Payment</button>
            </form>
        </body>

        </html>
        <?php
        exit();
    }

    // ========== COD Payment ==========
    else {
        $order_sql = "INSERT INTO orders(user_id,name,location,grand_total,payment_option,payment_status,order_status)
                      VALUES($user_id,'$name','$location',$grand_total,'Cash on Delivery','Pending','Pending')";

        if (mysqli_query($conn, $order_sql)) {
            $order_id = mysqli_insert_id($conn);

            // Insert order items and update stock
            foreach ($items as $item) {
                $item_sql = "INSERT INTO order_items(order_id,product_id,pname,category,jersey_size,quality,base_price,print_name,print_number,print_cost,quantity,final_price,subtotal,shipping,product_image)
                            VALUES($order_id,{$item['pid']},'{$item['pname']}','{$item['category']}','{$item['size']}','{$item['quality']}',{$item['base_price']},'{$item['print_name']}','{$item['print_number']}',{$item['print_cost']},{$item['qty']},{$item['final_price']},{$item['subtotal']},$shipping,'{$item['image']}')";
                mysqli_query($conn, $item_sql);

                $stock_sql = "UPDATE product_sizes SET stock = stock - {$item['qty']} WHERE product_id = {$item['pid']} AND size = '{$item['size']}'";
                mysqli_query($conn, $stock_sql);
            }

            // Clear cart
            mysqli_query($conn, "DELETE FROM cart_items WHERE user_id = $user_id");

            // Redirect to thank you page
            header("Location: thankyou.php?order_id=$order_id");
            exit();
        } else {
            echo "Error processing order: " . mysqli_error($conn);
            exit();
        }
    }
}
?>