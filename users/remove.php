<?php
session_start();
require_once "../shared/dbconnect.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: loginsignup/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if product ID is provided
if (isset($_GET['pid'])) {
    $product_id = intval($_GET['pid']); // sanitize input

    // Prepare and execute delete query
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: displaycart.php");
        exit();
    } else {
        $stmt->close();
        echo "Failed to remove item from cart: " . $conn->error;
    }
} else {
    // If no product ID, redirect to cart
    header("Location: displaycart.php");
    exit();
}
?>
