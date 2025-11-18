<?php
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
        .carousel {
            padding: 15px;
            overflow: hidden;
            border-radius: 12px;
        }

        .carousel-item {
            height: 420px;
            display: block;
            background-color: rgba(224, 244, 242, 0.85);
        }

        .carousel-img {
            max-height: 100%;
            width: auto;
            margin: 0 auto;
            display: block;
            border-radius: 10px;
            transition: transform 0.6s ease;
        }

        .carousel-img:hover {
            transform: scale(1.05);
        }

        .carousel-fade .carousel-item {
            opacity: 0;
        }

        .carousel-fade .carousel-item.active {
            opacity: 1;
        }

        h1,h3 {
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

        @media (max-width: 768px) {
            .carousel-item {
                height: 250px;
            }

            .carousel-img {
                height: 100%;
            }
        }
    </style>
</head>

<body>
    <?php include_once 'header.php'; ?>

    <!-- Carousel -->
    <div class="container py-3">
        <div id="carouselExampleAutoplaying" class="carousel slide carousel-fade" data-bs-ride="carousel"
            data-bs-interval="2000">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="images/cricket/2026.jpg" class="carousel-img" alt="Jersey 1">
                </div>
                <div class="carousel-item">
                    <img src="images/football/home.jpg" class="carousel-img" alt="Jersey 2">
                </div>
                <div class="carousel-item">
                    <img src="images/football/third.jpg" class="carousel-img" alt="Jersey 3">
                </div>
                <div class="carousel-item">
                    <img src="images/NPL/janakpur.png" class="carousel-img" alt="Jersey 4">
                </div>
                <div class="carousel-item">
                    <img src="images/NPL/karnali.png" class="carousel-img" alt="Jersey 5">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleAutoplaying"
                data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying"
                data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>

    <!-- Explore Our Page -->
    <h1 class="text-center mt-5 mb-4 display-5 fw-bold animate__animated animate__fadeInDown">
        <span>Explore Our Page</span>
    </h1>

    <div class="container my-5">
        <!-- Nepal Football Jerseys -->
        <h3 class="text-center section-title">Nepal Football Jerseys</h3>
        <div class="row justify-content-start">
            <?php
            // Example: Repeatable card structure
            $footballJerseys = [
                ['img' => 'images/football/home.jpg', 'title' => 'Nepal Home Jersey 2025 Edition', 'desc' => 'Official Nepal national team jersey.', 'size' => 'M', 'color' => 'Red', 'price' => '$35'],
                ['img' => 'images/football/away.jpg', 'title' => 'Nepal Football Full Set', 'desc' => 'Includes jersey and matching shorts.', 'size' => 'M', 'color' => 'Red & Blue', 'price' => '$55'],
                ['img' => 'images/football/third.jpg', 'title' => 'Nepal Football Full Set', 'desc' => 'Includes jersey and matching shorts.', 'size' => 'M', 'color' => 'Red & Blue', 'price' => '$55']
            ];
            foreach ($footballJerseys as $jersey) {
                echo '
                <div class="col-sm-12 col-md-6 col-lg-4 mb-4">
                    <div class="card text-center shadow-sm border-0 h-100">
                        <img src="' . $jersey['img'] . '" class="card-img-top mx-auto mt-3" alt="' . $jersey['title'] . '" style="height:310px; width:auto; object-fit:contain;">
                        <div class="card-body p-2 d-flex flex-column">
                            <h6 class="card-title fw-semibold mb-1">' . $jersey['title'] . '</h6>
                            <p class="card-text text-muted small mb-2">' . $jersey['desc'] . '</p>
                            <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width: 200px;">
                                <li><strong>Price:</strong> ' . $jersey['price'] . '</li>
                            </ul>
                            <button class="btn btn-sm btn-primary mt-auto">Add to cart</button>
                        </div>
                    </div>
                </div>
                ';
            }
            ?>
        </div>

        <hr>

        <!-- Nepal Cricket Jerseys -->
        <h3 class="text-center section-title">Nepal Cricket Jerseys</h3>
        <div class="row justify-content-start">
            <!-- Cards can be dynamically generated like above -->
        </div>

        <hr>

        <!-- NPL Jerseys -->
        <h3 class="text-center section-title">Nepal Premier League (NPL)</h3>
        <div class="row justify-content-start">
            <!-- Cards can be dynamically generated like above -->
        </div>
    </div>

    <?php include_once 'footer.php'; ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
</body>

</html>