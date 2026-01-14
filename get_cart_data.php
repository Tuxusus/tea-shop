<?php
// get_cart_data.php - получение данных корзины
session_start();
require_once 'config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Требуется авторизация']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Получаем товары в корзине
$sql = "SELECT c.id as cart_id, c.quantity, 
               p.id as product_id, p.name, p.price, p.image, p.base_weight, p.stock
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
        ORDER BY c.added_at DESC";
$result = db_query($sql, [$user_id]);

$items = [];
$total_items = 0;
$total_price = 0;

if ($result) {
    while($item = $result->fetch_assoc()) {
        $item_total = $item['price'] * $item['quantity'];
        $image_url = (!empty($item['image']) && file_exists('uploads/' . $item['image'])) 
            ? 'uploads/' . htmlspecialchars($item['image']) 
            : null;
        
        $items[] = [
            'cart_id' => (int)$item['cart_id'],
            'product_id' => (int)$item['product_id'],
            'name' => $item['name'],
            'price' => (float)$item['price'],
            'quantity' => (int)$item['quantity'],
            'total_price' => $item_total,
            'weight' => $item['base_weight'],
            'stock' => (int)$item['stock'],
            'image_url' => $image_url,
            'max_quantity' => min(99, $item['stock'])
        ];
        
        $total_items += $item['quantity'];
        $total_price += $item_total;
    }
}

echo json_encode([
    'success' => true,
    'items' => $items,
    'total_items' => $total_items,
    'total_price' => number_format($total_price, 2, '.', ''),
    'formatted_total' => number_format($total_price, 0, '.', ' ') . ' руб.'
]);
?>