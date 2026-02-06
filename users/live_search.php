<?php
header('Content-Type: application/json');
require_once '../shared/dbconnect.php';

/* 
   READ INPUT VALUES
 */
$key = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : "";
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : "popularity";
$category = isset($_GET['category']) ? $_GET['category'] : "all";

/* 
   CATEGORY VALUE MAPPING
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
   LOAD PRODUCTS FROM DATABASE
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
   FILTER BY SEARCH KEYWORD
 */
if ($key != "") {
    $products = array_filter($products, function ($p) use ($key) {
        return (
            stripos($p['j_name'], $key) !== false ||
            stripos($p['category'], $key) !== false ||
            stripos($p['country'], $key) !== false ||
            stripos($p['quality'], $key) !== false
        );
    });
    $products = array_values($products);
}

/* 
   FILTER BY CATEGORY
 */
if ($category != "all") {
    $products = array_filter($products, function ($p) use ($category) {
        return $p['category'] == $category;
    });
    $products = array_values($products);
}

/* 
   CONTENT BASED SCORE FUNCTION
 */
function contentScore($product, $key)
{
    $score = 0;

    if (stripos($product['j_name'], $key) !== false)
        $score += 10;
    if (stripos($product['category'], $key) !== false)
        $score += 5;
    if (stripos($product['country'], $key) !== false)
        $score += 5;
    if (stripos($product['quality'], $key) !== false)
        $score += 3;

    return $score;
}

/* 
   COLLABORATIVE FILTERING
 */
$maxOrders = 0;
foreach ($products as $p) {
    if ($p['total_orders'] > $maxOrders)
        $maxOrders = $p['total_orders'];
}

foreach ($products as &$p) {

    $p['popularity_score'] = $p['total_orders'];

    $p['is_bestseller'] = ($maxOrders > 0 && $p['total_orders'] >= ($maxOrders * 0.6));
    $p['is_trending'] = ($p['total_orders'] >= 5);

    $p['final_price'] = ($p['discount'] > 0)
        ? $p['price'] - ($p['price'] * $p['discount'] / 100)
        : $p['price'];

    $p['relevance_score'] = ($key != "") ? contentScore($p, $key) : 0;

    $p['rank_score'] = ($p['relevance_score'] * 2) + ($p['popularity_score'] * 0.5);

    $p['date_value'] = strtotime($p['date_added']);
}
unset($p);

/* 
   SIMPLE BUBBLE SORT
 */
function bubbleSort(&$arr, $field, $order)
{
    $n = count($arr);
    for ($i = 0; $i < $n - 1; $i++) {
        for ($j = 0; $j < $n - $i - 1; $j++) {

            if ($order == "asc" && $arr[$j][$field] > $arr[$j + 1][$field]) {
                $temp = $arr[$j];
                $arr[$j] = $arr[$j + 1];
                $arr[$j + 1] = $temp;
            }

            if ($order == "desc" && $arr[$j][$field] < $arr[$j + 1][$field]) {
                $temp = $arr[$j];
                $arr[$j] = $arr[$j + 1];
                $arr[$j + 1] = $temp;
            }
        }
    }
}

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
   SEND JSON RESPONSE
 */
echo json_encode([
    "success" => true,
    "total" => count($products),
    "products" => $products
]);
?>