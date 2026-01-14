<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

$page_title = 'Профиль';
include 'header.php';


$wishlist_count = db_query("SELECT COUNT(*) as count FROM wishlist WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc();


$messages_count = db_query("SELECT COUNT(*) as count FROM messages WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc();
?>

<div style="max-width: 600px; margin: 30px auto; padding: 30px; border: 1px solid #ddd; border-radius: 5px;">
    <h2 style="text-align: center;">Ваш профиль</h2>
    
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="width: 80px; height: 80px; background: #2d5016; color: white; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 30px; font-weight: bold;">
            <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
        </div>
    </div>
    
    <div style="margin-bottom: 20px;">
        <p><strong>Имя пользователя:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
        <p><strong>В списке желаний:</strong> <?php echo $wishlist_count['count']; ?> товаров</p>
        <p><strong>Отправлено сообщений:</strong> <?php echo $messages_count['count']; ?></p>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="index.php" style="display: inline-block; padding: 10px 20px; background: #2d5016; color: white; text-decoration: none; border-radius: 3px; margin: 5px;">
            На главную
        </a>
        <a href="wishlist.php" style="display: inline-block; padding: 10px 20px; background: #c17a1f; color: white; text-decoration: none; border-radius: 3px; margin: 5px;">
            💖 Список желаний
        </a>
        <a href="contact.php" style="display: inline-block; padding: 10px 20px; background: #2d5016; color: white; text-decoration: none; border-radius: 3px; margin: 5px;">
            ✉️ Обратная связь
        </a>
        <a href="auth.php?logout=1" style="display: inline-block; padding: 10px 20px; background: #666; color: white; text-decoration: none; border-radius: 3px; margin: 5px;">
            Выйти
        </a>
    </div>
</div>

<?php include 'footer.php'; ?>