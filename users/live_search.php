<?php
header('Content-Type: application/json');
require_once '../shared/dbconnect.php';

/* 
   READ INPUT VALUES
*/
$key = "";
if (isset($_GET['q'])) {
    $key = trim($_GET['q']);
}

$sortBy = isset($_GET['sort']) ? $_GET['sort'] : "popularity";
$category = isset($_GET['category']) ? $_GET['category'] : "all";

/* 
   CATEGORY MAP
*/
$categoryMap = [
    'football' => 'Football',
    'cricket' => 'Cricket',
    'npl' => 'NPL cricket',
    'nsl' => 'NSL football'
];

if ($category != "all" && isset($categoryMap[$category])) {
    $category = $categoryMap[$category];
}

/* 
   LOAD PRODUCTS
*/
$sql = "SELECT p.*, COALESCE(SUM(oi.quantity),0) AS total_orders
        FROM products p
        LEFT JOIN order_items oi ON p.id = oi.product_id
        GROUP BY p.id";

$result = mysqli_query($conn, $sql);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

/* 
   SUBSTRING CHECK (Built-in)
*/
function contains($text, $search)
{
    if ($search == "")
        return true;

    return strpos($text, $search) !== false;
}

/* 
   FILTER PRODUCTS
*/
$filtered = [];

for ($i = 0; $i < count($products); $i++) {

    $match = true;

    if ($key != "") {
        if (
            !contains($products[$i]['j_name'], $key) &&
            !contains($products[$i]['category'], $key) &&
            !contains($products[$i]['country'], $key) &&
            !contains($products[$i]['quality'], $key)
        ) {

            $match = false;
        }
    }

    if ($category != "all") {
        if ($products[$i]['category'] != $category) {
            $match = false;
        }
    }

    if ($match) {
        $filtered[] = $products[$i];
    }
}

$products = $filtered;

/* 
   1️⃣ CONTENT BASED SCORE ALGORITHM
*/
function applyContentBasedScore(&$products, $key)
{
    for ($i = 0; $i < count($products); $i++) {

        $score = 0;

        if (contains($products[$i]['j_name'], $key))
            $score += 10;
        if (contains($products[$i]['category'], $key))
            $score += 5;
        if (contains($products[$i]['country'], $key))
            $score += 5;
        if (contains($products[$i]['quality'], $key))
            $score += 3;

        $products[$i]['relevance_score'] = $score;
    }
}

/* 
   2️⃣ COLLABORATIVE FILTERING ALGORITHM
*/
function applyCollaborativeFiltering(&$products)
{
    $maxOrders = 0;

    /* Find Maximum Orders */
    for ($i = 0; $i < count($products); $i++) {
        if ($products[$i]['total_orders'] > $maxOrders) {
            $maxOrders = $products[$i]['total_orders'];
        }
    }

    /* Apply Popularity Logic */
    for ($i = 0; $i < count($products); $i++) {

        $products[$i]['popularity_score'] =
            $products[$i]['total_orders'];

        /* Bestseller */
        if (
            $maxOrders > 0 &&
            $products[$i]['total_orders'] >= ($maxOrders * 0.6)
        ) {
            $products[$i]['is_bestseller'] = true;
        } else {
            $products[$i]['is_bestseller'] = false;
        }

        /* Trending */
        if ($products[$i]['total_orders'] >= 5) {
            $products[$i]['is_trending'] = true;
        } else {
            $products[$i]['is_trending'] = false;
        }

        /* Final Price Calculation */
        if ($products[$i]['discount'] > 0) {
            $products[$i]['final_price'] =
                $products[$i]['price'] -
                ($products[$i]['price'] *
                    $products[$i]['discount'] / 100);
        } else {
            $products[$i]['final_price'] =
                $products[$i]['price'];
        }

        /* Rank Score */
        $products[$i]['rank_score'] =
            ($products[$i]['relevance_score'] * 2) +
            ($products[$i]['popularity_score'] * 0.5);

        /* Date Value (YYYY-MM-DD → YYYYMMDD) */
        $date = $products[$i]['date_added'];
        $products[$i]['date_value'] =
            ($date[0] . $date[1] . $date[2] . $date[3] .
                $date[5] . $date[6] .
                $date[8] . $date[9]);
    }
}

/* 
   3️⃣ BUBBLE SORT ALGORITHM
*/
function bubbleSort(&$arr, $field, $order)
{
    $n = count($arr);

    for ($i = 0; $i < $n - 1; $i++) {

        for ($j = 0; $j < $n - $i - 1; $j++) {

            if (
                $order == "asc" &&
                $arr[$j][$field] > $arr[$j + 1][$field]
            ) {

                $temp = $arr[$j];
                $arr[$j] = $arr[$j + 1];
                $arr[$j + 1] = $temp;
            }

            if (
                $order == "desc" &&
                $arr[$j][$field] < $arr[$j + 1][$field]
            ) {

                $temp = $arr[$j];
                $arr[$j] = $arr[$j + 1];
                $arr[$j + 1] = $temp;
            }
        }
    }
}

/* 
   APPLY ALGORITHMS
*/
applyContentBasedScore($products, $key);
applyCollaborativeFiltering($products);

/* 
   SORT CONTROL
*/
if ($sortBy == "price_low")
    bubbleSort($products, "final_price", "asc");
else if ($sortBy == "price_high")
    bubbleSort($products, "final_price", "desc");
else if ($sortBy == "discount")
    bubbleSort($products, "discount", "desc");
else if ($sortBy == "newest")
    bubbleSort($products, "date_value", "desc");
else if ($sortBy == "relevance")
    bubbleSort($products, "rank_score", "desc");
else
    bubbleSort($products, "total_orders", "desc");

/* 
   OUTPUT JSON
*/
echo json_encode([
    "success" => true,
    "total" => count($products),
    "products" => $products
]);
?>