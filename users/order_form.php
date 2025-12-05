<?php
// order_form.php
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

$user_id = $_SESSION['user_id'];

// fetch cart and calculate total
$stmt = $conn->prepare("SELECT id, product_id, pname, quantity, final_price, shipping, image FROM cart_items WHERE user_id = ?");
$stmt->bind_param("i",$user_id);
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

$grand_total = 0;
foreach ($items as $it) {
    $grand_total += intval($it['final_price'])*intval($it['quantity']) + intval($it['shipping']);
}

// simple user info: you might want to fetch user's name/email from DB
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

?>
<!doctype html>
<html>
<head><title>Checkout - Jersey E-mart</title></head>
<body style="background:#f8f9fa">
<div class="container py-5">
  <h4>Checkout</h4>

  <div class="row">
    <div class="col-md-6">
      <form action="place_order.php" method="post">
        <div class="mb-2">
          <label>Full name</label>
          <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user_name); ?>" required>
        </div>

        <div class="mb-2">
          <label>Delivery address</label>
          <textarea name="location" class="form-control" required></textarea>
        </div>

        <div class="mb-2">
          <label>Contact phone</label>
          <input type="text" name="contact" class="form-control" required>
        </div>

        <div class="mb-2">
          <label>Payment option</label>
          <select name="payment_option" class="form-select" required>
            <option value="Cash on Delivery">Cash on Delivery</option>
            <option value="Online Payment">Online Payment</option>
          </select>
        </div>

        <input type="hidden" name="grand_total" value="<?php echo intval($grand_total); ?>">

        <button type="submit" class="btn btn-success">Place order (Rs <?php echo number_format($grand_total); ?>)</button>
      </form>
    </div>

    <div class="col-md-6">
      <div class="bg-white p-3 rounded shadow">
        <h5>Order summary</h5>
        <?php foreach ($items as $it): ?>
          <div class="d-flex mb-2">
            <img src="<?php echo !empty($it['image']) ? "../shared/products/".$it['image'] : "images/placeholder.png"; ?>" style="height:60px;object-fit:contain;margin-right:10px;">
            <div>
              <div><?php echo htmlspecialchars($it['pname']); ?></div>
              <small>Qty: <?php echo intval($it['quantity']); ?> &nbsp; Price: Rs <?php echo number_format($it['final_price']); ?></small>
            </div>
          </div>
        <?php endforeach; ?>
        <hr>
        <h6>Grand Total: Rs <?php echo number_format($grand_total); ?></h6>
      </div>
    </div>
  </div>
</div>
</body>
</html>
