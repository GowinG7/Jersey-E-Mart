<?php
session_start();
require_once "../shared/dbconnect.php";
include_once "../shared/commonlinks.php";
include "header.php";

if (!isset($_SESSION['user_id'])) {
  $_SESSION['alert'] = "Please login to place an order.";
  $_SESSION['alert_type'] = "warning";
  header("Location: loginsignup/login.php");
  exit();
}

$shipping_cost = 150; //flat shipping cost(per each order not per item)

$user_id = $_SESSION['user_id'];

// fetch cart
$stmt = $conn->prepare("SELECT id, product_id, pname, quantity, price_after_discount, image, quality, category, print_name, print_number FROM cart_items WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$items = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($items)) {
  $_SESSION['alert'] = "Your cart is empty.";
  $_SESSION['alert_type'] = "info";
  header("Location: cart.php");
  exit();
}

// calculate grand total
$grand_total = 0;
foreach ($items as $it) {
  $grand_total += floatval($it['price_after_discount']) * intval($it['quantity']);
}
$grand_total += $shipping_cost; // add flat shipping once

$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
?>

<!doctype html>
<html>

<head>
  <title>Jersey E-mart: Order Form</title>
</head>

<body style="background-color: #e9f8f6;">
  <div class="container py-5">

    <div class="row">
      <div class="col-md-6 mb-4">
        <form action="place_order.php" method="post" class="bg-white p-4 rounded shadow-sm">
          <div class="mb-3">
            <label>Full name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Delivery address</label>
            <textarea name="location" class="form-control" required></textarea>
          </div>
          <div class="mb-3">
            <label>Contact phone</label>
            <input type="text" name="contact" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Payment Option</label>

            <!-- Cash on Delivery -->
            <div class="form-check border rounded p-3 mb-2">
              <input class="form-check-input" type="radio" name="payment_option" id="cod" value="Cash on Delivery"
                checked>
              <label class="form-check-label fw-bold" for="cod" >
                <b style="color:gray;" >Cash on Delivery</b>
              </label>
            </div>

            <!-- eSewa Online Payment -->
            <div class="form-check border rounded p-3 d-flex align-items-center gap-3">
              <input class="form-check-input" type="radio" name="payment_option" id="esewa" value="Online Payment">
              <label class="form-check-label d-flex align-items-center gap-2" for="esewa">
                <img src="images/esewa.png" alt="eSewa" style="height:55px;object-fit:contain;">
                <span class="fw-bold">
                  <h4 style="color:green;">eSewa</h4>
                </span>
              </label>
            </div>
          </div>

          <input type="hidden" name="grand_total" value="<?php echo intval($grand_total); ?>">
          <button type="submit" class="btn btn-success w-100">Place order (Rs
            <?php echo number_format($grand_total); ?>)</button>
        </form>
      </div>

      <!-- Order Summary -->
      <div class="col-md-6">
        <div class="bg-white p-3 rounded shadow-sm">
          <h5 class="mb-3">Order Summary</h5>
          <?php foreach ($items as $it):
            $quantity = intval($it['quantity']);
            $unit_price = floatval($it['price_after_discount']);
            $subtotal = $unit_price * $quantity;
            ?>
            <div class="d-flex align-items-center mb-3">
              <img
                src="<?php echo !empty($it['image']) ? "../shared/products/" . $it['image'] : "images/placeholder.png"; ?>"
                style="height:60px;width:60px;object-fit:contain;margin-right:15px;border:1px solid #ddd;padding:2px;border-radius:4px;">
              <div class="flex-grow-1">
                <div class="fw-bold"><?php echo htmlspecialchars($it['pname']); ?></div>
                <?php if (!empty($it['category'])): ?>
                  <small class="text-muted">Category: <?php echo htmlspecialchars($it['category']); ?></small><br>
                <?php endif; ?>
                <?php if (!empty($it['quality'])): ?>
                  <small class="text-muted">Quality: <?php echo htmlspecialchars($it['quality']); ?></small><br>
                <?php endif; ?>
                <?php if (!empty($it['print_name'])): ?>
                  <small class="text-muted">Print: <?php echo htmlspecialchars($it['print_name']); ?>
                    #<?php echo intval($it['print_number']); ?></small><br>
                <?php endif; ?>
                <small>Qty: <?php echo $quantity; ?> | Unit Price: Rs <?php echo number_format($unit_price); ?></small>
              </div>
              <div class="fw-semibold ms-3">Rs <?php echo number_format($subtotal); ?></div>
            </div>
          <?php endforeach; ?>
          <hr>
          <div class="d-flex justify-content-between fw-bold">
            <span>Shipping:</span>
            <span>Rs <?php echo number_format($shipping_cost); ?></span>
          </div>
          <div class="d-flex justify-content-between fw-bold fs-5 mt-2">
            <span>Grand Total:</span>
            <span>Rs <?php echo number_format($grand_total); ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>