<?php
session_start();
require_once("../shared/dbconnect.php");

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login again.']);
    exit;
}

$order_id = intval($_POST['order_id'] ?? 0);
if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid order id.']);
    exit;
}

// Optional: check if order is already delivered or paid
$stmt = $conn->prepare("SELECT order_status, payment_status FROM orders WHERE order_id=?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found.']);
    exit;
}

// Rule: prevent deleting ONLY when BOTH are complete
// Rule: prevent deleting if ANY of them is complete (Delivered or Paid)
if ($order['order_status'] === 'Delivered' || $order['payment_status'] === 'Completed') {
    if ($order['order_status'] === 'Delivered' && $order['payment_status'] === 'Completed') {
        echo json_encode(['success' => false, 'message' => 'Cannot delete: Order status is Delivered and payment status is Paid.']);
        exit;
    }

    if ($order['order_status'] === 'Delivered') {
        echo json_encode(['success' => false, 'message' => 'Cannot delete: Order status is Delivered.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Cannot delete: Payment status is Paid.']);
    exit;
}

// Delete order items first
$stmt = $conn->prepare("DELETE FROM order_items WHERE order_id=?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->close();

// Delete order
$stmt = $conn->prepare("DELETE FROM orders WHERE order_id=?");
$stmt->bind_param("i", $order_id);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
