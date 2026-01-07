<!-- User Orders Page -->
<?php
session_start();
require_once "../shared/dbconnect.php";

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: loginsignup/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// Validate date inputs (YYYY-MM-DD) to avoid SQL injection
$startDateSafe = '';
$endDateSafe = '';
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) $startDateSafe = $startDate;
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) $endDateSafe = $endDate;

$whereSql = "WHERE user_id = " . (int)$user_id;
if ($startDateSafe) {
    $whereSql .= " AND order_date >= '" . $conn->real_escape_string($startDateSafe) . " 00:00:00'";
}
if ($endDateSafe) {
    $whereSql .= " AND order_date <= '" . $conn->real_escape_string($endDateSafe) . " 23:59:59'";
}

// Summary
$summarySql = "SELECT COUNT(*) AS cnt, COALESCE(SUM(grand_total),0) AS total_amount FROM orders $whereSql";
$res = $conn->query($summarySql);
$summary = $res ? $res->fetch_assoc() : ['cnt' => 0, 'total_amount' => 0];

$totalOrders = (int)$summary['cnt'];
$totalAmount = $summary['total_amount'];

// Orders list
$listSql = "SELECT order_id, payment_status, order_status, order_date, 
               (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = orders.order_id) AS total_items,
               (SELECT pname FROM order_items oi WHERE oi.order_id = orders.order_id LIMIT 1) AS first_item_name
            FROM orders $whereSql
            ORDER BY order_date DESC";

$orders_res = $conn->query($listSql);

?>

<!DOCTYPE html>
<html>

<head>
    <title>My Orders</title>
    <style>
        body{background:#e0f4f2;font-family:Segoe UI,SegoeUI,Arial,sans-serif;color:#234;}
        .profile-card{max-width:900px;margin:30px auto;padding:20px;border-radius:10px;background:#fff}
        .msg{font-weight:600;text-align:center;margin-bottom:12px}
        button{background:#1c6059;color:#fff;padding:8px 14px;border-radius:8px;border:0;cursor:pointer}
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);align-items:center;justify-content:center}
        .modal-box{background:#fff;padding:18px;border-radius:10px;width:95%;max-width:760px}
        table{width:100%;border-collapse:collapse;margin-top:8px}
        th,td{padding:8px;border:1px solid #eee;text-align:left}
    </style>
</head>

<body style="background-color: #e0f4f2;">

    <?php include_once 'header.php'; ?>

    <div class="profile-card">
        <h3>My Orders</h3>

        <form method="get" class="search-form" style="margin-bottom:12px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <label style="display:flex;gap:6px;align-items:center">Start Date: <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>"></label>
            <label style="display:flex;gap:6px;align-items:center">End Date: <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>"></label>
            <div style="margin-left:auto">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="profile_order.php" class="btn btn-secondary" style="margin-left:8px;">Reset</a>
            </div>
        </form>

        <div style="margin-bottom:12px;padding:10px;border-radius:8px;background:#f4f9f8;border:1px solid #e6f3f1;display:flex;gap:12px;align-items:center;">
            <div><strong>Total orders:</strong> <?= $totalOrders ?></div>
            <div><strong>Total amount:</strong> Rs. <?= number_format($totalAmount) ?></div>
        </div>

        <div style="overflow-x:auto">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>S.N.</th>
                    <th>Order No</th>
                    <th>Order Items</th>
                    <th>Payment Status</th>
                    <th>Order Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders_res->num_rows === 0): ?>
                    <tr><td colspan="6" class="text-center text-muted">No orders found for selected filters</td></tr>
                <?php else: ?>
                    <?php $sn=1; while ($o = $orders_res->fetch_assoc()): ?>
                        <tr id="orderRow<?= $o['order_id'] ?>">
                            <td><?= $sn++ ?></td>
                            <td>Order #<?= $o['order_id'] ?> <br><a href="#" class="btn btn-sm btn-outline-info view-order" data-order="<?= $o['order_id'] ?>" style="margin-top:6px;display:inline-block">View</a></td>
                            <td><?= $o['total_items'] ?> item<?= $o['total_items']>1?'s':'' ?><?= !empty($o['first_item_name']) ? ': ' . htmlspecialchars($o['first_item_name']) : '' ?></td>
                            <td><?= htmlspecialchars($o['payment_status']) ?></td>
                            <td><?= htmlspecialchars($o['order_status']) ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($o['order_date'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- ORDER DETAILS MODAL -->
    <div id="orderModal" class="modal-overlay">
        <div class="modal-box">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <h4 style="margin:0">Order Details</h4>
                <button onclick="closeOrderModal()" class="modal-cancel">Close</button>
            </div>
            <div id="orderModalBody">Loading...</div>
        </div>
    </div>

    <script>
        // Order modal logic
        document.addEventListener('click', function(e){
            if (e.target && e.target.matches('.view-order')) {
                e.preventDefault();
                const id = e.target.dataset.order;
                const body = new URLSearchParams();
                body.append('order_id', id);
                fetch('profile_change_pass.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({action: 'get_order_details', order_id: id}).toString()
                })
                .then(res => {
                    if (!res.ok) throw new Error('Could not fetch order');
                    return res.text();
                })
                .then(html => {
                    document.getElementById('orderModalBody').innerHTML = html;
                    document.getElementById('orderModal').style.display = 'flex';
                })
                .catch(err => {
                    document.getElementById('orderModalBody').innerHTML = '<div class="msg error">' + err.message + '</div>';
                    document.getElementById('orderModal').style.display = 'flex';
                });
            }
        });

        function closeOrderModal(){
            document.getElementById('orderModal').style.display = 'none';
        }

        // close on overlay click
        document.getElementById('orderModal').addEventListener('click', function(e){ if (e.target === this) closeOrderModal(); });

        // AJAX Date Search - No page reload
        document.querySelector('.search-form').addEventListener('submit', function(e){
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);
            
            fetch('profile_change_pass.php?action=get_orders&' + params.toString())
                .then(res => res.json())
                .then(data => {
                    // Update summary stats
                    document.querySelectorAll('.profile-card > div')[1].innerHTML = '<strong>Total orders:</strong> ' + data.totalOrders + '<br><strong>Total amount:</strong> Rs. ' + data.totalAmount;
                    
                    // Update table body
                    document.querySelector('.table tbody').innerHTML = data.tableHtml;
                    
                    // Update URL without reload
                    const startDate = formData.get('start_date');
                    const endDate = formData.get('end_date');
                    let newUrl = 'profile_order.php';
                    if (startDate || endDate) {
                        newUrl += '?';
                        if (startDate) newUrl += 'start_date=' + startDate + '&';
                        if (endDate) newUrl += 'end_date=' + endDate;
                        newUrl = newUrl.replace(/&$/, '');
                    }
                    history.pushState({}, '', newUrl);
                })
                .catch(err => {
                    console.error('Search failed:', err);
                });
        });
    </script>

    <?php include 'footer.php'; ?>

</body>

</html>
