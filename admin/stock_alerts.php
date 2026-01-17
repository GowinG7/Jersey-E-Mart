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
    <link rel="stylesheet" href="css/stock_alerts.css">
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