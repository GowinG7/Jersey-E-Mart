<?php
header('Content-Type: application/json');
require_once '../shared/dbconnect.php';

// Error handling
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed', 'products' => []]);
    exit;
}

try {
    // Get search parameters
    $key = strtolower(trim($_GET['q'] ?? ''));
    $sortBy = $_GET['sort'] ?? 'popularity';
        $category = strtolower(trim($_GET['category'] ?? 'all'));

    /* 
       DATABASE SEARCH
       If search query exists, filter by name/category/country/quality
       If no search, load all products
    */
    if ($key !== '') {
        // Search mode - filter by search query
            $sql = "SELECT p.id, p.j_name, p.image, p.price, p.discount, p.quality, ";
            $sql .= "p.description, p.category, p.country, p.date_added, ";
            $sql .= "COALESCE(SUM(oi.quantity), 0) as total_orders ";
            $sql .= "FROM products p ";
            $sql .= "LEFT JOIN order_items oi ON p.id = oi.product_id ";
            $sql .= "WHERE (LOWER(p.j_name) LIKE ?  OR LOWER(p.category) LIKE ?  OR LOWER(p.country) LIKE ?  OR LOWER(p.quality) LIKE ?) ";
            if ($category !== 'all') {
                $sql .= " AND LOWER(p.category) = ? ";
            }
            $sql .= "GROUP BY p.id";

            $stmt = $conn->prepare($sql);
            $searchPattern = "%{$key}%";
            if ($category !== 'all') {
                $stmt->bind_param("sssss", $searchPattern, $searchPattern, $searchPattern, $searchPattern, $category);
            } else {
                $stmt->bind_param("ssss", $searchPattern, $searchPattern, $searchPattern, $searchPattern);
            }
    } else {
        // Default mode - load all products
            $sql = "SELECT p.id, p.j_name, p.image, p.price, p.discount, p.quality, ";
            $sql .= "p.description, p.category, p.country, p.date_added, ";
            $sql .= "COALESCE(SUM(oi.quantity), 0) as total_orders ";
            $sql .= "FROM products p ";
            $sql .= "LEFT JOIN order_items oi ON p.id = oi.product_id ";
            if ($category !== 'all') {
                $sql .= "WHERE LOWER(p.category) = ? ";
            }
            $sql .= "GROUP BY p.id";
        
            $stmt = $conn->prepare($sql);
            if ($category !== 'all') {
                $stmt->bind_param("s", $category);
            }
    }

    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }

    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();

/* 
   ALGORITHM 1: CONTENT-BASED RECOMMENDATION
   Matches products based on similarity to search query
   Scoring: Name match(10), Category(5), Country(5), Quality(3)
*/
function contentBasedScore(array $product, string $searchKey): int
{
    $score = 0;
    $key = strtolower($searchKey);
    
    // Name match - highest priority
    if (stripos($product['j_name'], $key) !== false) {
        $score += 10;
        // Bonus for exact word match
        if (strpos(strtolower($product['j_name']), $key) === 0) {
            $score += 5;
        }
    }
    
    // Category match
    if (stripos($product['category'], $key) !== false) {
        $score += 5;
    }
    
    // Country match
    if (stripos($product['country'], $key) !== false) {
        $score += 5;
    }
    
    // Quality match
    if (stripos($product['quality'], $key) !== false) {
        $score += 3;
    }
    
    return $score;
}

/* 
   ALGORITHM 2: COLLABORATIVE FILTERING
   Recommends products based on popularity (total orders)
   Used to identify bestsellers and trending items
*/
function applyCollaborativeFiltering(&$products)
{
    // Find max orders to calculate relative popularity
    $maxOrders = 0;
    foreach ($products as $p) {
        if ($p['total_orders'] > $maxOrders) {
            $maxOrders = $p['total_orders'];
        }
    }
    
    foreach ($products as &$item) {
        $orders = (int)$item['total_orders'];
        
        // Bestseller: top 20% of orders
        $item['is_bestseller'] = $maxOrders > 0 && $orders >= ($maxOrders * 0.6);
        
        // Trending: recent orders with good frequency
        $item['is_trending'] = $orders >= 5 && $orders < ($maxOrders * 0.6);
        
        // Popularity score for ranking
        $item['popularity_score'] = $orders;
    }
    unset($item);
}

// Apply algorithms
applyCollaborativeFiltering($products);

foreach ($products as &$item) {
    $item['relevance_score'] = $key !== '' ? contentBasedScore($item, $key) : 0;
    $item['final_price'] = $item['discount'] > 0 
        ? $item['price'] - ($item['price'] * $item['discount'] / 100)
        : $item['price'];
}
unset($item);

/* 
   SORTING (Professional E-commerce Standards)
*/
switch ($sortBy) {
    case 'popularity':
        // Most ordered first (collaborative filtering)
        usort($products, fn($a, $b) => $b['total_orders'] <=> $a['total_orders']);
        break;
        
    case 'price_low':
        usort($products, fn($a, $b) => $a['final_price'] <=> $b['final_price']);
        break;
        
    case 'price_high':
        usort($products, fn($a, $b) => $b['final_price'] <=> $a['final_price']);
        break;
        
    case 'newest':
        usort($products, fn($a, $b) => strtotime($b['date_added']) <=> strtotime($a['date_added']));
        break;
        
    case 'relevance':
    default:
        // Only use relevance scoring if search query exists
        if ($key !== '') {
            // Content-based + Collaborative (relevance + popularity)
            usort($products, function($a, $b) {
                $scoreA = ($a['relevance_score'] * 2) + ($a['popularity_score'] * 0.5);
                $scoreB = ($b['relevance_score'] * 2) + ($b['popularity_score'] * 0.5);
                return $scoreB <=> $scoreA;
            });
        } else {
            // No search - default to popularity
            usort($products, fn($a, $b) => $b['total_orders'] <=> $a['total_orders']);
        }
        break;
    }

    // Return JSON response
    echo json_encode([
        'products' => $products,
        'total' => count($products),
        'success' => true
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'products' => [],
        'success' => false
    ]);
}
?>