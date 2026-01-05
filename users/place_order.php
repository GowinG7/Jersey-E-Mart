<?php
session_start();
require_once "../shared/dbconnect.php";

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: displaycart.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = trim($_POST['name']);
$location = trim($_POST['location']);
$contact = trim($_POST['contact']);
$payment_option = $_POST['payment_option'];
$grand_total = intval($_POST['grand_total']);
$shipping_cost = 150;

// Validate input
if ($name === "" || $location === "" || $contact === "" || !in_array($payment_option, ['Cash on Delivery', 'Online Payment'])) {
    $_SESSION['alert'] = "Invalid order data.";
    $_SESSION['alert_type'] = "danger";
    header("Location: order_form.php");
    exit();
}

// Get cart items
$stmt = $conn->prepare("
    SELECT product_id, pname, category, jersey_size, quality, base_price, 
           print_name, print_number, print_cost, quantity, price_after_discount AS final_price, image 
    FROM cart_items 
    WHERE user_id=?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($cart_items)) {
    $_SESSION['alert'] = "Your cart is empty.";
    $_SESSION['alert_type'] = "danger";
    header("Location: displaycart.php");
    exit();
}

// Check stock
foreach ($cart_items as $item) {
    $stmt = $conn->prepare("SELECT stock FROM product_sizes WHERE product_id=? AND size=?");
    $stmt->bind_param("is", $item['product_id'], $item['jersey_size']);
    $stmt->execute();
    $stmt->bind_result($stock);
    $stmt->fetch();
    $stmt->close();

    if ($stock < $item['quantity']) {
        $_SESSION['alert'] = "Insufficient stock for {$item['pname']} (Size: {$item['jersey_size']})";
        $_SESSION['alert_type'] = "danger";
        header("Location: displaycart.php");
        exit();
    }
}

// Begin transaction
$conn->begin_transaction();

try {
    // Insert order
    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, name, location, grand_total, payment_option, payment_status, order_status) 
        VALUES (?, ?, ?, ?, ?, 'Pending', 'Pending')
    ");
    $stmt->bind_param("issis", $user_id, $name, $location, $grand_total, $payment_option);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Prepare order_items insert and stock update
    $insert_item = $conn->prepare("
        INSERT INTO order_items 
        (order_id, product_id, pname, category, jersey_size, quality, base_price, print_name, print_number, print_cost, quantity, final_price, subtotal, shipping, product_image) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $update_stock = $conn->prepare("
        UPDATE product_sizes SET stock = stock - ? 
        WHERE product_id=? AND size=? AND stock >= ?
    ");

    foreach ($cart_items as $item) {
        $qty = $item['quantity'];
        $subtotal = $item['final_price'] * $qty;

        // Insert into order_items (include shipping)
  $insert_item->bind_param(
    "iissssisiiiiiis",
    $order_id,
    $item['product_id'],
    $item['pname'],
    $item['category'],
    $item['jersey_size'],
    $item['quality'],
    $item['base_price'],
    $item['print_name'],
    $item['print_number'],
    $item['print_cost'],
    $qty,
    $item['final_price'],
    $subtotal,
    $shipping_cost,
    $item['image']
);

        $insert_item->execute();
        if ($insert_item->affected_rows === 0) {
            throw new Exception("Failed to insert item {$item['pname']}");
        }

        // Reduce stock
        $update_stock->bind_param("iisi", $qty, $item['product_id'], $item['jersey_size'], $qty);
        $update_stock->execute();
        if ($update_stock->affected_rows === 0) {
            throw new Exception("Stock update failed for {$item['pname']} (Size: {$item['jersey_size']})");
        }
    }

    $insert_item->close();
    $update_stock->close();

    // Clear cart
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    header("Location: thankyou.php?order_id=" . $order_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['alert'] = "Order failed: " . $e->getMessage();
    $_SESSION['alert_type'] = "danger";
    header("Location: displaycart.php");
    exit();
}
?>