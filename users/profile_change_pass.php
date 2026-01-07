<?php
session_start();
require_once "../shared/dbconnect.php";

// Check login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle profile update
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $security_question = trim($_POST['security_question']);
    $security_answer = trim($_POST['security_answer']);

    $errors = [];

    /* ---------- VALIDATION ---------- */

    // Full name: letters only, spaces allowed between words
    if (!preg_match('/^[A-Za-z]+( [A-Za-z]+)*$/', $name)) {
        $errors[] = "Name should only contain letters, and spaces are allowed between words but not at the start.";
    }

    // Username: allowed chars + minimum length 4
    if (!preg_match('/^[a-zA-Z0-9_@]+$/', $username) || strlen($username) < 4) {
        $errors[] = "Username must have atleast 4 character and can only contain letters, numbers, underscores, and the @ symbol";
    }

    // Email
    if (!preg_match('/^[a-z0-9.]+@[a-z0-9.-]+\.[a-z]{2,}$/', $email)) {
        $errors[] = "Email must contain only letters (a-z), numbers (0-9), and periods (.) before the @, and must have a valid domain.";
    }

    // Phone
    if (strlen(trim($phone)) < 10) {
        $errors[] = "Phone number must be at least 10 digits";
    }

    // Security question
    if ($security_question === '') {
        $errors[] = "Security question cannot be empty.";
    }

    // Security answer
    if (trim($security_answer) === '') {
        $errors[] = "Security answer cannot be empty.";
    }

    /* ---------- GET CURRENT DATA ---------- */
    $stmt = $conn->prepare("SELECT name, username, email, phone, security_question, security_answer FROM user_creden WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Check if anything actually changed
    if ($current['name'] === $name && 
        $current['username'] === $username && 
        $current['email'] === $email && 
        $current['phone'] === $phone && 
        $current['security_question'] === $security_question && 
        $current['security_answer'] === $security_answer) {
        echo "No changes detected";
        exit();
    }

    /* ---------- UNIQUE USERNAME CHECK ---------- */
    if ($username !== $current['username']) {
        $stmt = $conn->prepare("SELECT id FROM user_creden WHERE username = ? AND id != ? LIMIT 1");
        $stmt->bind_param("si", $username, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Username already exists. Please choose another.";
        }
        $stmt->close();
    }

    /* ---------- UNIQUE EMAIL CHECK ---------- */
    if ($email !== $current['email']) {
        $stmt = $conn->prepare("SELECT id FROM user_creden WHERE email = ? AND id != ? LIMIT 1");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Email already exists. Please choose another.";
        }
        $stmt->close();
    }

    /* ---------- HANDLE ERRORS ---------- */
    if (!empty($errors)) {
        echo implode(" ", $errors);
        exit();
    }

    /* ---------- UPDATE PROFILE ---------- */
    $stmt = $conn->prepare("UPDATE user_creden SET name=?, username=?, email=?, phone=?, security_question=?, security_answer=? WHERE id=?");
    $stmt->bind_param("ssssssi", $name, $username, $email, $phone, $security_question, $security_answer, $user_id);

    if ($stmt->execute()) {
        $_SESSION['username'] = $username;
        echo "success";
    } else {
        echo "Failed to update profile.";
    }

    $stmt->close();
    exit();
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question'])) {
    $question = trim($_POST['question']);
    $answer = trim($_POST['answer']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Get stored security answer
    $stmt = $conn->prepare("SELECT security_answer FROM user_creden WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$result) {
        echo "User not found";
        exit();
    }

    if ($result['security_answer'] !== $answer) {
        echo "Security answer is incorrect";
        exit();
    }

    if ($new_password !== $confirm_password) {
        echo "Passwords do not match";
        exit();
    }

    if (strlen($new_password) < 6) {
        echo "Password must be at least 6 characters";
        exit();
    }

    // Hash and update password
    $hashed_pass = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE user_creden SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_pass, $user_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Failed to change password";
    }

    $stmt->close();
    exit();
}

// Handle get orders (AJAX for date filtering)
if (isset($_GET['action']) && $_GET['action'] === 'get_orders') {
    header('Content-Type: application/json');
    
    $startDate = $_GET['start_date'] ?? '';
    $endDate = $_GET['end_date'] ?? '';

    // Validate date inputs (YYYY-MM-DD)
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

    // Build table HTML
    $tableHtml = '';
    if ($orders_res->num_rows === 0) {
        $tableHtml = '<tr><td colspan="6" class="text-center text-muted">No orders found for selected filters</td></tr>';
    } else {
        $sn = 1;
        while ($o = $orders_res->fetch_assoc()) {
            $tableHtml .= '<tr id="orderRow' . $o['order_id'] . '">';
            $tableHtml .= '<td>' . $sn++ . '</td>';
            $tableHtml .= '<td>Order #' . $o['order_id'] . ' <br><a href="#" class="btn btn-sm btn-outline-info view-order" data-order="' . $o['order_id'] . '" style="margin-top:6px;display:inline-block">View</a></td>';
            $tableHtml .= '<td>' . $o['total_items'] . ' item' . ($o['total_items']>1?'s':'') . (!empty($o['first_item_name']) ? ': ' . htmlspecialchars($o['first_item_name']) : '') . '</td>';
            $tableHtml .= '<td>' . htmlspecialchars($o['payment_status']) . '</td>';
            $tableHtml .= '<td>' . htmlspecialchars($o['order_status']) . '</td>';
            $tableHtml .= '<td>' . date('Y-m-d H:i', strtotime($o['order_date'])) . '</td>';
            $tableHtml .= '</tr>';
        }
    }

    // Return JSON response
    echo json_encode([
        'totalOrders' => $totalOrders,
        'totalAmount' => number_format($totalAmount),
        'tableHtml' => $tableHtml
    ]);
    exit();
}

