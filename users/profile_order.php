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

// Handle AJAX: get order details (for modal)
if (isset($_POST['action']) && $_POST['action'] === 'get_order_details') {
    $order_id = intval($_POST['order_id'] ?? 0);
    if ($order_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid order id']);
        exit();
    }

    // Verify order belongs to user
    $stmt = $conn->prepare("SELECT order_id, name, location, grand_total, payment_status, order_status, transaction_id, order_date FROM orders WHERE order_id = ? AND user_id = ? LIMIT 1");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        exit();
    }

    // Fetch items
    $stmt = $conn->prepare("SELECT pname, jersey_size, quantity, final_price, subtotal, shipping, product_image FROM order_items WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Compute totals
    $shipping_total = 0;
    foreach ($items as $it) {
        $shipping_total += floatval($it['shipping'] ?? 0);
    }
    $subtotal = floatval($order['grand_total']) - $shipping_total;

    // Get first image
    $firstImage = '';
    if (!empty($items) && !empty($items[0]['product_image'])) {
        $candidate = __DIR__ . '/../shared/products/' . $items[0]['product_image'];
        if (file_exists($candidate)) {
            $firstImage = $items[0]['product_image'];
        }
    }

    // Return HTML
    ob_start();
    ?>
    <div style="display:flex;gap:12px;margin-bottom:16px">
        <div style="flex:0 0 100px">
            <?php if (!empty($firstImage)): ?>
                <img src="../shared/products/<?= htmlspecialchars($firstImage) ?>" alt="Jersey"
                    style="width:100px;height:100px;object-fit:contain;background:#f7f7f7;padding:4px;border-radius:6px">
            <?php else: ?>
                <div
                    style="width:100px;height:100px;background:#f0f0f0;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;color:#999">
                    No image</div>
            <?php endif; ?>
        </div>
        <div style="flex:1">
            <p style="margin:0 0 8px 0"><strong>Order #<?= $order['order_id'] ?></strong></p>
            <p style="margin:0 0 4px 0;font-size:14px">Name: <?= htmlspecialchars($order['name']) ?></p>
            <p style="margin:0 0 4px 0;font-size:14px">Location: <?= htmlspecialchars($order['location']) ?></p>
            <p style="margin:0 0 4px 0;font-size:14px">Payment: <span
                    style="color:#1c6059;font-weight:600"><?= htmlspecialchars($order['payment_status']) ?></span></p>
            <p style="margin:0 0 4px 0;font-size:14px">Status: <span
                    style="color:#1c6059;font-weight:600"><?= htmlspecialchars($order['order_status']) ?></span></p>
            <p style="margin:0;font-size:13px;color:#666">Date: <?= date('d M Y, H:i', strtotime($order['order_date'])) ?>
            </p>
        </div>
    </div>

    <hr style="margin:12px 0;border:none;border-top:1px solid #eee">

    <h5 style="margin:12px 0 8px 0">Items (<?= count($items) ?>)</h5>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="background:#f4f9f8;border-bottom:1px solid #ddd">
                    <th style="padding:8px;text-align:left">Jersey</th>
                    <th style="padding:8px;text-align:center">Size</th>
                    <th style="padding:8px;text-align:center">Qty</th>
                    <th style="padding:8px;text-align:right">Price</th>
                    <th style="padding:8px;text-align:right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr style="border-bottom:1px solid #f0f0f0">
                        <td style="padding:8px"><?= htmlspecialchars($item['pname']) ?></td>
                        <td style="padding:8px;text-align:center"><?= htmlspecialchars($item['jersey_size']) ?></td>
                        <td style="padding:8px;text-align:center"><?= $item['quantity'] ?></td>
                        <td style="padding:8px;text-align:right">Rs. <?= number_format(floatval($item['final_price'])) ?></td>
                        <td style="padding:8px;text-align:right">Rs. <?= number_format(floatval($item['subtotal'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top:12px;padding:12px;background:#f9f9f9;border-radius:6px">
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:14px">
            <span>Subtotal:</span>
            <strong>Rs. <?= number_format($subtotal) ?></strong>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:14px">
            <span>Shipping:</span>
            <strong>Rs. <?= number_format($shipping_total) ?></strong>
        </div>
        <div style="display:flex;justify-content:space-between;border-top:2px solid #ddd;padding-top:8px;font-size:15px">
            <strong>Total:</strong>
            <strong style="color:#1c6059">Rs. <?= number_format(floatval($order['grand_total'])) ?></strong>
        </div>
    </div>
    <?php
    echo ob_get_clean();
    exit();
}

// Handle AJAX: get orders with date filter
if (isset($_GET['action']) && $_GET['action'] === 'get_orders') {
    ob_clean(); // Clear any accidental output
    header('Content-Type: application/json');

    try {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 5;
        $offset = ($page - 1) * $perPage;

        // Build query with date filter
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $startDateSafe = '';
        $endDateSafe = '';
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate))
            $startDateSafe = $startDate;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate))
            $endDateSafe = $endDate;

        $whereSql = "WHERE user_id = " . (int) $user_id;
        if ($startDateSafe) {
            $whereSql .= " AND order_date >= '" . $conn->real_escape_string($startDateSafe) . " 00:00:00'";
        }
        if ($endDateSafe) {
            $whereSql .= " AND order_date <= '" . $conn->real_escape_string($endDateSafe) . " 23:59:59'";
        }

        // Count total
        $countRes = $conn->query("SELECT COUNT(*) AS cnt FROM orders $whereSql");
        if (!$countRes)
            throw new Exception("Count query failed");

        $totalOrders = ($row = $countRes->fetch_assoc()) ? (int) $row['cnt'] : 0;
        $totalPages = max(1, (int) ceil($totalOrders / $perPage));

        // Ensure page is within bounds
        $page = min($page, $totalPages);

        // Get paginated orders
        $listSql = "SELECT order_id, payment_status, order_status, order_date,
                       (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = orders.order_id) AS total_items
                    FROM orders $whereSql
                    ORDER BY order_date DESC
                    LIMIT $perPage OFFSET $offset";

        $orders_res = $conn->query($listSql);
        if (!$orders_res)
            throw new Exception("Orders query failed");

        // Build table HTML
        $tableHtml = '';
        if ($orders_res->num_rows === 0) {
            $tableHtml = '<tr><td colspan="6" style="text-align:center;color:#999;">No orders found</td></tr>';
        } else {
            $sn = $offset + 1;
            while ($o = $orders_res->fetch_assoc()) {
                $tableHtml .= '<tr id="orderRow' . $o['order_id'] . '">';
                $tableHtml .= '<td>' . $sn++ . '</td>';
                $tableHtml .= '<td><strong>Order #' . $o['order_id'] . '</strong><br><button type="button" class="btn btn-view view-order" data-order="' . $o['order_id'] . '" style="margin-top:4px">View Details</button></td>';
                $tableHtml .= '<td>' . $o['total_items'] . ' item' . ($o['total_items'] > 1 ? 's' : '') . '</td>';
                $tableHtml .= '<td><span class="badge badge-payment">' . htmlspecialchars($o['payment_status']) . '</span></td>';
                $tableHtml .= '<td><span class="badge badge-status">' . htmlspecialchars($o['order_status']) . '</span></td>';
                $tableHtml .= '<td>' . date('d M Y, H:i', strtotime($o['order_date'])) . '</td>';
                $tableHtml .= '</tr>';
            }
        }

        // Return JSON response
        echo json_encode([
            'success' => true,
            'totalOrders' => $totalOrders,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'tableHtml' => $tableHtml
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit();
}

// Regular page load
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// pagination params
$perPage = 5;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Validate date inputs (YYYY-MM-DD) to avoid SQL injection
$startDateSafe = '';
$endDateSafe = '';
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate))
    $startDateSafe = $startDate;
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate))
    $endDateSafe = $endDate;

