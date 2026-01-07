<!-- User Profile management -->
<?php
session_start();
require_once "../shared/dbconnect.php";

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: loginsignup/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

//FETCH USER DATA 
$stmt = $conn->prepare("
    SELECT name, username, email, phone, security_question, security_answer, is_verified, created_at
    FROM user_creden
    WHERE id = ? LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ----- Orders (simplified) -----
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

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

// Summary
$summarySql = "SELECT COUNT(*) AS cnt, COALESCE(SUM(grand_total),0) AS total_amount FROM orders $whereSql";
$res = $conn->query($summarySql);
$summary = $res ? $res->fetch_assoc() : ['cnt' => 0, 'total_amount' => 0];

$totalOrders = (int) $summary['cnt'];
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
    <title>User Profile</title>
    <style>
        /* Minimal profile styles */
        body {
            background: #e0f4f2;
            font-family: Segoe UI, SegoeUI, Arial, sans-serif;
            color: #234;
        }

        .profile-card {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            border-radius: 10px;
            background: #fff
        }

        .row {
            display: flex;
            gap: 12px;
            margin-bottom: 12px
        }

        .label {
            width: 35%;
            font-weight: 600
        }

        .value {
            flex: 1
        }

        input,
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #cfe8e5;
            border-radius: 6px
        }

        .msg {
            font-weight: 600;
            text-align: center;
            margin-bottom: 12px
        }

        .success {
            color: #1a7f37
        }

        .error {
            color: #b42318
        }

        button {
            background: #1c6059;
            color: #fff;
            padding: 8px 14px;
            border-radius: 8px;
            border: 0;
            cursor: pointer
        }

        .change-password {
            background: #c0392b
        }

        .update-profile {
            background: #1c6059
        }

        /* modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .5);
            align-items: center;
            justify-content: center
        }

        .modal-box {
            background: #fff;
            padding: 18px;
            border-radius: 10px;
            width: 340px
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px
        }

        th,
        td {
            padding: 8px;
            border: 1px solid #eee;
            text-align: left
        }
    </style>
</head>

<body style="background-color: #e0f4f2;">

    <?php include_once 'header.php'; ?>

    <div class="profile-card">
       

        <div id="profileMsg" class="msg" style="display:none"></div>

        <form id="profileForm">

            <div class="row">
                <div class="label">Full Name</div>
                <div class="value"><input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>"></div>
            </div>

            <div class="row">
                <div class="label">Username</div>
                <div class="value"><input type="text" name="username"
                        value="<?= htmlspecialchars($user['username']) ?>"></div>
            </div>

            <div class="row">
                <div class="label">Email</div>
                <div class="value"><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                </div>
            </div>

            <div class="row">
                <div class="label">Phone</div>
                <div class="value"><input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
                </div>
            </div>

            <div class="row">
                <div class="label">Security Question</div>
                <div class="value"><input type="text" name="security_question"
                        value="<?= htmlspecialchars($user['security_question']) ?>"></div>
            </div>

            <div class="row">
                <div class="label">Security Answer</div>
                <div class="value"><input type="text" name="security_answer"
                        value="<?= htmlspecialchars($user['security_answer']) ?>"></div>
            </div>

            <div class="row">
                <div class="label">Account Status</div>
                <div class="value status"><?= $user['is_verified'] ? 'Verified' : 'Not Verified' ?></div>
            </div>

            <div class="row">
                <div class="label">Joined On</div>
                <div class="value"><?= date("d M Y", strtotime($user['created_at'])) ?></div>
            </div>

            <div style="text-align:center;margin-top:20px;">
                <button type="submit" name="update_profile" class="update-profile">Update Profile</button>
            </div>

            <div style="text-align:center;margin-top:25px;">
                <button type="button" onclick="openModal()" class="change-password">
                    Change Password
                </button>
            </div>
        </form>
    </div>

    <!-- CHANGE PASSWORD MODAL -->
    <div id="passwordModal" class="modal-overlay">
        <div class="modal-box">
            <h4>Change Password</h4>

            <div id="modalMsg" class="msg error"></div>

            <form id="changePasswordForm">
                <select name="question" required>
                    <option value="">Select Security Question</option>
                    <option value="color">Favourite Color</option>
                    <option value="food">Favourite Food</option>
                    <option value="fruit">Favourite Fruit</option>
                    <option value="pet">Favourite Pet</option>
                    <option value="subject">Favourite Subject</option>
                    <option value="place">Favourite Place</option>
                    <option value="laptop">Favourite Laptop</option>
                </select>

                <input type="text" name="answer" placeholder="Security Answer" required>
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>

                <div class="modal-actions">
                    <button type="submit" class="modal-confirm">Change Password</button>
                    <button type="button" class="modal-cancel" onclick="closeModal()">Close</button>
                </div>
            </form>
        </div>
    </div>

    <!-- USER ORDERS SECTION -->
    <div class="profile-card" style="margin-top:25px;">
        <h3>My Orders</h3>

        <form method="get" class="search-form"
            style="margin-bottom:12px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <label style="display:flex;gap:6px;align-items:center">Start Date: <input type="date" name="start_date"
                    value="<?= htmlspecialchars($startDate) ?>"></label>
            <label style="display:flex;gap:6px;align-items:center">End Date: <input type="date" name="end_date"
                    value="<?= htmlspecialchars($endDate) ?>"></label>
            <div style="margin-left:auto">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="profile.php" class="btn btn-secondary" style="margin-left:8px;">Reset</a>
            </div>
        </form>

        <div
            style="margin-bottom:12px;padding:10px;border-radius:8px;background:#f4f9f8;border:1px solid #e6f3f1;display:flex;gap:12px;align-items:center;">
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
                        <tr>
                            <td colspan="6" class="text-center text-muted">No orders found for selected filters</td>
                        </tr>
                    <?php else: ?>
                        <?php $sn = 1;
                        while ($o = $orders_res->fetch_assoc()): ?>
                            <tr id="orderRow<?= $o['order_id'] ?>">
                                <td><?= $sn++ ?></td>
                                <td>Order #<?= $o['order_id'] ?> <br><a href="#" class="btn btn-sm btn-outline-info view-order"
                                        data-order="<?= $o['order_id'] ?>" style="margin-top:6px;display:inline-block">View</a>
                                </td>
                                <td><?= $o['total_items'] ?>
                                    item<?= $o['total_items'] > 1 ? 's' : '' ?><?= !empty($o['first_item_name']) ? ': ' . htmlspecialchars($o['first_item_name']) : '' ?>
                                </td>
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
        <div class="modal-box" style="max-width:760px;width:95%">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <h4 style="margin:0">Order Details</h4>
                <button onclick="closeOrderModal()" class="modal-cancel">Close</button>
            </div>
            <div id="orderModalBody">Loading...</div>
        </div>
    </div>

    <script src="js/profile.js"></script>
    <script>
        // AJAX Profile Update - No page reload
        document.getElementById('profileForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('update_profile', '1');

            const msgEl = document.getElementById('profileMsg');
            msgEl.style.display = 'none';

            fetch('profile_change_pass.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.text())
                .then(msg => {
                    if (msg.trim() === 'success') {
                        msgEl.className = 'msg success';
                        msgEl.textContent = 'Profile updated successfully.';
                        msgEl.style.display = 'block';

                        // Auto-dismiss after 4 seconds
                        setTimeout(() => {
                            msgEl.style.transition = 'opacity 0.45s ease, max-height 0.45s ease, margin 0.45s ease';
                            msgEl.style.opacity = '0';
                            msgEl.style.maxHeight = '0';
                            msgEl.style.margin = '0';
                            setTimeout(() => {
                                msgEl.style.display = 'none';
                                msgEl.style.transition = '';
                                msgEl.style.opacity = '';
                                msgEl.style.maxHeight = '';
                                msgEl.style.margin = '';
                            }, 500);
                        }, 4000);
                    } else if (msg.trim() === 'No changes detected') {
                        msgEl.className = 'msg';
                        msgEl.style.color = '#856404';
                        msgEl.style.backgroundColor = '#fff3cd';
                        msgEl.style.borderColor = '#ffeeba';
                        msgEl.textContent = 'No changes made to update.';
                        msgEl.style.display = 'block';

                        // Auto-dismiss after 3 seconds
                        setTimeout(() => {
                            msgEl.style.transition = 'opacity 0.45s ease';
                            msgEl.style.opacity = '0';
                            setTimeout(() => {
                                msgEl.style.display = 'none';
                                msgEl.style.transition = '';
                                msgEl.style.opacity = '';
                            }, 500);
                        }, 3000);
                    } else {
                        msgEl.className = 'msg error';
                        msgEl.textContent = msg;
                        msgEl.style.display = 'block';
                    }
                })
                .catch(err => {
                    msgEl.className = 'msg error';
                    msgEl.textContent = 'Failed to update profile. Please try again.';
                    msgEl.style.display = 'block';
                });
        });

        // Order modal logic
        document.addEventListener('click', function (e) {
            if (e.target && e.target.matches('.view-order')) {
                e.preventDefault();
                const id = e.target.dataset.order;
                const body = new URLSearchParams();
                body.append('action', 'get_order_details');
                body.append('order_id', id);
                fetch('profile_change_pass.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString()
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

        function closeOrderModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        // close on overlay click
        document.getElementById('orderModal').addEventListener('click', function (e) { if (e.target === this) closeOrderModal(); });

        // AJAX Date Search - No page reload
        document.querySelector('.search-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);

            fetch('profile_change_pass.php?action=get_orders&' + params.toString())
                .then(res => res.json())
                .then(data => {
                    // Update summary stats
                    document.querySelectorAll('.profile-card')[1].innerHTML = '<h3>My Orders</h3><form method="get" class="search-form" style="margin-bottom:12px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;"><label style="display:flex;gap:6px;align-items:center">Start Date: <input type="date" name="start_date" value="' + (formData.get('start_date') || '') + '"></label><label style="display:flex;gap:6px;align-items:center">End Date: <input type="date" name="end_date" value="' + (formData.get('end_date') || '') + '"></label><div style="margin-left:auto"><button type="submit" class="btn btn-primary">Search</button><a href="profile.php" class="btn btn-secondary" style="margin-left:8px;">Reset</a></div></form><div style="margin-bottom:12px;padding:10px;border-radius:8px;background:#f4f9f8;border:1px solid #e6f3f1;display:flex;gap:12px;align-items:center;"><div><strong>Total orders:</strong> ' + data.totalOrders + '</div><div><strong>Total amount:</strong> Rs. ' + data.totalAmount + '</div></div><div style="overflow-x:auto"><table class="table table-bordered table-striped"><thead class="table-dark"><tr><th>S.N.</th><th>Order No</th><th>Order Items</th><th>Payment Status</th><th>Order Status</th><th>Date</th></tr></thead><tbody>' + data.tableHtml + '</tbody></table></div>';

                    // Update URL without reload
                    const startDate = formData.get('start_date');
                    const endDate = formData.get('end_date');
                    let newUrl = 'profile.php';
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



</body>

</html>