// Handle get order details (AJAX for modal)
if (isset($_POST['action']) && $_POST['action'] === 'get_order_details') {
    $order_id = intval($_POST['order_id'] ?? 0);
    if ($order_id <= 0) {
        http_response_code(400);
        echo "Invalid order id";
        exit();
    }

    // Verify order belongs to user
    $stmt = $conn->prepare("SELECT order_id, name, location, grand_total, payment_option, payment_status, order_status, transaction_id, order_date FROM orders WHERE order_id = ? AND user_id = ? LIMIT 1");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) {
        http_response_code(404);
        echo "Order not found";
        exit();
    }

    // Fetch items (include shipping and base_price to compute discount)
    $stmt = $conn->prepare("SELECT pname, category, jersey_size, quality, quantity, base_price, print_name, print_number, print_cost, final_price, subtotal, shipping, product_image FROM order_items WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Compute shipping total
    $shipping_total = 0;
    foreach ($items as $it) {
        $shipping_total += floatval($it['shipping'] ?? 0);
    }

    // Output HTML for modal body
    $firstImage = '';
    if (!empty($items) && !empty($items[0]['product_image'])) {
        $candidate = __DIR__ . '/../shared/products/' . $items[0]['product_image'];
        if (file_exists($candidate)) {
            $firstImage = $items[0]['product_image'];
        }
    }

    ?>
    <div style="display:flex;gap:12px;margin-bottom:16px">
        <div style="flex:0 0 120px">
            <?php if (!empty($firstImage)): ?>
                <img src="../shared/products/<?= htmlspecialchars($firstImage) ?>" alt="Jersey" style="width:120px;height:120px;object-fit:contain;background:#f7f7f7;padding:4px;border-radius:4px">
            <?php else: ?>
                <div style="width:120px;height:120px;background:#f0f0f0;border-radius:4px;display:flex;align-items:center;justify-content:center">No image</div>
            <?php endif; ?>
        </div>
        <div style="flex:1">
            <p><strong>Order #<?= $order['order_id'] ?></strong></p>
            <p>Name: <?= htmlspecialchars($order['name']) ?></p>
            <p>Location: <?= htmlspecialchars($order['location']) ?></p>
            <p>Payment: <?= htmlspecialchars($order['payment_status']) ?> | Status: <?= htmlspecialchars($order['order_status']) ?></p>
            <p>Date: <?= date('Y-m-d H:i', strtotime($order['order_date'])) ?></p>
            <?php if ($order['transaction_id']): ?>
                <p>Transaction ID: <?= htmlspecialchars($order['transaction_id']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <h5>Items</h5>
    <div style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse;margin:8px 0">
        <thead>
            <tr style="background:#f4f9f8;border-bottom:2px solid #ddd">
                <th style="padding:8px;text-align:left">Jersey</th>
                <th style="padding:8px;text-align:left">Size</th>
                <th style="padding:8px;text-align:center">Qty</th>
                <th style="padding:8px;text-align:right">Price</th>
                <th style="padding:8px;text-align:right">Discount</th>
                <th style="padding:8px;text-align:right">Shipping</th>
                <th style="padding:8px;text-align:right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr style="border-bottom:1px solid #eee">
                    <td style="padding:8px"><?= htmlspecialchars($item['pname']) ?><?= $item['print_name'] ? ' (' . htmlspecialchars($item['print_name']) . '#' . htmlspecialchars($item['print_number']) . ')' : '' ?></td>
                    <td style="padding:8px"><?= htmlspecialchars($item['jersey_size']) ?></td>
                    <td style="padding:8px;text-align:center"><?= $item['quantity'] ?></td>
                    <td style="padding:8px;text-align:right">Rs. <?= number_format(floatval($item['final_price'])) ?></td>
                    <td style="padding:8px;text-align:right">Rs. <?= number_format(max(0, floatval($item['base_price']) - floatval($item['final_price']))) ?></td>
                    <td style="padding:8px;text-align:right">Rs. <?= number_format(floatval($item['shipping'])) ?></td>
                    <td style="padding:8px;text-align:right">Rs. <?= number_format(floatval($item['subtotal'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <div style="margin-top:12px;padding:12px;background:#f9f9f9;border-radius:4px">
        <div style="display:flex;justify-content:space-between;margin-bottom:6px">
            <strong>Subtotal:</strong>
            <span>Rs. <?= number_format(floatval($order['grand_total']) - $shipping_total) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:6px">
            <strong>Shipping:</strong>
            <span>Rs. <?= number_format($shipping_total) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;border-top:1px solid #ddd;padding-top:6px;font-size:1.1em;font-weight:bold">
            <strong>Total:</strong>
            <span>Rs. <?= number_format(floatval($order['grand_total'])) ?></span>
        </div>
    </div>
    <?php
    exit();
}
