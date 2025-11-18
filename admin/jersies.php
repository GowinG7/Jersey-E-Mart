<?php
require('../shared/commonlinks.php');
require('../shared/dbconnect.php');

/*
   ADD JERSEY
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_jersey'])) {

    $j_name = $_POST['j_name'];
    $category = $_POST['category'];
    $country = $_POST['country'];
    $type = $_POST['type'] ?: null;
    $sizes = $_POST['sizes'] ?: null;
    $quantity = $_POST['quantity'] ?: 0;
    $price = $_POST['price'];
    $discount = $_POST['discount'] ?: 0;
    $stock = $_POST['stock'];
    $shipping = $_POST['shipping'] ?: 0;
    $description = $_POST['description'] ?: '';

    // Image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $img_name = time() . '_' . basename($_FILES['image']['name']);
        $target = "../shared/products/" . $img_name;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $image = $img_name;
    }

    $stmt = $conn->prepare("INSERT INTO products (j_name, category, country, type, sizes, quantity, price, discount, stock, shipping, description, image)
                            VALUES(?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssiiisssss", $j_name, $category, $country, $type, $sizes, $quantity, $price, $discount, $stock, $shipping, $description, $image);
    $stmt->execute();

    header("Location: jersies.php");
    exit;
}

/*
   UPDATE JERSEY
*/
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_id'])) {

    $id = $_POST['edit_id'];
    $j_name = $_POST['j_name'];
    $category = $_POST['category'];
    $country = $_POST['country'];
    $type = $_POST['type'] ?: null;
    $sizes = $_POST['sizes'] ?: null;
    $quantity = $_POST['quantity'] ?: 0;
    $price = $_POST['price'];
    $discount = $_POST['discount'];
    $stock = $_POST['stock'];
    $shipping = $_POST['shipping'];
    $description = $_POST['description'];

    // NEW IMAGE ?
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $img_name = time() . '_' . basename($_FILES['image']['name']);
        $target = "../shared/products/" . $img_name;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);

        // delete old
        $old = $conn->query("SELECT image FROM products WHERE id=$id")->fetch_assoc()['image'];
        if ($old && file_exists("../shared/products/" . $old)) {
            unlink("../shared/products/" . $old);
        }

        $conn->query("UPDATE products SET image='$img_name' WHERE id=$id");
    }

    $stmt = $conn->prepare("UPDATE products SET 
        j_name=?, category=?, country=?, type=?, sizes=?, quantity=?, price=?, discount=?, stock=?, shipping=?, description=?
        WHERE id=?");
    $stmt->bind_param(
        "ssssiiissssi",
        $j_name,
        $category,
        $country,
        $type,
        $sizes,
        $quantity,
        $price,
        $discount,
        $stock,
        $shipping,
        $description,
        $id
    );
    $stmt->execute();

    header("Location: jersies.php");
    exit;
}

