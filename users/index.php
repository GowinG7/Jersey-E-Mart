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
    <title>Home Page</title>

    <style>
        body {
            background-color: #e0f4f2;
            color: #2d5d58;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        h1{
            color: rgb(155, 125, 170, 1);
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
            color: rgba(28, 96, 89, 0.55);
        }

        hr {
            border: 1px solid #cdeeea;
            margin: 40px 0;
        }

        .card ul li {
            font-size: 0.9rem;
        }

        /* Carousel Wrapper */
        .carousel {
            border-radius: 14px;
            padding: 0;
            overflow: hidden;
            background-color: #dff3f0;
        }

        /* Single Slide */
        .carousel-item {
            height: 400px;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #dff3f0;
            transition: opacity 0.6s ease-in-out;
        }

        /* Fade Effect */
        .carousel-fade .carousel-item {
            opacity: 0;
        }

        .carousel-fade .carousel-item.active {
            opacity: 1;
        }

        /* Carousel Image */
        .carousel-item img {
            height: 100%;
            width: auto;
            object-fit: contain;
            border-radius: 12px;
            transition: transform 0.4s ease;
        }

        .carousel-item img:hover {
            transform: scale(1.03);
        }

        /* Prev/Next Controls */
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            filter: invert(80%);
        }


        .discount-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 15px;
            z-index: 5;
            /* stays above hover shadow */
            box-shadow: none !important;
            /* never gets shadow */
            filter: brightness(1.15);
            /* keeps pop effect even on hover */
        }

        .card:hover .discount-badge {
            box-shadow: none !important;
            /* ensures hover won't affect it */
        }



        /* For Mobile Screens */
        @media (max-width: 768px) {
            .carousel-item {
                min-height: 180px;
                /* smaller carousel on mobile */
            }

            .carousel-item img {
                height: auto;
                width: 90%;
                /* small padding on sides */
            }
        }
    </style>
</head>

