<?php
require_once "../shared/dbconnect.php"; 
include_once "../shared/commonlinks.php";

if (!isset($_GET['id'])) {
    die("Invalid product request.");
}

$id = intval($_GET['id']);

// Fetch single product
$sql = "SELECT * FROM products WHERE id = $id LIMIT 1";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Product not found.");
}

$product = mysqli_fetch_assoc($result);

// Image path
$img = "../shared/products/" . $product['image'];
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($product['j_name']); ?></title>
   
    <style>
        .product-img {
            height: 380px;
            width: auto;
            object-fit: contain;
        }
    </style>
</head>

<body>
<div class="container py-4">

    <div class="row">
        <!-- Image Section -->
        <div class="col-md-6 text-center">
            <img src="<?php echo $img; ?>" class="img-fluid product-img" alt="Product Image">
        </div>

        <!-- Details Section -->
        <div class="col-md-6">
            <h3><?php echo htmlspecialchars($product['j_name']); ?></h3>

            <p class="text-muted">
                <?php echo htmlspecialchars($product['description']); ?>
            </p>

            <h4 class="text-success">Price: Rs. <?php echo htmlspecialchars($product['price']); ?></h4>

            <div class="mt-3 mb-2">
                <label class="fw-semibold">Select Size:</label>
                <select class="form-select w-50">
                    <?php
                    // If your product table has sizes as comma list (e.g., "S,M,L")
                    $sizes = explode(',', $product['sizes']);

                    foreach ($sizes as $s) {
                        echo "<option value='{$s}'>" . strtoupper(trim($s)) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mt-3">
                <a href="add_to_cart.php?id=<?php echo $id; ?>" class="btn btn-primary me-2">
                    Add to Cart
                </a>

                <a href="customize.php?id=<?php echo $id; ?>" class="btn btn-warning">
                    Customize
                </a>
            </div>
        </div>
    </div>

</div>

</body>
</html>
