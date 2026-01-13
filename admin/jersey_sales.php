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
    <?php include "header.php"; ?>
    <style>
        /* Keep global font-family but avoid changing header/sidebar sizes */
        * {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        /* Scope page font sizing to content only */
        .admin-content,
        .admin-content * {
            font-size: 14px;
        }
        
        .admin-content {
            padding: 20px;
            background: #e5f0eb;
            margin-top: var(--admin-topbar-height);
            min-height: calc(100vh - var(--admin-topbar-height));
        }

        @media (min-width: 992px) {
            .admin-content {
                margin-left: var(--admin-sidebar-width);
            }
        }

        h3 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .filter-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 150px;
        }

        .filter-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 14px;
            color: #333;
        }

        .filter-group input {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        /* Scope buttons to this page to avoid affecting navbar hamburger */
        .filter-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: 0.3s;
        }

        .filter-btn-primary {
            background: #007bff;
            color: white;
        }

        .filter-btn-primary:hover {
            background: #0056b3;
        }

        .filter-btn-secondary {
            background: #6c757d;
            color: white;
        }

        .filter-btn-secondary:hover {
            background: #545b62;
        }

        .table-responsive {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        thead {
            background: #f8f9fa;
            border-bottom: 2px solid #ddd;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .product-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .product-img {
            width: 40px;
            height: 40px;
            object-fit: contain;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 2px;
        }

        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="admin-content">
        <h3>📊 Jersey Sales Report</h3>

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
