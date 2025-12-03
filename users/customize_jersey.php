<?php
require_once "../shared/dbconnect.php";

if (!isset($_GET['id']))
    die("Invalid product");

$id = intval($_GET['id']);
$product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT j_name,image FROM products WHERE id=$id"));
?>

<!DOCTYPE html>
<html>

<head>
    <title>Customize <?php echo $product['j_name']; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body style="background:#f7f7f7;">

    <div class="container py-5">
        <div class="row justify-content-center">

            <div class="col-md-7">
                <div class="card shadow rounded-4 p-4">

                    <h3 class="text-center mb-3 fw-bold">
                        Customize Your Jersey
                    </h3>
                    <p class="text-center text-muted"><?php echo $product['j_name']; ?></p>

                    <div class="text-center mb-3">
                        <img src="../shared/products/<?php echo $product['image']; ?>" class="img-fluid"
                            style="max-height:300px; object-fit:contain;">
                    </div>

                    <form action="save_custom_cart.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $id; ?>">

                        <!-- TYPE -->
                        <label class="form-label fw-semibold">Jersey Type:</label>
                        <select class="form-select" name="type" required>
                            <option value="">Select Jersey Type</option>
                            <option value="First Copy">First Copy</option>
                            <option value="Original">Original</option>
                            <option value="Budget Friendly">Budget Friendly</option>
                        </select>

                        <!-- SIZE -->
                        <label class="form-label fw-semibold mt-3">Select Size:</label>
                        <select class="form-select" name="size" required>
                            <?php
                            $sizes = mysqli_query($conn, "SELECT size,stock FROM product_sizes WHERE product_id=$id AND stock>0");
                            while ($s = mysqli_fetch_assoc($sizes)) {
                                echo "<option value='{$s['size']}'>{$s['size']}  (In stock {$s['stock']} available)</option>";
                            }
                            ?>
                        </select>

                        <!-- NAME & NUMBER -->
                        <label class="form-label fw-semibold mt-3">Name to Print (optional):</label>
                        <input type="text" class="form-control" name="print_name" placeholder="e.g. Messi">

                        <label class="form-label fw-semibold mt-3">Jersey Number (optional):</label>
                        <input type="number" class="form-control" name="print_number" min="0" max="99"
                            placeholder="e.g. 10">

                        <!-- QUANTITY -->
                        <label class="form-label fw-semibold mt-3">Quantity:</label>
                        <input type="number" class="form-control" name="qty" value="1" min="1" required>

                        <button type="submit" class="btn btn-dark w-100 mt-4 fw-semibold">
                            Add to Cart
                        </button>
                    </form>

                </div>
            </div>

        </div>
    </div>

</body>

</html>