<?php
// orders page ma bayeko order status ra payment status toggle garne button haru lai update garne code

session_start();
require_once("../shared/dbconnect.php");
require_once("../shared/commonlinks.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

/* Current page name for active sidebar highlighting */
$currentPage = basename($_SERVER['PHP_SELF']);

/* Handle filters from search form */
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$orderStatus = $_GET['order_status'] ?? '';

$where = [];
$params = [];
$types = '';

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

if ($orderStatus && in_array($orderStatus, ['Pending', 'Delivered'])) {
    $where[] = "o.order_status = ?";
    $params[] = $orderStatus;
    $types .= 's';
}

/* Build SQL */
$sql = "SELECT o.order_id, o.user_id, o.name, o.location, o.grand_total, 
               o.payment_option, o.payment_status, o.order_status, o.transaction_id, o.order_date,
               COUNT(oi.item_id) AS total_items
        FROM orders o
        LEFT JOIN order_items oi ON o.order_id = oi.order_id";

if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

$sql .= " GROUP BY o.order_id
          ORDER BY o.order_date DESC";

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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Orders | Admin Panel</title>
    <style>
        .admin-content {
            margin-top: 0 !important;
            border-top: 4px solid black;
            background-color: #f2f2f2;
        }

        .table-header {
            background: #00796b;
            color: #fff;
        }

        .badge-status {
            font-size: 0.85rem;
        }

        .bulk-actions {
            margin-bottom: 15px;
        }

        .modal-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }

        .search-form {
            margin-bottom: 15px;
        }

        .search-form input,
        .search-form select {
            padding: 5px 8px;
            margin-right: 8px;
        }
    </style>
</head>

<body>

    <?php include_once "header.php"; ?>

    <div class="admin-content">
        <h3 class="mb-3">All Jersey orders:</h3>

        <!-- Search / Filter Form -->
        <form method="get" class="search-form">
            <label>Start Date: <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>"></label>
            <label>End Date: <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>"></label>
            <label>Status:
                <select name="order_status">
                    <option value="">All</option>
                    <option value="Pending" <?= $orderStatus == 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Delivered" <?= $orderStatus == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                </select>
            </label>
            <button type="submit" class="btn btn-sm btn-primary">Search</button>
            <a href="orders.php" class="btn btn-sm btn-secondary">Reset</a>
            <!-- Link to user orders summary (preserves date filters) -->
            <a href="user_orders.php?start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>" class="btn btn-sm btn-info">View Users Summary</a>
        </form>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Location</th>
                    <th>Items</th>
                    <th>Grand Total</th>
                    <th>Payment</th>
                    <th>Payment Status</th>
                    <th>Order Status</th>
                    <th>Transaction ID</th>
                    <th>Date</th>
                    <th>Action</th>
                    <th>Invoice</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($res->num_rows === 0): ?>
                    <tr>
                        <td colspan="12" class="text-center text-muted">No orders found</td>
                    </tr>
                <?php else: ?>
                    <?php while ($row = $res->fetch_assoc()): ?>
                        <tr id="orderRow<?= $row['order_id'] ?>">
                            <td>
                                <?= $row['order_id'] ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['name']) ?> (ID:
                                <?= $row['user_id'] ?>)
                            </td>
                            <td>
                                <?= htmlspecialchars($row['location']) ?>
                            </td>
                            <td>
                                <?= $row['total_items'] ?>
                            </td>
                            <td>Rs.
                                <?= number_format($row['grand_total']) ?>
                            </td>
                            <td>
                                <?= $row['payment_option'] ?>
                            </td>


                            <td>
                                <?php if ($row['payment_status'] === 'Completed'): ?>
                                    <button class="btn btn-sm btn-success toggle-status" data-id="<?= $row['order_id'] ?>"
                                        data-type="payment_status" data-value="Completed">Paid</button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-warning text-dark toggle-status" data-id="<?= $row['order_id'] ?>"
                                        data-type="payment_status" data-value="Pending">Pending</button>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($row['order_status'] === 'Delivered'): ?>
                                    <button class="btn btn-sm btn-success toggle-status" data-id="<?= $row['order_id'] ?>"
                                        data-type="order_status" data-value="Delivered">Delivered</button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-warning text-dark toggle-status" data-id="<?= $row['order_id'] ?>"
                                        data-type="order_status" data-value="Pending">Pending</button>
                                <?php endif; ?>
                            </td>


                            <td>
                                <?= $row['transaction_id'] ?: '-' ?>
                            </td>
                            <td>
                                <?= $row['order_date'] ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary view-items" data-order="<?= $row['order_id'] ?>">View
                                    Items</button>
                                <button class="btn btn-sm btn-danger delete-order mt-1"
                                    data-id="<?= $row['order_id'] ?>">Delete</button>

                            </td>
                            <td>
                                <a href="download_invoice.php?order_id=<?= $row['order_id'] ?>" target="_blank"
                                    class="btn btn-sm btn-dark">Download Invoice</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>

    </div>

    <!-- Modal for Order Items -->
    <div class="modal fade" id="itemsModal" tabindex="-1" aria-labelledby="itemsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="itemsModalLabel">Order Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="itemsModalBody">
                    <!-- Items will be loaded here via JS -->
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.view-items').forEach(btn => {
            btn.addEventListener('click', function () {
                const orderId = this.dataset.order;
                fetch('order_items_ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'order_id=' + orderId
                })
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('itemsModalBody').innerHTML = html;
                        const itemsModal = new bootstrap.Modal(document.getElementById('itemsModal'));
                        itemsModal.show();
                    });
            });
        });



        document.querySelectorAll('.toggle-status').forEach(btn => {
            btn.addEventListener('click', function () {
                const orderId = this.dataset.id;
                const column = this.dataset.type;
                const newValue = this.dataset.value;

                fetch('update_order_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'order_id=' + orderId + '&column=' + column + '&value=' + newValue
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Update button style dynamically
                            if (column === 'order_status') {
                                this.className = data.value === 'Delivered' ? 'btn btn-sm btn-success toggle-status' : 'btn btn-sm btn-warning text-dark toggle-status';
                                this.textContent = data.value;
                                this.dataset.value = data.value === 'Delivered' ? 'Pending' : 'Delivered';
                            } else if (column === 'payment_status') {
                                this.className = data.value === 'Completed' ? 'btn btn-sm btn-success toggle-status' : 'btn btn-sm btn-warning text-dark toggle-status';
                                this.textContent = data.value === 'Completed' ? 'Paid' : 'Pending';
                                this.dataset.value = data.value === 'Completed' ? 'Pending' : 'Completed';
                            }
                        } else {
                            alert('Update failed!');
                        }
                    });
            });
        });

        //for confirming delete order
        document.querySelectorAll('.delete-order').forEach(btn => {
            btn.addEventListener('click', function () {
                const orderId = this.dataset.id;
                if (confirm("Are you sure you want to delete this order? This action cannot be undone.")) {
                    fetch('delete_order.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'order_id=' + orderId
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('orderRow' + orderId).remove();
                            } else {
                                alert('Failed to delete order.');
                            }
                        });
                }
            });
        });

    </script>


</body>

</html>