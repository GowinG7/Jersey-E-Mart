<?php
session_start();
include("../shared/dbconnect.php");
include("../shared/commonlinks.php");
include("header.php");

$isLoggedIn = isset($_SESSION['user_id']);
$result = null;

if ($isLoggedIn) {
    $user_id = intval($_SESSION['user_id']);
    $query = "SELECT * FROM cart_items WHERE user_id = $user_id";
    $result = mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>My Cart - Jersey E-mart</title>
    <link rel="stylesheet" href="css/displaycart.css">
</head>

<body>
    <div class="cart-container">
        <?php if ($isLoggedIn): ?>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php
                $grand_total = 0;
                $shipping_cost = 150;
                $has_out_of_stock = false;
                ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Jersey Size</th>
                                <th>Quality</th>
                                <th>Base Price</th>
                                <th>Print Name</th>
                                <th>Print Number</th>
                                <th>Print Cost</th>
                                <th>Discount</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <?php
                                $price_after_discount = floatval($row['price_after_discount']);
                                $quantity = intval($row['quantity']);
                                $total = round($price_after_discount * $quantity, 2);
                                $grand_total += $total;

                                $stock = 0;
                                $res = mysqli_query($conn, "SELECT stock FROM product_sizes WHERE product_id={$row['product_id']} AND size='{$row['jersey_size']}' LIMIT 1");
                                if ($res && mysqli_num_rows($res) > 0) {
                                    $r = mysqli_fetch_assoc($res);
                                    $stock = intval($r['stock']);
                                }
                                if ($stock == 0) {
                                    $has_out_of_stock = true;
                                }
                                ?>
                                <tr>
                                    <td data-label="Image"><img src="../shared/products/<?php echo $row['image']; ?>" class="img-thumb"></td>
                                    <td data-label="Name"><?php echo $row['pname']; ?></td>
                                    <td data-label="Category"><?php echo $row['category']; ?></td>
                                    <td data-label="Jersey Size"><?php echo $row['jersey_size']; ?></td>
                                    <td data-label="Quality"><?php echo $row['quality']; ?></td>
                                    <td data-label="Base Price">Rs. <?php echo number_format($row['base_price']); ?></td>
                                    <td data-label="Print Name"><?php echo $row['print_name']; ?></td>
                                    <td data-label="Print Number"><?php echo $row['print_number']; ?></td>
                                    <td data-label="Print Cost"><?php echo $row['print_cost'] > 0 ? 'Rs. ' . $row['print_cost'] : ''; ?></td>
                                    <td data-label="Discount"><?php echo $row['discount'] ? $row['discount'] . '%' : ''; ?></td>
                                    <td data-label="Quantity">
                                        <form method="POST" action="update.php" class="update-form">
                                            <input type="number" name="qty[<?php echo $row['id']; ?>]" value="<?php echo $quantity; ?>" min="1" <?php echo $stock > 0 ? 'max="' . $stock . '"' : ''; ?>
                                                oninput="this.setCustomValidity('')"
                                                oninvalid="
                                                    if (this.validity.rangeOverflow) {
                                                        this.setCustomValidity('There is only <?php echo $stock; ?> item<?php echo ($stock == 1 ? '' : 's'); ?> left in the stock.');
                                                    } else if (this.validity.rangeUnderflow) {
                                                        this.setCustomValidity('Quantity must be at least 1.');
                                                    } else {
                                                        this.setCustomValidity('Please enter a valid quantity.');
                                                    }
                                                ">
                                            <button type="submit" name="update_cart" class="btn" <?php echo $stock == 0 ? 'disabled' : ''; ?>>Update</button>
                                        </form>
                                    </td>
                                    <td data-label="Total">Rs. <?php echo number_format($total); ?></td>
                                    <td data-label="Status"><?php echo $stock == 0 ? '<span style="color:red;font-weight:bold;">Out of stock</span>' : '<span style="color:green;">Available</span>'; ?></td>
                                    <td data-label="Action">
                                        <a href="remove.php?pid=<?php echo $row['product_id']; ?>&quality=<?php echo $row['quality']; ?>&size=<?php echo $row['jersey_size']; ?>" class="btn btn-remove" onclick="return confirm('Remove this item from cart?');">Remove</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <tr>
                                <td colspan="12"><b>Shipping</b></td>
                                <td colspan="2">Rs. <?php echo number_format($shipping_cost); ?></td>
                            </tr>
                            <?php $grand_total += $shipping_cost; ?>
                            <tr style="color:green;">
                                <td colspan="12"><b>Grand Total</b></td>
                                <td colspan="2"><b>Rs. <?php echo number_format($grand_total); ?></b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="cart-summary">
                    <form method="POST" action="order_form.php">
                        <button type="submit" name="order" class="btn" style="background-color:teal;" <?php echo $has_out_of_stock ? 'disabled' : ''; ?>>Order Now</button>
                    </form>
                    <a href="jersey.php" class="btn" style="margin-top:0px;">Continue Shopping</a>
                </div>
            <?php else: ?>
                <p>Your cart is empty. <a href="jersey.php">Start Shopping</a></p>
            <?php endif; ?>
        <?php else: ?>
            <div id="guestCartWrapper" class="table-responsive" style="display:none;">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Jersey Size</th>
                            <th>Quality</th>
                            <th>Print Name</th>
                            <th>Print Number</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="guestCartBody"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="8"><b>Shipping</b></td>
                            <td colspan="2">Rs. <span id="guestShipping">150</span></td>
                        </tr>
                        <tr style="color:green;">
                            <td colspan="8"><b>Grand Total</b></td>
                            <td colspan="2"><b>Rs. <span id="guestGrandTotal">0</span></b></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div id="guestCartEmpty" style="display:none;">
                <p>Your cart is empty. <a href="jersey.php">Start Shopping</a></p>
            </div>

            <div class="cart-summary" id="guestCartActions" style="display:none;">
                <a href="loginsignup/login.php" class="btn" style="background-color:teal;">Login to Order</a>
                <a href="jersey.php" class="btn" style="margin-top:0px;">Continue Shopping</a>
            </div>

            <script>
                (function () {
                    const guestBody = document.getElementById("guestCartBody");
                    const guestWrapper = document.getElementById("guestCartWrapper");
                    const guestEmpty = document.getElementById("guestCartEmpty");
                    const guestActions = document.getElementById("guestCartActions");
                    const guestGrandTotal = document.getElementById("guestGrandTotal");

                    if (!window.guestCart || !guestBody) {
                        guestEmpty.style.display = "block";
                        return;
                    }

                    function esc(value) {
                        return String(value || "")
                            .replace(/&/g, "&amp;")
                            .replace(/</g, "&lt;")
                            .replace(/>/g, "&gt;")
                            .replace(/\"/g, "&quot;")
                            .replace(/'/g, "&#039;");
                    }

                    function formatMoney(value) {
                        const n = Number(value) || 0;
                        return n.toLocaleString();
                    }

                    function renderGuestCart() {
                        const items = window.guestCart.getCart();
                        guestBody.innerHTML = "";

                        if (!items.length) {
                            guestWrapper.style.display = "none";
                            guestActions.style.display = "none";
                            guestEmpty.style.display = "block";
                            guestGrandTotal.textContent = "0";
                            return;
                        }

                        let subtotal = 0;

                        items.forEach(function (item, index) {
                            const qty = Math.max(1, parseInt(item.quantity, 10) || 1);
                            const maxStock = Math.max(1, parseInt(item.max_stock, 10) || 1);
                            const unitPrice = Number(item.price_after_discount) || 0;
                            const total = unitPrice * qty;
                            subtotal += total;

                            const imgPath = item.image ? "../shared/products/" + esc(item.image) : "images/placeholder.png";
                            guestBody.insertAdjacentHTML("beforeend", `
                                <tr>
                                    <td data-label="Image"><img src="${imgPath}" class="img-thumb"></td>
                                    <td data-label="Name">${esc(item.pname)}</td>
                                    <td data-label="Category">${esc(item.category)}</td>
                                    <td data-label="Jersey Size">${esc(item.size)}</td>
                                    <td data-label="Quality">${esc(item.quality)}</td>
                                    <td data-label="Print Name">${esc(item.print_name)}</td>
                                    <td data-label="Print Number">${esc(item.print_number)}</td>
                                    <td data-label="Quantity">
                                        <input type="number" class="form-control form-control-sm" min="1" max="${maxStock}" value="${qty}" data-role="qty" data-index="${index}">
                                    </td>
                                    <td data-label="Total">Rs. ${formatMoney(total)}</td>
                                    <td data-label="Action">
                                        <button type="button" class="btn btn-remove" data-role="remove" data-index="${index}">Remove</button>
                                    </td>
                                </tr>
                            `);
                        });

                        const shipping = 150;
                        guestGrandTotal.textContent = formatMoney(subtotal + shipping);
                        guestWrapper.style.display = "block";
                        guestActions.style.display = "flex";
                        guestEmpty.style.display = "none";
                    }

                    guestBody.addEventListener("click", function (e) {
                        const target = e.target;
                        if (target && target.dataset.role === "remove") {
                            const idx = parseInt(target.dataset.index, 10);
                            if (!Number.isNaN(idx)) {
                                window.guestCart.removeItem(idx);
                            }
                        }
                    });

                    guestBody.addEventListener("change", function (e) {
                        const target = e.target;
                        if (target && target.dataset.role === "qty") {
                            const idx = parseInt(target.dataset.index, 10);
                            const value = parseInt(target.value, 10) || 1;
                            if (!Number.isNaN(idx)) {
                                window.guestCart.updateQuantity(idx, value);
                            }
                        }
                    });

                    document.addEventListener("guest-cart-updated", renderGuestCart);
                    renderGuestCart();
                })();
            </script>
        <?php endif; ?>
    </div>
</body>

</html>