<?php
session_start();
require_once("../shared/dbconnect.php");

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo "Forbidden";
    exit;
}

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

$dateWhere = "WHERE order_date >= '" . $conn->real_escape_string($startDate) . " 00:00:00' AND order_date <= '" . $conn->real_escape_string($endDate) . " 23:59:59'";

$category = $_GET['category'] ?? '';
if ($category) {
    $cat = $conn->real_escape_string($category);
    $res = $conn->query("SELECT DISTINCT o.order_id, o.user_id, o.name, o.location, o.grand_total, o.payment_option, o.payment_status, o.order_status, o.transaction_id, DATE_FORMAT(o.order_date, '%Y-%m-%d %H:%i:%s') as order_date
                         FROM orders o
                         JOIN order_items oi ON oi.order_id=o.order_id
                         JOIN products p ON p.id=oi.product_id
                         $dateWhere AND p.category='$cat'
                         ORDER BY o.order_date DESC");
} else {
    $res = $conn->query("SELECT order_id, user_id, name, location, grand_total, payment_option, payment_status, order_status, transaction_id, DATE_FORMAT(order_date, '%Y-%m-%d %H:%i:%s') as order_date FROM orders $dateWhere ORDER BY order_date DESC");
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=orders_'. $startDate . '_to_' . $endDate .'.csv');

$out = fopen('php://output', 'w');
// Add UTF-8 BOM for Excel compatibility
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($out, ['Order ID','User ID','Name','Location','Grand Total','Payment Option','Payment Status','Order Status','Transaction ID','Order Date']);

if ($res) {
    while ($row = $res->fetch_assoc()) {
        fputcsv($out, [
            $row['order_id'],
            $row['user_id'],
            $row['name'],
            $row['location'],
            $row['grand_total'],
            $row['payment_option'],
            $row['payment_status'],
            $row['order_status'],
            $row['transaction_id'],
            $row['order_date'],
        ]);
    }
}

fclose($out);
exit;
?>
