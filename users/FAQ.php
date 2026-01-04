<?php 
session_start();
require_once '../shared/dbconnect.php';

include_once 'header.php'; ?>
<!DOCTYPE html>
<html>

<head>
  <title>FAQs</title>
  <style>
    body {
      background: #e0f4f2;
      color: #2d5d58;
      font-family: 'Segoe UI';
    }

    .card {
      background: linear-gradient(145deg, #fff, #cdeeea);
      border-radius: 14px;
      padding: 30px;
    }

    h4 {
      color: #1c6059;
    }
  </style>
</head>

<body>

  <div class="container my-5">
    <div class="card">
      <h3>Frequently Asked Questions</h3>

      <h4>QN)How can I place an order?</h4>
      <p>Ans: Select a jersey, add it to cart, and proceed to checkout.</p>

      <h4>QN)Which payment methods are available?</h4>
      <p>Ans: Cash on Delivery and Online Payment (eSewa).</p>
      <h4>Can I cancel my order?</h4>
      <p>Ans: To cancel the orders, users directly have to contact admin within 2-5 hours.</p>

      <h4>QN)Are the jerseys original?</h4>
      <p>Ans: Yes, all jerseys are quality-checked before shipping. However, if any issues happen you can return or
        exchange within few hours of delivery.</p>
    </div>
  </div>

  <?php include_once 'footer.php'; ?>
</body>

</html>