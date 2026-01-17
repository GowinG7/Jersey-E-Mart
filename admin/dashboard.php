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
$category = $_GET['category'] ?? '';

/* Base WHERE clause for all queries */
$dateWhere = "WHERE order_date >= '$startDate 00:00:00' AND order_date <= '$endDate 23:59:59'";
// For alias-safe filtering on orders
$orderDateWhere = "WHERE o.order_date >= '$startDate 00:00:00' AND o.order_date <= '$endDate 23:59:59'";
$categoryWhere = $category ? "AND p.category = '" . $conn->real_escape_string($category) . "'" : '';
// Category-aware JOIN parts for order-scoped queries
$catJoin = $category ? "JOIN order_items oi ON oi.order_id = o.order_id JOIN products p ON oi.product_id = p.id" : "";
$catWhereOrders = $category ? "AND p.category = '" . $conn->real_escape_string($category) . "'" : '';

/*  OVERALL SUMMARY WITH FILTERS  */
$totalOrders = $conn->query("SELECT COUNT(DISTINCT o.order_id) t FROM orders o $catJoin $orderDateWhere $catWhereOrders")->fetch_assoc()['t'];
$totalUsers = $conn->query("SELECT COUNT(DISTINCT o.user_id) t FROM orders o $catJoin $orderDateWhere $catWhereOrders")->fetch_assoc()['t'];

$paidData = $category
    ? $conn->query("SELECT COUNT(DISTINCT o.order_id) c, SUM(oi.subtotal) t FROM orders o JOIN order_items oi ON oi.order_id=o.order_id JOIN products p ON oi.product_id=p.id $orderDateWhere AND o.payment_status='Completed' AND p.category='" . $conn->real_escape_string($category) . "'")->fetch_assoc()
    : $conn->query("SELECT COUNT(*) c, SUM(grand_total) t FROM orders $dateWhere AND payment_status='Completed'")->fetch_assoc();
$paidOrders = $paidData['c'];
$totalPaidSales = $paidData['t'] ?? 0;

$unpaidData = $category
    ? $conn->query("SELECT COUNT(DISTINCT o.order_id) c, SUM(oi.subtotal) t FROM orders o JOIN order_items oi ON oi.order_id=o.order_id JOIN products p ON oi.product_id=p.id $orderDateWhere AND o.payment_status='Pending' AND p.category='" . $conn->real_escape_string($category) . "'")->fetch_assoc()
    : $conn->query("SELECT COUNT(*) c, SUM(grand_total) t FROM orders $dateWhere AND payment_status='Pending'")->fetch_assoc();
$unpaidOrders = $unpaidData['c'];
$unpaidAmount = $unpaidData['t'] ?? 0;

$deliveredOrders = $conn->query("SELECT COUNT(DISTINCT o.order_id) t FROM orders o $catJoin $orderDateWhere $catWhereOrders AND o.order_status='Delivered'")->fetch_assoc()['t'];
$pendingOrders = $conn->query("SELECT COUNT(DISTINCT o.order_id) t FROM orders o $catJoin $orderDateWhere $catWhereOrders AND o.order_status='Pending'")->fetch_assoc()['t'];

$codOrders = $conn->query("SELECT COUNT(DISTINCT o.order_id) t FROM orders o $catJoin $orderDateWhere $catWhereOrders AND o.payment_option='Cash on Delivery'")->fetch_assoc()['t'];
$onlineOrders = $conn->query("SELECT COUNT(DISTINCT o.order_id) t FROM orders o $catJoin $orderDateWhere $catWhereOrders AND o.payment_option='Esewa'")->fetch_assoc()['t'];

$avgOrderValue = $totalOrders > 0 ? ($totalPaidSales + $unpaidAmount) / $totalOrders : 0;

/* Additional KPIs */
// Total items across filtered orders
$itemsRes = $conn->query(
    "SELECT SUM(oi.quantity) AS item_count
     FROM order_items oi
     JOIN orders o ON oi.order_id = o.order_id
     " . str_replace('WHERE', 'WHERE', $orderDateWhere) .
     ($category ? " AND EXISTS (SELECT 1 FROM products p2 WHERE p2.id = oi.product_id AND p2.category='" . $conn->real_escape_string($category) . "')" : "")
);
$totalItems = ($itemsRes && ($row = $itemsRes->fetch_assoc())) ? intval($row['item_count']) : 0;

$avgItemsPerOrder = $totalOrders > 0 ? round($totalItems / $totalOrders, 2) : 0;
$conversionRate = $totalOrders > 0 ? round(($paidOrders / $totalOrders) * 100, 2) : 0;

