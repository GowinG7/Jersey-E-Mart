<?php
session_start();
include("header.php");
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
            color: #1c605994;
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
            margin-top: 30px;
            margin-bottom: 18px;
            font-weight: 700;
        }

        hr {
            border: 1px solid #cdeeea;
            margin: 40px 0;
        }

        .card ul li {
            font-size: 0.9rem;
        }

        .discount-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 15px;
            z-index: 5;
            box-shadow: none !important;
            filter: brightness(1.15);
        }

        .card:hover .discount-badge {
            box-shadow: none !important;
        }
    </style>
</head>

<body>
    <?php include_once 'header.php'; ?>

    <h2 class="text-center section-title animate__animated" style="color:rgb(155,125,170,1);">Explore our Premium
        Products</h2>

    <!-- Search Bar -->
    <div class="container mt-3">
        <div class="row justify-content-center">
            <div class="col-sm-3 col-md-5 col-lg-6">
                <input id="searchInput" class="form-control me-2" type="search" placeholder="Search jersey..."
                    oninput="liveSearch(this.value)">
            </div>
        </div>
    </div>

    <!-- Live Search Result Container -->
    <div class="container my-3">
        <div class="row" id="searchResult"></div>
    </div>

    <div class="container my-3" id="defaultProducts">

        <!-- Football Jerseys -->
        <h3 class="text-center section-title animate__animated">Nepal Football Jerseys</h3>
        <div class="row justify-content-start">
            <?php
            $res = $conn->query("SELECT id, j_name AS title, description, image, quality, price, discount 
                             FROM products WHERE category='football' ORDER BY id DESC LIMIT 6");
            if ($res && $res->num_rows) {
                while ($r = $res->fetch_assoc()) {
                    $id = $r['id'];
                    $img = !empty($r['image']) ? '../shared/products/' . htmlspecialchars($r['image']) : 'images/placeholder.png';
                    $title = htmlspecialchars($r['title']);
                    $quality = htmlspecialchars($r['quality'] ?? '');
                    $desc = htmlspecialchars($r['description'] ?? '');
                    $price = intval($r['price']);
                    $discount = intval($r['discount']);

                    echo "
                <div class='col-sm-12 col-md-6 col-lg-4 mb-4'>
                    <a href='view_jersey.php?id={$id}' class='text-decoration-none text-dark'>
                        <div class='card text-center shadow-sm border-0 h-100 position-relative'>
                            " . ($discount > 0 ? "<span class='badge bg-danger discount-badge position-absolute'>{$discount}% OFF</span>" : "") . "
                            <img src='{$img}' class='card-img-top mx-auto mt-3' alt='{$title}' style='height:310px;width:auto;object-fit:contain;'>
                            <div class='card-body p-2 d-flex flex-column'>
                                <h6 class='card-title fw-semibold mb-1'>{$title}</h6>
                                <p class='card-text fw-semibold text-muted small mb-2'>{$quality}</p>
                                <p class='card-text text-muted small mb-2'>{$desc}</p>
                                <ul class='list-unstyled small mb-2 text-start mx-auto' style='max-width:200px;'>
                                    <li>
                                        " . ($discount > 0 ? "<span style='text-decoration:line-through; color:#888;'>Rs " . number_format($price) . "</span>
                                        <b class='ms-2 text-success'>Rs " . number_format($price - ($price * $discount / 100)) . "</b>"
                        : "<b>Rs " . number_format($price) . "</b>") . "
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </a>
                </div>";
                }
            } else {
                echo '<p class="text-muted">No football jerseys available.</p>';
            }
            ?>
        </div>

        <!-- Cricket Jerseys -->
        <h3 class="text-center section-title animate__animated">Nepal Cricket Jerseys</h3>
        <div class="row justify-content-start">
            <?php
            $res = $conn->query("SELECT id, j_name AS title, description, image, price, discount 
                             FROM products WHERE category='Cricket' ORDER BY id DESC LIMIT 6");
            if ($res && $res->num_rows) {
                while ($r = $res->fetch_assoc()) {
                    $id = $r['id'];
                    $img = !empty($r['image']) ? '../shared/products/' . htmlspecialchars($r['image']) : 'images/placeholder.png';
                    $title = htmlspecialchars($r['title']);
                    $desc = htmlspecialchars($r['description'] ?? '');
                    $price = intval($r['price']);
                    $discount = intval($r['discount']);

                    echo "
                <div class='col-sm-12 col-md-6 col-lg-4 mb-4'>
                    <a href='view_jersey.php?id={$id}' class='text-decoration-none text-dark'>
                        <div class='card text-center shadow-sm border-0 h-100 position-relative'>
                            " . ($discount > 0 ? "<span class='badge bg-danger discount-badge position-absolute'>{$discount}% OFF</span>" : "") . "
                            <img src='{$img}' class='card-img-top mx-auto mt-3' alt='{$title}' style='height:310px;width:auto;object-fit:contain;'>
                            <div class='card-body p-2 d-flex flex-column'>
                                <h6 class='card-title fw-semibold mb-1'>{$title}</h6>
                                <p class='card-text text-muted small mb-2'>{$desc}</p>
                                <ul class='list-unstyled small mb-2 text-start mx-auto' style='max-width:200px;'>
                                    <li>
                                        " . ($discount > 0 ? "<span style='text-decoration:line-through; color:#888;'>Rs " . number_format($price) . "</span>
                                        <b class='ms-2 text-success'>Rs " . number_format($price - ($price * $discount / 100)) . "</b>"
                        : "<b>Rs " . number_format($price) . "</b>") . "
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </a>
                </div>";
                }
            } else {
                echo '<p class="text-muted">No cricket jerseys available.</p>';
            }
            ?>
        </div>

        <!-- NPL Jerseys -->
        <h3 class="text-center section-title animate__animated">Nepal Premier League (NPL)</h3>
        <div class="row justify-content-start">
            <?php
            $res = $conn->query("SELECT id, j_name AS title, description, image, price, discount 
                             FROM products WHERE category='NPL cricket' ORDER BY id DESC LIMIT 6");
            if ($res && $res->num_rows) {
                while ($r = $res->fetch_assoc()) {
                    $id = $r['id'];
                    $img = !empty($r['image']) ? '../shared/products/' . htmlspecialchars($r['image']) : 'images/placeholder.png';
                    $title = htmlspecialchars($r['title']);
                    $desc = htmlspecialchars($r['description'] ?? '');
                    $price = intval($r['price']);
                    $discount = intval($r['discount']);

                    echo "
                <div class='col-sm-12 col-md-6 col-lg-4 mb-4'>
                    <a href='view_jersey.php?id={$id}' class='text-decoration-none text-dark'>
                        <div class='card text-center shadow-sm border-0 h-100 position-relative'>
                            " . ($discount > 0 ? "<span class='badge bg-danger discount-badge position-absolute'>{$discount}% OFF</span>" : "") . "
                            <img src='{$img}' class='card-img-top mx-auto mt-3' alt='{$title}' style='height:310px;width:auto;object-fit:contain;'>
                            <div class='card-body p-2 d-flex flex-column'>
                                <h6 class='card-title fw-semibold mb-1'>{$title}</h6>
                                <p class='card-text text-muted small mb-2'>{$desc}</p>
                                <ul class='list-unstyled small mb-2 text-start mx-auto' style='max-width:200px;'>
                                    <li>
                                        " . ($discount > 0 ? "<span style='text-decoration:line-through; color:#888;'>Rs " . number_format($price) . "</span>
                                        <b class='ms-2 text-success'>Rs " . number_format($price - ($price * $discount / 100)) . "</b>"
                        : "<b>Rs " . number_format($price) . "</b>") . "
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </a>
                </div>";
                }
            } else {
                echo '<p class="text-muted">No NPL jerseys available.</p>';
            }
            ?>
        </div>

    </div>

    <?php include_once 'footer.php'; ?>

    <!-- Live Search JS -->
    <script>
        function liveSearch(value) {
            const resultBox = document.getElementById("searchResult");

            if (value.trim() === "") {
                resultBox.innerHTML = "";
                document.getElementById("defaultProducts").style.display = "block";
                return;
            }

            document.getElementById("defaultProducts").style.display = "none";

            const xhr = new XMLHttpRequest();
            xhr.open("GET", "live_search.php?q=" + encodeURIComponent(value), true);
            xhr.onload = function () {
                resultBox.innerHTML = this.responseText;
            };
            xhr.send();
        }
    </script>
</body>

</html>