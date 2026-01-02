<?php
session_start();
include("header.php");
require_once "../shared/dbconnect.php";
include_once "../shared/commonlinks.php";

if (!isset($_GET['id']))
    die("Invalid product request.");

$id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM products WHERE id=$id LIMIT 1");
if (!$result || mysqli_num_rows($result) == 0)
    die("Product not found.");

$p = mysqli_fetch_assoc($result);
$img = "../shared/products/" . $p['image'];

// Base price & discount
$basePrice = floatval($p['price']);
$discount = floatval($p['discount']);
$displayPrice = $basePrice;
if ($discount > 0) {
    $displayPrice = $basePrice - ($basePrice * $discount / 100);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($p['j_name']); ?></title>
    <style>
        .size-btn {
            padding: 8px 18px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background: #fff;
            cursor: pointer;
            margin: 4px;
        }

        .size-btn:hover {
            border-color: #1c6059;
        }

        .size-btn.active {
            background: #1c6059;
            color: #fff;
            border-color: #1c6059;
        }

        .qty-box {
            width: 70px;
        }

        #sizeAlert {
            display: none;
            color: red;
            margin: 10px 0;
            font-weight: bold;
        }
    </style>
</head>

<body style="background-color:#e9f8f6; font-family:Segoe UI;">
    <div class="container py-5">
        <div class="row g-4">
            <!-- Image -->
            <div class="col-md-6 text-center">
                <img src="<?php echo $img; ?>" class="img-fluid shadow-sm rounded"
                    style="background:#fff;padding:18px;max-height:480px;object-fit:contain;">
            </div>

            <!-- Details -->
            <div class="col-md-6">
                <h2 class="fw-bold" style="color:#1c6059;"><?php echo htmlspecialchars($p['j_name']); ?></h2>
                <p class="text-muted"><?php echo htmlspecialchars($p['description']); ?></p>

                <div class="my-3">
                    <?php if ($discount > 0):
                        $new = $basePrice - ($basePrice * $discount / 100); ?>
                        <span style="text-decoration:line-through;color:#777;">Rs
                            <?php echo number_format($basePrice); ?></span>
                        <span class="fw-bold text-success h4 ms-2">Rs <?php echo number_format($new); ?></span>
                        <span class="badge bg-danger ms-2"><?php echo $discount; ?>% OFF</span>
                    <?php else: ?>
                        <h4 class="text-success fw-bold">Rs <?php echo number_format($basePrice); ?></h4>
                    <?php endif; ?>
                </div>

                <div class="alert alert-warning mt-3" role="alert" style="border-radius:6px;">
                     For full-set or bulk orders, please
                    <a href="Contact.php" class="alert-link" style="font-weight:600; text-decoration: none;color:#1c6059;">contact the admin</a> for negotiable prices 
                </div>

                <h6 class="fw-semibold">Available Sizes</h6>
                <div class="d-flex flex-wrap mb-3">
                    <?php
                    $sizes = mysqli_query($conn, "
                        SELECT size, stock 
                        FROM product_sizes 
                        WHERE product_id = $id AND stock > 0
                        ORDER BY size ASC
                    ");

                    $hasSizes = ($sizes && mysqli_num_rows($sizes) > 0);

                    if ($hasSizes) {

                        while ($s = mysqli_fetch_assoc($sizes)) {
                            $size = strtoupper($s['size']);
                            $stock = $s['stock'];
                            echo "
                                <button type='button' class='size-btn' 
                                    data-size='$size' 
                                    data-stock='$stock'
                                    onclick='selectSize(this)'>
                                    $size • In stock: $stock
                                </button>
                                ";

                        }
                    } else {
                        echo "<span class='text-danger fw-semibold'>Out of stock</span>";
                    }
                    ?>
                </div>

                <input type="hidden" id="sizeInput" value="">
                <div id="sizeAlert"></div>

                <label class="fw-semibold mt-3">Quantity</label>
                <input type="number" id="qty" class="form-control qty-box mb-3" value="1" min="1">

                <div class="d-flex mt-3">
                    <button id="addToCartBtn" class="btn btn-danger px-4 me-3" <?php echo !$hasSizes ? 'disabled' : ''; ?>>
                        Add to Cart
                    </button>
                    <button class="btn btn-dark px-4" data-bs-toggle="modal" data-bs-target="#customizeModal"
                        onclick="updatePrice()" <?php echo !$hasSizes ? 'disabled' : ''; ?>>
                        Customize
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Customize Modal -->
    <div class="modal fade" id="customizeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Customize Jersey</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="cart.php" method="POST" id="customForm">
                    <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                    <input type="hidden" id="finalPrice" name="final_price" value="<?php echo $displayPrice; ?>">
                    <input type="hidden" name="size" id="customSize">
                    <input type="hidden" name="quantity" id="customQty" value="1">
                    <div class="modal-body">
                        <label class="fw-semibold">Final Price:</label>
                        <h4 class="text-success fw-bold" id="priceDisplay">Rs
                            <?php echo number_format($displayPrice); ?>
                        </h4>

                        <label class="form-label fw-semibold mt-3">Name to Print:</label>
                        <input type="text" class="form-control" name="print_name">

                        <label class="form-label fw-semibold mt-3">Number to Print:</label>
                        <input type="number" class="form-control" name="print_number" min="0" max="99">
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-dark w-100 fw-semibold">Add to Cart</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let selectedStock = 0;

        const hasSizes = <?php echo $hasSizes ? 'true' : 'false'; ?>;

        if (!hasSizes) {
            document.getElementById("addToCartBtn").disabled = true;
        }


        const qtyInput = document.getElementById("qty");

        const displayPrice = <?php echo $displayPrice; ?>;
        const priceDisplay = document.getElementById("priceDisplay");
        const finalPriceInput = document.getElementById("finalPrice");
        const nameInput = document.querySelector("input[name='print_name']");
        const numberInput = document.querySelector("input[name='print_number']");
        const customForm = document.getElementById("customForm");

        function updatePrice() {
            // Start with server-provided display price
            let adjustedPrice = Number(displayPrice) || 0;

            // Add print surcharges: +100 for name, +50 for number
            if (nameInput.value.trim() !== "") adjustedPrice += 100;
            if (numberInput.value.trim() !== "") adjustedPrice += 50;

            adjustedPrice = Math.round(adjustedPrice);
            finalPriceInput.value = adjustedPrice;
            priceDisplay.innerText = "Rs " + adjustedPrice;
        }

        // Live updates when user types print name/number
        nameInput.addEventListener("input", updatePrice);
        numberInput.addEventListener("input", updatePrice);

        // Initialize display
        updatePrice();

        function selectSize(btn) {
            document.querySelectorAll(".size-btn").forEach(b => b.classList.remove("active"));
            btn.classList.add("active");

            const size = btn.dataset.size;
            const stock = parseInt(btn.dataset.stock, 10);

            document.getElementById("sizeInput").value = size;
            document.getElementById("sizeAlert").style.display = "none";

            // Store stock
            selectedStock = stock;

            // Reset quantity safely
            qtyInput.value = 1;
            qtyInput.min = 1;
            qtyInput.max = stock;
        }

        document.getElementById("addToCartBtn").addEventListener("click", function () {
            const size = document.getElementById("sizeInput").value;
            const qty = parseInt(qtyInput.value, 10) || 1;

            if (!size) {
                const alertDiv = document.getElementById("sizeAlert");
                alertDiv.innerText = "Please select a size first.";
                alertDiv.style.display = "block";
                return;
            }

            if (qty > selectedStock) {
                alert("Requested quantity exceeds available stock.");
                return;
            }

            window.location.href = `cart.php?id=<?php echo $id; ?>&size=${size}&qty=${qty}`;
        });



        // Customize modal form
        customForm.addEventListener("submit", function (e) {
            const selectedSize = document.getElementById("sizeInput").value;
            const selectedQty = document.getElementById("qty").value || 1;

            if (!selectedSize) {
                alert("Please select a size first.");
                e.preventDefault();
                return false;
            }

            if (selectedQty > selectedStock) {
                alert("Requested quantity exceeds available stock.");
                e.preventDefault();
                return false;
            }


            document.getElementById("customSize").value = selectedSize;
            document.getElementById("customQty").value = selectedQty;

            let final = Number(displayPrice) || 0;
            if (nameInput.value.trim() !== "") final += 100;
            if (numberInput.value.trim() !== "") final += 50;
            finalPriceInput.value = Math.round(final);
        });

        qtyInput.addEventListener("input", function () {
            let val = parseInt(this.value, 10);

            if (!selectedStock) {
                this.value = 1;
                return;
            }

            if (val < 1) this.value = 1;
            if (val > selectedStock) {
                alert("Only " + selectedStock + " item(s) available in stock.");
                this.value = selectedStock;
            }
        });

    </script>

</body>

</html>