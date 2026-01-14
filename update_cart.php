<?php
// update_cart.php - обновление корзины
session_start();
require_once 'config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'update_quantity':
        $cart_id = (int)($_POST['cart_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if ($cart_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Неверный ID товара']);
            exit();
        }
        
        if ($quantity <= 0) {
            // Если количество 0 или меньше - удаляем товар
            db_query("DELETE FROM cart WHERE id = ? AND user_id = ?", [$cart_id, $user_id]);
            echo json_encode(['success' => true, 'message' => 'Товар удален', 'removed' => true]);
        } else {
            // Проверяем наличие на складе
            $check_sql = "SELECT p.stock FROM cart c 
                         JOIN products p ON c.product_id = p.id 
                         WHERE c.id = ? AND c.user_id = ?";
            $check = db_fetch_one($check_sql, [$cart_id, $user_id]);
            
            if ($check && $quantity > $check['stock']) {
                $quantity = $check['stock'];
            }
            
            if ($quantity > 99) $quantity = 99;
            
            db_query("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?", 
                    [$quantity, $cart_id, $user_id]);
            echo json_encode(['success' => true, 'message' => 'Количество обновлено', 'quantity' => $quantity]);
        }
        break;
        
    case 'remove_item':
        $cart_id = (int)($_POST['cart_id'] ?? 0);
        
        if ($cart_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Неверный ID товара']);
            exit();
        }
        
        db_query("DELETE FROM cart WHERE id = ? AND user_id = ?", [$cart_id, $user_id]);
        echo json_encode(['success' => true, 'message' => 'Товар удален из корзины']);
        break;
        
    case 'clear_cart':
        db_query("DELETE FROM cart WHERE user_id = ?", [$user_id]);
        echo json_encode(['success' => true, 'message' => 'Корзина очищена']);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Неизвестное действие']);
}
?>