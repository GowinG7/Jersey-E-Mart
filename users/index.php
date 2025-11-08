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
            background-color: rgb(180, 235, 230);
        }

        .carousel {
            padding: 15px;
            overflow: hidden;
            border-radius: 12px;
        }

        /* Each slide fixed size and centered */
        .carousel-item {
            height: 420px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #ffffff98;
            /* transition: opacity 1s ease-in-out; */
        }


        /* Image styling */
        .carousel-img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
            display: block;
            margin: 0 auto;
            border-radius: 10px;
            transition: 2s all linear;
        }

        Prevent layout shift (the key fix) .carousel-inner {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .carousel-fade .carousel-item {
            opacity: 0;
            transition-property: opacity;
            transition-duration: 1s;
            transition-timing-function: ease-in-out;
        }

        .carousel-fade .carousel-item.active {
            opacity: 1;
        }
    </style>
</head>

<body>
    <?php include_once 'header.php'; ?>

    <div class="container py-3">
        <div id="carouselExampleAutoplaying" class="carousel slide carousel-fade" data-bs-ride="carousel"
            data-bs-interval="2000">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="images/j1.png" class="carousel-img" alt="Jersey 1">
                </div>
                <div class="carousel-item">
                    <img src="images/j2.png" class="carousel-img" alt="Jersey 2">
                </div>
                <div class="carousel-item">
                    <img src="images/j3.png" class="carousel-img" alt="Jersey 3">
                </div>
                <div class="carousel-item">
                    <img src="images/j4.png" class="carousel-img" alt="Jersey 4">
                </div>
                <div class="carousel-item">
                    <img src="images/j5.png" class="carousel-img" alt="Jersey 5">
                </div>
            </div>

            <!-- Controls -->
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

    <h1 class="text-center mt-4">Explore Our Page</h1>
    <p class="text-center mb-4">Please check out our premium products</p>

    <div class="container">
        <div class="row justify-content-start">
            <!-- Jersey Card -->
            <div class="col-sm-12 col-md-6 col-lg-4 mb-4">
                <div class="card text-center shadow-sm border-0" style="max-height: 420px;">
                    <img src="images/j1.png" class="card-img-top mx-auto mt-3" alt="Team Jersey"
                        style="height:150px; width:auto; object-fit:contain;">
                    <div class="card-body p-2 d-flex flex-column">
                        <h6 class="card-title fw-semibold mb-1">Team Jersey</h6>
                        <p class="card-text text-muted small mb-2">Sleek, breathable design for peak performance.</p>
                        <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width: 200px;">
                            <li><strong>Size:</strong> M</li>
                            <li><strong>Color:</strong> Blue</li>
                            <li><strong>Price:</strong> $45</li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-auto">Buy Now</button>
                    </div>
                </div>
            </div>

            <!-- Repeat this block for each jersey -->
            <div class="col-sm-12 col-md-6 col-lg-4 mb-4">
                <div class="card text-center shadow-sm border-0" style="max-height: 420px;">
                    <img src="images/j2.png" class="card-img-top mx-auto mt-3" alt="Team Jersey"
                        style="height:150px; width:auto; object-fit:contain;">
                    <div class="card-body p-2 d-flex flex-column">
                        <h6 class="card-title fw-semibold mb-1">Team Jersey</h6>
                        <p class="card-text text-muted small mb-2">Sleek, breathable design for peak performance.</p>
                        <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width: 200px;">
                            <li><strong>Size:</strong> M</li>
                            <li><strong>Color:</strong> Blue</li>
                            <li><strong>Price:</strong> $45</li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-auto">Buy Now</button>
                    </div>
                </div>
            </div>

            <div class="col-sm-12 col-md-6 col-lg-4 mb-4">
                <div class="card text-center shadow-sm border-0" style="max-height: 420px;">
                    <img src="images/j3.png" class="card-img-top mx-auto mt-3" alt="Team Jersey"
                        style="height:150px; width:auto; object-fit:contain;">
                    <div class="card-body p-2 d-flex flex-column">
                        <h6 class="card-title fw-semibold mb-1">Team Jersey</h6>
                        <p class="card-text text-muted small mb-2">Sleek, breathable design for peak performance.</p>
                        <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width: 200px;">
                            <li><strong>Size:</strong> M</li>
                            <li><strong>Color:</strong> Blue</li>
                            <li><strong>Price:</strong> $45</li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-auto">Buy Now</button>
                    </div>
                </div>
            </div>

            <div class="col-sm-12 col-md-6 col-lg-4 mb-4">
                <div class="card text-center shadow-sm border-0" style="max-height: 420px;">
                    <img src="images/j4.png" class="card-img-top mx-auto mt-3" alt="Team Jersey"
                        style="height:150px; width:auto; object-fit:contain;">
                    <div class="card-body p-2 d-flex flex-column">
                        <h6 class="card-title fw-semibold mb-1">Team Jersey</h6>
                        <p class="card-text text-muted small mb-2">Sleek, breathable design for peak performance.</p>
                        <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width: 200px;">
                            <li><strong>Size:</strong> M</li>
                            <li><strong>Color:</strong> Blue</li>
                            <li><strong>Price:</strong> $45</li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-auto">Buy Now</button>
                    </div>
                </div>
            </div>

            <div class="col-sm-12 col-md-6 col-lg-4 mb-4">
                <div class="card text-center shadow-sm border-0" style="max-height: 420px;">
                    <img src="images/j5.png" class="card-img-top mx-auto mt-3" alt="Team Jersey"
                        style="height:150px; width:auto; object-fit:contain;">
                    <div class="card-body p-2 d-flex flex-column">
                        <h6 class="card-title fw-semibold mb-1">Team Jersey</h6>
                        <p class="card-text text-muted small mb-2">Sleek, breathable design for peak performance.</p>
                        <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width: 200px;">
                            <li><strong>Size:</strong> M</li>
                            <li><strong>Color:</strong> Blue</li>
                            <li><strong>Price:</strong> $45</li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-auto">Buy Now</button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php include_once 'footer.php'; ?>
</body>

</html>