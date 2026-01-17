<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("../shared/dbconnect.php");
require_once("../shared/commonlinks.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

/* Get filter parameters */
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

/* Build WHERE clause */
$dateWhere = "WHERE o.order_date >= '$startDate 00:00:00' AND o.order_date <= '$endDate 23:59:59'";

/* Get all jersey sales with sizes */
    $query = "
        SELECT 
            p.id,
            p.j_name,
            p.category,
            p.image,
            GROUP_CONCAT(DISTINCT oi.jersey_size ORDER BY oi.jersey_size SEPARATOR ', ') AS sizes_sold,
            SUM(oi.quantity) AS total_quantity_sold
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.order_id
        $dateWhere
        GROUP BY oi.product_id, p.id, p.j_name, p.category, p.image
        ORDER BY total_quantity_sold DESC
    ";

$jerseyResults = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Jersey Sales Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../shared/jquery-3.7.1.min.js" rel="preload">
    <link rel="stylesheet" href="css/jersey_sales.css">
    <?php include "header.php"; ?>
</head>

<body>
    <div class="admin-content">
        <h3>📊 Jersey Sales</h3>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" style="margin: 0;">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
                    </div>
                    <div class="filter-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
                    </div>
                    <button type="submit" class="filter-btn filter-btn-primary">Filter</button>
                    <a href="jersey_sales.php" class="filter-btn filter-btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        <!-- Jersey Sales Table -->
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">Image</th>
                        <th>Jersey Name</th>
                        <th>Category</th>
                        <th>Sold Sizes</th>
                        <th>Quantity Sold</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($jerseyResults && $jerseyResults->num_rows > 0): ?>
                        <?php while ($row = $jerseyResults->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <img src="<?= '../shared/products/' . htmlspecialchars($row['image']) ?>" class="product-img" alt="">
                                </td>
                                <td><strong><?= htmlspecialchars($row['j_name']) ?></strong></td>
                                <td><?= htmlspecialchars($row['category']) ?></td>
                                <td><?= htmlspecialchars($row['sizes_sold']) ?></td>
                                <td><strong><?= intval($row['total_quantity_sold']) ?></strong></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                        <tr>
                            <td colspan="5">
                                <div class="no-data">No jersey sales data found for the selected period.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
