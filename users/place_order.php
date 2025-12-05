<?php
// place_order.php
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

// Basic validation
if ($name=="" || $location=="" || $contact=="" || ($payment_option != 'Cash on Delivery' && $payment_option != 'Online Payment')) {
    $_SESSION['alert'] = "Invalid order data.";
    $_SESSION['alert_type'] = "danger";
    header("Location: order_form.php");
    exit();
}

// Begin transaction
$conn->begin_transaction();

try {
    // insert order
    $payment_status = ($payment_option == 'Cash on Delivery') ? 'Pending' : 'Pending';
    $order_status = 'Pending';
    $stmt = $conn->prepare("INSERT INTO orders (user_id, name, location, grand_total, payment_option, payment_status, order_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississs", $user_id, $name, $location, $grand_total, $payment_option, $payment_status, $order_status);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // fetch cart items
    $stmt = $conn->prepare("SELECT product_id, pname, category, jersey_type, quality, base_price, print_name, print_number, print_cost, quantity, final_price, shipping, image FROM cart_items WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $insert_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, pname, category, jersey_type, quality, base_price, print_name, print_number, print_cost, quantity, final_price, subtotal, shipping, product_image) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

    while ($it = $res->fetch_assoc()) {
        $subtotal = (intval($it['final_price']) * intval($it['quantity'])) + intval($it['shipping']);
        $insert_item->bind_param(
            "iissssiiiidisss",
            $order_id,
            $it['product_id'],
            $it['pname'],
            $it['category'],
            $it['jersey_type'],
            $it['quality'],
            $it['base_price'],
            $it['print_name'],
            $it['print_number'],
            $it['print_cost'],
            $it['quantity'],
            $it['final_price'],
            $subtotal,
            $it['shipping'],
            $it['image']
        );
        $insert_item->execute();
    }
    $insert_item->close();
    $stmt->close();

    // clear cart
    $del = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $del->bind_param("i", $user_id);
    $del->execute();
    $del->close();

    $conn->commit();

    // if payment_option is Online Payment, you would redirect to payment gateway here.
    // For now we treat both as order placed and redirect to thankyou page.
    header("Location: thankyou.php?order_id=".$order_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['alert'] = "Order failed: " . $e->getMessage();
    $_SESSION['alert_type'] = "danger";
    header("Location: order_form.php");
    exit();
}
