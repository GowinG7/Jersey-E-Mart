<?php
session_start();
require_once("../shared/dbconnect.php");
require_once("../shared/commonlinks.php");


if (!isset($_GET['order_id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = intval($_GET['order_id']);

/* FETCH ORDER */
$stmt = $conn->prepare("
    SELECT order_id, user_id, name, location, grand_total, payment_option, payment_status, order_status, transaction_id, order_date 
    FROM orders 
    WHERE order_id = ? LIMIT 1
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo "<p>Order not found.</p>";
    exit();
}

/* FETCH ORDER ITEMS */
$stmt = $conn->prepare("
    SELECT pname, category, jersey_size, quality, quantity, base_price, print_name, print_number, print_cost, final_price, subtotal, product_image
    FROM order_items
    WHERE order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* Shipping cost per order */
$shipping_cost = 150;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice | Admin Panel</title>
    <style>
        body {
            background: #eef2f4;
            font-family: Arial, sans-serif;
        }

        .container {
            padding: 20px;
            max-width: 900px;
            margin: auto;
        }

        .order-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .1);
            margin-bottom: 15px;
        }

        .order-header {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .order-info {
            font-size: 14px;
            margin-bottom: 15px;
        }

        .product-card {
            display: flex;
            gap: 15px;
            background: #f9f9f9;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
        }

        .product-img {
            width: 80px;
            height: 90px;
            object-fit: cover;
            border-radius: 5px;
        }

        .product-info {
            flex: 1;
            font-size: 13px;
        }

        .product-info p {
            margin: 3px 0;
        }

        .label {
            font-weight: bold;
            color: #555;
        }

        .total-box {
            background: #f7f7f7;
            padding: 10px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
            margin-top: 10px;
        }

        .no-print {
            margin-top: 10px;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                background: white;
            }
        }
    </style>
</head>

<body>

    <div class="container">

        <div class="order-card">
            <div class="order-header">Order #<?php echo $order['order_id']; ?> -
                <?php echo htmlspecialchars($order['name']); ?></div>
            <div class="order-info">
                <strong>Order Date:</strong> <?php echo $order['order_date']; ?> |
                <strong>Payment:</strong> <?php echo htmlspecialchars($order['payment_option']); ?>
                (<?php echo htmlspecialchars($order['payment_status']); ?>) |
                <strong>Status:</strong> <?php echo htmlspecialchars($order['order_status']); ?> |
                <strong>Delivery:</strong> <?php echo htmlspecialchars($order['location']); ?>
                <?php if (!empty($order['transaction_id'])): ?> |
                    <strong>Transaction ID:</strong> <?php echo htmlspecialchars($order['transaction_id']); ?>
                <?php endif; ?>
            </div>

            <?php foreach ($items as $it):
                $discount = $it['base_price'] - $it['final_price'];
                $image_path = !empty($it['product_image']) ? '../shared/products/' . htmlspecialchars($it['product_image']) : 'images/placeholder.png';
                ?>
                <div class="product-card">
                    <img src="<?php echo $image_path; ?>" class="product-img">
                    <div class="product-info">
                        <p><span class="label">Product:</span> <?php echo htmlspecialchars($it['pname']); ?></p>
                        <p><span class="label">Category:</span> <?php echo htmlspecialchars($it['category']); ?> | <span
                                class="label">Quality:</span> <?php echo htmlspecialchars($it['quality']); ?></p>
                        <p><span class="label">Size:</span> <?php echo htmlspecialchars($it['jersey_size']); ?> | <span
                                class="label">Qty:</span> <?php echo intval($it['quantity']); ?></p>
                        <p><span class="label">Base Price:</span> RS. <?php echo number_format($it['base_price'], 2); ?> |
                            <span class="label">Discount:</span> RS. <?php echo number_format($discount); ?></p>
                        <p><span class="label">Print Name:</span> <?php echo htmlspecialchars($it['print_name']); ?> | <span
                                class="label">Print Number:</span> <?php echo htmlspecialchars($it['print_number']); ?></p>
                        <p><span class="label">Print Cost:</span> RS. <?php echo number_format($it['print_cost'], 2); ?> |
                            <span class="label">Final Price:</span> RS. <?php echo number_format($it['final_price'], 2); ?></p>
                        <p><span class="label">Subtotal:</span> RS. <?php echo number_format($it['subtotal'], 2); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="total-box">
                Shipping: RS. <?php echo number_format($shipping_cost); ?><br>
                Grand Total: RS. <?php echo number_format($order['grand_total']); ?>
            </div>

            <div class="no-print">
                <button onclick="window.print()" class="btn btn-dark">Download / Print Invoice</button>
                <a href="orders.php" class="btn btn-primary">Back to Orders</a>
            </div>
        </div>

    </div>

</body>

</html>