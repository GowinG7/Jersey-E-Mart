<?php
session_start();
require_once("../shared/dbconnect.php");
require_once("../shared/commonlinks.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 5;
$offset = ($page - 1) * $perPage;

// Helper to append date params to links
function dateQuery() {
    $q = [];
    if (!empty($_GET['start_date'])) $q['start_date'] = $_GET['start_date'];
    if (!empty($_GET['end_date'])) $q['end_date'] = $_GET['end_date'];
    return http_build_query($q);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Users Orders Summary | Admin</title>
    <style>
        .admin-content { margin-top: 0 !important; border-top: 4px solid black; background-color: #f9f9f9; padding: 15px; }
        .table-header { background: #00796b; color: #fff; }
        .summary-box { margin-bottom: 12px; }
        .pagination a { margin: 0 4px; }
    </style>
</head>
<body>

<?php include_once "header.php"; ?>

<div class="admin-content">
    <h3 class="mb-3"><?php echo $user_id ? 'Orders for user #' . $user_id : 'Users Order Summary'; ?></h3>

    <form method="get" class="search-form">
        <label>Start Date: <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>"></label>
        <label>End Date: <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>"></label>
        <?php if ($user_id): ?>
            <input type="hidden" name="user_id" value="<?= $user_id ?>">
        <?php endif; ?>
        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        <a href="user_orders.php" class="btn btn-sm btn-secondary">Reset</a>
        <a href="orders.php" class="btn btn-sm btn-dark">Back to All Orders</a>
    </form>

<?php
if ($user_id <= 0) {
    // Users summary grouped by user
    $params = [];
    $types = '';

    $sql = "SELECT u.id AS user_id, u.name, COUNT(o.order_id) AS order_count, COALESCE(SUM(o.grand_total),0) AS total_amount, MAX(o.order_date) AS last_order_date
            FROM user_creden u
            JOIN orders o ON u.id = o.user_id";

    $where = [];
    if ($startDate) {
        $where[] = "o.order_date >= ?";
        $params[] = $startDate . ' 00:00:00';
        $types .= 's';
    }
    if ($endDate) {
        $where[] = "o.order_date <= ?";
        $params[] = $endDate . ' 23:59:59';
        $types .= 's';
    }

    if (!empty($where)) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }

    $sql .= " GROUP BY u.id ORDER BY total_amount DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $bind_params = array_merge([$types], $params);
        $refs = [];
        foreach ($bind_params as $k => $v) {
            $refs[$k] = & $bind_params[$k];
        }
        call_user_func_array([$stmt, 'bind_param'], $refs);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    ?>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Total Orders</th>
                <th>Total Amount</th>
                <th>Last Order Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($res->num_rows === 0): ?>
                <tr><td colspan="6" class="text-center text-muted">No records found for selected dates</td></tr>
            <?php else: ?>
                <?php while ($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['user_id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= $row['order_count'] ?></td>
                        <td>Rs. <?= number_format($row['total_amount']) ?></td>
                        <td><?= $row['last_order_date'] ?: '-' ?></td>
                        <td><a href="user_orders.php?user_id=<?= $row['user_id'] ?>&<?= dateQuery() ?>" class="btn btn-sm btn-primary">View Orders</a></td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>

<?php
    $stmt->close();
} else {
    // Specific user's paginated orders with summary
    $params = [$user_id];
    $types = 'i';
    $where = "WHERE o.user_id = ?";

    if ($startDate) {
        $where .= " AND o.order_date >= ?";
        $params[] = $startDate . ' 00:00:00';
        $types .= 's';
    }
    if ($endDate) {
        $where .= " AND o.order_date <= ?";
        $params[] = $endDate . ' 23:59:59';
        $types .= 's';
    }

    // Summary (count and sum)
    $summarySql = "SELECT COUNT(*) AS cnt, COALESCE(SUM(o.grand_total),0) AS total_amount FROM orders o $where";
    $stmt = $conn->prepare($summarySql);
    if (!empty($params)) {
        $bind_params = array_merge([$types], $params);
        $refs = [];
        foreach ($bind_params as $k => $v) {
            $refs[$k] = & $bind_params[$k];
        }
        call_user_func_array([$stmt, 'bind_param'], $refs);
    }
    $stmt->execute();
    $summary = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $totalOrders = (int)$summary['cnt'];
    $totalAmount = $summary['total_amount'];

    // Pagination total count
    $totalPages = max(1, (int)ceil($totalOrders / $perPage));
    if ($page > $totalPages) $page = $totalPages;
    $offset = ($page - 1) * $perPage;

    // Orders list
    $listSql = "SELECT o.order_id, o.name, o.location, o.grand_total, o.payment_option, o.payment_status, o.order_status, o.transaction_id, o.order_date, COUNT(oi.item_id) AS total_items
                FROM orders o
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                $where
                GROUP BY o.order_id
                ORDER BY o.order_date DESC
                LIMIT ?, ?";

    $stmt = $conn->prepare($listSql);
    // bind existing params + offset + perPage
    $bindTypes = $types . 'ii';
    $bind_params = array_merge([$bindTypes], $params, [$offset, $perPage]);
    $refs = [];
    foreach ($bind_params as $k => $v) {
        $refs[$k] = & $bind_params[$k];
    }
    call_user_func_array([$stmt, 'bind_param'], $refs);
    $stmt->execute();
    $res = $stmt->get_result();
    ?>

    <div class="summary-box">
        <strong>Total orders:</strong> <?= $totalOrders ?> &nbsp; | &nbsp; <strong>Total amount:</strong> Rs. <?= number_format($totalAmount) ?>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Order ID</th>
                <th>Items</th>
                <th>Grand Total</th>
                <th>Payment</th>
                <th>Payment Status</th>
                <th>Order Status</th>
                <th>Transaction ID</th>
                <th>Date</th>
                <th>Invoice</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($res->num_rows === 0): ?>
                <tr><td colspan="9" class="text-center text-muted">No orders found for this user and date range</td></tr>
            <?php else: ?>
                <?php while ($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['order_id'] ?></td>
                        <td><?= $row['total_items'] ?></td>
                        <td>Rs. <?= number_format($row['grand_total']) ?></td>
                        <td><?= htmlspecialchars($row['payment_option']) ?></td>
                        <td><?= htmlspecialchars($row['payment_status']) ?></td>
                        <td><?= htmlspecialchars($row['order_status']) ?></td>
                        <td><?= $row['transaction_id'] ?: '-' ?></td>
                        <td><?= $row['order_date'] ?></td>
                        <td><a href="download_invoice.php?order_id=<?= $row['order_id'] ?>" target="_blank" class="btn btn-sm btn-dark">Invoice</a></td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php
        $base = 'user_orders.php?user_id=' . $user_id . '&' . dateQuery();
        if ($page > 1): ?>
            <a href="<?= $base ?>&page=<?= $page - 1 ?>" class="btn btn-sm btn-outline-primary">&laquo; Prev</a>
        <?php endif; ?>
        Page <?= $page ?> of <?= $totalPages ?>
        <?php if ($page < $totalPages): ?>
            <a href="<?= $base ?>&page=<?= $page + 1 ?>" class="btn btn-sm btn-outline-primary">Next &raquo;</a>
        <?php endif; ?>
    </div>

<?php
    $stmt->close();
}

$conn->close();
?>

</div>

</body>
</html>