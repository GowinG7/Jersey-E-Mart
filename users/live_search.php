<!-- live search ra binary search (with sorting) apply gareko cha yo file ma -->
<?php
require_once '../shared/dbconnect.php';

$key = strtolower(trim($_GET['q'] ?? ''));

if ($key === '')
    exit;

// Fetch all products (include fields used in results)
$data = [];
$res = $conn->query("SELECT id, j_name, image, price, discount, quality, description FROM products");
while ($row = $res->fetch_assoc())
    $data[] = $row;

// Sort by jersey name
usort($data, fn($a, $b) => strcmp(strtolower($a['j_name']), strtolower($b['j_name'])));

// Binary Search with neighbor check
function binarySearch($arr, $key)
{
    $low = 0;
    $high = count($arr) - 1;
    $results = [];
    while ($low <= $high) {
        $mid = floor(($low + $high) / 2);
        $name = strtolower($arr[$mid]['j_name']);

        if (strpos($name, $key) !== false) {
            $results[] = $arr[$mid];

            // check left neighbors
            $i = $mid - 1;
            while ($i >= 0 && strpos(strtolower($arr[$i]['j_name']), $key) !== false)
                $results[] = $arr[$i--];

            // check right neighbors
            $i = $mid + 1;
            while ($i < count($arr) && strpos(strtolower($arr[$i]['j_name']), $key) !== false)
                $results[] = $arr[$i++];
            break;
        } elseif ($key < $name)
            $high = $mid - 1;
        else
            $low = $mid + 1;
    }

    // Sort results alphabetically
    usort($results, fn($a, $b) => strcmp(strtolower($a['j_name']), strtolower($b['j_name'])));
    return $results;
}

$found = binarySearch($data, $key);

if (empty($found)) {
    echo "<p class='text-center text-muted'>No jersey found</p>";
    exit;
}

// Display search results
foreach ($found as $r) {
    $img = !empty($r['image']) ? '../shared/products/' . htmlspecialchars($r['image']) : 'images/placeholder.png';
    $price = intval($r['price']);
    $discount = intval($r['discount']);
    $title = htmlspecialchars($r['j_name']);
    $quality = htmlspecialchars($r['quality'] ?? '');
    $desc = htmlspecialchars($r['description'] ?? '');

    echo "
    <div class='col-sm-12 col-md-6 col-lg-4 mb-4'>
        <a href='view_jersey.php?id={$r['id']}' class='text-decoration-none text-dark'>
            <div class='card text-center shadow-sm border-0 h-100 position-relative'>
                " . ($discount > 0 ? "<span class='badge bg-danger discount-badge position-absolute'>{$discount}% OFF</span>" : "") . "
                <img src='{$img}' class='card-img-top mx-auto mt-3' alt='{$title}' style='height:310px;width:auto;object-fit:contain;'>
                <div class='card-body p-2 d-flex flex-column'>
                    <h6 class='card-title fw-semibold mb-1'>{$title}</h6>
                    <p class='card-text fw-semibold text-muted small mb-2'>{$quality}</p>
                    <p class='card-text text-muted small mb-2'>{$desc}</p>
                    <ul class='list-unstyled small mb-2 text-start mx-auto' style='max-width:200px;'>
                        <li>
                            " . ($discount > 0
        ? "<span style='text-decoration:line-through; color:#888;'>Rs " . number_format($price) . "</span>
                                            <b class='ms-2 text-success'>Rs " . number_format($price - ($price * $discount / 100)) . "</b>"
        : "<b>Rs " . number_format($price) . "</b>") . "
                        </li>
                    </ul>
                </div>
            </div>
        </a>
    </div>";
}
































