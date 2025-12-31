<?php
session_start();
include("../shared/dbconnect.php");
include("../shared/commonlinks.php");
include("header.php");

if (!isset($_SESSION['user_id'])) {
    echo "<script>
            alert('Please login to view your cart.');
            window.location.href='loginsignup/login.php';
          </script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items
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
            padding: 6px 10px;
            font-size: 16px;
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
            padding: 8px 14px;
        }

        .btn-remove:hover {
            background-color: #b30000;
        }
    </style>
</head>

<body>
    <div class="cart-container">
        <h2>Shopping Cart</h2>

        <?php if (mysqli_num_rows($result) > 0): ?>

            <?php
            $grand_total = 0;
            $shipping_cost = 150;
            $has_out_of_stock = false;
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
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <?php while ($row = mysqli_fetch_assoc($result)): ?>

                    <?php
                    $price_after_discount = floatval($row['price_after_discount']);
                    $quantity = intval($row['quantity']);
                    $total = round($price_after_discount * $quantity, 2);
                    $grand_total += $total;

                    // Fetch stock for product & size
                    $stock = 0;
                    $res = mysqli_query(
                        $conn,
                        "SELECT stock FROM product_sizes 
                        WHERE product_id = {$row['product_id']} 
                        AND size = '{$row['jersey_size']}' 
                        LIMIT 1"
                    );

                    if ($res && mysqli_num_rows($res) > 0) {
                        $r = mysqli_fetch_assoc($res);
                        $stock = intval($r['stock']);
                    }

                    if ($stock == 0) {
                        $has_out_of_stock = true;
                    }
                    ?>

                    <tr>
                        <td><img src="../shared/products/<?php echo $row['image']; ?>" class="img-thumb"></td>
                        <td><?php echo $row['pname']; ?></td>
                        <td><?php echo $row['category']; ?></td>
                        <td><?php echo $row['jersey_size']; ?></td>
                        <td><?php echo $row['quality']; ?></td>
                        <td>Rs. <?php echo number_format($row['base_price']); ?></td>
                        <td><?php echo $row['print_name']; ?></td>
                        <td><?php echo $row['print_number']; ?></td>
                        <td><?php echo $row['print_cost'] > 0 ? 'Rs. ' . $row['print_cost'] : ''; ?></td>
                        <td><?php echo $row['discount'] ? $row['discount'] . '%' : ''; ?></td>

                        <td>
                            <form method="POST" action="update.php">
                                <input type="number" name="qty[<?php echo $row['id']; ?>]" value="<?php echo $quantity; ?>"
                                    min="1" max="<?php echo $stock; ?>">
                                <button type="submit" name="update_cart" class="btn" <?php echo $stock == 0 ? 'disabled' : ''; ?>>
                                    Update
                                </button>
                            </form>
                        </td>

                        <td>Rs. <?php echo number_format($total); ?></td>

                        <td>
                            <?php if ($stock == 0): ?>
                                <span style="color:red;font-weight:bold;">Out of stock </span>
                                <span style="color:green;font-weight:semi-bold;"> (contact admin) </span>


                            <?php else: ?>
                                <span style="color:green;">Available</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <a href="remove.php?pid=<?php echo $row['product_id']; ?>&quality=<?php echo $row['quality']; ?>&size=<?php echo $row['jersey_size']; ?>"
                                class="btn btn-remove" onclick="return confirm('Remove this item from cart?');">
                                Remove
                            </a>
                        </td>
                    </tr>

                <?php endwhile; ?>

                <tr>
                    <td colspan="12"><b>Shipping</b></td>
                    <td colspan="2">Rs. <?php echo number_format($shipping_cost); ?></td>
                </tr>

                <?php $grand_total += $shipping_cost; ?>

                <tr style="color:green;">
                    <td colspan="12"><b>Grand Total</b></td>
                    <td colspan="2"><b>Rs. <?php echo number_format($grand_total); ?></b></td>
                </tr>

            </table>

            <br>

            <form method="POST" action="order_form.php">
                <button type="submit" name="order" class="btn" style="background-color:teal;" <?php echo $has_out_of_stock ? 'disabled' : ''; ?>>
                    Order Now
                </button>
            </form>

            <a href="jersey.php" class="btn">Continue Shopping</a>

        <?php else: ?>
            <p>Your cart is empty. <a href="jersey.php">Start Shopping</a></p>
        <?php endif; ?>

    </div>
</body>

</html>