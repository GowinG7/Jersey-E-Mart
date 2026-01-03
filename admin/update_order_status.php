<?php
session_start();
require_once("../shared/dbconnect.php");

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$order_id = intval($_POST['order_id'] ?? 0);
$column = $_POST['column'] ?? '';
$value = $_POST['value'] ?? '';

// Only allow these columns and values
$validColumns = ['order_status', 'payment_status'];
$validValues = [
    'order_status' => ['Pending', 'Delivered'],
    'payment_status' => ['Pending', 'Completed']
];

if (!$order_id || !in_array($column, $validColumns) || !in_array($value, $validValues[$column])) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $conn->prepare("UPDATE orders SET $column = ? WHERE order_id = ?");
$stmt->bind_param("si", $value, $order_id);
$success = $stmt->execute();
$stmt->close();

echo json_encode(['success' => $success, 'value' => $value]);
