<?php
session_start();
require_once "../shared/dbconnect.php";

// Check session (remove contact check)
if (!isset($_SESSION['user_id'], $_SESSION['name'], $_SESSION['location'])) {
    echo "Session expired. Please try again.";
    exit();
}

// Get eSewa response
if (!isset($_REQUEST['data'])) {
    echo "No payment data received.";
    exit();
}

$response = json_decode(base64_decode($_REQUEST['data']), true);

$transaction_code = $response['transaction_code'];
$status = $response['status'];
$total_amount = $response['total_amount'];
$transaction_uuid = $response['transaction_uuid'];
$product_code = $response['product_code'];
$signed_field_names = $response['signed_field_names'];
$provided_signature = $response['signature'];

// Verify signature
$secret_key = '8gBm/:&EnhH.1/q';
$message = "transaction_code={$transaction_code},status={$status},total_amount={$total_amount},transaction_uuid={$transaction_uuid},product_code={$product_code},signed_field_names={$signed_field_names}";
$expected_signature = base64_encode(hash_hmac('sha256', $message, $secret_key, true));

if ($expected_signature === $provided_signature && $status === "COMPLETE") {
    $user_id = $_SESSION['user_id'];
    $name = mysqli_real_escape_string($conn, $_SESSION['name']);
    $location = mysqli_real_escape_string($conn, $_SESSION['location']);
    $payment_option = "Esewa";
    $shipping = 150;

    // Fetch cart items
    $cart_query = mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id = $user_id");
    if (mysqli_num_rows($cart_query) == 0) {
        echo "Your cart is empty.";
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

    // Verify amount matches eSewa payment
    if ($total_amount != $grand_total) {
        echo "Payment amount mismatch! Expected: NPR $grand_total, Received: NPR $total_amount";
        exit();
    }

    // Insert order with payment_status = 'Completed' 
    $insert_order = mysqli_query($conn, "INSERT INTO orders 
        (user_id, name, location, grand_total, payment_option, payment_status, order_status, transaction_id) 
        VALUES 
        ($user_id, '$name', '$location', $grand_total, '$payment_option', 'Completed', 'Pending', '$transaction_code')");

    if ($insert_order) {
        $order_id = mysqli_insert_id($conn);

        // Insert order items and update stock
        foreach ($items as $item) {
            $item_sql = "INSERT INTO order_items(order_id, product_id, pname, category, jersey_size, quality, base_price, print_name, print_number, print_cost, quantity, final_price, subtotal, shipping, product_image)
                        VALUES($order_id, {$item['pid']}, '{$item['pname']}', '{$item['category']}', '{$item['size']}', '{$item['quality']}', {$item['base_price']}, '{$item['print_name']}', '{$item['print_number']}', {$item['print_cost']}, {$item['qty']}, {$item['final_price']}, {$item['subtotal']}, $shipping, '{$item['image']}')";
            mysqli_query($conn, $item_sql);

            // Update stock
            $stock_sql = "UPDATE product_sizes SET stock = stock - {$item['qty']} WHERE product_id = {$item['pid']} AND size = '{$item['size']}'";
            mysqli_query($conn, $stock_sql);
        }

        // Clear cart
        mysqli_query($conn, "DELETE FROM cart_items WHERE user_id = $user_id");
        
        // Clear session
        unset($_SESSION['name'], $_SESSION['location'], $_SESSION['transaction_id']);
        
        // Redirect to thank you page
        header("Location: thankyou.php?order_id=$order_id");
        exit();
    } else {
        echo "Failed to place order after payment: " . mysqli_error($conn);
    }
} else {
    echo "Invalid payment signature or incomplete payment.";
}
?>