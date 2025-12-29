<?php
require('../shared/commonlinks.php');
require('../shared/dbconnect.php');

// Upload image
if (isset($_POST['submit'])) {
  if (!empty($_FILES['carousel_picture']['name']) && $_FILES['carousel_picture']['error'] === 0) {

    $file = $_FILES['carousel_picture'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($ext, $allowed)) {
      $newName = time() . '_' . rand(1000, 9999) . '.' . $ext;
      move_uploaded_file($file['tmp_name'], "../shared/carousel/$newName");

      $conn->query("INSERT INTO carousel (image) VALUES ('$newName')");
      header("Location: carousel.php");
      exit;
    }
  }
}

// Delete image
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $res = $conn->query("SELECT image FROM carousel WHERE id=$id");
  if ($res->num_rows) {
    $row = $res->fetch_assoc();
    $path = "../shared/carousel/" . $row['image'];

    if (file_exists($path))
      unlink($path);

    $conn->query("DELETE FROM carousel WHERE id=$id");
  }
}

// Fetch all images
$images = [];
$res = $conn->query("SELECT * FROM carousel ORDER BY id DESC");
if ($res->num_rows) {
  $images = $res->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Admin Panel - Carousel</title>
  <link rel="stylesheet" href="css/header.css">
</head>

<body>

  <?php require('header.php'); ?>

  <main class="admin-content">
    <div class="container-fluid px-4 mt-3">
      <h3 class="mb-4">Manage Carousel Images</h3>

      <!-- Upload Section -->
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <form method="post" enctype="multipart/form-data" class="d-flex flex-wrap gap-2 align-items-center"
            onsubmit="return validateCarouselForm();">
            <input type="file" name="carousel_picture" id="carousel_picture" class="form-control w-auto" required>
            <button type="submit" name="submit" class="btn btn-primary">Upload</button>
            <button type="button" class="btn btn-secondary"
              onclick="document.getElementById('carousel_picture').value = '';">Cancel</button>
          </form>
        </div>
      </div>

      <!-- Display Images Grid -->
      <div class="row">
        <?php foreach ($images as $img): ?>
          <div class="col-6 col-sm-4 col-md-3 mb-3">
            <div class="card shadow-sm">
              <img src="../shared/carousel/<?= $img['image'] ?>" class="card-img-top"
                style="height:300px; object-fit:cover;">
              <div class="card-body text-center p-2">
                <a href="?delete=<?= $img['id'] ?>" class="btn btn-sm btn-danger w-100"
                  onclick="return confirm('Delete this image?')">Delete</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
  </main>

  <script>
    // GLOBALS
    const allowedCarouselTypes = ["image/jpeg", "image/png", "image/webp"];
    const maxCarouselSize = 2 * 1024 * 1024; // 2MB

    // Carousel form validation
    function validateCarouselForm(form) {
      const fileInput = form.querySelector('input[name="carousel_picture"]');
      if (!fileInput || fileInput.files.length === 0) {
        alert("Please select an image to upload.");
        return false;
      }

      const file = fileInput.files[0];
      const ext = file.name.split('.').pop().toLowerCase();

      if (!allowedCarouselTypes.includes(file.type) || !["jpg", "jpeg", "png", "webp"].includes(ext)) {
        alert("Invalid image type. Only JPG, PNG, WEBP allowed.");
        fileInput.value = "";
        return false;
      }

      if (file.size > maxCarouselSize) {
        alert("Image size must be less than 2MB.");
        fileInput.value = "";
        return false;
      }

      return true;
    }

    // Attach validation to carousel form
    document.querySelectorAll('form[enctype="multipart/form-data"]').forEach(form => {
      form.onsubmit = function () {
        return validateCarouselForm(this);
      };
    });
  </script>

</body>

</html>