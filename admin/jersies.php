<?php
require_once "../shared/dbconnect.php";


/* ---------------- FETCH PRODUCT DATA FOR EDIT ---------------- */
if (isset($_POST['fetch_product'])) {
    $id = (int) $_POST['id'];
    $res = $conn->query("SELECT * FROM products WHERE id=$id");
    header('Content-Type: application/json'); // important for JSON
    echo json_encode($res->fetch_assoc());
    exit;
}

/* ---------------- FETCH SIZES FOR CUSTOMIZATION ---------------- */
if (isset($_POST['fetch_sizes'])) {
    $pid = (int) $_POST['pid'];
    $q = $conn->query("SELECT * FROM product_sizes WHERE product_id=$pid");
    if ($q->num_rows == 0) {
        echo "<span class='text-muted'>No Sizes Added</span>";
        exit;
    }
    while ($s = $q->fetch_assoc()) {
        echo "
        <div class='d-flex align-items-center mb-1'>
            <b style='width:45px;'>$s[size]</b>
            <input type='number' class='form-control form-control-sm mx-2'
                value='$s[stock]' onchange='updateStock($s[id],this.value)' style='width:70px'>
            <button onclick='deleteSize($s[id])' class='btn btn-danger btn-sm py-0'>x</button>
        </div>";
    }
    exit;
}

/* ---------------- ADD / UPDATE SIZE ---------------- */
if (isset($_POST['add_size'])) {
    $pid = (int) $_POST['product_id'];
    $size = $_POST['size'];
    $stock = $_POST['stock'];

    if ($size == '')
        exit; // prevent empty size

    $exist = $conn->query("SELECT id FROM product_sizes WHERE product_id=$pid AND size='$size'");
    if ($exist->num_rows) {
        $conn->query("UPDATE product_sizes SET stock='$stock' WHERE product_id=$pid AND size='$size'");
        echo "updated";
        exit;
    } else {
        $conn->query("INSERT INTO product_sizes(product_id,size,stock) VALUES($pid,'$size','$stock')");
        echo "inserted";
        exit;
    }
}

/* ---------------- DELETE SIZE ---------------- */
if (isset($_POST['del_size'])) {
    $conn->query("DELETE FROM product_sizes WHERE id=" . (int) $_POST['del_size']);
    echo "deleted";
    exit;
}

/* ============================================================
   PRODUCT HANDLERS — add, edit, delete
   ============================================================ */

/* ---------------- ADD PRODUCT ---------------- */
if (isset($_POST['add_product'])) {
    $name = $_POST['j_name'];
    $cat = $_POST['category'];
    $country = $_POST['country'];
    $type = $_POST['type'];
    $price = $_POST['price'];
    $discount = $_POST['discount'];
    $desc = $_POST['description'];

    $image = "";
    if (!empty($_FILES['image']['name'])) {
        $image = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "../shared/products/$image");
    }

    $q = $conn->prepare("INSERT INTO products(j_name,category,country,type,price,discount,description,image)
    VALUES(?,?,?,?,?,?,?,?)");
    $q->bind_param("ssssddss", $name, $cat, $country, $type, $price, $discount, $desc, $image);
    $q->execute();
    header("Location: jersies.php");
    exit;
}

/* ---------------- EDIT PRODUCT ---------------- */
if (isset($_POST['edit_product'])) {
    $id = $_POST['edit_id'];
    $name = $_POST['j_name'];
    $cat = $_POST['category'];
    $country = $_POST['country'];
    $type = $_POST['type'];
    $price = $_POST['price'];
    $discount = $_POST['discount'];
    $desc = $_POST['description'];

    $imageSQL = "";
    if (!empty($_FILES['image']['name'])) {
        $image = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "../shared/products/$image");
        $imageSQL = ", image='$image'";
    }

    $conn->query("UPDATE products SET j_name='$name', category='$cat', country='$country', type='$type', price='$price',
    discount='$discount', description='$desc' $imageSQL WHERE id=$id");
    header("Location: jersies.php");
    exit;
}

/* ---------------- DELETE PRODUCT ---------------- */
if (isset($_GET['del'])) {
    $conn->query("DELETE FROM products WHERE id=" . $_GET['del']);
    header("Location: jersies.php");
    exit;
}
?>

<html>

