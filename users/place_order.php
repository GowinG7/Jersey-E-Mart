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
$shipping_cost = 150;     // flat rate shipping

// Basic validation
if (
    $name == "" || $location == "" || $contact == "" ||
    ($payment_option != 'Cash on Delivery' && $payment_option != 'Online Payment')
) {

    $_SESSION['alert'] = "Invalid order data.";
    $_SESSION['alert_type'] = "danger";
    header("Location: order_form.php");
    exit();
}

// Begin DB transaction
$conn->begin_transaction();

try {

    // For now, both are Pending. For Online Payment, will update after eSewa.
    $payment_status = 'Pending';
    $order_status = 'Pending';

    // Insert into orders table
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

    // Fetch cart items (REAL columns)
    $stmt = $conn->prepare("
        SELECT product_id, pname, category, quality, base_price, print_name, print_number, 
               print_cost, quantity, price_after_discount AS final_price, image 
        FROM cart_items 
        WHERE user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    // Prepare insert into order_items
    $insert_item = $conn->prepare("
        INSERT INTO order_items 
        (order_id, product_id, pname, category, jersey_type, quality, base_price, print_name, 
         print_number, print_cost, quantity, final_price, subtotal, shipping, product_image) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    while ($it = $res->fetch_assoc()) {

        $qty = intval($it['quantity']);
        $unit_price = intval($it['final_price']);

        // subtotal = unit price * quantity + flat shipping
        $subtotal = ($unit_price * $qty) + $shipping_cost;

        // jersey_type does not exist → send NULL
        $jersey_type = NULL;

        $insert_item->bind_param(
            "iissssissiidiss",
            $order_id,
            $it['product_id'],
            $it['pname'],
            $it['category'],
            $jersey_type,
            $it['quality'],
            $it['base_price'],
            $it['print_name'],
            $it['print_number'],
            $it['print_cost'],
            $it['quantity'],
            $unit_price,
            $subtotal,
            $shipping_cost,
            $it['image']
        );

        $insert_item->execute();
    }

    $insert_item->close();
    $stmt->close();

    // Clear cart after success
    $del = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $del->bind_param("i", $user_id);
    $del->execute();
    $del->close();

    $conn->commit();

    // For online payment: After future eSewa verification, update payment_status + store transaction_id
    header("Location: thankyou.php?order_id=" . $order_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['alert'] = "Order failed: " . $e->getMessage();
    $_SESSION['alert_type'] = "danger";
    header("Location: order_form.php");
    exit();
}
?>