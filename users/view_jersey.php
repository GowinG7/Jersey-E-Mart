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

        .note-box {
            background: #fff8e6;
            border-left: 4px solid #ffc107;
            padding: 10px 12px;
            border-radius: 6px;
            margin-top: 15px;
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
            <!-- Product Image -->
            <div class="col-md-6 text-center">
                <img src="<?php echo $img; ?>" class="img-fluid shadow-sm rounded"
                    style="background:#fff;padding:18px;max-height:480px;object-fit:contain;">
            </div>

            <!-- Product Details -->
            <div class="col-md-6">
                <h2 class="fw-bold" style="color:#1c6059;"><?php echo htmlspecialchars($p['j_name']); ?></h2>
                <p class="text-muted"><?php echo htmlspecialchars($p['description']); ?></p>

                <div class="my-3">
                    <?php if ($p['discount'] > 0):
                        $new = $p['price'] - ($p['price'] * $p['discount'] / 100); ?>
                        <span style="text-decoration:line-through;color:#777;">Rs
                            <?php echo number_format($p['price'], 0); ?></span>
                        <span class="fw-bold text-success h4 ms-2">Rs <?php echo number_format($new, 0); ?></span>
                        <span class="badge bg-danger ms-2"><?php echo $p['discount']; ?>% OFF</span>
                    <?php else: ?>
                        <h4 class="text-success fw-bold">Rs <?php echo number_format($p['price'], 0); ?></h4>
                    <?php endif; ?>
                </div>

                <h6 class="fw-semibold">Available Sizes</h6>
                <div class="d-flex flex-wrap mb-3">
                    <?php
                    $sizes = mysqli_query($conn, "SELECT size,stock FROM product_sizes WHERE product_id=$id AND stock>0 ORDER BY size ASC");
                    if (mysqli_num_rows($sizes) > 0) {
                        while ($s = mysqli_fetch_assoc($sizes)) {
                            $size = strtoupper($s['size']);
                            $stock = $s['stock'];
                            echo "<button type='button' class='size-btn' data-size='$size' onclick='selectSize(this)'>$size • In stock: $stock</button>";
                        }
                    } else {
                        echo "<span class='text-danger'>No sizes available</span>";
                    }
                    ?>
                </div>

                <input type="hidden" id="sizeInput" value="">
                <div id="sizeAlert"></div>

                <div class="note-box">If you need a complete jersey set (full kit), contact the admin for special
                    pricing.</div>

                <label class="fw-semibold mt-3">Quantity</label>
                <input type="number" id="qty" class="form-control qty-box mb-3" value="1" min="1">

                <div class="d-flex mt-3">
                    <button id="addToCartBtn" class="btn btn-danger px-4 me-3">Add to Cart</button>
                    <button class="btn btn-dark px-4" data-bs-toggle="modal"
                        data-bs-target="#customizeModal">Customize</button>
                </div>
            </div>
        </div>
    </div>

    <!-- CUSTOMIZE MODAL -->
    <div class="modal fade" id="customizeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Customize Jersey</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="cart.php" method="POST" id="customForm">
                    <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                    <input type="hidden" id="finalPrice" name="final_price" value="<?php echo $p['price']; ?>">
                    <input type="hidden" name="size" id="customSize">
                    <input type="hidden" name="quantity" id="customQty" value="1">

                    <div class="modal-body">
                        <label class="fw-semibold">Final Price:</label>
                        <h4 class="text-success fw-bold" id="priceDisplay">Rs
                            <?php echo number_format($p['price'], 0); ?></h4>

                        <label class="form-label fw-semibold">Jersey Type:</label>
                        <select class="form-select" id="jerseyType" name="type">
                            <option value="">First Copy</option>
                            <option value="Premium">Premium (+1000)</option>
                            <option value="Replica">Replica (-700)</option>
                        </select>

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
        const basePrice = <?php echo $p['price']; ?>;
        const priceDisplay = document.getElementById("priceDisplay");
        const finalPriceInput = document.getElementById("finalPrice");
        const typeSelect = document.getElementById("jerseyType");
        const nameInput = document.querySelector("input[name='print_name']");
        const numberInput = document.querySelector("input[name='print_number']");
        const customForm = document.getElementById("customForm");

        function updatePrice() {
            let finalPrice = basePrice;
            if (typeSelect.value === "Premium") finalPrice += 1000;
            if (typeSelect.value === "Replica") finalPrice -= 700;
            if (nameInput.value.trim() !== "") finalPrice += 150;
            if (numberInput.value.trim() !== "") finalPrice += 100;

            finalPriceInput.value = Math.round(finalPrice);
            priceDisplay.innerText = "Rs " + finalPriceInput.value;
        }

        typeSelect.addEventListener("change", updatePrice);
        nameInput.addEventListener("input", updatePrice);
        numberInput.addEventListener("input", updatePrice);

        function selectSize(btn) {
            document.querySelectorAll(".size-btn").forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            document.getElementById("sizeInput").value = btn.dataset.size;
            document.getElementById("sizeAlert").style.display = "none";
        }

        document.getElementById("addToCartBtn").addEventListener("click", function () {
            const size = document.getElementById("sizeInput").value;
            const qty = document.getElementById("qty").value || 1;

            if (!size) {
                const alertDiv = document.getElementById("sizeAlert");
                alertDiv.innerText = "Please select a size first.";
                alertDiv.style.display = "block";
                return;
            }

            window.location.href = `cart.php?id=<?php echo $id; ?>&size=${size}&qty=${qty}`;
        });

        // Before submitting customize modal, set size and qty
        customForm.addEventListener("submit", function (e) {
            const selectedSize = document.getElementById("sizeInput").value;
            const selectedQty = document.getElementById("qty").value || 1;

            if (!selectedSize) {
                alert("Please select a size first.");
                e.preventDefault();
                return false;
            }

            document.getElementById("customSize").value = selectedSize;
            document.getElementById("customQty").value = selectedQty;
        });
    </script>
</body>

</html>