<?php
session_start();
require_once "../shared/dbconnect.php";

// must login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['alert'] = "Please login to add items to cart.";
    $_SESSION['alert_type'] = "warning";
    header("Location: loginsignup/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// GET or POST
$product_id = intval($_GET['id'] ?? $_POST['product_id'] ?? 0);
$jersey_size = trim($_GET['size'] ?? $_POST['size'] ?? '');
$quantity = intval($_GET['qty'] ?? $_POST['quantity'] ?? 1);

// customize modal fields (may be empty)
$quality = trim($_POST['type'] ?? '');
$print_name = trim($_POST['print_name'] ?? '');
$print_number = trim($_POST['print_number'] ?? '');
$final_price = isset($_POST['final_price']) ? floatval($_POST['final_price']) : null;

// ⭐ DEFAULT QUALITY WHEN CUSTOMIZE IS NOT USED
if ($quality === '') {
    $quality = "first_copy";
}

if (!$product_id) {
    $_SESSION['alert'] = "Invalid product.";
    $_SESSION['alert_type'] = "danger";
    header("Location: jersey.php");
    exit();
}

if ($jersey_size === '') {
    $_SESSION['alert'] = "Please select a size.";
    $_SESSION['alert_type'] = "warning";
    header("Location: view_jersey.php?id=" . $product_id);
    exit();
}

if ($quantity < 1)
    $quantity = 1;

// get product info
$stmtp = $conn->prepare("SELECT j_name, price, discount, category, image, shipping 
                         FROM products WHERE id=? LIMIT 1");
$stmtp->bind_param("i", $product_id);
$stmtp->execute();
$p = $stmtp->get_result()->fetch_assoc();
$stmtp->close();

$pname = $p['j_name'];
$base_price = floatval($p['price']);
$discount = floatval($p['discount']);
$category = $p['category'];
$image = $p['image'];
$shipping = intval($p['shipping']);

// generate price if not from modal
if ($final_price === null) {
    if ($discount > 0) {
        $final_price = round($base_price - ($base_price * $discount / 100));
    } else {
        $final_price = $base_price;
    }
}

$print_cost = 0;

// check if same item exists 
$check = $conn->prepare("
    SELECT id, quantity FROM cart_items 
    WHERE user_id=? AND product_id=? AND jersey_size=? AND quality=? 
          AND print_name=? AND print_number=?
    LIMIT 1
");
$check->bind_param(
    "iissss",
    $user_id,
    $product_id,
    $jersey_size,
    $quality,
    $print_name,
    $print_number
);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    // update qty
    $row = $res->fetch_assoc();
    $new_q = $row['quantity'] + $quantity;

    $u = $conn->prepare("UPDATE cart_items SET quantity=?, final_price=? WHERE id=?");
    $u->bind_param("idi", $new_q, $final_price, $row['id']);
    $u->execute();
    $u->close();

} else {
    // INSERT
    $ins = $conn->prepare("
        INSERT INTO cart_items
        (user_id, product_id, pname, category, jersey_size, quality, base_price, 
         print_name, print_number, print_cost, quantity, final_price, shipping, image, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $ins->bind_param(
        "iisssssdsdiids",
        $user_id,
        $product_id,
        $pname,
        $category,
        $jersey_size,
        $quality,
        $base_price,
        $print_name,
        $print_number,
        $print_cost,
        $quantity,
        $final_price,
        $shipping,
        $image
    );

    if (!$ins->execute()) {
        $_SESSION['alert'] = "Database error: " . $ins->error;
        $_SESSION['alert_type'] = "danger";
        $ins->close();
        header("Location: view_jersey.php?id=" . $product_id);
        exit();
    }

    $ins->close();
}

$check->close();

// success
$_SESSION['alert'] = "Item added to cart.";
$_SESSION['alert_type'] = "success";
header("Location: displaycart.php");
exit();
