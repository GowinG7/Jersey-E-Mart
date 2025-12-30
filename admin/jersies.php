<?php
require_once "../shared/dbconnect.php";


/*  FETCH PRODUCT DATA FOR EDIT  */
if (isset($_POST['fetch_product'])) {
    $id = (int) $_POST['id'];
    $res = $conn->query("SELECT * FROM products WHERE id=$id");
    header('Content-Type: application/json'); // important for JSON
    echo json_encode($res->fetch_assoc());
    exit;
}

/*  FETCH SIZES FOR CUSTOMIZATION  */
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
            <input type='number' readonly class='form-control form-control-sm mx-2'
                value='$s[stock]' style='width:70px' aria-label='stock-$s[id]'>
            <button onclick='deleteSize($s[id])' class='btn btn-danger btn-sm py-0'>x</button>
        </div>";
    }
    exit;
}

/*  ADD / UPDATE SIZE  */
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

/*  DELETE SIZE  */
if (isset($_POST['del_size'])) {
    $conn->query("DELETE FROM product_sizes WHERE id=" . (int) $_POST['del_size']);
    echo "deleted";
    exit;
}

/*  UPDATE STOCK BY SIZE ID */
if (isset($_POST['update_stock'])) {
    $sid = (int) $_POST['id'];
    $stock = (int) $_POST['stock'];
    $conn->query("UPDATE product_sizes SET stock='$stock' WHERE id=$sid");
    echo "updated";
    exit;
}

/* 
   PRODUCT HANDLERS — add, edit, delete
   */