<body>

    <!-- Carousel -->
    <div class="container py-4">
        <div id="carouselExampleAutoplaying" class="carousel slide carousel-fade shadow-sm" data-bs-ride="carousel"
            data-bs-interval="2700" style="border-radius: 14px; overflow:hidden;">

            <div class="carousel-inner" style="border-radius: 14px;">

                <?php
                $carousel = mysqli_query($conn, "SELECT image FROM carousel ORDER BY id DESC");

                $isFirst = true;
                while ($row = mysqli_fetch_assoc($carousel)) {
                    $active = $isFirst ? "active" : "";
                    $isFirst = false;
                    ?>
                    <div class="carousel-item <?php echo $active; ?>" style="height: 550px; background:#dff3f0;">
                        <img src="../shared/carousel/<?php echo $row['image']; ?>" class="d-block mx-auto"
                            alt="carousel image"
                            style="height: 100%; width: auto; object-fit: contain; border-radius:12px;">
                    </div>
                <?php } ?>

            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleAutoplaying"
                data-bs-slide="prev">
                <span class="carousel-control-prev-icon" style="filter:invert(80%);"></span>
                <span class="visually-hidden">Previous</span>
            </button>

            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying"
                data-bs-slide="next">
                <span class="carousel-control-next-icon" style="filter:invert(80%);"></span>
                <span class="visually-hidden">Next</span>
            </button>

        </div>
    </div>




    <!-- Explore Our Page -->
    <h1 class="text-center mt-5 mb-4 display-5 fw-bold animate__animated">
        <span>Explore Our Page</span>
    </h1>

    <div class="container my-5">

        <!-- Nepal Football Jerseys -->
        <h3 class="text-center section-title animate__animated">Nepal Football Jerseys</h3>
        <div class="row justify-content-start">
            <?php
            $res = $conn->query("SELECT id, j_name AS title, description, image,quality, price,discount 
                         FROM products 
                         WHERE category = 'football' 
                         ORDER BY id DESC LIMIT 6");

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

                            " . ($discount > 0 ? "<span class='badge bg-danger discount-badge discount-badge discount-badge discount-badge discount-badge discount-badge discount-badge discount-badge position-absolute' style='top:10px;font-size:15px; right:10px;'>{$discount}% OFF</span>" : "") . "

                            <img src='{$img}' class='card-img-top mx-auto mt-3' 
                                alt='{$title}' style='height:310px; width:auto; object-fit:contain;'>

                            <div class='card-body p-2 d-flex flex-column'>
                            <h6 class='card-title fw-semibold mb-1'>{$title}</h6>
                            <p class='card-text fw-semibold text-muted small mb-2'>{$quality}</p>
                            <p class='card-text text-muted small mb-2'>{$desc}</p>

                            <ul class='list-unstyled small mb-2 text-start mx-auto' style='max-width:200px;'>
                                <li>
                                    " . ($discount > 0
                        ? "<span style='text-decoration:line-through; color:#888;'>Rs " . number_format($price) . "</span>
                                            <b class='ms-2 text-success'>Rs " . number_format($price - ($price * $discount / 100)) . "</b>"
                        : "<b>Rs " . number_format($price) . "</b>"
                    ) . "
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


        <hr>

        <!-- Nepal Cricket Jerseys -->
        <h3 class="text-center section-title animate__animated">Nepal Cricket Jerseys</h3>
        <div class="row justify-content-start">
            <?php
            $res = $conn->query("SELECT id, j_name AS title,quality, description, image, price, discount 
                         FROM products 
                         WHERE category = 'Cricket' 
                         ORDER BY id DESC LIMIT 6");

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

                            " . ($discount > 0 ? "<span class='badge bg-danger discount-badge discount-badge discount-badge discount-badge discount-badge discount-badge discount-badge discount-badge position-absolute' style='top:10px;font-size:15px;right:10px;'>{$discount}% OFF</span>" : "") . "

                            <img src='{$img}' class='card-img-top mx-auto mt-3'
                                    alt='{$title}' style='height:310px; width:auto; object-fit:contain;'>

                            <div class='card-body p-2 d-flex flex-column'>
                                <h6 class='card-title fw-semibold mb-1'>{$title}</h6>
                                <p class='card-text fw-semibold text-muted small mb-2'>{$quality}</p>
                                <p class='card-text text-muted small mb-2'>{$desc}</p>

                                <ul class='list-unstyled small mb-2 text-start mx-auto' style='max-width:200px;'>
                                <li>
                                    " . ($discount > 0
                        ? "<span style='text-decoration:line-through; color:#888;'>Rs " . number_format($price) . "</span>
                                            <b class='ms-2 text-success'>Rs " . number_format($price - ($price * $discount / 100)) . "</b>"
                        : "<b>Rs " . number_format($price) . "</b>"
                    ) . "
                                </li>
                                </ul>

                            </div>
                            </div>
                        </a>
                        </div>";
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
            $res = $conn->query("SELECT id, j_name AS title, quality, description, image, price,discount 
                     FROM products 
                     WHERE category = 'NPL cricket' 
                     ORDER BY id DESC LIMIT 6");

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

                            <!-- DISCOUNT BADGE (only display if >0) -->
                            " . ($discount > 0
                        ? "<span class='badge bg-danger discount-badge position-absolute' style='top:10px;font-size:15px; right:10px;'>{$discount}% OFF</span>"
                        : ""
                    ) . "

                            <img src='{$img}' class='card-img-top mx-auto mt-3'
                                    alt='{$title}' style='height:310px; width:auto; object-fit:contain;'>

                            <div class='card-body p-2 d-flex flex-column'>
                                <h6 class='card-title fw-semibold mb-1'>{$title}</h6>
                                <p class='card-text fw-semibold text-muted small mb-2'>{$quality}</p>
                                <p class='card-text text-muted small mb-2'>{$desc}</p>

                                <ul class='list-unstyled small mb-2 text-start mx-auto' style='max-width:200px;'>
                                    <li>
                                        " . ($discount > 0
                        ? "<span style='text-decoration:line-through; color:#888;'>Rs " . number_format($price) . "</span>
                                                <b class='ms-2 text-success'>Rs " . number_format($price - ($price * $discount / 100)) . "</b>"
                        : "<b class='ms-2 text-success'>Rs " . number_format($price) . "</b>"
                    ) . "
                                    </li>
                                </ul>
                            </div>
                        </div>
                        </a>
                        </div>";
                }
            } else {
                echo "<p class='text-muted'>No NPL jerseys available.</p>";
            }
            ?>
        </div>

    </div>

    <!-- Reach Us / Head Office -->
    <section class="container my-5">
        <div class="row align-items-start">
            <!-- Map -->
            <div class="col-lg-7 col-md-8 col-12 mb-4 px-4">
                <div class="bg-white rounded shadow p-0 overflow-hidden">
                    <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Reach Us — Head Office</h5>
                    </div>
                    <iframe class="w-100 d-block" style="min-height:320px; border:0;" height="320"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d8407.168480256954!2d84.38693763278744!3d27.631362988094352!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3994fa7bee03649d%3A0x6eb3396ddd7fc183!2sindrapuri%20mandir%2C%20Bharatpur%2044200!5e0!3m2!1sen!2snp!4v1764871807942!5m2!1sen!2snp"
                        loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        aria-label="Jersey E-Mart Head Office map"></iframe>
                </div>
            </div>

            <!-- Contact details -->
            <div class="col-lg-5 col-md-4 col-12 mb-4 px-4">
                <div class="bg-white rounded shadow p-4 h-100">
                    <h6 class="fw-semibold"><i class="bi bi-geo-alt-fill me-2"></i>Address</h6>
                    <p class="mb-2">Bharatpur, Nepal</p>


                    <div class="d-grid gap-2">
                        <a href="https://www.google.com/maps/dir/?api=1&destination=Bharatpur+Nepal" target="_blank"
                            rel="noopener noreferrer" class="btn btn-success btn-sm">Get Directions</a>
                        <a href="https://www.google.com/maps/search/?api=1&query=Bharatpur+Nepal" target="_blank"
                            rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm">View Larger Map</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include_once 'footer.php'; ?>

</body>

</html>