<?php
// update.php
session_start();
require_once "../shared/dbconnect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: loginsignup/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['alert'] = "Item removed from cart.";
    $_SESSION['alert_type'] = "success";
    header("Location: cart.php");
    exit();
}

if (isset($_POST['update_cart']) && isset($_POST['qty']) && is_array($_POST['qty'])) {
    foreach ($_POST['qty'] as $cart_id => $q) {
        $cart_id = intval($cart_id);
        $q = intval($q);
        if ($q < 1) $q = 1;
        // ensure ownership
        $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $q, $cart_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
    $_SESSION['alert'] = "Cart updated.";
    $_SESSION['alert_type'] = "success";
}

header("Location: cart.php");
exit();