$whereSql = "WHERE user_id = " . (int) $user_id;
if ($startDateSafe) {
    $whereSql .= " AND order_date >= '" . $conn->real_escape_string($startDateSafe) . " 00:00:00'";
}
if ($endDateSafe) {
    $whereSql .= " AND order_date <= '" . $conn->real_escape_string($endDateSafe) . " 23:59:59'";
}

// Count for pagination
$countSql = "SELECT COUNT(*) AS cnt FROM orders $whereSql";
$countRes = $conn->query($countSql);
$totalOrders = ($countRes && $row = $countRes->fetch_assoc()) ? (int) $row['cnt'] : 0;
$totalPages = max(1, (int) ceil($totalOrders / $perPage));

// Orders list (paged)
$listSql = "SELECT order_id, payment_status, order_status, order_date,
               (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = orders.order_id) AS total_items,
               (SELECT pname FROM order_items oi WHERE oi.order_id = orders.order_id LIMIT 1) AS first_item_name
            FROM orders $whereSql
            ORDER BY order_date DESC
            LIMIT $perPage OFFSET $offset";

$orders_res = $conn->query($listSql);
?>
<!DOCTYPE html>
<html>

<head>
    <title>My Orders</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #e0f4f2;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #333;
        }

        .profile-card {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        h3 {
            color: #1c6059;
            margin-bottom: 20px;
        }

        .search-form {
            margin-bottom: 16px;
            padding: 12px;
            background: #f9f9f9;
            border-radius: 8px;
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-form label {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .search-form input {
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
            font-weight: 500;
        }

        .btn-primary {
            background: #1c6059;
            color: #fff;
        }

        .btn-primary:hover {
            background: #0f3d32;
        }

        .btn-view {
            background: #1c6059;
            color: #fff;
            padding: 6px 12px;
            font-size: 0.85em;
            border: 0;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(28, 96, 89, 0.3);
            transition: all 0.2s;
        }

        .btn-view:hover {
            background: #0f3d32;
            box-shadow: 0 4px 8px rgba(28, 96, 89, 0.4);
            transform: translateY(-1px);
        }

        .search-stats {
            background: #f4f9f8;
            border-left: 4px solid #1c6059;
            padding: 12px;
            margin-bottom: 16px;
            border-radius: 6px;
            font-size: 0.95em;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f4f9f8;
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
            font-weight: 600;
            color: #1c6059;
        }

        td {
            padding: 12px;
            border: 1px solid #ddd;
            font-size: 0.95em;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: 500;
        }

        .badge-payment {
            background: #1976d2;
            color: #fff;
            font-weight: 600;
        }

        .badge-status {
            background: #388e3c;
            color: #fff;
            font-weight: 600;
        }

        .pagination {
            display: flex;
            gap: 6px;
            margin-top: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #1c6059;
            transition: all 0.2s;
        }

        .pagination a:hover {
            background: #f0f0f0;
        }

        .pagination .active {
            background: #1c6059;
            color: #fff;
            border-color: #1c6059;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background: #fff;
            padding: 24px;
            border-radius: 10px;
            width: 100%;
            max-width: 720px;
            max-height: 85vh;
            overflow-y: auto;
        }

        .modal-box h4 {
            color: #1c6059;
            margin-bottom: 16px;
        }

        .modal-close {
            background: #ddd;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1.1em;
        }

        .modal-close:hover {
            background: #bbb;
        }
    </style>
</head>

<body style="background-color: #e0f4f2;">

    <?php include_once 'header.php'; ?>

    <div class="profile-card">
        <h3>📦 My Orders</h3>

        <form class="search-form">
            <label>From: <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>"></label>
            <label>To: <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>"></label>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="profile_order.php" class="btn" style="background: #6c757d; color: #fff;">Reset</a>
        </form>

        <div class="search-stats">
            <strong>Total Orders: </strong><span id="totalOrdersDisplay"><?= $totalOrders ?></span>
        </div>

        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order</th>
                        <th>Items</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                    <?php if (!$orders_res || $orders_res->num_rows === 0): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;color:#999;">No orders found</td>
                        </tr>
                    <?php else: ?>
                        <?php $sn = $offset + 1;
                        while ($o = $orders_res->fetch_assoc()): ?>
                            <tr id="orderRow<?= $o['order_id'] ?>">
                                <td><?= $sn++ ?></td>
                                <td><strong>Order #<?= $o['order_id'] ?></strong><br><button type="button"
                                        class="btn btn-view view-order" data-order="<?= $o['order_id'] ?>"
                                        style="margin-top:4px">View Details</button></td>
                                <td><?= $o['total_items'] ?> item<?= $o['total_items'] > 1 ? 's' : '' ?></td>
                                <td><span class="badge badge-payment"><?= htmlspecialchars($o['payment_status']) ?></span></td>
                                <td><span class="badge badge-status"><?= htmlspecialchars($o['order_status']) ?></span></td>
                                <td><?= date('d M Y, H:i', strtotime($o['order_date'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination" id="paginationContainer">
            <?php if ($totalPages > 1): ?>
                <?php if ($page > 1): ?>
                    <a href="?page=1">« First</a>
                    <a href="?page=<?= $page - 1 ?>">‹ Prev</a>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <?php if ($p === $page): ?>
                        <span class="active"><?= $p ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $p ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>">Next ›</a>
                    <a href="?page=<?= $totalPages ?>">Last »</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal-overlay">
        <div class="modal-box">
            <div
                style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:2px solid #f0f0f0;padding-bottom:12px;">
                <h4 style="margin:0">Order Details</h4>
                <button onclick="closeOrderModal()" class="modal-close">✕</button>
            </div>
            <div id="orderModalBody">Loading...</div>
        </div>
    </div>

    <script>
        // View order details in modal
        document.addEventListener('click', function (e) {
            if (e.target.matches('.view-order')) {
                e.preventDefault();
                const orderId = e.target.dataset.order;
                const modal = document.getElementById('orderModal');
                const body = document.getElementById('orderModalBody');

                body.innerHTML = '<div style="text-align:center;padding:20px;color:#666;">Loading order details...</div>';
                modal.classList.add('active');

                fetch('profile_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=get_order_details&order_id=' + encodeURIComponent(orderId)
                })
                    .then(res => res.text())
                    .then(html => {
                        body.innerHTML = html;
                    })
                    .catch(err => {
                        body.innerHTML = '<div style="color:#c33;padding:20px;background:#ffebee;border-radius:6px;">Error loading order details. Please try again.</div>';
                        console.error('Error:', err);
                    });
            }
        });

        function closeOrderModal() {
            document.getElementById('orderModal').classList.remove('active');
        }

        // Close modal on overlay click
        document.getElementById('orderModal').addEventListener('click', function (e) {
            if (e.target === this) closeOrderModal();
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeOrderModal();
        });

        // Date filter search - AJAX without page reload
        document.querySelector('.search-form').addEventListener('submit', function (e) {
            e.preventDefault();

            const startDate = document.querySelector('input[name="start_date"]').value;
            const endDate = document.querySelector('input[name="end_date"]').value;

            let url = 'profile_order.php?action=get_orders';
            if (startDate) url += '&start_date=' + encodeURIComponent(startDate);
            if (endDate) url += '&end_date=' + encodeURIComponent(endDate);

            fetch(url)
                .then(res => {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.error || 'Unknown error');
                    }

                    // Update summary
                    document.getElementById('totalOrdersDisplay').textContent = data.totalOrders;

                    // Update table
                    document.getElementById('ordersTableBody').innerHTML = data.tableHtml;

                    // Update pagination
                    let paginationHtml = '';
                    if (data.totalPages > 1) {
                        if (data.currentPage > 1) {
                            paginationHtml += '<a href="?action=get_orders&start_date=' + (startDate || '') + '&end_date=' + (endDate || '') + '&page=1">« First</a>';
                            paginationHtml += '<a href="?action=get_orders&start_date=' + (startDate || '') + '&end_date=' + (endDate || '') + '&page=' + (data.currentPage - 1) + '">‹ Prev</a>';
                        }
                        for (let p = 1; p <= data.totalPages; p++) {
                            if (p === data.currentPage) {
                                paginationHtml += '<span class="active">' + p + '</span>';
                            } else {
                                paginationHtml += '<a href="?action=get_orders&start_date=' + (startDate || '') + '&end_date=' + (endDate || '') + '&page=' + p + '">' + p + '</a>';
                            }
                        }
                        if (data.currentPage < data.totalPages) {
                            paginationHtml += '<a href="?action=get_orders&start_date=' + (startDate || '') + '&end_date=' + (endDate || '') + '&page=' + (data.currentPage + 1) + '">Next ›</a>';
                            paginationHtml += '<a href="?action=get_orders&start_date=' + (startDate || '') + '&end_date=' + (endDate || '') + '&page=' + data.totalPages + '">Last »</a>';
                        }
                    }
                    document.getElementById('paginationContainer').innerHTML = paginationHtml;

                    // Update URL
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
                    console.error('Filter error:', err);
                    document.getElementById('ordersTableBody').innerHTML = '<tr><td colspan="6" style="text-align:center;color:#c33;">Error: ' + err.message + '</td></tr>';
                });
        });
    </script>

    <?php include 'footer.php'; ?>

</body>

</html>