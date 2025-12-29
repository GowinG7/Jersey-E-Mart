<?php
// thankyou.php
session_start();
require_once "../shared/dbconnect.php";
include_once "../shared/commonlinks.php";
include "header.php";

if (!isset($_GET['order_id'])) {
  header("Location: index.php");
  exit();
}

$order_id = intval($_GET['order_id']);

// Fetch order
$stmt = $conn->prepare("
    SELECT order_id, name, location, grand_total, payment_option, payment_status, order_date 
    FROM orders 
    WHERE order_id = ? LIMIT 1
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_res = $stmt->get_result();
$order = $order_res->fetch_assoc();
$stmt->close();

if (!$order) {
  echo "Order not found.";
  exit();
}

// Fetch items
$stmt = $conn->prepare("
    SELECT pname, quantity, final_price, subtotal, product_image 
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
    .summary-table th {
      background: #f1f1f1;
    }

    .product-img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 6px;
    }

    @media print {
      .no-print {
        display: none;
      }

      body {
        background: white;
      }

      .card {
        box-shadow: none;
      }
    }
  </style>
</head>

<body style="background:#eef2f4;">
  <div class="container py-5">

    <div class="card p-4 shadow-sm">

      <h3 class="mb-3">Thank you, <?php echo htmlspecialchars($order['name']); ?>.</h3>
      <p>
        Your order <strong>#<?php echo $order['order_id']; ?></strong> has been successfully placed.<br>
        Order Date: <?php echo $order['order_date']; ?><br>
        Payment Method: <?php echo htmlspecialchars($order['payment_option']); ?><br>
        Payment Status: <?php echo htmlspecialchars($order['payment_status']); ?>
      </p>

      <hr>

      <h5>Order Summary</h5>

      <div class="table-responsive">
        <table class="table summary-table">
          <thead>
            <tr>
              <th>Image</th>
              <th>Product</th>
              <th>Qty</th>
              <th>Rate (Rs)</th>
              <th>Subtotal (Rs)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $it): ?>
              <tr>
                <td>
                  <img src="<?php echo !empty($it['product_image']) ? '../shared/products/'.htmlspecialchars($it['product_image']) : 'images/placeholder.png'; ?>" class="product-img" alt="<?php echo htmlspecialchars($it['pname']); ?>">
                </td>
                <td><?php echo htmlspecialchars($it['pname']); ?></td>
                <td><?php echo intval($it['quantity']); ?></td>
                <td><?php echo number_format($it['final_price']); ?></td>
                <td><?php echo number_format($it['subtotal']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <h5 class="mt-3">Grand Total: Rs <?php echo number_format($order['grand_total']); ?></h5>

      <div class="mt-4 no-print">
        <button onclick="window.print()" class="btn btn-dark">Print Invoice</button>
        <a href="index.php" class="btn btn-primary">Continue Shopping</a>

      </div>

    </div>

  </div>
</body>

</html>