/* 
   DELETE JERSEY
 */
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // delete image
    $img = $conn->query("SELECT image FROM products WHERE id=$id")->fetch_assoc()['image'];
    if ($img && file_exists("../shared/products/" . $img)) {
        unlink("../shared/products/" . $img);
    }

    $conn->query("DELETE FROM products WHERE id=$id");

    header("Location: jersies.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Admin Panel - Jerseys</title>
    <link rel="stylesheet" href="css/header.css">
</head>

<body style="background-color:lightgray">
    <?php require('header.php'); ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-10 ms-auto p-4">

                <h3 class="mb-4">Jerseys</h3>

                <div class="d-flex justify-content-end mb-3">

                    <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                        Add Jersey
                    </button>
                </div>
                <!-- Search box -->
                <div class="mb-3">
                    <input type="text" class="form-control" id="searchBox" placeholder="Search jersies details...">
                </div>


                <div class="table-responsive border" style="height:450px;overflow:auto;">
                    <table class="table table-hover" id="jersiesTable">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Product ID</th>
                                <th>Category</th>
                                <th>Country</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Discount</th>
                                <th>Stock</th>
                                <th>Shipping</th>
                                <th>Image</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php
                            $res = $conn->query("SELECT * FROM products ORDER BY id DESC");
                            $i = 1;

                            while ($j = $res->fetch_assoc()) {

                              $qty = isset($j['quantity']) ? (int)$j['quantity'] : 0;
                              $stockText = ($j['stock'] === "In Stock") ? "In Stock: {$qty}" : "Out of Stock";

                              echo "<tr>";
                              echo "<td>{$i}</td>";
                              echo "<td>{$j['j_name']}</td>";
                              echo "<td>{$j['id']}</td>";
                              echo "<td>{$j['category']}</td>";
                              echo "<td>{$j['country']}</td>";
                              echo "<td>{$j['type']}</td>";
                              echo "<td>{$j['sizes']}</td>";
                              echo "<td>{$qty}</td>";
                              echo "<td>{$j['price']}</td>";
                              echo "<td>{$j['discount']}</td>";
                              echo "<td>{$stockText}</td>";
                              echo "<td>{$j['shipping']}</td>";
                              echo "<td><img src='../shared/products/{$j['image']}' width='50'></td>";
                              echo "<td>
                                      <button class='btn btn-primary btn-sm edit-btn mb-1' data-id='{$j['id']}'>Edit</button>
                                      <button class='btn btn-danger btn-sm delete-btn mb-1' data-id='{$j['id']}'>Delete</button>
                                    </td>";
                              echo "</tr>";

                              $i++;
                            }
                        ?>
                        </tbody>
                    </table>
                </div>

                <!-- Add Jersey Modal -->
                <div class="modal fade" id="addModal">
                    <div class="modal-dialog modal-lg">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="add_jersey" value="1">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5>Add Jersey</h5>
                                </div>
                                <div class="modal-body">
                                    <div class="row">

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Name</label>
                                            <input type="text" name="j_name" class="form-control" required>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Category</label>
                                            <input type="text" name="category" class="form-control" required>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Country</label>
                                            <input type="text" name="country" class="form-control" required>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Type</label>
                                            <select name="type" class="form-select">
                                                <option value="">None</option>
                                                <option>Home</option>
                                                <option>Away</option>
                                                <option>Third</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Size</label>
                                            <select name="sizes" class="form-select">
                                                <option value="">Select</option>
                                                <option>S</option>
                                                <option>M</option>
                                                <option>L</option>
                                                <option>XL</option>
                                                <option>XXL</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Quantity</label>
                                            <input type="number" name="quantity" class="form-control" value="0"
                                                required>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Price</label>
                                            <input type="number" name="price" class="form-control" required>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Discount</label>
                                            <input type="number" name="discount" class="form-control" value="0">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Stock</label>
                                            <select name="stock" class="form-select">
                                                <option>In Stock</option>
                                                <option>Out of Stock</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Shipping</label>
                                            <input type="number" name="shipping" class="form-control" value="0">
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label class="fw-bold">Image</label>
                                            <input type="file" name="image" class="form-control" required>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label class="fw-bold">Description</label>
                                            <textarea name="description" class="form-control"></textarea>
                                        </div>

                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button class="btn btn-primary">Add</button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal">
                    <div class="modal-dialog modal-lg">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="edit_id" id="edit_id">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5>Edit Jersey</h5>
                                </div>
                                <div class="modal-body">
                                    <div class="row">

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Name</label>
                                            <input type="text" name="j_name" id="edit_j_name" class="form-control">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Category</label>
                                            <input type="text" name="category" id="edit_category" class="form-control">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Country</label>
                                            <input type="text" name="country" id="edit_country" class="form-control">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Type</label>
                                            <select name="type" id="edit_type" class="form-select">
                                                <option value="">None</option>
                                                <option>Home</option>
                                                <option>Away</option>
                                                <option>Third</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Size</label>
                                            <select name="sizes" id="edit_sizes" class="form-select">
                                                <option value="">Select</option>
                                                <option>S</option>
                                                <option>M</option>
                                                <option>L</option>
                                                <option>XL</option>
                                                <option>XXL</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Quantity</label>
                                            <input type="number" name="quantity" id="edit_quantity"
                                                class="form-control">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Price</label>
                                            <input type="number" name="price" id="edit_price" class="form-control">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Discount</label>
                                            <input type="number" name="discount" id="edit_discount"
                                                class="form-control">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Stock</label>
                                            <select name="stock" id="edit_stock" class="form-select">
                                                <option>In Stock</option>
                                                <option>Out of Stock</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Shipping</label>
                                            <input type="number" name="shipping" id="edit_shipping"
                                                class="form-control">
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label class="fw-bold">Image (optional)</label>
                                            <input type="file" name="image" class="form-control">
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label class="fw-bold">Description</label>
                                            <textarea name="description" id="edit_description"
                                                class="form-control"></textarea>
                                        </div>

                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button class="btn btn-primary">Update</button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>

        // Simple search
        const searchBox = document.getElementById('searchBox');
        const tbody = document.getElementById('jersiesTable').getElementsByTagName('tbody')[0];
        searchBox?.addEventListener('input', function () {
            const filter = this.value.toLowerCase();
            for (let row of tbody.rows) {
                row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
            }
        });
        // DELETE
        document.querySelectorAll(".delete-btn").forEach(btn => {
            btn.onclick = function () {
                let id = this.dataset.id;
                if (confirm("Confirm delete?")) {
                    location.href = "jersies.php?delete=" + id;
                }
            }
        });

        // EDIT
        document.querySelectorAll(".edit-btn").forEach(btn => {
            btn.onclick = function () {
                let id = this.dataset.id;

                fetch("fetch_single.php?id=" + id)
                    .then(res => res.json())
                    .then(d => {
                        document.getElementById("edit_id").value = d.id;
                        document.getElementById("edit_j_name").value = d.j_name;
                        document.getElementById("edit_category").value = d.category;
                        document.getElementById("edit_country").value = d.country;
                        document.getElementById("edit_type").value = d.type;
                        document.getElementById("edit_sizes").value = d.sizes;
                        document.getElementById("edit_quantity").value = d.quantity;
                        document.getElementById("edit_price").value = d.price;
                        document.getElementById("edit_discount").value = d.discount;
                        document.getElementById("edit_stock").value = d.stock;
                        document.getElementById("edit_shipping").value = d.shipping;
                        document.getElementById("edit_description").value = d.description;

                        new bootstrap.Modal(document.getElementById("editModal")).show();
                    });
            }
        });
    </script>

</body>

</html>