/*  ADD PRODUCT  */
if (isset($_POST['add_product'])) {
    $name = $_POST['j_name'];
    $cat = $_POST['category'];
    $country = $_POST['country'];
    $quality = $_POST['quality'];
    $price = $_POST['price'];
    $discount = $_POST['discount'];
    $desc = $_POST['description'];

    $image = "";
    // Validate uploaded image (optional). Allowed: jpg, jpeg, png, gif, webp. Max size 2MB.
    if (!empty($_FILES['image']['name'])) {
        $tmp = $_FILES['image']['tmp_name'];
        $size = $_FILES['image']['size'];
        $err = $_FILES['image']['error'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $tmp) : mime_content_type($tmp);
        finfo_close($finfo);
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if ($err !== UPLOAD_ERR_OK || $size > $maxSize || !in_array($mime, $allowedMimes) || !in_array($ext, $allowedExt)) {
            header('Location: jersies.php?img_err=invalid');
            exit;
        }

        $image = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($tmp, "../shared/products/$image");
    }

    $q = $conn->prepare("INSERT INTO products(j_name,category,country,quality,price,discount,description,image)
    VALUES(?,?,?,?,?,?,?,?)");
    $q->bind_param("ssssddss", $name, $cat, $country, $quality, $price, $discount, $desc, $image);
    $q->execute();
    header("Location: jersies.php");
    exit;
}

/*  EDIT PRODUCT  */
if (isset($_POST['edit_product'])) {
    $id = $_POST['edit_id'];
    $name = $_POST['j_name'];
    $cat = $_POST['category'];
    $country = $_POST['country'];
    $quality = $_POST['quality'];
    $price = $_POST['price'];
    $discount = $_POST['discount'];
    $desc = $_POST['description'];

    $imageSQL = "";
    if (!empty($_FILES['image']['name'])) {
        $tmp = $_FILES['image']['tmp_name'];
        $size = $_FILES['image']['size'];
        $err = $_FILES['image']['error'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $tmp) : mime_content_type($tmp);
        finfo_close($finfo);
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if ($err !== UPLOAD_ERR_OK || $size > $maxSize || !in_array($mime, $allowedMimes) || !in_array($ext, $allowedExt)) {
            header('Location: jersies.php?img_err=invalid');
            exit;
        }
        //euta problem aathiyo admin panel bata chaie image delete bayo tara admin>shared>products folder bata delete bayeko thiyena so this
        // get old image name so we can delete it after successful upload
        $resImg = $conn->query("SELECT image FROM products WHERE id=" . (int) $id);
        $oldImage = "";
        if ($resImg && $rimg = $resImg->fetch_assoc()) {
            $oldImage = $rimg['image'];
        }

        $image = time() . "_" . basename($_FILES['image']['name']);
        $dst = __DIR__ . "/../shared/products/" . $image;
        if (!move_uploaded_file($tmp, $dst)) {
            header('Location: jersies.php?img_err=invalid');
            exit;
        }

        // remove old image file if present
        if (!empty($oldImage)) {
            $oldPath = __DIR__ . "/../shared/products/" . $oldImage;
            if (file_exists($oldPath) && is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $imageSQL = ", image='$image'";
    }

    $conn->query("UPDATE products SET j_name='$name', category='$cat', country='$country', quality='$quality', price='$price',
    discount='$discount', description='$desc' $imageSQL WHERE id=$id");
    header("Location: jersies.php");
    exit;
}

/*  DELETE PRODUCT  */
if (isset($_GET['del'])) {
    $id = (int) $_GET['del'];
    // fetch image filename and unlink file if exists
    $res = $conn->query("SELECT image FROM products WHERE id=$id");
    if ($res && $row = $res->fetch_assoc()) {
        if (!empty($row['image'])) {
            $imgPath = __DIR__ . "/../shared/products/" . $row['image'];
            if (file_exists($imgPath) && is_file($imgPath)) {
                @unlink($imgPath);
            }
        }
    }
    $conn->query("DELETE FROM products WHERE id=$id");
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
        <?php if (isset($_GET['img_err'])) { ?>
            <div class="alert alert-danger">Invalid image upload. Allowed types: PNG, JPG, GIF, WEBP. Max size 2MB.</div>
        <?php } ?>
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
                        <th>Quality</th>
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
                            <td><?= $p['quality'] ?></td>
                            <td>Rs.<?= intval($p['price']) ?></td>
                            <td><?= $p['discount'] ?>%</td>
                            <td class="fw-bold text-success">Rs.<?= $final ?></td>

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
                                <button type="button" class="btn btn-primary btn-sm btn-manage" data-pid="<?= $pid ?>"
                                    data-name='<?= htmlspecialchars($p['j_name'], ENT_QUOTES) ?>'>Manage</button>
                                <button type="button" class="btn btn-warning btn-sm"
                                    onclick="openEditModal(<?= $pid ?>)">Edit</button>
                                <a href="?del=<?= $pid ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Remove Jersey?')">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!--  ADD PRODUCT MODAL  -->
        <div class="modal fade" id="addModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content p-3">
                    <h5>Add Jersey</h5>
                    <hr>
                    <form method="POST" enctype="multipart/form-data" class="row g-2"
                        onsubmit="return validateJerseyForm();">
                        <!-- Product fields -->
                        <div class="col-md-6"><input class="form-control" name="j_name" required
                                placeholder="Enter Jersey Name...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="category" id="add_category" aria-label="Category">
                                <option value="">Select Category</option>
                                <option>Football</option>
                                <option>Cricket</option>
                                <option>NPL cricket</option>
                                <option>NSL football</option>
                            </select>
                        </div>
                        <div class="col-md-3"><input class="form-control" name="country"
                                placeholder="Enter the name of country this jersey is..">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="quality" id="add_quality" aria-label="Quality">
                                <option value="">Select Quality</option>
                                <option>Premium</option>
                                <option>First Copy</option>
                                <option>replicas</option>
                            </select>
                        </div>
                        <div class="col-md-3"><input class="form-control" name="price" type="number"
                                placeholder="Enter price in Rs."></div>
                        <div class="col-md-3"><input class="form-control" name="discount" type="number"
                                placeholder="Enter discount in %"></div>
                        <div class="col-12"><textarea class="form-control" name="description"
                                placeholder="Write description..."></textarea></div>
                        <div class="col-md-6">
                            <input type="file" name="image" class="form-control"
                                accept="image/jpg,image/jpeg,image/png,image/gif,image/webp">
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" name="add_product" class="btn btn-success">Save</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!--  EDIT PRODUCT MODAL -->
        <div class="modal fade" id="editModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content p-3">
                    <h5>Edit Jersey</h5>
                    <hr>
                    <form method="POST" enctype="multipart/form-data" class="row g-2" id="editForm"
                        onsubmit="return validateJerseyForm();">
                        <input type="hidden" name="edit_id" id="edit_id">
                        <!-- Edit fields -->
                        <div class="col-md-6"><input class="form-control" name="j_name" id="edit_name" required
                                placeholder="Enter Jersey Name.."></div>
                        <div class="col-md-3">
                            <select class="form-select" name="category" id="edit_category" aria-label="Edit Category">
                                <option value="">Select Category</option>
                                <option>Football</option>
                                <option>Cricket</option>
                                <option>NPL cricket</option>
                                <option>NSL football</option>
                            </select>
                        </div>
                        <div class="col-md-3"><input class="form-control" name="country" id="edit_country"
                                placeholder="Enter the name of country this jersey is.."></div>
                        <div class="col-md-3">
                            <select class="form-select" name="quality" id="edit_quality" aria-label="Edit Quality">
                                <option value="">Select Quality</option>
                                <option>Premium</option>
                                <option>First Copy</option>
                                <option>replicas</option>
                            </select>
                        </div>
                        <div class="col-md-3"><input class="form-control" name="price" type="number" id="edit_price"
                                placeholder="Enter price in Rs."></div>
                        <div class="col-md-3"><input class="form-control" name="discount" type="number"
                                id="edit_discount" placeholder="Enter discount in %"></div>
                        <div class="col-12"><textarea class="form-control" name="description" id="edit_description"
                                placeholder="Write description..."></textarea></div>
                        <div class="col-md-6">
                            <input type="file" name="image" class="form-control"
                                accept="image/jpg,image/jpeg,image/png,image/gif,image/webp">
                        </div>
                        <div class="text-end mt-3">
                            <button name="edit_product" class="btn btn-success">Update</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!--  SIZE MODAL (CUSTOMIZE SIZES)-->
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

    </script>
    <script src="js/jersies.js"></script>
</body>

</html>