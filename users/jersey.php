<?php
require_once '../shared/dbconnect.php';
include_once '../shared/commonlinks.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jersey Page</title>

    <style>
        body {
            background-color: #e0f4f2;
            color: #2d5d58;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        h1,
        h3 {
            color: #1c6059;
        }

        h1 span {
            border-bottom: 3px solid #47a589;
            padding-bottom: 5px;
        }

        .card {
            border-radius: 14px;
            background: linear-gradient(145deg, #ffffff, #cdeeea);
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 24px rgba(48, 107, 101, 0.25);
        }

        .card img {
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.7);
            padding: 10px;
            transition: transform 0.3s ease-in-out;
        }

        .card:hover img {
            transform: scale(1.03);
        }

        .card-title {
            color: #1c6059;
            font-weight: 700;
        }

        .card-text {
            color: #4f6765;
        }

        .btn-primary {
            background-color: #379069;
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #2c6b60;
        }

        a {
            color: #1c6059;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #47a589;
        }

        .section-title {
            margin-top: 60px;
            margin-bottom: 30px;
            font-weight: 700;
        }

        hr {
            border: 1px solid #cdeeea;
            margin: 40px 0;
        }

        .card ul li {
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <?php include_once 'header.php'; ?>

    <!-- for search bar -->
    <div class="container mt-3">
        <div class="row justify-content-center">
            <div class="col-sm-8 col-md-10 col-lg-12">
                <form class="d-flex justify-content-center mx-auto" action="#" method="GET" style="max-width: 700px;">
                    <!-- Dropdown for Jersey Type -->
                    <!-- <select id="jerseyType" name="type" class="form-select me-2" style="max-width: 180px;">
                        <option value="">Choose</option>
                        <option value="national-football">Nepal National Football</option>
                        <option value="national-cricket">Nepal National Cricket</option>
                        <option value="npl">NPL Jersey</option>
                    </select> -->

                    <!-- Search Input -->
                    <input id="searchInput" class="form-control me-2" type="search" name="query"
                        placeholder="Search jersey..." aria-label="Search">

                    <!-- Submit Button -->
                    <button class="btn btn-primary" type="submit">Search</button>
                </form>
            </div>
        </div>
    </div>


    <div class="container my-5">
        <h1 class="text-center mt-5 mb-4 display-5 fw-bold animate__animated">
            <span>Explore Jersies</span>
        </h1>

        <div class="row justify-content-start">
            <?php
            // Query product rows (limit to 6 for homepage)
            $res = $conn->query("SELECT id, j_name AS title, description,type, image, price 
                         FROM products 
                         WHERE category = 'football' 
                         ORDER BY id DESC LIMIT 6");

            if ($res && $res->num_rows) {
                while ($r = $res->fetch_assoc()) {

                    $id = $r['id'];
                    $img = !empty($r['image']) ? '../shared/products/' . htmlspecialchars($r['image']) : 'images/placeholder.png';
                    $title = htmlspecialchars($r['title']);
                    $type = htmlspecialchars($r['type'] ?? '');
                    $desc = htmlspecialchars($r['description'] ?? '');
                    $price = htmlspecialchars($r['price'] ?? '');
                    $price = intval($price);

                    echo "
            <div class='col-sm-12 col-md-6 col-lg-4 mb-4'>
              <a href='view_jersey.php?id={$id}' class='text-decoration-none text-dark'>
                <div class='card text-center shadow-sm border-0 h-100'>
                  <img src='{$img}' class='card-img-top mx-auto mt-3' 
                       alt='{$title}' 
                       style='height:310px; width:auto; object-fit:contain;'>

                  <div class='card-body p-2 d-flex flex-column'>
                    <h6 class='card-title fw-semibold mb-1'>{$title}</h6>
                    <p class='card-text fw-semibold text-muted small mb-2'>{$type}</p>
                    <p class='card-text text-muted small mb-2'>{$desc}</p>

                    <ul class='list-unstyled small mb-2 text-start mx-auto' style='max-width: 200px;'>
                      <li><b>Rs {$price}</b></li>
                    </ul>
                  </div>
                </div>
              </a>
            </div>
            ";
                }
            } else {
                echo '<p class="text-muted">No football jerseys available.</p>';
            }
            ?>
        </div>


        <hr>

        <!-- Nepal Cricket Jerseys -->
        <h3 class="text-center section-title animate__animated">Nepal Cricket Jerseys</h3>
        <div class="row justify-content-start">
            <?php
            // Query product rows (limit to 6 for homepage)
            $res = $conn->query("SELECT id, j_name AS title, description, image, price FROM products WHERE category = 'Cricket' ORDER BY id DESC LIMIT 6");
            if ($res && $res->num_rows) {
                while ($r = $res->fetch_assoc()) {
                    //yeha $id assign garena baney view jersey ma previous jersey dekhinxa
                    $id = $r['id'];
                    $img = !empty($r['image']) ? '../shared/products/' . htmlspecialchars($r['image']) : 'images/placeholder.png';
                    $title = htmlspecialchars($r['title']);
                    $desc = htmlspecialchars($r['description'] ?? '');
                    $price =intval( htmlspecialchars($r['price'] ?? ''));
                    echo "
                <div class='col-sm-12 col-md-6 col-lg-4 mb-4'>
                 <a href='view_jersey.php?id={$id}' class='text-decoration-none text-dark'>
                <div class='card text-center shadow-sm border-0 h-100'>
                  <img src='{$img}' class='card-img-top mx-auto mt-3' 
                       alt='{$title}' 
                       style='height:310px; width:auto; object-fit:contain;'>

                  <div class='card-body p-2 d-flex flex-column'>
                    <h6 class='card-title fw-semibold mb-1'>{$title}</h6>
                    <p class='card-text text-muted small mb-2'>{$desc}</p>

                    <ul class='list-unstyled small mb-2 text-start mx-auto' style='max-width: 200px;'>
                      <li><b>Rs {$price}</b></li>
                    </ul>
                  </div>
                </div>
              </a>
            </div>
                    ";
                }
            } else {
                echo '<p class="text-muted">No Cricket jerseys available.</p>';
            }
            ?>
        </div>

        <hr>

        <!-- NPL Jerseys -->
        <h3 class="text-center section-title animate__animated">Nepal Premier League (NPL)</h3>
        <div class="row justify-content-start">
            <?php
            // Query product rows (limit to 6 for homepage)
            $res = $conn->query("SELECT id, j_name AS title, description, image, price FROM products WHERE category = 'NPL cricket' ORDER BY id DESC LIMIT 6");
            if ($res && $res->num_rows) {
                while ($r = $res->fetch_assoc()) {
                    //yeha assign garena baney view_jersey ma previous jersey id select hunca
                    $id = $r['id'];
                    $img = !empty($r['image']) ? '../shared/products/' . htmlspecialchars($r['image']) : 'images/placeholder.png';
                    $title = htmlspecialchars($r['title']);
                    $desc = htmlspecialchars($r['description'] ?? '');
                    $price = intval(htmlspecialchars($r['price'] ?? ''));
                    echo "
                    <div class='col-sm-12 col-md-6 col-lg-4 mb-4'>
                 <a href='view_jersey.php?id={$id}' class='text-decoration-none text-dark'>
                <div class='card text-center shadow-sm border-0 h-100'>
                  <img src='{$img}' class='card-img-top mx-auto mt-3' 
                       alt='{$title}' 
                       style='height:310px; width:auto; object-fit:contain;'>

                  <div class='card-body p-2 d-flex flex-column'>
                    <h6 class='card-title fw-semibold mb-1'>{$title}</h6>
                    <p class='card-text text-muted small mb-2'>{$desc}</p>

                    <ul class='list-unstyled small mb-2 text-start mx-auto' style='max-width: 200px;'>
                      <li><b>Rs {$price}</b></li>
                    </ul>
                  </div>
                </div>
              </a>
            </div>
                    ";
                }
            } else {
                echo '<p class="text-muted">No NPL jerseys available.</p>';
            }
            ?>
        </div>
    </div>

    <?php include_once 'footer.php'; ?>

</body>

</html>