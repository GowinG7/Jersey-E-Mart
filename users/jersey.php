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

        /* Ensure badges sit above the image even on hover */
        .card .card-img-top {
            position: relative;
            z-index: 1;
        }

        .card .badge-overlay {
            z-index: 5;
            /* Above image */
            pointer-events: none;
            /* Don't block hover/clicks on card */
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

        /* Pro filter bar */
        .filters-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
            padding: 14px 18px;
            border: 1px solid #e6f0ee;
        }

        .filters-card .label-text {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #5b7f7a;
            margin-bottom: 6px;
        }

        .filters-card .form-control,
        .filters-card .form-select {
            height: 46px;
            border-radius: 10px;
            border: 1px solid #d7e7e4;
        }

        .filters-card .helper {
            font-size: 13px;
            color: #6c7f7c;
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

    <!-- <h2 class="text-center section-title animate__animated" style="color:rgb(155,125,170,1);">Explore our Premium
        Products</h2> -->

    <!-- Pro Filter Bar -->
    <div class="container mt-3">
        <div class="row justify-content-center">
            <div class="col-sm-12 col-md-11 col-lg-10">
                <div class="filters-card">
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                        <div>
                            <div class="label-text">Find your jersey</div>
                            <div class="helper">Search by name, team, or type. Filter by category and sort instantly.
                            </div>
                        </div>
                        <div class="helper">Showing: <span id="resultCount" style="font-weight:600;">–</span></div>
                    </div>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <div class="flex-grow-1">
                            <input id="searchInput" class="form-control" type="search"
                                placeholder="Search jerseys, teams, tournaments..." oninput="filterAndSort()">
                        </div>
                        <div style="min-width:170px;">
                            <select id="categoryFilter" class="form-select" onchange="filterAndSort()">
                                <option value="all" selected>All Categories</option>
                                <option value="football">Football</option>
                                <option value="cricket">Cricket</option>
                                <option value="npl">NPL</option>
                                <option value="nsl">NSL</option>
                            </select>
                        </div>
                        <div style="min-width:180px;">
                            <select id="sortFilter" class="form-select" onchange="filterAndSort()">
                                <option value="all">All Products</option>
                                <option value="popularity">Popularity</option>
                                <option value="price_low">Price: Low to High</option>
                                <option value="price_high">Price: High to Low</option>
                                <option value="discount">Discount %</option>
                                <option value="newest">Newest</option>
                            </select>
                        </div>
                    </div>
                </div>
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
            $res = $conn->query("SELECT id, j_name AS title, quality, description, image, price, discount 
                             FROM products WHERE category='Cricket' ORDER BY id DESC LIMIT 6");
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
                echo '<p class="text-muted">No cricket jerseys available.</p>';
            }
            ?>
        </div>

        <!-- NPL Jerseys -->
        <h3 class="text-center section-title animate__animated">Nepal Premier League (NPL)</h3>
        <div class="row justify-content-start">
            <?php
            $res = $conn->query("SELECT id, j_name AS title,quality, description, image, price, discount 
                             FROM products WHERE category='NPL cricket' ORDER BY id DESC LIMIT 6");
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
                echo '<p class="text-muted">No NPL jerseys available.</p>';
            }
            ?>
        </div>

    </div>

    <?php include_once 'footer.php'; ?>

    <!-- Live Search JS -->
    <script>
        let allProducts = [];

        // Load products on page load
        function loadProducts(sortBy = 'popularity', searchQuery = '', category = 'all') {
            const resultBox = document.getElementById("searchResult");
            const resultCount = document.getElementById("resultCount");
            const defaultProducts = document.getElementById("defaultProducts");

            const xhr = new XMLHttpRequest();
            const url = "live_search.php?sort=" + sortBy
                + (searchQuery ? "&q=" + encodeURIComponent(searchQuery) : "")
                + (category ? "&category=" + encodeURIComponent(category) : "");
            console.log("Loading products: " + url);

            // Hide static defaults while loading dynamic data
            if (defaultProducts) defaultProducts.style.display = "none";

            xhr.open("GET", url, true);
            xhr.onload = function () {
                try {
                    console.log("Response status: " + xhr.status);
                    console.log("Response text: " + this.responseText.substring(0, 200));

                    const response = JSON.parse(this.responseText);

                    if (!response.success && response.error) {
                        resultBox.innerHTML = "<p class='text-center text-danger'><strong>Error:</strong> " + response.error + "</p>";
                        console.error("Server error:", response.error);
                        return;
                    }

                    allProducts = response.products || [];

                    if (allProducts.length > 0) {
                        resultCount.textContent = allProducts.length;
                        if (defaultProducts) defaultProducts.style.display = "none";
                        renderProducts(allProducts);
                    } else {
                        resultCount.textContent = 0;
                        resultBox.innerHTML = "<p class='text-center text-muted'>No jerseys found</p>";
                    }
                } catch (e) {
                    console.error('Parse error:', e);
                    console.log('Response:', this.responseText);
                    resultBox.innerHTML = "<p class='text-center text-danger'><strong>Parse Error:</strong> " + e.message + "<br><small>" + this.responseText.substring(0, 100) + "</small></p>";
                }
            };
            xhr.onerror = function () {
                resultBox.innerHTML = "<p class='text-center text-danger'>Network error - could not load products</p>";
                console.error("Network error");
            };
            xhr.send();
        }

        // Filter and sort based on search + dropdown
        function filterAndSort() {
            const searchQuery = document.getElementById("searchInput").value.trim();
            const sortBy = document.getElementById("sortFilter").value;
            const category = document.getElementById("categoryFilter").value;
            console.log("Filter: '" + searchQuery + "', Sort: '" + sortBy + "', Category: '" + category + "'");

            loadProducts(sortBy, searchQuery, category);
        }

        function renderProducts(products) {
            const resultBox = document.getElementById("searchResult");
            resultBox.innerHTML = products.map(p => {
                const img = p.image ? `../shared/products/${p.image}` : 'images/placeholder.png';
                const finalPrice = p.discount > 0 ? p.price - (p.price * p.discount / 100) : p.price;

                let badges = '';
                if (p.is_bestseller) badges += `<span class="badge badge-overlay bg-warning text-dark position-absolute" style="top:10px;left:10px;">Popular</span>`;
                if (p.discount >= 15) badges += `<span class="badge badge-overlay bg-info text-white position-absolute" style="top:${p.is_bestseller ? '40px' : '10px'};left:10px;">Top Discount</span>`;
                if (p.discount > 0) badges += `<span class="badge badge-overlay bg-danger position-absolute" style="top:10px;right:10px;">${p.discount}% OFF</span>`;
                if (p.is_trending) badges += `<span class="badge badge-overlay bg-success position-absolute" style="top:${p.is_bestseller || p.discount >= 15 ? '70px' : '40px'};left:10px;">Trending</span>`;


                const priceHTML = p.discount > 0
                    ? `<span style="text-decoration:line-through; color:#888;">
                        Rs ${Math.floor(p.price).toLocaleString()}
                        </span>
                        <b class="ms-2 text-success">
                        Rs ${Math.floor(finalPrice).toLocaleString()}
                        </b>`
                    : `<b>Rs ${Math.floor(p.price).toLocaleString()}</b>`;


                return `
                <div class="col-sm-12 col-md-6 col-lg-4 mb-4">
                    <a href="view_jersey.php?id=${p.id}" class="text-decoration-none text-dark">
                        <div class="card text-center shadow-sm border-0 h-100 position-relative">
                            ${badges}
                            <img src="${img}" class="card-img-top mx-auto mt-3" alt="${p.j_name}" 
                                 style="height:310px;width:auto;object-fit:contain;">
                            <div class="card-body p-2 d-flex flex-column">
                                <h6 class="card-title fw-semibold mb-1">${p.j_name}</h6>
                                <p class="card-text fw-semibold text-muted small mb-2">${p.quality}</p>
                                ${p.total_orders > 0 ? `<small class="text-muted mb-2"><i class="bi bi-bag-check"></i> ${p.total_orders} orders</small>` : ''}
                                <ul class="list-unstyled small mb-2 text-start mx-auto" style="max-width:200px;">
                                    <li>${priceHTML}</li>
                                </ul>
                            </div>
                        </div>
                    </a>
                </div>`;
            }).join('');
        }

        // Load most popular products on page load
        document.addEventListener('DOMContentLoaded', function () {
            loadProducts('popularity', '', document.getElementById('categoryFilter')?.value || 'all');
        });
    </script>
</body>

</html>