/* STOCK ALERTS - Check for out of stock and low stock items */
$outOfStockCount = $conn->query("SELECT COUNT(*) as count FROM product_sizes WHERE stock = 0")->fetch_assoc()['count'];
$lowStockCount = $conn->query("SELECT COUNT(*) as count FROM product_sizes WHERE stock > 0 AND stock <= 5")->fetch_assoc()['count'];
$totalStockIssues = $outOfStockCount + $lowStockCount;

/* TOP PRODUCTS */
$topProducts = $conn->query(
    "SELECT p.j_name, p.image, SUM(oi.quantity) total_qty, SUM(oi.quantity * p.price) total_revenue
     FROM order_items oi
     JOIN products p ON oi.product_id = p.id
     JOIN orders o ON oi.order_id = o.order_id
     $dateWhere $categoryWhere
     GROUP BY oi.product_id
     ORDER BY total_qty DESC
     LIMIT 5"
);

/* Get categories for filter */
$categories = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");

/*  ORDER STATUS DATA  */
$orderStatusLabels = [];
$orderStatusData = [];
$res = $conn->query("SELECT o.order_status, COUNT(DISTINCT o.order_id) t FROM orders o $catJoin $orderDateWhere $catWhereOrders GROUP BY o.order_status");
while ($r = $res->fetch_assoc()) {
    $orderStatusLabels[] = $r['order_status'];
    $orderStatusData[] = $r['t'];
}

/*  PAYMENT STATUS DATA  */
$paymentStatusLabels = ['Completed', 'Pending'];
$paymentStatusData = [$paidOrders, $unpaidOrders];

/*  PAYMENT METHOD DATA  */
$paymentMethodLabels = ['Cash on Delivery', 'Esewa'];
$paymentMethodData = [$codOrders, $onlineOrders];

/*  CATEGORY-WISE SUMMARY  */
$categoryLabels = [];
$categoryRevenue = [];
$categoryOrders = [];
$categoryQuantity = [];

// Predefined categories
$predefCats = ['Football', 'Cricket', 'NPL cricket', 'NSL football'];
$categoryDisplayMap = [
    'Football' => 'National team Football',
    'Cricket' => 'National team Cricket',
    'NPL cricket' => 'NPL',
    'NSL football' => 'NSL'
];

// Build data array with category names as keys
$categoryData = [];
foreach ($predefCats as $c) {
    $categoryData[$c] = [
        'revenue' => 0,
        'orders' => 0,
        'quantity' => 0
    ];
}

// Query database for actual data
$catRes = $conn->query(
    "SELECT p.category,
            COUNT(DISTINCT o.order_id) order_count,
            SUM(oi.quantity) total_qty,
            SUM(oi.subtotal) total_revenue
     FROM order_items oi
     JOIN products p ON oi.product_id = p.id
     JOIN orders o ON oi.order_id = o.order_id
     $dateWhere" . ($category ? " AND p.category='" . $conn->real_escape_string($category) . "'" : "") . "
     GROUP BY p.category"
);

while ($r = $catRes->fetch_assoc()) {
    if (isset($categoryData[$r['category']])) {
        $categoryData[$r['category']] = [
            'revenue' => floatval($r['total_revenue']),
            'orders' => intval($r['order_count']),
            'quantity' => intval($r['total_qty'])
        ];
    }
}

