<?php
session_start();
require_once "../shared/dbconnect.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: loginsignup/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if all parameters are provided
if (isset($_GET['pid']) && isset($_GET['size']) && isset($_GET['quality'])) {
    $product_id = intval($_GET['pid']);
    $jersey_size = trim($_GET['size']);
    $quality = trim($_GET['quality']);

    // Delete the exact item
    $stmt = $conn->prepare("
        DELETE FROM cart_items 
        WHERE user_id = ? AND product_id = ? AND jersey_size = ? AND quality = ?
    ");
    $stmt->bind_param("iiss", $user_id, $product_id, $jersey_size, $quality);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: displaycart.php?removed=1");
        exit();
    } else {
        echo "Failed to remove item: " . $conn->error;
        $stmt->close();
    }
} else {
    header("Location: displaycart.php");
    exit();
}
?>