<head>
    <title>Jersey Inventory</title>
    <?php include_once '../shared/commonlinks.php'; ?>
    <link rel="stylesheet" href="css/header.css">
    <style>
        body {
            background: #e9fffa;
            font-family: calibri;
        }

        .table-header {
            background: #00796b;
            color: #fff;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <?php
    include_once "header.php";
    ?>
    <div class="container p-3" style="margin-top:70px;margin-left:230px;"> <!-- space for sticky header -->
        <div class="row " style="margin-right:40px;">
            <div class="d-flex justify-content-between mb-3">
                <h3 style="font-weight:700;">Jersey Inventory</h3>
                <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addModal">+ Add Jersey</button>
            </div>

            <!-- search box -->
            <div class="mb-3">
                <input type="text" id="searchBox" class="form-control"
                    placeholder="Search Jerseys by Name or Category...">
            </div>


            <!-- PRODUCT TABLE -->
            <table class="table table-bordered table-striped">
                <thead class="table-header table-dark">
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Country</th>
                        <th>Price</th>
                        <th>Discount</th>
                        <th>Final</th>
                        <th>Sizes</th>
                        <th width="200">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $r = $conn->query("SELECT * FROM products ORDER BY id DESC");
                    while ($p = $r->fetch_assoc()) {
                        $pid = $p['id'];
                        $final = $p['price'] - ($p['price'] * $p['discount'] / 100);
                        ?>
                        <tr>
                            <td><img src="../shared/products/<?= $p['image'] ?>" width="55"></td>
                            <td><?= $p['j_name'] ?></td>
                            <td><?= $p['category'] ?></td>
                            <td><?= $p['country'] ?></td>
                            <td><?= $p['price'] ?></td>
                            <td><?= $p['discount'] ?>%</td>
                            <td class="fw-bold text-success"><?= $final ?></td>

                            <td id="sizeCell<?= $pid ?>"> <!-- Display Sizes -->
                                <?php
                                $sz = $conn->query("SELECT * FROM product_sizes WHERE product_id=$pid");
                                if ($sz->num_rows) {
                                    while ($x = $sz->fetch_assoc())
                                        echo "<span class='badge bg-dark mx-1'> $x[size] : $x[stock] </span>";
                                } else
                                    echo "<span class='text-muted'>None</span>";
                                ?>
                            </td>

                            <td>
                                <!-- BUTTONS: Size Modal (Customize), Edit Modal, Delete -->
                                <button class="btn btn-primary btn-sm"
                                    onclick="openSizeModal(<?= $pid ?>,'<?= $p['j_name'] ?>')">Manage</button>
                                <button class="btn btn-warning btn-sm" onclick="openEditModal(<?= $pid ?>)">Edit</button>
                                <a href="?del=<?= $pid ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Remove Jersey?')">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- ================= ADD PRODUCT MODAL ================= -->
        <div class="modal fade" id="addModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content p-3">
                    <h5>Add Jersey</h5>
                    <hr>
                    <form method="POST" enctype="multipart/form-data" class="row g-2">
                        <!-- Product fields -->
                        <div class="col-md-6"><input class="form-control" name="j_name" required
                                placeholder="Jersey Name">
                        </div>
                        <div class="col-md-3"><input class="form-control" name="category" placeholder="Category">
                        </div>
                        <div class="col-md-3"><input class="form-control" name="country" placeholder="Country">
                        </div>
                        <div class="col-md-3"><input class="form-control" name="type" placeholder="Type"></div>
                        <div class="col-md-3"><input class="form-control" name="price" type="number" step="any"
                                placeholder="Price"></div>
                        <div class="col-md-3"><input class="form-control" name="discount" type="number"
                                placeholder="Discount %"></div>
                        <div class="col-12"><textarea class="form-control" name="description"
                                placeholder="Description"></textarea></div>
                        <div class="col-md-6"><input type="file" name="image" class="form-control"></div>
                        <div class="text-end mt-3">
                            <button type="submit" name="add_product" class="btn btn-success">Save</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ================= EDIT PRODUCT MODAL ================= -->
        <div class="modal fade" id="editModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content p-3">
                    <h5>Edit Jersey</h5>
                    <hr>
                    <form method="POST" enctype="multipart/form-data" class="row g-2" id="editForm">
                        <input type="hidden" name="edit_id" id="edit_id">
                        <!-- Edit fields -->
                        <div class="col-md-6"><input class="form-control" name="j_name" id="edit_name" required
                                placeholder="Jersey Name"></div>
                        <div class="col-md-3"><input class="form-control" name="category" id="edit_category"
                                placeholder="Category"></div>
                        <div class="col-md-3"><input class="form-control" name="country" id="edit_country"
                                placeholder="Country"></div>
                        <div class="col-md-3"><input class="form-control" name="type" id="edit_type" placeholder="Type">
                        </div>
                        <div class="col-md-3"><input class="form-control" name="price" type="number" step="any"
                                id="edit_price" placeholder="Price"></div>
                        <div class="col-md-3"><input class="form-control" name="discount" type="number"
                                id="edit_discount" placeholder="Discount %"></div>
                        <div class="col-12"><textarea class="form-control" name="description" id="edit_description"
                                placeholder="Description"></textarea></div>
                        <div class="col-md-6"><input type="file" name="image" class="form-control"></div>
                        <div class="text-end mt-3">
                            <button name="edit_product" class="btn btn-success">Update</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- ================= SIZE MODAL (CUSTOMIZE SIZES) ================= -->
        <div class="modal fade" id="sizeModal">
            <div class="modal-dialog">
                <div class="modal-content p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 id="sizeTitle">Customize Sizes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <select id="sizeSelect" class="form-select mb-2">
                        <option>Small</option>
                        <option>Medium</option>
                        <option>Large</option>
                        <option>XL</option>
                        <option>XXL</option>
                    </select>
                    <input type="number" id="stockInput" placeholder="Stock" class="form-control mb-2">
                    <button class="btn btn-success w-100" onclick="saveSize()">Save</button>
                    <hr>
                    <div id="sizesList"></div>
                </div>
            </div>
        </div>
    </div>


    <script>
        // For search functionality
        const searchBox = document.getElementById('searchBox');
        const table = document.querySelector('table tbody');

        searchBox.addEventListener('keyup', function () {
            const filter = this.value.toLowerCase();
            const rows = table.querySelectorAll('tr');

            rows.forEach(row => {
                const name = row.cells[1].textContent.toLowerCase(); // Name column
                const category = row.cells[2].textContent.toLowerCase(); // Category column
                const country = row.cells[3].textContent.toLowerCase(); // Country column

                if (name.includes(filter) || category.includes(filter) || country.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });



        let PROD = 0;

        /* ================= SIZE MODAL FUNCTIONS ================= */
        function openSizeModal(id, name) {
            PROD = id;
            document.getElementById("sizeTitle").innerHTML = "Sizes — " + name;
            fetch("jersies.php", {
                method: "POST",
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: "fetch_sizes=1&pid=" + id
            })
                .then(r => r.text()).then(d => document.getElementById("sizesList").innerHTML = d);
            new bootstrap.Modal(document.getElementById("sizeModal")).show();
        }

        function saveSize() {
            fetch("jersies.php", {
                method: "POST",
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `add_size=1&product_id=${PROD}&size=${sizeSelect.value}&stock=${stockInput.value}`
            })
                .then(() => location.reload());
        }

        function updateStock(id, val) {
            fetch("jersies.php", {
                method: "POST",
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `add_size=1&product_id=${PROD}&size=&stock=${val}`
            });
        }

        function deleteSize(id) {
            fetch("jersies.php", {
                method: "POST",
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `del_size=${id}`
            }).then(() => location.reload());
        }

        /* ================= EDIT MODAL FUNCTIONS ================= */
        function openEditModal(id) {
            // use current path to avoid accidental relative path issues
            fetch(location.pathname, {
                method: "POST",
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `fetch_product=1&id=${id}`
            })
                .then(async (r) => {
                    if (!r.ok) throw new Error('Network response not ok: ' + r.status);
                    const ct = r.headers.get('Content-Type') || '';
                    if (ct.includes('application/json')) return r.json();
                    // if not JSON, log full response for debugging
                    const text = await r.text();
                    console.error('Expected JSON but got:', text);
                    throw new Error('Invalid server response');
                })
                .then(d => {
                    if (!d || !d.id) {
                        console.error('Product data missing:', d);
                        alert('Failed to load product data');
                        return;
                    }
                    document.getElementById('edit_id').value = d.id;
                    document.getElementById('edit_name').value = d.j_name;
                    document.getElementById('edit_category').value = d.category;
                    document.getElementById('edit_country').value = d.country;
                    document.getElementById('edit_type').value = d.type;
                    document.getElementById('edit_price').value = d.price;
                    document.getElementById('edit_discount').value = d.discount;
                    document.getElementById('edit_description').value = d.description;
                    new bootstrap.Modal(document.getElementById('editModal')).show();
                })
                .catch(err => {
                    console.error('Failed fetching product:', err);
                    alert('Unable to open edit dialog. See console for details.');
                });
        }
    </script>

</body>

</html>