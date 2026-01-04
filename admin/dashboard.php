<?php
session_start();
require_once("../shared/dbconnect.php");
require_once("../shared/commonlinks.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

/* ===== OVERALL SUMMARY ===== */
$totalOrders = $conn->query("SELECT COUNT(*) t FROM orders")->fetch_assoc()['t'];
$totalUsers = $conn->query("SELECT COUNT(DISTINCT user_id) t FROM orders")->fetch_assoc()['t'];

$paidData = $conn->query("SELECT COUNT(*) c, SUM(grand_total) t FROM orders WHERE payment_status='Completed'")->fetch_assoc();
$paidOrders = $paidData['c'];
$totalPaidSales = $paidData['t'] ?? 0;

$unpaidData = $conn->query("SELECT COUNT(*) c, SUM(grand_total) t FROM orders WHERE payment_status='Pending'")->fetch_assoc();
$unpaidOrders = $unpaidData['c'];
$unpaidAmount = $unpaidData['t'] ?? 0;

$codOrders = $conn->query("SELECT COUNT(*) t FROM orders WHERE payment_option='Cash on Delivery'")->fetch_assoc()['t'];
$onlineOrders = $conn->query("SELECT COUNT(*) t FROM orders WHERE payment_option='Online Payment'")->fetch_assoc()['t'];

/* ===== ORDER STATUS DATA ===== */
$orderStatusLabels = [];
$orderStatusData = [];
$res = $conn->query("SELECT order_status, COUNT(*) t FROM orders GROUP BY order_status");
while ($r = $res->fetch_assoc()) {
    $orderStatusLabels[] = $r['order_status'];
    $orderStatusData[] = $r['t'];
}

/* ===== PAYMENT STATUS DATA ===== */
$paymentStatusLabels = ['Completed', 'Pending'];
$paymentStatusData = [$paidOrders, $unpaidOrders];

/* ===== PAYMENT METHOD DATA ===== */
$paymentMethodLabels = ['Cash on Delivery', 'Online Payment'];
$paymentMethodData = [$codOrders, $onlineOrders];

/* ===== MONTH-WISE SUMMARY ===== */
$monthLabels = [];
$monthPaidOrders = [];
$monthUnpaidOrders = [];
$monthDeliveredOrders = [];
$monthPendingOrders = [];
$monthTotalAmount = [];

$res = $conn->query(
    "SELECT DATE_FORMAT(order_date,'%Y-%m') month,
            SUM(CASE WHEN payment_status='Completed' THEN 1 ELSE 0 END) paid_count,
            SUM(CASE WHEN payment_status='Pending' THEN 1 ELSE 0 END) unpaid_count,
            SUM(CASE WHEN order_status='Delivered' THEN 1 ELSE 0 END) delivered_count,
            SUM(CASE WHEN order_status='Pending' THEN 1 ELSE 0 END) pending_count,
            SUM(grand_total) total_amount
     FROM orders
     GROUP BY month
     ORDER BY month ASC"
);

while ($r = $res->fetch_assoc()) {
    $monthLabels[] = $r['month'];
    $monthPaidOrders[] = intval($r['paid_count']);
    $monthUnpaidOrders[] = intval($r['unpaid_count']);
    $monthDeliveredOrders[] = intval($r['delivered_count']);
    $monthPendingOrders[] = intval($r['pending_count']);
    $monthTotalAmount[] = floatval($r['total_amount']);
}

/* ===== YEAR-WISE SUMMARY ===== */
$yearLabels = [];
$yearPaidOrders = [];
$yearUnpaidOrders = [];
$yearDeliveredOrders = [];
$yearPendingOrders = [];
$yearTotalAmount = [];

$res = $conn->query(
    "SELECT YEAR(order_date) year,
            SUM(CASE WHEN payment_status='Completed' THEN 1 ELSE 0 END) paid_count,
            SUM(CASE WHEN payment_status='Pending' THEN 1 ELSE 0 END) unpaid_count,
            SUM(CASE WHEN order_status='Delivered' THEN 1 ELSE 0 END) delivered_count,
            SUM(CASE WHEN order_status='Pending' THEN 1 ELSE 0 END) pending_count,
            SUM(grand_total) total_amount
     FROM orders
     GROUP BY year
     ORDER BY year ASC"
);

while ($r = $res->fetch_assoc()) {
    $yearLabels[] = $r['year'];
    $yearPaidOrders[] = intval($r['paid_count']);
    $yearUnpaidOrders[] = intval($r['unpaid_count']);
    $yearDeliveredOrders[] = intval($r['delivered_count']);
    $yearPendingOrders[] = intval($r['pending_count']);
    $yearTotalAmount[] = floatval($r['total_amount']);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    
    <style>
        body {
            background: #f4f6f9;
        }

        .admin-content {
            padding: 20px;
            margin-top: 0px !important;
        }

        .stat-card {
            padding: 18px;
            border-radius: 10px;
            color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, .15);
        }

        .bg1 {
            background: #00695c;
        }

        .bg2 {
            background: #283593;
        }

        .bg3 {
            background: #ef6c00;
        }

        .bg4 {
            background: #c62828;
        }

        .bg5 {
            background: #2e7d32;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, .12);
        }

        .small-chart {
            max-width: 260px;
            margin: auto;
        }

        options: {
            plugins: {
                legend: {
                    position: 'top'
                }
            }

            ,
            scales: {
                x: {

                    stacked: true,
                    ticks: {
                        maxRotation: 45, minRotation: 30
                    }

                    // rotate labels to save space
                }

                ,
                y: {
                    beginAtZero: true, stacked: true
                }
            }
        }
    </style>
</head>

<body>

    <?php include "header.php"; ?>

    <div class="admin-content">

        <h3 class="mb-4">Dashboard Overview</h3>

        <!-- KPI CARDS -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card bg1">
                    <p>Total Orders</p>
                    <h4>
                        <?= $totalOrders ?>
                    </h4>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg2">
                    <p>Total Customers</p>
                    <h4>
                        <?= $totalUsers ?>
                    </h4>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg5">
                    <p>Paid Sales</p>
                    <h4>
                        <?= $paidOrders ?> Orders<br>₹
                        <?= number_format($totalPaidSales) ?>
                    </h4>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg4">
                    <p>Unpaid Orders</p>
                    <h4>
                        <?= $unpaidOrders ?> Orders<br>₹
                        <?= number_format($unpaidAmount) ?>
                    </h4>
                </div>
            </div>
        </div>

        <!-- CHARTS ROW 1 -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">Order Status</div>
                    <div class="card-body"><canvas id="orderStatusChart"></canvas></div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">Payment Status</div>
                    <div class="card-body small-chart"><canvas id="paymentStatusChart"></canvas></div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">Payment Method</div>
                    <div class="card-body"><canvas id="paymentMethodChart"></canvas></div>
                </div>
            </div>
        </div>

        <!-- Month-wise Orders -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-dark text-white">Month-wise Orders</div>
                <div class="card-body" style="overflow-x:auto;">
                    <canvas id="monthOrdersChart" style="min-width:800px; height:200px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Year-wise Orders -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-dark text-white">Year-wise Orders</div>
                <div class="card-body" style="overflow-x:auto;">
                    <canvas id="yearOrdersChart" style="min-width:500px; height:200px;"></canvas>
                </div>
            </div>
        </div>


    </div>

    <script>
        /* ORDER STATUS BAR */
        new Chart(orderStatusChart, {
            type: 'bar',
            data: { labels: <?= json_encode($orderStatusLabels) ?>, datasets: [{ data: <?= json_encode($orderStatusData) ?>, backgroundColor: ['#fbc02d', '#4caf50'] }] },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        /* PAYMENT STATUS PIE */
        new Chart(paymentStatusChart, {
            type: 'pie',
            data: { labels: <?= json_encode($paymentStatusLabels) ?>, datasets: [{ data: <?= json_encode($paymentStatusData) ?>, backgroundColor: ['#2e7d32', '#c62828'] }] },
            options: { plugins: { legend: { position: 'bottom' } } }
        });

        /* PAYMENT METHOD BAR */
        new Chart(paymentMethodChart, {
            type: 'bar',
            data: { labels: <?= json_encode($paymentMethodLabels) ?>, datasets: [{ data: <?= json_encode($paymentMethodData) ?>, backgroundColor: ['#1565c0', '#ef6c00'] }] },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        /* MONTH-WISE ORDERS BAR */
        new Chart(monthOrdersChart, {
            type: 'bar',
            data: {
                labels: <?= json_encode($monthLabels) ?>,
                datasets: [
                    { label: 'Paid', data: <?= json_encode($monthPaidOrders) ?>, backgroundColor: '#2e7d32' },
                    { label: 'Unpaid', data: <?= json_encode($monthUnpaidOrders) ?>, backgroundColor: '#c62828' },
                    { label: 'Delivered', data: <?= json_encode($monthDeliveredOrders) ?>, backgroundColor: '#4caf50' },
                    { label: 'Pending', data: <?= json_encode($monthPendingOrders) ?>, backgroundColor: '#ff9800' }
                ]
            },
            options: { plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
        });

        /* YEAR-WISE ORDERS BAR */
        new Chart(yearOrdersChart, {
            type: 'bar',
            data: {
                labels: <?= json_encode($yearLabels) ?>,
                datasets: [
                    { label: 'Paid', data: <?= json_encode($yearPaidOrders) ?>, backgroundColor: '#2e7d32' },
                    { label: 'Unpaid', data: <?= json_encode($yearUnpaidOrders) ?>, backgroundColor: '#c62828' },
                    { label: 'Delivered', data: <?= json_encode($yearDeliveredOrders) ?>, backgroundColor: '#4caf50' },
                    { label: 'Pending', data: <?= json_encode($yearPendingOrders) ?>, backgroundColor: '#ff9800' }
                ]
            },
            options: { plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
        });
    </script>

</body>

</html>