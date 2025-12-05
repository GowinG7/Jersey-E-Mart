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

// Update quantity if form submitted
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['update_quantity'])) {
    $pid = $_POST['pid'];
    $quantity = intval($_POST['quantity']);
    if ($quantity < 1) $quantity = 1;

    mysqli_query($conn, "UPDATE cart_items SET quantity = $quantity WHERE user_id = $user_id AND product_id = $pid");
}

// fetch cart items
$query = "SELECT c.*, p.j_name, p.description, p.category, p.price, p.discount, p.image, p.shipping
          FROM cart_items c
          JOIN products p ON c.product_id = p.id
          WHERE c.user_id = $user_id";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Cart - Jersey E-mart</title>
    <style>
        body { 
            font-family: Arial; 
            margin:0; 
            padding:0; 
        }
        .cart-container { 
            max-width: 1100px;
             margin: 30px auto;
             }
        table {
             width: 100%;
              border-collapse: collapse; 
            }
        th, td {
             border: 1px solid black;
              padding: 10px;
               text-align:center; 
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
            background-color:#45a049; 
            color:white;
             border:none;
              cursor:pointer;
               text-decoration:none;
            }
        .btn:hover { 
            background-color:#3d8b40;
         }
        .btn-remove { 
            background-color:red;
         }
    </style>
</head>
<body>
<div class="cart-container">
    <h2>Shopping Cart</h2>

    <?php if (mysqli_num_rows($result) > 0): 
        $grand_total = 0; ?>
        <table>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Description</th>
                <th>Category</th>
                <th>Price</th>
                <th>Discount</th>
                <th>Shipping</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result)):
                $price = $row['price'];
                $discount = $row['discount'];
                $shipping = $row['shipping'];
                $quantity = $row['quantity'];
                $final_price = $price - ($price * $discount / 100) + $shipping;
                $total = $final_price * $quantity;
                $grand_total += $total;
            ?>
            <tr>
                <td><img src="../shared/products/<?php echo $row['image']; ?>" class="img-thumb"></td>
                <td><?php echo $row['j_name']; ?></td>
                <td><?php echo $row['description']; ?></td>
                <td><?php echo $row['category']; ?></td>
                <td>Rs. <?php echo number_format($price,2); ?></td>
                <td><?php echo $discount>0 ? $discount."%" : "No Discount"; ?></td>
                <td><?php echo $shipping>0 ? "Rs. ".$shipping : "Free"; ?></td>
                <td>
                    <form method="POST" action="displaycart.php">
                        <input type="number" name="quantity" value="<?php echo $quantity; ?>" min="1">
                        <input type="hidden" name="pid" value="<?php echo $row['product_id']; ?>">
                        <button type="submit" name="update_quantity" class="btn">Update</button>
                    </form>
                </td>
                <td>Rs. <?php echo number_format($total,2); ?></td>
                <td>
                    <a href="remove.php?pid=<?php echo $row['product_id']; ?>" class="btn btn-remove"
                       onclick="return confirm('Remove this item from cart?');">Remove</a>
                </td>
            </tr>
            <?php endwhile; ?>
            <tr style="color:green;">
                <td colspan="8"><b>Grand Total</b></td>
                <td colspan="2"><b>Rs. <?php echo number_format($grand_total,2); ?></b></td>
            </tr>
        </table>
        <br>
        <form method="POST" action="order_form.php">
            <button type="submit" name="order" class="btn" style="background-color:teal;">Order Now</button>
        </form>
        <br><br>
        <a href="jersey.php" class="btn">Continue Shopping</a>
    <?php else: ?>
        <p>Your cart is empty. <a href="jersey.php">Start Shopping</a></p>
    <?php endif; ?>
</div>
</body>
</html>
