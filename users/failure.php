<?php
session_start();
require_once "../shared/dbconnect.php";

// Clear session data if payment failed
if (isset($_SESSION['name'])) {
    unset($_SESSION['name']);
}
if (isset($_SESSION['location'])) {
    unset($_SESSION['location']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - Jersey E-Mart</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .error-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .error-icon {
            width: 80px;
            height: 80px;
            background: #e74c3c;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 20px;
            font-size: 40px;
            color: white;
        }

        h2 {
            color: #e74c3c;
            margin-bottom: 15px;
            font-size: 28px;
        }

        p {
            color: #555;
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .reason {
            background: #fdecea;
            border-left: 4px solid #e74c3c;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
            border-radius: 5px;
        }

        .reason strong {
            color: #c0392b;
        }

        .button-group {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
            display: inline-block;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(149, 165, 166, 0.3);
        }

        .help-text {
            margin-top: 20px;
            font-size: 14px;
            color: #777;
        }

        @media (max-width: 500px) {
            .error-container {
                padding: 30px 20px;
            }

            h2 {
                font-size: 24px;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="error-container">
        <div class="error-icon">❌</div>
        
        <h2>Payment Failed</h2>
        
        <p>We're sorry, but your payment could not be completed via eSewa.</p>
        
        <div class="reason">
            <strong>Possible reasons:</strong>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>Payment was cancelled</li>
                <li>Insufficient balance</li>
                <li>Connection timeout</li>
                <li>Invalid credentials</li>
            </ul>
        </div>

        <p>Your cart items are still saved. Please try again or choose Cash on Delivery.</p>

        <div class="button-group">
            <a href="displaycart.php" class="btn btn-primary">← Back to Cart</a>
            <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
        </div>

        <p class="help-text">
            Need help? Contact us at <strong>support@jerseyemart.com</strong>
        </p>
    </div>

</body>

</html>