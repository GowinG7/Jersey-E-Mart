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

    <link rel="stylesheet" href="css/index.css">
</head>

<body>
    <?php
    // Build carousel slides: Most Popular products (top 2) + Top Discount products (top 2) + Bestseller
    $carouselSlides = [];
    $seenIds = [];

    // 1. Get top 5 Most Popular products
    $popularCarouselSql = "SELECT p.id, p.j_name AS title, p.description, p.image, p.category, p.price, p.discount, 
                                   COALESCE(SUM(oi.quantity), 0) AS total_orders
                            FROM products p
                            LEFT JOIN order_items oi ON p.id = oi.product_id
                            GROUP BY p.id
                            ORDER BY total_orders DESC, p.date_added DESC
                            LIMIT 5";

    if ($popRes = $conn->query($popularCarouselSql)) {
        while ($row = $popRes->fetch_assoc()) {
            $carouselSlides[] = $row + ['highlight' => 'Most Popular'];
            $seenIds[$row['id']] = true;
        }
    }

    // 2. Get top 2 Discount products that aren't already shown
    $discountCarouselSql = "SELECT p.id, p.j_name AS title, p.description, p.image, p.category, p.price, p.discount,
                                   COALESCE(SUM(oi.quantity), 0) AS total_orders
                            FROM products p
                            LEFT JOIN order_items oi ON p.id = oi.product_id
                            WHERE p.discount > 0";

    if (!empty($seenIds)) {
        $idList = implode(',', array_keys($seenIds));
        $discountCarouselSql .= " AND p.id NOT IN ($idList)";
    }

    $discountCarouselSql .= " GROUP BY p.id ORDER BY p.discount DESC, p.date_added DESC LIMIT 5";

    if ($discRes = $conn->query($discountCarouselSql)) {
        while ($row = $discRes->fetch_assoc()) {
            $carouselSlides[] = $row + ['highlight' => 'Top Discount'];
            $seenIds[$row['id']] = true;
        }
    }

    // 3. Add one Bestseller (most orders >= 10) if available and not already shown
    $bestsellerSql = "SELECT p.id, p.j_name AS title, p.description, p.image, p.category, p.price, p.discount,
                             COALESCE(SUM(oi.quantity), 0) AS total_orders
                      FROM products p
                      LEFT JOIN order_items oi ON p.id = oi.product_id
                      GROUP BY p.id
                      HAVING total_orders >= 10";

    if (!empty($seenIds)) {
        $idList = implode(',', array_keys($seenIds));
        $bestsellerSql .= " AND p.id NOT IN ($idList)";
    }

    $bestsellerSql .= " ORDER BY total_orders DESC LIMIT 1";

    if ($bestRes = $conn->query($bestsellerSql)) {
        if ($bestRow = $bestRes->fetch_assoc()) {
            $carouselSlides[] = $bestRow + ['highlight' => 'Bestseller'];
        }
    }
    ?>

    <div class="container my-4">
        <?php if (!empty($carouselSlides)): ?>
            <div id="heroCarousel" class="carousel slide carousel-fade carousel-hero" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <?php foreach ($carouselSlides as $idx => $_): ?>
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $idx ?>"
                            class="<?= $idx === 0 ? 'active' : '' ?>" aria-label="Slide <?= $idx + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>

                <div class="carousel-inner">
                    <?php foreach ($carouselSlides as $idx => $slide):
                        $sid = (int) ($slide['id'] ?? 0);
                        
                        // Skip if product ID is invalid
                        if ($sid <= 0) continue;
                        
                        $title = htmlspecialchars($slide['title']);
                        $desc = htmlspecialchars($slide['description'] ?? '');
                        $img = !empty($slide['image']) ? '../shared/products/' . htmlspecialchars($slide['image']) : 'images/placeholder.png';
                        $discount = (int) ($slide['discount'] ?? 0);
                        $price = (int) ($slide['price'] ?? 0);
                        $finalPrice = $discount > 0 ? $price - ($price * $discount / 100) : $price;
                        $badge = htmlspecialchars($slide['highlight']);
                        ?>
                        <div class="carousel-item carousel-item-hero <?= $idx === 0 ? 'active' : '' ?>">
                            <div class="carousel-gradient"></div>
                            <div class="container">
                                <div class="row align-items-center">
                                    <div class="col-lg-6 col-md-7 order-2 order-md-1">
                                        <div class="carousel-caption-custom">
                                            <div class="d-flex gap-2 mb-3">
                                                <span class="carousel-badge d-inline-flex align-items-center gap-2">
                                                    <i class="bi bi-lightning-charge-fill text-warning"></i> <?= $badge ?>
                                                </span>
                                                <a class="btn btn-success btn-sm" href="view_jersey.php?id=<?= $sid ?>">View
                                                    Jersey</a>
                                            </div>
                                            <div class="title mt-2"><?= $title ?></div>
                                            <div class="d-flex align-items-center justify-content-between gap-3 mt-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php if ($discount > 0): ?>
                                                        <span class="fs-6 text-decoration-line-through opacity-75">Rs
                                                            <?= number_format($price) ?></span>
                                                        <span class="fs-5 fw-bold text-warning">Rs
                                                            <?= number_format($finalPrice) ?></span>
                                                    <?php else: ?>
                                                        <span class="fs-5 fw-bold text-warning">Rs
                                                            <?= number_format($price) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-5 text-center order-1 order-md-2">
                                        <div class="carousel-img-box">
                                            <a href="view_jersey.php?id=<?= $sid ?>" class="d-inline-block w-100 h-100">
                                                <img src="<?= $img ?>" alt="<?= $title ?>" class="img-fluid">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        <?php else: ?>
            <div class="alert alert-info mb-4">Add products to see the carousel highlights.</div>
        <?php endif; ?>
    </div>
    <!-- Explore Our Page -->
    <h1 class="text-center mt-4  display-5 fw-bold animate__animated">
        <span>Explore Our Page</span>
    </h1>

    <div class="container my-2">
        <?php
        // Category-specific popular sections
        $categorySections = [
            'football' => ['title' => 'Football Jerseys', 'empty' => 'No football jerseys available.'],
            'cricket' => ['title' => 'National team Cricket Jerseys', 'empty' => 'National team cricket jerseys not available.'],
            'npl' => ['title' => 'NPL Jerseys', 'empty' => 'No NPL jerseys available.'],
        ];
        ?>

        <?php foreach ($categorySections as $catKey => $meta):
            $likeCat = $conn->real_escape_string($catKey);
            $catSql = "SELECT p.id, p.j_name AS title, p.description, p.image, p.quality, p.price, p.discount, p.category,
                              COALESCE(SUM(oi.quantity), 0) AS total_orders
                       FROM products p
                       LEFT JOIN order_items oi ON p.id = oi.product_id
                       WHERE LOWER(p.category) LIKE '{$likeCat}%'
                       GROUP BY p.id
                       ORDER BY total_orders DESC, p.date_added DESC
                       LIMIT 6";
            $catRes = $conn->query($catSql);
            ?>
            <hr class="my-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="section-title mb-1"><?= htmlspecialchars($meta['title']) ?></h3>
                </div>
            </div>

            <div class="row justify-content-start">
                <?php
                if ($catRes && $catRes->num_rows) {
                    while ($r = $catRes->fetch_assoc()) {
                        $id = $r['id'];
                        $img = !empty($r['image']) ? '../shared/products/' . htmlspecialchars($r['image']) : 'images/placeholder.png';
                        $title = htmlspecialchars($r['title']);
                        $quality = htmlspecialchars($r['quality'] ?? '');
                        $desc = htmlspecialchars($r['description'] ?? '');
                        $price = (int) $r['price'];
                        $discount = (int) $r['discount'];
                        $orders = (int) $r['total_orders'];
                        $finalPrice = $discount > 0 ? $price - ($price * $discount / 100) : $price;
                        ?>
                        <div class='col-sm-12 col-md-6 col-lg-4 mb-4'>
                            <a href='view_jersey.php?id=<?= $id ?>' class='text-decoration-none text-dark'>
                                <div class='card text-center shadow-sm border-0 h-100 position-relative'>

                                    <?= $discount > 0 ? "<span class='badge bg-danger position-absolute' style='top:10px;right:10px;'>$discount% OFF</span>" : "" ?>
                                    <?= $orders >= 10 ? "<span class='badge bg-warning text-dark position-absolute' style='top:10px;left:10px;'>Bestseller</span>" : "" ?>

                                    <img src='<?= $img ?>' class='card-img-top mx-auto mt-3' alt='<?= $title ?>'
                                        style='height:300px; width:auto; object-fit:contain;'>

                                    <div class='card-body p-2 d-flex flex-column'>
                                        <h6 class='card-title fw-semibold mb-1'><?= $title ?></h6>
                                        <p class='card-text fw-semibold text-muted small mb-1'><?= $quality ?></p>
                                        <p class='card-text text-muted small mb-2' style='min-height:36px;'><?= $desc ?></p>

                                        <div class='d-flex justify-content-center align-items-center gap-2 mb-2'>
                                            <?php if ($discount > 0): ?>
                                                <span style='text-decoration:line-through; color:#888;'>Rs
                                                    <?= number_format($price) ?></span>
                                                <b class='text-success'>Rs <?= number_format($finalPrice) ?></b>
                                            <?php else: ?>
                                                <b>Rs <?= number_format($price) ?></b>
                                            <?php endif; ?>
                                        </div>

                                        <?= $orders > 0 ? "<small class='text-muted'><i class='bi bi-bag-check'></i> {$orders} orders</small>" : "<small class='text-muted'>New arrival</small>" ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p class='text-muted'>{$meta['empty']}</p>";
                }
                ?>
            </div>
        <?php endforeach; ?>
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