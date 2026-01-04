<?php
session_start();
require_once '../shared/dbconnect.php';
include_once 'header.php';
?>
<!DOCTYPE html>
<html>

<head>
    <title>Terms & Conditions</title>
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
    </style>
</head>

<body>

    <div class="container my-5">
        <div class="card">
            <h3>Terms & Conditions</h3>

            <p>By using Jersey E-Mart, you agree to the following terms:</p>

            <ul>
                <li>Prices and availability may change without notice</li>
                <li>User must provide accurate delivery information</li>
                <li>Misuse of the website is strictly prohibited</li>
            </ul>

            <p>We reserve the right to update these terms at any time.</p>
        </div>
    </div>

    <?php include_once 'footer.php'; ?>
</body>

</html>