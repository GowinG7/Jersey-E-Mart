<?php
// thankyou.php
session_start();
require_once "../shared/dbconnect.php";
include_once "../shared/commonlinks.php";
include "header.php";

if (!isset($_GET['order_id'])) {
    header("Location: index.php");
    exit();
}
$order_id = intval($_GET['order_id']);

// fetch order
$stmt = $conn->prepare("SELECT order_id, name, location, grand_total, payment_option, payment_status, order_date FROM orders WHERE order_id = ? LIMIT 1");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$res = $stmt->get_result();
$order = $res->fetch_assoc();
$stmt->close();

if (!$order) {
    echo "Order not found.";
    exit();
}

// fetch items
$stmt = $conn->prepare("SELECT product_id, pname, quantity, final_price, subtotal FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$res = $stmt->get_result();
$items = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html>
<head><title>Thank you</title></head>
<body style="background:#f8f9fa;">
<div class="container py-5">
  <div class="card p-4">
    <h3>Thank you, <?php echo htmlspecialchars($order['name']); ?>!</h3>
    <p>Your order <strong>#<?php echo $order['order_id']; ?></strong> has been placed on <?php echo $order['order_date']; ?>.</p>
    <p>Payment Method: <?php echo htmlspecialchars($order['payment_option']); ?> | Payment Status: <?php echo htmlspecialchars($order['payment_status']); ?></p>

    <h5 class="mt-3">Order Summary</h5>
    <ul>
      <?php foreach ($items as $it): ?>
        <li><?php echo htmlspecialchars($it['pname']); ?> — Qty: <?php echo intval($it['quantity']); ?> — Rs <?php echo number_format($it['final_price']); ?> — Subtotal: Rs <?php echo number_format($it['subtotal']); ?></li>
      <?php endforeach; ?>
    </ul>

    <h5>Grand Total: Rs <?php echo number_format($order['grand_total']); ?></h5>

    <a href="index.php" class="btn btn-primary mt-3">Continue Shopping</a>
    <a href="orders.php?order_id=<?php echo $order_id; ?>" class="btn btn-outline-secondary mt-3">View Order Details</a>
  </div>
</div>
</body>
</html>
