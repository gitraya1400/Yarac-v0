<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../classes/Product.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'Product ID is required']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $product = new Product($db);
    
    $product_id = intval($_GET['id']);
    
    if ($product->getById($product_id)) {
        $response = [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'category' => $product->category,
            'gender' => $product->gender,
            'image' => $product->image,
            'stock' => $product->stock,
            'rating' => $product->rating,
            'total_reviews' => $product->total_reviews,
            'sizes' => $product->sizes,
            'featured' => $product->featured,
            'created_at' => $product->created_at
        ];
        
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Product not found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
