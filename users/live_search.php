<!-- live search ra binary search (with sorting) apply gareko cha yo file ma -->
<?php
require_once '../shared/dbconnect.php';

// Get search key
$key = strtolower(trim($_GET['q'] ?? ''));
if ($key === '') {
    exit;
}

// Fetch all products
$data = [];
$result = $conn->query("
    SELECT id, j_name, image, price, discount, quality, description 
    FROM products
");

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

/* 
   MERGE SORT (By Name)
    */
function mergeSortByName(array $arr): array
{
    $count = count($arr);
    if ($count <= 1) {
        return $arr;
    }

    $mid = intdiv($count, 2);
    $left = array_slice($arr, 0, $mid);
    $right = array_slice($arr, $mid);

    return mergeByName(
        mergeSortByName($left),
        mergeSortByName($right)
    );
}

function mergeByName(array $left, array $right): array
{
    $result = [];
    $i = $j = 0;
    $lCount = count($left);
    $rCount = count($right);

    while ($i < $lCount && $j < $rCount) {
        if (strcasecmp($left[$i]['j_name'], $right[$j]['j_name']) <= 0) {
            $result[] = $left[$i++];
        } else {
            $result[] = $right[$j++];
        }
    }

    while ($i < $lCount) {
        $result[] = $left[$i++];
    }

    while ($j < $rCount) {
        $result[] = $right[$j++];
    }

    return $result;
}

// Sort data before binary search
$data = mergeSortByName($data);

/* 
   BINARY SEARCH (Partial)
    */
function binarySearch(array $arr, string $key): array
{
    $low = 0;
    $high = count($arr) - 1;
    $results = [];

    while ($low <= $high) {
        $mid = intdiv($low + $high, 2);
        $name = strtolower($arr[$mid]['j_name']);

        if (strpos($name, $key) !== false) {
            $results[] = $arr[$mid];

            // Check left side
            for ($i = $mid - 1; $i >= 0; $i--) {
                if (strpos(strtolower($arr[$i]['j_name']), $key) === false) {
                    break;
                }
                $results[] = $arr[$i];
            }

            // Check right side
            $len = count($arr);
            for ($i = $mid + 1; $i < $len; $i++) {
                if (strpos(strtolower($arr[$i]['j_name']), $key) === false) {
                    break;
                }
                $results[] = $arr[$i];
            }
            break;
        }

        if ($key < $name) {
            $high = $mid - 1;
        } else {
            $low = $mid + 1;
        }
    }

    // Sort matched results alphabetically
    usort($results, function ($a, $b) {
        return strcasecmp($a['j_name'], $b['j_name']);
    });

    return $results;
}

// Perform search
$found = binarySearch($data, $key);

if (empty($found)) {
    echo "<p class='text-center text-muted'>No jersey found</p>";
    exit;
}

/* 
   DISPLAY RESULTS
    */
foreach ($found as $r) {
    $img = !empty($r['image'])
        ? '../shared/products/' . htmlspecialchars($r['image'])
        : 'images/placeholder.png';

    $price = (int) $r['price'];
    $discount = (int) $r['discount'];
    $title = htmlspecialchars($r['j_name']);
    $quality = htmlspecialchars($r['quality'] ?? '');
    $desc = htmlspecialchars($r['description'] ?? '');

    $finalPrice = $discount > 0
        ? $price - ($price * $discount / 100)
        : $price;

    echo "
    <div class='col-sm-12 col-md-6 col-lg-4 mb-4'>
        <a href='view_jersey.php?id={$r['id']}' class='text-decoration-none text-dark'>
            <div class='card text-center shadow-sm border-0 h-100 position-relative'>
                " . ($discount > 0
        ? "<span class='badge bg-danger discount-badge position-absolute'>{$discount}% OFF</span>"
        : "") . "
                <img src='{$img}' class='card-img-top mx-auto mt-3'
                     alt='{$title}' style='height:310px;width:auto;object-fit:contain;'>
                <div class='card-body p-2 d-flex flex-column'>
                    <h6 class='card-title fw-semibold mb-1'>{$title}</h6>
                    <p class='card-text fw-semibold text-muted small mb-2'>{$quality}</p>
                    <p class='card-text text-muted small mb-2'>{$desc}</p>
                    <ul class='list-unstyled small mb-2 text-start mx-auto' style='max-width:200px;'>
                        <li>
                            " . ($discount > 0
        ? "<span style='text-decoration:line-through;color:#888;'>Rs " . number_format($price) . "</span>
                                   <b class='ms-2 text-success'>Rs " . number_format($finalPrice) . "</b>"
        : "<b>Rs " . number_format($price) . "</b>") . "
                        </li>
                    </ul>
                </div>
            </div>
        </a>
    </div>";
}
?>