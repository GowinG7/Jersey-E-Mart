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

// AJAX: Update stock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $size_id = (int) $_POST['size_id'];
    $new_stock = (int) $_POST['new_stock'];

    $stmt = $conn->prepare("UPDATE product_sizes SET stock = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_stock, $size_id);
    $stmt->execute();
    $stmt->close();

    header("Location: stock_alerts.php");
    exit;
}

// Fetch out of stock items
$outOfStock = $conn->query("
    SELECT 
        p.id, 
        p.j_name,
        ps.id as size_id,
        ps.size,
        ps.stock
    FROM product_sizes ps
    JOIN products p ON ps.product_id = p.id
    WHERE ps.stock = 0
    ORDER BY p.j_name, ps.size
");

// Fetch low stock items
$lowStock = $conn->query("
    SELECT 
        p.id, 
        p.j_name,
        ps.id as size_id,
        ps.size,
        ps.stock
    FROM product_sizes ps
    JOIN products p ON ps.product_id = p.id
    WHERE ps.stock > 0 AND ps.stock <= 5
    ORDER BY ps.stock ASC, p.j_name, ps.size
");

$outOfStockCount = $outOfStock->num_rows ?? 0;
$lowStockCount = $lowStock->num_rows ?? 0;
$outOfStock->data_seek(0);
$lowStock->data_seek(0);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Alerts - Admin Panel</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .stock-page-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

        .stock-back-link {
            color: #164a44;
            text-decoration: none;
            margin-bottom: 15px;
            display: inline-block;
            font-weight: 500;
        }

        .stock-back-link:hover {
            text-decoration: underline;
        }

        .stock-summary {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .stock-summary-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            text-align: center;
            flex: 1;
            min-width: 150px;
        }

        .stock-summary-number {
            font-size: 32px;
            font-weight: bold;
            color: #dc3545;
        }

        .stock-summary-box.low-stock .stock-summary-number {
            color: #ffc107;
        }

        .stock-summary-label {
            font-size: 13px;
            color: #666;
            margin-top: 8px;
        }

        .stock-section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .stock-section h3 {
            margin-top: 0;
            margin-bottom: 15px;
            border-bottom: 3px solid #dc3545;
            padding-bottom: 10px;
            color: #333;
            font-size: 18px;
        }

        .stock-section.low-stock h3 {
            border-bottom-color: #ffc107;
        }

        .stock-table {
            width: 100%;
            border-collapse: collapse;
        }

        .stock-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #ddd;
            font-weight: 600;
            font-size: 14px;
            color: #555;
        }

        .stock-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .stock-table tr:hover {
            background: #f9f9f9;
        }

        .stock-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
        }

        .stock-badge-out {
            background: #dc3545;
            color: white;
        }

        .stock-badge-low {
            background: #ffc107;
            color: #333;
        }

        .stock-empty {
            color: #999;
            padding: 30px;
            text-align: center;
            font-size: 14px;
        }

        .stock-update-form {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .stock-update-form input {
            width: 80px;
            padding: 6px 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .stock-update-form button {
            padding: 6px 14px;
            background: #164a44;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
        }

        .stock-update-form button:hover {
            background: #0f3631;
        }
    </style>
</head>

<body>
    <?php include "header.php"; ?>

    <div class="admin-content">

        <div class="stock-page-title">📦 Stock Alerts</div>

        <div class="stock-summary">
            <div class="stock-summary-box">
                <div class="stock-summary-number"><?= $outOfStockCount ?></div>
                <div class="stock-summary-label">Out of Stock</div>
            </div>
            <div class="stock-summary-box low-stock">
                <div class="stock-summary-number"><?= $lowStockCount ?></div>
                <div class="stock-summary-label">Low Stock (≤ 5)</div>
            </div>
        </div>

        <div class="stock-section">
            <h3>🔴 Out of Stock Items</h3>
            <?php if ($outOfStockCount > 0): ?>
                <table class="stock-table">
                    <thead>
                        <tr>
                            <th>Jersey Name</th>
                            <th>Size</th>
                            <th>Stock</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $outOfStock->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['j_name']) ?></td>
                                <td><?= htmlspecialchars($item['size']) ?></td>
                                <td><span class="stock-badge stock-badge-out">0</span></td>
                                <td>
                                    <form method="POST" class="stock-update-form">
                                        <input type="hidden" name="size_id" value="<?= $item['size_id'] ?>">
                                        <input type="hidden" name="update_stock" value="1">
                                        <input type="number" name="new_stock" placeholder="Qty" min="0" required>
                                        <button type="submit">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="stock-empty">✓ No out of stock items</div>
            <?php endif; ?>
        </div>

        <div class="stock-section low-stock">
            <h3>🟡 Low Stock Items (≤ 5 units)</h3>
            <?php if ($lowStockCount > 0): ?>
                <table class="stock-table">
                    <thead>
                        <tr>
                            <th>Jersey Name</th>
                            <th>Size</th>
                            <th>Stock</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $lowStock->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['j_name']) ?></td>
                                <td><?= htmlspecialchars($item['size']) ?></td>
                                <td><span class="stock-badge stock-badge-low"><?= $item['stock'] ?></span></td>
                                <td>
                                    <form method="POST" class="stock-update-form">
                                        <input type="hidden" name="size_id" value="<?= $item['size_id'] ?>">
                                        <input type="hidden" name="update_stock" value="1">
                                        <input type="number" name="new_stock" placeholder="Qty" min="0" required>
                                        <button type="submit">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="stock-empty">✓ All items have sufficient stock</div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>