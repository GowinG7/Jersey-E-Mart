<?php
session_start();
require_once("../shared/dbconnect.php");

if (!isset($_SESSION['admin_id']))
    exit;

$order_id = intval($_POST['order_id']);

$res = $conn->query("SELECT * FROM order_items WHERE order_id = $order_id");

if ($res->num_rows === 0) {
    echo "<p class='text-center text-muted'>No items found</p>";
    exit;
}

echo "<div class='table-responsive'>";
echo "<table class='table table-bordered table-striped'>";
echo "<thead class='table-dark'>
<tr>
    <th>Product</th>
    <th>Category</th>
    <th>Size</th>
    <th>Quality</th>
    <th>Qty</th>
    <th>Base Price</th>
    <th>Print Cost</th>
    <th>Final Price</th>
    <th>Subtotal</th>
    <th>Image</th>
</tr>
</thead>
<tbody>";

while ($row = $res->fetch_assoc()) {
    $image_path = "../shared/products/" . htmlspecialchars($row['product_image']);

    // fallback if image not found
    if (!file_exists($image_path) || empty($row['product_image'])) {
        $image_path = "../shared/products/default.png"; // you can add a default placeholder
    }

    echo "<tr>
        <td>" . htmlspecialchars($row['pname']) . "</td>
        <td>" . htmlspecialchars($row['category']) . "</td>
        <td>" . htmlspecialchars($row['jersey_size']) . "</td>
        <td>" . htmlspecialchars($row['quality']) . "</td>
        <td>" . intval($row['quantity']) . "</td>
        <td>₹ " . number_format($row['base_price'], 2) . "</td>
        <td>₹ " . number_format($row['print_cost'], 2) . "</td>
        <td>₹ " . number_format($row['final_price'], 2) . "</td>
        <td>₹ " . number_format($row['subtotal'], 2) . "</td>
        <td><img src='" . $image_path . "' alt='" . htmlspecialchars($row['pname']) . "' style='max-width:80px; height:auto;'></td>
    </tr>";
}

echo "</tbody></table></div>";
