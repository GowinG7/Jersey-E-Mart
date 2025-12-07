<?php
session_start();
include("../shared/dbconnect.php");
include("../shared/commonlinks.php");
include("header.php");

// user must login
if (!isset($_SESSION['user_id'])) {
    echo "<script>
            alert('Please login to view your cart.');
            window.location.href='loginsignup/login.php';
          </script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Update quantity
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['update_quantity'])) {
    $pid = $_POST['pid'];
    $quantity = max(1, intval($_POST['quantity']));
    mysqli_query($conn, "UPDATE cart_items SET quantity = $quantity WHERE user_id = $user_id AND product_id = $pid");
}

// fetch cart items
$query = "SELECT * FROM cart_items WHERE user_id = $user_id";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>

<head>
    <title>My Cart - Jersey E-mart</title>
    <style>
        body {
            font-family: Arial;
            background-color: #e9f8f6;
            margin: 0;
            padding: 0;
        }

        .cart-container {
            max-width: 1100px;
            margin: 30px auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 10px;
            text-align: center;
        }

        th {
            color: blue;
        }

        .img-thumb {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }

        .btn {
            padding: 8px 14px;
            background-color: #45a049;
            color: white;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #3d8b40;
        }

        .btn-remove {
            background-color: red;
            color: white;
            border: none;
            padding: 8px 14px;
            cursor: pointer;
            text-decoration: none;
            transition: 0.2s;
        }

        .btn-remove:hover {
            background-color: #b30000;
        }
    </style>
</head>

<body>
    <div class="cart-container">
        <h2>Shopping Cart</h2>

        <?php if (mysqli_num_rows($result) > 0):
            $grand_total = 0;
            $shipping_cost = 150; // shipping cost per order
            ?>
            <table>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Jersey Size</th>
                    <th>Quality</th>
                    <th>Base Price</th>
                    <th>Print Name</th>
                    <th>Print Number</th>
                    <th>Print Cost</th>
                    <th>Discount</th>
                    <th>Quantity</th>
                    <th>Price after discount</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($result)):
                    $price_after_discount = floatval($row['price_after_discount']);
                    $quantity = intval($row['quantity']);
                    $total = round($price_after_discount * $quantity, 2);
                    $grand_total += $total;
                    ?>
                    <tr>
                        <td><img src="../shared/products/<?php echo $row['image']; ?>" class="img-thumb"></td>
                        <td><?php echo $row['pname']; ?></td>
                        <td><?php echo $row['category']; ?></td>
                        <td><?php echo $row['jersey_size']; ?></td>
                        <td><?php echo $row['quality']; ?></td>
                        <td>Rs. <?php echo number_format(intval($row['base_price'])); ?></td>
                        <td><?php echo $row['print_name'] ?: '-'; ?></td>
                        <td><?php echo $row['print_number'] ?: '-'; ?></td>
                        <td><?php echo $row['print_cost'] > 0 ? 'Rs. ' . number_format(intval($row['print_cost'])) : '-'; ?></td>
                        <td><?php echo (!empty($row['discount']) && intval($row['discount']) > 0) ? number_format(floatval($row['discount'])) . '%' : '-'; ?></td>
                        <td>
                            <form method="POST" action="displaycart.php">
                                <input type="number" name="quantity" value="<?php echo $row['quantity']; ?>" min="1">
                                <input type="hidden" name="pid" value="<?php echo $row['product_id']; ?>">
                                <button type="submit" name="update_quantity" class="btn">Update</button>
                            </form>
                        </td>
                        <td>Rs. <?php echo number_format(intval($total)); ?></td>
                        <td>
                            <a href="remove.php?pid=<?php echo $row['product_id']; ?>&quality=<?php echo $row['quality']; ?>&size=<?php echo $row['jersey_size']; ?>"
                                class="btn btn-remove" onclick="return confirm('Are you sure to remove this item from cart?');">
                                Remove
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <tr>
                    <td colspan="11"><b>Shipping</b></td>
                    <td colspan="2">Rs. <?php echo number_format(intval($shipping_cost)); ?></td>
                </tr>
                <?php $grand_total += $shipping_cost; ?>
                <tr style="color:green;">
                    <td colspan="11"><b>Grand Total</b></td>
                    <td colspan="2"><b>Rs. <?php echo number_format(intval($grand_total )); ?></b></td>
                </tr>
            </table>

            <br>
            <form method="POST" action="order_form.php">
                <button type="submit" name="order" class="btn" style="background-color:teal;">Order Now</button>
            </form>
            
            <a href="jersey.php" class="btn">Continue Shopping</a>

        <?php else: ?>
            <p>Your cart is empty. <a href="jersey.php">Start Shopping</a></p>
        <?php endif; ?>
    </div>
</body>

</html>