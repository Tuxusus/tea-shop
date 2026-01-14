<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

$page_title = 'Оформление заказа';
include 'header.php';
?>

<h1>Оформление заказа</h1>
<p>Эта страница находится в разработке...</p>

<div style="text-align: center; padding: 40px;">
    <p style="font-size: 20px; color: #666;">Функция оформления заказа будет реализована в следующей версии</p>
    <a href="cart.php" style="display: inline-block; margin-top: 20px; padding: 10px 30px; background: #2d5016; color: white; text-decoration: none; border-radius: 5px;">
        Вернуться в корзину
    </a>
</div>

<?php include 'footer.php'; ?>