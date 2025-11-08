<?php
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
            background-color: rgb(180, 235, 230);
        }
    </style>
</head>

<body>
    <?php include_once 'header.php'; ?>

    <h1 class="text-center mt-4">Explore Jersies </h1>

    <!-- for search bar -->
    <div class="container mt-3">
        <div class="row justify-content-center">
            <div class="col-sm-8 col-md-10 col-lg-12">
                <form class="d-flex justify-content-center mx-auto" action="#" method="GET" style="max-width: 700px;">
                    <!-- Dropdown for Jersey Type -->
                    <select id="jerseyType" name="type" class="form-select me-2" style="max-width: 180px;">
                        <option value="">Choose</option>
                        <option value="national-football">Nepal National Football</option>
                        <option value="national-cricket">Nepal National Cricket</option>
                        <option value="npl">NPL Jersey</option>
                    </select>

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

        <!--  Nepal Football Section -->
        <h3 class="text-center mb-4 fw-bold text-black">Nepal Football Jerseys</h3>
        <div class="row justify-content-start">

            <!-- Nepal Football Jersey -->
            <div class="col-sm-12 col-md-6 col-lg-4 mb-4" data-type="national-football">
                <div class="card text-center shadow-sm border-0 h-100">
                    <img src="images/football/home.jpg" class="card-img-top mx-auto mt-3" alt="Nepal Football Jersey"
                        style="height:150px; width:auto; object-fit:contain;">
                    <div class="card-body p-2 d-flex flex-column">
                        <h6 class="card-title fw-semibold mb-1">Nepal Home Jersey 2025 Edition</h6>
                        <p class="card-text text-muted small mb-2">
                            Show your pride with the official Nepal national team jersey—crafted with breathable,
                            moisture-wicking fabric for peak performance and comfort.
                        </p>
                        <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width: 200px;">
                            <li><strong>Size:</strong> M</li>
                            <li><strong>Color:</strong> Red</li>
                            <li><strong>Price:</strong> $35</li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-auto">Add to cart</button>
                    </div>
                </div>
            </div>

            <div class="col-sm-12 col-md-6 col-lg-4 mb-4" data-type="national-football-set">
                <div class="card text-center shadow-sm border-0 h-100">
                    <img src="images/football/away.jpg" class="card-img-top mx-auto mt-3" alt="Nepal Football Full Set"
                        style="height:150px; width:auto; object-fit:contain;">
                    <div class="card-body p-2 d-flex flex-column">
                        <h6 class="card-title fw-semibold mb-1">Nepal Football Full Set</h6>
                        <p class="card-text text-muted small mb-2">Includes jersey and matching shorts for full
                            match-day style.</p>
                        <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width: 200px;">
                            <li><strong>Size:</strong> M</li>
                            <li><strong>Color:</strong> Red & Blue</li>
                            <li><strong>Price:</strong> $55</li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-auto">Add to cart</button>
                    </div>
                </div>
            </div>

            <div class="col-sm-12 col-md-6 col-lg-4 mb-4" data-type="national-football-set">
                <div class="card text-center shadow-sm border-0 h-100">
                    <img src="images/football/third.jpg" class="card-img-top mx-auto mt-3" alt="Nepal Football Full Set"
                        style="height:150px; width:auto; object-fit:contain;">
                    <div class="card-body p-2 d-flex flex-column">
                        <h6 class="card-title fw-semibold mb-1">Nepal Football Full Set</h6>
                        <p class="card-text text-muted small mb-2">Includes jersey and matching shorts for full
                            match-day style.</p>
                        <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width: 200px;">
                            <li><strong>Size:</strong> M</li>
                            <li><strong>Color:</strong> Red & Blue</li>
                            <li><strong>Price:</strong> $55</li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-auto">Add to cart</button>
                    </div>
                </div>
            </div>
        </div>

        <!--  Nepal Cricket Section -->
        <h3 class="text-center mt-5 mb-4 fw-bold text-black">Nepal Cricket Jerseys</h3>
        <div class="row justify-content-start">

            <!-- Nepal Cricket Jersey -->
            <div class="col-sm-12 col-md-6 col-lg-4 mb-4" data-type="national-cricket">
                <div class="card text-center shadow-sm border-0 h-100">
                    <img src="images/cricket/2014.jpg" class="card-img-top mx-auto mt-3" alt="Nepal Cricket Jersey"
                        style="height:150px; width:auto; object-fit:contain;">
                    <div class="card-body p-2 d-flex flex-column">
                        <h6 class="card-title fw-semibold mb-1">Nepal Cricket Jersey</h6>
                        <p class="card-text text-muted small mb-2">Lightweight and stylish jersey for cricket fans.</p>
                        <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width: 200px;">
                            <li><strong>Size:</strong> L</li>
                            <li><strong>Color:</strong> Blue</li>
                            <li><strong>Price:</strong> $38</li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-auto">Add to cart</button>
                    </div>
                </div>
            </div>

            <!-- Nepal Cricket Full Set -->
            <div class="col-sm-12 col-md-6 col-lg-4 mb-4" data-type="national-cricket-set">
                <div class="card text-center shadow-sm border-0 h-100">
                    <img src="images/cricket/2024.jpg" class="card-img-top mx-auto mt-3" alt="Nepal Cricket Full Set"
                        style="height:150px; width:auto; object-fit:contain;">
                    <div class="card-body p-2 d-flex flex-column">
                        <h6 class="card-title fw-semibold mb-1">Nepal Cricket Full Set</h6>
                        <p class="card-text text-muted small mb-2">Complete cricket kit with jersey and pants.</p>
                        <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width: 200px;">
                            <li><strong>Size:</strong> L</li>
                            <li><strong>Color:</strong> Blue & White</li>
                            <li><strong>Price:</strong> $60</li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-auto">Add to cart</button>
                    </div>
                </div>
            </div>

             <div class="col-sm-12 col-md-6 col-lg-4 mb-4" data-type="national-cricket-set">
                <div class="card text-center shadow-sm border-0 h-100">
                    <img src="images/cricket/2024.jpg" class="card-img-top mx-auto mt-3" alt="Nepal Cricket Full Set"
                        style="height:150px; width:auto; object-fit:contain;">
                    <div class="card-body p-2 d-flex flex-column">
                        <h6 class="card-title fw-semibold mb-1">Nepal Cricket Full Set</h6>
                        <p class="card-text text-muted small mb-2">Complete cricket kit with jersey and pants.</p>
                        <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width: 200px;">
                            <li><strong>Size:</strong> L</li>
                            <li><strong>Color:</strong> Blue & White</li>
                            <li><strong>Price:</strong> $60</li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-auto">Add to cart</button>
                    </div>
                </div>
            </div>

        </div>

        <!-- NPL Section -->
        <h3 class="text-center mt-5 mb-4 fw-bold text-black">Nepal Premier League (NPL)</h3>
        <div class="row justify-content-start">

            <!-- NPL Jersey -->
            <div class="col-sm-12 col-md-6 col-lg-4 mb-4" data-type="npl">
                <div class="card text-center shadow-sm border-0 h-100">
                    <img src="images/npl/biratnagar.png" class="card-img-top mx-auto mt-3" alt="NPL Jersey"
                        style="height:150px; width:auto; object-fit:contain;">
                    <div class="card-body p-2 d-flex flex-column">
                        <h6 class="card-title fw-semibold mb-1">NPL Club Jersey</h6>
                        <p class="card-text text-muted small mb-2">Stylish club jersey for Nepal Premier League fans.
                        </p>
                        <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width: 200px;">
                            <li><strong>Size:</strong> S</li>
                            <li><strong>Color:</strong> Black</li>
                            <li><strong>Price:</strong> $32</li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-auto">Add to cart</button>
                    </div>
                </div>
            </div>

            <!-- NPL Full Set -->
            <div class="col-sm-12 col-md-6 col-lg-4 mb-4" data-type="npl-set">
                <div class="card text-center shadow-sm border-0 h-100">
                    <img src="images/npl/chitwan.png" class="card-img-top mx-auto mt-3" alt="NPL Full Set"
                        style="height:150px; width:auto; object-fit:contain;">
                    <div class="card-body p-2 d-flex flex-column">
                        <h6 class="card-title fw-semibold mb-1">NPL Full Set</h6>
                        <p class="card-text text-muted small mb-2">Includes jersey and shorts for full club support.</p>
                        <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width: 200px;">
                            <li><strong>Size:</strong> S</li>
                            <li><strong>Color:</strong> Black & Red</li>
                            <li><strong>Price:</strong> $50</li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-auto">Add to cart</button>
                    </div>
                </div>
            </div>

               <!-- NPL Jersey -->
            <div class="col-sm-12 col-md-6 col-lg-4 mb-4" data-type="npl">
                <div class="card text-center shadow-sm border-0 h-100">
                    <img src="images/npl/janakpur.png" class="card-img-top mx-auto mt-3" alt="NPL Jersey"
                        style="height:150px; width:auto; object-fit:contain;">
                    <div class="card-body p-2 d-flex flex-column">
                        <h6 class="card-title fw-semibold mb-1">NPL Club Jersey</h6>
                        <p class="card-text text-muted small mb-2">Stylish club jersey for Nepal Premier League fans.
                        </p>
                        <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width: 200px;">
                            <li><strong>Size:</strong> S</li>
                            <li><strong>Color:</strong> Black</li>
                            <li><strong>Price:</strong> $32</li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-auto">Add to cart</button>
                    </div>
                </div>
            </div>

            <!-- NPL Full Set -->
            <div class="col-sm-12 col-md-6 col-lg-4 mb-4" data-type="npl-set">
                <div class="card text-center shadow-sm border-0 h-100">
                    <img src="images/npl/karnali.png" class="card-img-top mx-auto mt-3" alt="NPL Full Set"
                        style="height:150px; width:auto; object-fit:contain;">
                    <div class="card-body p-2 d-flex flex-column">
                        <h6 class="card-title fw-semibold mb-1">NPL Full Set</h6>
                        <p class="card-text text-muted small mb-2">Includes jersey and shorts for full club support.</p>
                        <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width: 200px;">
                            <li><strong>Size:</strong> S</li>
                            <li><strong>Color:</strong> Black & Red</li>
                            <li><strong>Price:</strong> $50</li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-auto">Add to cart</button>
                    </div>
                </div>
            </div>

               <!-- NPL Jersey -->
            <div class="col-sm-12 col-md-6 col-lg-4 mb-4" data-type="npl">
                <div class="card text-center shadow-sm border-0 h-100">
                    <img src="images/npl/kathmandu.png" class="card-img-top mx-auto mt-3" alt="NPL Jersey"
                        style="height:150px; width:auto; object-fit:contain;">
                    <div class="card-body p-2 d-flex flex-column">
                        <h6 class="card-title fw-semibold mb-1">NPL Club Jersey</h6>
                        <p class="card-text text-muted small mb-2">Stylish club jersey for Nepal Premier League fans.
                        </p>
                        <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width: 200px;">
                            <li><strong>Size:</strong> S</li>
                            <li><strong>Color:</strong> Black</li>
                            <li><strong>Price:</strong> $32</li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-auto">Add to cart</button>
                    </div>
                </div>
            </div>

            <!-- NPL Full Set -->
            <div class="col-sm-12 col-md-6 col-lg-4 mb-4" data-type="npl-set">
                <div class="card text-center shadow-sm border-0 h-100">
                    <img src="images/npl/lumbini.png" class="card-img-top mx-auto mt-3" alt="NPL Full Set"
                        style="height:150px; width:auto; object-fit:contain;">
                    <div class="card-body p-2 d-flex flex-column">
                        <h6 class="card-title fw-semibold mb-1">NPL Full Set</h6>
                        <p class="card-text text-muted small mb-2">Includes jersey and shorts for full club support.</p>
                        <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width: 200px;">
                            <li><strong>Size:</strong> S</li>
                            <li><strong>Color:</strong> Black & Red</li>
                            <li><strong>Price:</strong> $50</li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-auto">Add to cart</button>
                    </div>
                </div>
            </div>

               <!-- NPL Jersey -->
            <div class="col-sm-12 col-md-6 col-lg-4 mb-4" data-type="npl">
                <div class="card text-center shadow-sm border-0 h-100">
                    <img src="images/npl/pokhara.png" class="card-img-top mx-auto mt-3" alt="NPL Jersey"
                        style="height:150px; width:auto; object-fit:contain;">
                    <div class="card-body p-2 d-flex flex-column">
                        <h6 class="card-title fw-semibold mb-1">NPL Club Jersey</h6>
                        <p class="card-text text-muted small mb-2">Stylish club jersey for Nepal Premier League fans.
                        </p>
                        <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width: 200px;">
                            <li><strong>Size:</strong> S</li>
                            <li><strong>Color:</strong> Black</li>
                            <li><strong>Price:</strong> $32</li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-auto">Add to cart</button>
                    </div>
                </div>
            </div>

            <!-- NPL Full Set -->
            <div class="col-sm-12 col-md-6 col-lg-4 mb-4" data-type="npl-set">
                <div class="card text-center shadow-sm border-0 h-100">
                    <img src="images/npl/sudurpaschim.png" class="card-img-top mx-auto mt-3" alt="NPL Full Set"
                        style="height:150px; width:auto; object-fit:contain;">
                    <div class="card-body p-2 d-flex flex-column">
                        <h6 class="card-title fw-semibold mb-1">NPL Full Set</h6>
                        <p class="card-text text-muted small mb-2">Includes jersey and shorts for full club support.</p>
                        <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width: 200px;">
                            <li><strong>Size:</strong> S</li>
                            <li><strong>Color:</strong> Black & Red</li>
                            <li><strong>Price:</strong> $50</li>
                        </ul>
                        <button class="btn btn-sm btn-primary mt-auto">Add to cart</button>
                    </div>
                </div>
            </div>


        </div>
    </div>


    <?php include_once 'footer.php'; ?>

    <script src="js/jersey.js"></script>
</body>

</html>