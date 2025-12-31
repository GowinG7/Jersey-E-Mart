<?php
session_start();
require_once "../shared/dbconnect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: loginsignup/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cart.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = trim($_POST['name']);
$location = trim($_POST['location']);
$contact = trim($_POST['contact']);
$payment_option = $_POST['payment_option'];
$grand_total = intval($_POST['grand_total']);
$shipping_cost = 150; // flat shipping cost

// Basic validation
if (
    $name === "" || $location === "" || $contact === "" ||
    !in_array($payment_option, ['Cash on Delivery', 'Online Payment'])
) {
    $_SESSION['alert'] = "Invalid order data.";
    $_SESSION['alert_type'] = "danger";
    header("Location: order_form.php");
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Order default statuses
    $payment_status = 'Pending';
    $order_status = 'Pending';

    // Insert main order
    $stmt = $conn->prepare("
        INSERT INTO orders 
        (user_id, name, location, grand_total, payment_option, payment_status, order_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "ississs",
        $user_id,
        $name,
        $location,
        $grand_total,
        $payment_option,
        $payment_status,
        $order_status
    );
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Fetch cart items
    $stmt = $conn->prepare("
        SELECT product_id, pname, category, jersey_size, quality, base_price,
               print_name, print_number, print_cost,
               quantity, price_after_discount AS final_price, image
        FROM cart_items
        WHERE user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    // Prepare order_items insert
    $insert_item = $conn->prepare("
        INSERT INTO order_items 
        (order_id, product_id, pname, category, jersey_size, quality, base_price,
         print_name, print_number, print_cost, quantity, final_price, subtotal, shipping, product_image)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    // Prepare stock update
    $update_stock = $conn->prepare("
        UPDATE product_sizes
        SET stock = stock - ?
        WHERE product_id = ? AND size = ? AND stock >= ?
    ");

    while ($it = $res->fetch_assoc()) {
        $qty = (int) $it['quantity'];
        $unit_price = floatval($it['final_price']);
        $subtotal = $unit_price * $qty; // Only item subtotal
        $jersey_size = $it['jersey_size'];

        // Deduct stock once
        $update_stock->bind_param("iisi", $qty, $it['product_id'], $jersey_size, $qty);
        $update_stock->execute();

        if ($update_stock->affected_rows === 0) {
            throw new Exception("Insufficient stock for {$it['pname']} (Size: $jersey_size)");
        }

        // Insert order item
        $insert_item->bind_param(
            "iissssissiidiss",
            $order_id,
            $it['product_id'],
            $it['pname'],
            $it['category'],
            $jersey_size,
            $it['quality'],
            $it['base_price'],
            $it['print_name'],
            $it['print_number'],
            $it['print_cost'],
            $qty,
            $unit_price,
            $subtotal,
            $shipping_cost, // stored for reference per order item
            $it['image']
        );
        $insert_item->execute();
    }

    $insert_item->close();
    $update_stock->close();
    $stmt->close();

    // Clear cart
    $del = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $del->bind_param("i", $user_id);
    $del->execute();
    $del->close();

    $conn->commit();

    header("Location: thankyou.php?order_id=" . $order_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['alert'] = $e->getMessage();
    $_SESSION['alert_type'] = "danger";
    header("Location: order_form.php");
    exit();
}
?>