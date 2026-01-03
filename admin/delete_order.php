<?php
session_start();
require_once("../shared/dbconnect.php");

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$order_id = intval($_POST['order_id'] ?? 0);
if (!$order_id) {
    echo json_encode(['success' => false]);
    exit;
}

// Optional: check if order is already delivered or paid
$stmt = $conn->prepare("SELECT order_status, payment_status FROM orders WHERE order_id=?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo json_encode(['success' => false]);
    exit;
}

// Example rule: prevent deleting completed/pending shipped orders
if ($order['order_status'] === 'Delivered' || $order['payment_status'] === 'Completed') {
    echo json_encode(['success' => false]);
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

echo json_encode(['success' => $success]);
