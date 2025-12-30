<?php
session_start();
require_once "../shared/dbconnect.php";

/*  AUTH CHECK  */
if (!isset($_SESSION['user_id'])) {
    header("Location: loginsignup/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/*  UPDATE CART QUANTITIES */
if (isset($_POST['update_cart']) && isset($_POST['qty']) && is_array($_POST['qty'])) {
    $stockError = false;

    foreach ($_POST['qty'] as $cart_id => $requested_qty) {
        $cart_id = intval($cart_id);
        $requested_qty = max(1, intval($requested_qty)); // Ensure minimum 1

        // 1. Get product_id and size from cart
        $stmt = $conn->prepare("SELECT product_id, jersey_size FROM cart_items WHERE id=? AND user_id=? LIMIT 1");
        $stmt->bind_param("ii", $cart_id, $user_id);
        $stmt->execute();
        $cart = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$cart) continue;

        // 2. Get available stock
        $stmt = $conn->prepare("SELECT stock FROM product_sizes WHERE product_id=? AND size=? LIMIT 1");
        $stmt->bind_param("is", $cart['product_id'], $cart['jersey_size']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) continue;

        $available_stock = intval($row['stock']);

        // 3. Enforce stock limit
        if ($requested_qty > $available_stock) {
            $stockError = true;
            continue; // Skip updating this item
        }

        // 4. Update cart quantity
        $stmt = $conn->prepare("UPDATE cart_items SET quantity=? WHERE id=? AND user_id=?");
        $stmt->bind_param("iii", $requested_qty, $cart_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    // 5. Alert message
    if ($stockError) {
        $_SESSION['alert'] = "One or more items were not updated because requested quantity exceeds available stock.";
        $_SESSION['alert_type'] = "danger";
    } else {
        $_SESSION['alert'] = "Cart updated successfully.";
        $_SESSION['alert_type'] = "success";
    }
}

header("Location: displaycart.php");
exit();
