<?php
session_start();
require_once "../shared/dbconnect.php";
include_once "../shared/commonlinks.php";
include "header.php";

if (!isset($_GET['order_id'])) {
  header("Location: index.php");
  exit();
}

$order_id = intval($_GET['order_id']);

/* FETCH ORDER */
$stmt = $conn->prepare("
    SELECT order_id, name, location, grand_total, payment_option, payment_status, order_date 
    FROM orders 
    WHERE order_id = ? LIMIT 1
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
  echo "Order not found.";
  exit();
}

/* FETCH ORDER ITEMS (ONLY EXISTING COLUMNS) */
$stmt = $conn->prepare("
    SELECT pname, jersey_size, quality, quantity, final_price, subtotal,
           shipping, product_image
    FROM order_items
    WHERE order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!doctype html>
<html>

<head>
  <title>Order Confirmation</title>

  <style>
    body {
      background: #eef2f4;
    }

    .product-card {
      display: flex;
      gap: 15px;
      background: #fff;
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 15px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, .08);
    }

    .product-img {
      width: 120px;
      height: 150px;
      object-fit: cover;
      border-radius: 8px;
    }

    .product-info {
      flex: 1;
    }

    .label {
      font-weight: bold;
      color: #555;
    }

    .total-box {
      background: #fff;
      padding: 15px;
      border-radius: 8px;
      margin-top: 20px;
      font-size: 18px;
      font-weight: bold;
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
  <div class="container py-5">

    <div class="card p-4 shadow-sm">

      <h3>Thank you, <?php echo htmlspecialchars($order['name']); ?></h3>
      <p>
        <strong>Order ID:</strong> #<?php echo $order['order_id']; ?><br>
        <strong>Order Date:</strong> <?php echo $order['order_date']; ?><br>
        <strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_option']); ?><br>
        <strong>Payment Status:</strong> <?php echo htmlspecialchars($order['payment_status']); ?><br>
        <strong>Delivery Location:</strong> <?php echo htmlspecialchars($order['location']); ?>
      </p>

      <hr>

      <h5>Order Summary</h5>

      <?php foreach ($items as $it): ?>
        <div class="product-card">
          <img
            src="<?php echo !empty($it['product_image']) ? '../shared/products/' . htmlspecialchars($it['product_image']) : 'images/placeholder.png'; ?>"
            class="product-img">

          <div class="product-info">
            <p><span class="label">Product:</span> <?php echo htmlspecialchars($it['pname']); ?></p>
            <p><span class="label">Size:</span> <?php echo htmlspecialchars($it['jersey_size']); ?></p>
            <p><span class="label">Quality:</span> <?php echo htmlspecialchars($it['quality']); ?></p>
            <p><span class="label">Quantity:</span> <?php echo intval($it['quantity']); ?></p>
            <p><span class="label">Rate:</span> Rs <?php echo number_format($it['final_price']); ?></p>
            
            <p style="font-weight:bold;">
              Subtotal: Rs <?php echo number_format($it['subtotal']); ?>
            </p>
          </div>
        </div>
      <?php endforeach; ?>

      <div class="total-box">
        <p><span class="label">Shipping cost:</span> Rs <?php echo number_format($it['shipping']); ?></p>

        Grand Total: Rs <?php echo number_format($order['grand_total']); ?>
      </div>

      <div class="mt-4 no-print">
        <button onclick="window.print()" class="btn btn-dark">
          Download Invoice (Print)
        </button>
        <a href="index.php" class="btn btn-primary">Continue Shopping</a>
      </div>

    </div>

  </div>
</body>

</html>