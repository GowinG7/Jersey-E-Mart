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

<body style="background:#e9f8f6; font-family:Segoe UI;">
    <div class="container py-4">

        <div class="row g-4 align-items-center">

            <!-- Product Image -->
            <div class="col-12 col-md-6 text-center">
                <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($product['j_name']); ?>"
                    class="img-fluid rounded shadow-sm"
                    style="max-height:430px;object-fit:contain;background:#fff;padding:15px;">
            </div>

            <!-- Product Info -->
            <div class="col-12 col-md-6">

                <h2 class="fw-bold mb-2" style="color:#1c6059;">
                    <?php echo htmlspecialchars($product['j_name']); ?>
                </h2>

                <p class="text-muted mb-3">
                    <?php echo htmlspecialchars($product['description']); ?>
                </p>

                <!-- Price with discount logic -->
                <div class="mb-3">
                    <?php if ($product['discount'] > 0) {
                        $newPrice = $product['price'] - ($product['price'] * $product['discount'] / 100); ?>

                        <span style="text-decoration:line-through;color:#777;">Rs <?php echo $product['price']; ?></span>
                        <span class="fw-bold text-success h4 ms-2">Rs <?php echo $newPrice; ?></span>
                        <span class="badge bg-danger ms-2"><?php echo $product['discount']; ?>% OFF</span>

                    <?php } else { ?>
                        <h4 class="text-success fw-bold mb-1">Rs <?php echo $product['price']; ?></h4>
                    <?php } ?>
                </div>


                <!-- Size Selection -->
                <label class="fw-semibold">Choose Size:</label>

                <select class="form-select w-50 mt-1 mb-3" required>
                    <?php
                    $sizeQuery = mysqli_query(
                        $conn,
                        "SELECT size, stock FROM product_sizes WHERE product_id = $id AND stock > 0 ORDER BY size ASC"
                    );

                    if (mysqli_num_rows($sizeQuery) > 0) {
                        while ($s = mysqli_fetch_assoc($sizeQuery)) {
                            $size = strtoupper($s['size']);
                            $stock = $s['stock'];

                            echo "<option value='$size'>$size (Stock: $stock)</option>";
                        }
                    } else {
                        echo "<option disabled>No Sizes Available</option>";
                    }
                    ?>
                </select>


                <!-- Buttons -->
                <div class="mt-3">
                    <a href="add_to_cart.php?id=<?php echo $id; ?>" class="btn btn-primary px-4 me-2">Add to Cart</a>

                    <a href="customize_jersey.php?id=<?php echo $id; ?>" class="btn btn-warning px-4">Customize</a>
                </div>
            </div>

        </div>
    </div>
</body>

</html>