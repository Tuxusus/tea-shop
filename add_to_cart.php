<?php
// add_to_cart.php - основной файл добавления в корзину
session_start();
require_once 'config/db.php';

header('Content-Type: application/json');

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit();
}

$product_id = intval($_GET['id'] ?? 0);
if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Неверный ID товара']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Проверяем существование товара и его наличие
$product = db_fetch_one(
    "SELECT id, name, price, stock FROM products WHERE id = ?",
    [$product_id]
);

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Товар не найден']);
    exit();
}

if ($product['stock'] <= 0) {
    echo json_encode(['success' => false, 'message' => 'Товар закончился']);
    exit();
}

// Проверяем наличие в корзине
$cart_item = db_fetch_one(
    "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?",
    [$user_id, $product_id]
);

if ($cart_item) {
    // Проверяем, не превышает ли новое количество остаток
    $new_quantity = $cart_item['quantity'] + 1;
    if ($new_quantity > $product['stock']) {
        $new_quantity = $product['stock'];
    }
    
    if ($new_quantity > 99) {
        $new_quantity = 99;
    }
    
    db_query(
        "UPDATE cart SET quantity = ? WHERE id = ?",
        [$new_quantity, $cart_item['id']]
    );
    
    $response = [
        'success' => true,
        'message' => 'Количество увеличено: ' . htmlspecialchars($product['name']),
        'quantity' => $new_quantity
    ];
} else {
    // Добавляем новый товар
    db_query(
        "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)",
        [$user_id, $product_id]
    );
    
    $response = [
        'success' => true,
        'message' => 'Добавлено в корзину: ' . htmlspecialchars($product['name'])
    ];
}

// Получаем общее количество товаров в корзине для обновления счетчика
$cart_count = db_fetch_one(
    "SELECT COUNT(*) as count FROM cart WHERE user_id = ?",
    [$user_id]
);

$response['cart_count'] = $cart_count['count'] ?? 0;

echo json_encode($response);
?>