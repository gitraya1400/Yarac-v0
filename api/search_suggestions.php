<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
    echo json_encode([]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $search_term = trim($_GET['q']);
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
    
    $query = "SELECT id, name, price, category, image 
              FROM products 
              WHERE stock > 0 AND (name LIKE ? OR description LIKE ? OR category LIKE ?) 
              ORDER BY rating DESC, total_reviews DESC 
              LIMIT ?";
    
    $stmt = $db->prepare($query);
    $search_param = "%$search_term%";
    $stmt->bindParam(1, $search_param);
    $stmt->bindParam(2, $search_param);
    $stmt->bindParam(3, $search_param);
    $stmt->bindParam(4, $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $suggestions = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $suggestions[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'category' => $row['category'],
            'image' => $row['image']
        ];
    }
    
    echo json_encode($suggestions);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
