<?php
session_start();
require_once "../shared/dbconnect.php";

// user must login
if (!isset($_SESSION['user_id'])) {
    echo "<script>
            alert('Please login to add items to your cart.');
            window.location.href='loginsignup/login.php';
          </script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// check GET parameters
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $qty = isset($_GET['qty']) ? intval($_GET['qty']) : 1;
    $size = isset($_GET['size']) && $_GET['size'] !== '' ? $_GET['size'] : 'Default';

    // fetch product details
    $product_res = mysqli_query($conn, "SELECT * FROM products WHERE id = $product_id LIMIT 1");

    if (mysqli_num_rows($product_res) > 0) {
        $product = mysqli_fetch_assoc($product_res);

        // check if already in cart
        $cart_check = mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id = $user_id AND product_id = $product_id AND jersey_type = '$size'");

        if (mysqli_num_rows($cart_check) > 0) {
            // update quantity
            mysqli_query($conn, "UPDATE cart_items SET quantity = quantity + $qty WHERE user_id = $user_id AND product_id = $product_id AND jersey_type = '$size'");
        } else {
            // insert new cart item
            $pname = $product['j_name'];
            $category = $product['category'];
            $base_price = $product['price'];
            $discount = $product['discount'];
            $final_price = $base_price;
            if ($discount > 0) $final_price = $base_price - ($base_price * $discount / 100);
            $shipping = 0; // default shipping
            $quality = 'Standard';
            $print_name = '';
            $print_number = 0;
            $print_cost = 0;
            $image = $product['image'];

            $insert_sql = "INSERT INTO cart_items 
                (user_id, product_id, pname, category, jersey_type, quality, base_price, print_name, print_number, print_cost, quantity, final_price, shipping, image, created_at)
                VALUES 
                ($user_id, $product_id, '$pname', '$category', '$size', '$quality', $base_price, '$print_name', $print_number, $print_cost, $qty, $final_price, $shipping, '$image', NOW())";

            mysqli_query($conn, $insert_sql);
        }

        // redirect to display cart
        header("Location: displaycart.php");
        exit();
    } else {
        echo "Product not found.";
    }
} else {
    echo "Invalid request.";
}