// Build label and data arrays with all 4 categories in order
foreach ($predefCats as $cat) {
    $displayName = $categoryDisplayMap[$cat];
    $categoryLabels[] = $displayName;
    $categoryRevenue[] = $categoryData[$cat]['revenue'];
    $categoryOrders[] = $categoryData[$cat]['orders'];
    $categoryQuantity[] = $categoryData[$cat]['quantity'];
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>

    <?php include "header.php"; ?>

    <div class="admin-content">

        <div class="dashboard-title">📊 Dashboard Overview</div>

        <!-- STOCK ALERT BANNER -->
        <?php if ($totalStockIssues > 0): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert" style="border-left: 5px solid #ff6b6b;">
            <strong>⚠️ Stock Alert!</strong> 
            You have <strong><?php echo $totalStockIssues; ?></strong> items with stock issues 
            (<strong><?php echo $outOfStockCount; ?></strong> out of stock, 
            <strong><?php echo $lowStockCount; ?></strong> low stock).
            <a href="stock_alerts.php" class="btn btn-warning btn-sm ms-2">View & Manage Stock</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- FILTER SECTION -->
        <div class="filter-section">
            <h6 class="mb-3">Filter Analytics</h6>
            <form method="get" class="d-flex flex-wrap filter-actions align-items-center">
                <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" required>
                <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" required>
                <select name="category">
                    <option value="">All Categories</option>
                    <?php
                        $predefCategories = ['Football', 'Cricket', 'NPL cricket', 'NSL football'];
                        // Render predefined categories first
                        foreach ($predefCategories as $c) {
                            $sel = ($category === $c) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($c) . '" ' . $sel . '>' . htmlspecialchars($c) . '</option>';
                        }
                        // Build a lowercase map to avoid duplicates from DB
                        $seen = array_change_key_case(array_flip($predefCategories), CASE_LOWER);
                        while ($cat = $categories->fetch_assoc()) {
                            $c = $cat['category'];
                            if (isset($seen[strtolower($c)])) continue; // skip duplicates
                            $sel = ($category === $c) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($c) . '" ' . $sel . '>' . htmlspecialchars($c) . '</option>';
                        }
                    ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Apply Filter</button>
                <a href="dashboard.php" class="btn btn-secondary btn-sm">Reset</a>
                <div class="vr mx-2 d-none d-md-block"></div>
                <div class="btn-group btn-group-sm" role="group" aria-label="Quick ranges">
                    <button type="button" class="btn btn-outline-secondary" onclick="setRange('today')">Today</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="setRange('7')">Last 7d</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="setRange('30')">Last 30d</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="setRange('month')">This Month</button>
                </div>
                <a href="orders_export.php?start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?><?= $category ? '&category=' . urlencode($category) : '' ?>" class="btn btn-dark btn-sm ms-auto">
                    Export CSV
                </a>
            </form>
            <small class="text-muted d-block mt-2">Showing data from <strong><?= htmlspecialchars($startDate) ?></strong> to <strong><?= htmlspecialchars($endDate) ?></strong><?= $category ? ' • Category: <strong>' . htmlspecialchars($category) . '</strong>' : '' ?></small>
        </div>

        <!-- KPI CARDS -->
        <div class="row mb-3 kpi-row">
            <div class="col-md-3 mb-3">
                <div class="stat-card bg1">
                    <p>📦 Total Orders</p>
                    <h4><?= $totalOrders ?></h4>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg2">
                    <p>👥 Total Customers</p>
                    <h4><?= $totalUsers ?></h4>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg5">
                    <p>✅ Delivered Orders</p>
                    <h4><?= $deliveredOrders ?></h4>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg3">
                    <p>⏳ Pending Orders</p>
                    <h4><?= $pendingOrders ?></h4>
                </div>
            </div>
        </div>

        <!-- REVENUE CARDS -->
        <div class="row mb-3 kpi-row">
            <div class="col-md-3 mb-3">
                <div class="stat-card bg5">
                    <p>💰 Total Paid Sales</p>
                    <h4>Rs. <?= number_format($totalPaidSales) ?></h4>
                    <small><?= $paidOrders ?> Orders</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg4">
                    <p>⚠️ Unpaid Amount</p>
                    <h4>Rs. <?= number_format($unpaidAmount) ?></h4>
                    <small><?= $unpaidOrders ?> Orders</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg6">
                    <p>📈 Avg Order Value</p>
                    <h4>Rs. <?= number_format($avgOrderValue) ?></h4>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg2">
                    <p>🛒 Total Revenue</p>
                    <h4>Rs. <?= number_format($totalPaidSales + $unpaidAmount) ?></h4>
                </div>
            </div>
        </div>

        <!-- EXTRA KPIs -->
        <div class="row mb-3 kpi-row">
            <div class="col-md-3 mb-3">
                <div class="stat-card bg1">
                    <p>🎯 Conversion Rate</p>
                    <h4><?= $conversionRate ?>%</h4>
                    <small>Paid / Total Orders</small>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg3">
                    <p>📦 Items per Order</p>
                    <h4><?= $avgItemsPerOrder ?></h4>
                    <small>Total items ordered: <?= number_format($totalItems) ?></small>
                </div>
            </div>
        </div>

        <!-- CHARTS ROW 1 -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">📊 Order Status Distribution</div>
                    <div class="card-body"><canvas id="orderStatusChart"></canvas></div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">💳 Payment Status</div>
                    <div class="card-body small-chart"><canvas id="paymentStatusChart"></canvas></div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">💵 Payment Method</div>
                    <div class="card-body"><canvas id="paymentMethodChart"></canvas></div>
                </div>
            </div>
        </div>

        <!-- Product Categories -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-dark text-white">📂 Product Categories</div>
                <div class="card-body" style="overflow-x:auto;">
                    <canvas id="categoriesChart" style="min-width:800px; height:300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- TOP PRODUCTS -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-dark text-white">🏆 Top 5 Products</div>
                    <div class="card-body top-products">
                        <?php if ($topProducts && $topProducts->num_rows > 0): ?>
                            <?php while ($product = $topProducts->fetch_assoc()): ?>
                                <div class="product-item">
                                    <div style="display: flex; align-items: center; flex: 1;">
                                        <img src="<?= '../shared/products/' . htmlspecialchars($product['image']) ?>" class="product-img" alt="">
                                        <div>
                                            <strong><?= htmlspecialchars($product['j_name']) ?></strong>
                                            <br>
                                            <small class="text-muted">Qty ordered: <?= $product['total_qty'] ?> | Revenue: Rs. <?= number_format($product['total_revenue']) ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted text-center">No products sold in this period</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function setRange(preset) {
            const sd = document.querySelector('input[name="start_date"]');
            const ed = document.querySelector('input[name="end_date"]');
            const today = new Date();

            function format(d) {
                const y = d.getFullYear();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${y}-${m}-${day}`;
            }

            let start = new Date(today);
            let end = new Date(today);

            if (preset === 'today') {
                // start and end already today
            } else if (preset === '7') {
                start.setDate(today.getDate() - 6);
            } else if (preset === '30') {
                start.setDate(today.getDate() - 29);
            } else if (preset === 'month') {
                start = new Date(today.getFullYear(), today.getMonth(), 1);
            }

            sd.value = format(start);
            ed.value = format(end);
            sd.form.submit();
        }
        /* ORDER STATUS BAR */
        new Chart(document.getElementById('orderStatusChart'), {
            type: 'bar',
            data: { labels: <?= json_encode($orderStatusLabels) ?>, datasets: [{ data: <?= json_encode($orderStatusData) ?>, backgroundColor: ['#ff9800', '#4caf50'] }] },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        /* PAYMENT STATUS PIE */
        new Chart(document.getElementById('paymentStatusChart'), {
            type: 'doughnut',
            data: { labels: <?= json_encode($paymentStatusLabels) ?>, datasets: [{ data: <?= json_encode($paymentStatusData) ?>, backgroundColor: ['#2e7d32', '#c62828'] }] },
            options: { plugins: { legend: { position: 'bottom' } } }
        });

        /* PAYMENT METHOD BAR */
        new Chart(document.getElementById('paymentMethodChart'), {
            type: 'bar',
            data: { labels: <?= json_encode($paymentMethodLabels) ?>, datasets: [{ data: <?= json_encode($paymentMethodData) ?>, backgroundColor: ['#1565c0', '#ef6c00'] }] },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        /* PRODUCT CATEGORIES CHART */
        (function() {
            const catLabels = <?= json_encode($categoryLabels) ?>;
            const catRevenue = <?= json_encode($categoryRevenue) ?>;
            const catOrders = <?= json_encode($categoryOrders) ?>;
            const catQty = <?= json_encode($categoryQuantity) ?>;
            const canvas = document.getElementById('categoriesChart');
            if (!catLabels || catLabels.length === 0) {
                if (canvas && canvas.parentElement) {
                    canvas.parentElement.innerHTML = '<p class="text-muted m-0">No categories found for the selected period.</p>';
                }
                return;
            }
            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: catLabels,
                    datasets: [
                        { 
                            label: 'Revenue (Rs.)', 
                            data: catRevenue, 
                            backgroundColor: 'rgba(46, 125, 50, 0.8)',
                            borderColor: '#2e7d32',
                            borderWidth: 1
                        }
                    ]
                },
                options: { 
                    responsive: true,
                    plugins: { 
                        legend: { position: 'top' },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            callbacks: {
                                label: function(context) {
                                    const idx = context.dataIndex;
                                    return 'Revenue: Rs. ' + catRevenue[idx].toLocaleString('en-IN', {maximumFractionDigits: 0});
                                },
                                afterLabel: function(context) {
                                    const idx = context.dataIndex;
                                    return 'Orders: ' + catOrders[idx] + ' | Quantity: ' + catQty[idx];
                                }
                            }
                        }
                    }, 
                    scales: { 
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Revenue (Rs.)'
                            }
                        }
                    } 
                }
            });
        })();
    </script>

</body>

</html>