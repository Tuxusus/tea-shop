<?php
// get_cart_count.php - получение количества товаров в корзине
session_start();
require_once 'config/db.php';

$count = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    $result = db_fetch_one(
        "SELECT COUNT(*) as count FROM cart WHERE user_id = ?",
        [$user_id]
    );
    if ($result) {
        $count = (int)$result['count'];
    }
}

header('Content-Type: application/json');
echo json_encode(['count' => $count]);
?>