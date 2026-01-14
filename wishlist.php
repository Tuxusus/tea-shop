<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

$page_title = 'Мой список желаний';
include 'header.php';

if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    $user_id = (int)$_SESSION['user_id'];
    
    db_query("DELETE FROM wishlist WHERE id = ? AND user_id = ?", [$remove_id, $user_id]);
    header('Location: wishlist.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$sql = "SELECT w.id as wish_id, p.*, c.name as cat_name 
        FROM wishlist w
        JOIN products p ON w.product_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE w.user_id = ?
        ORDER BY w.added_at DESC";
$result = db_query($sql, [$user_id]);

if (isset($_GET['add_to_cart'])) {
    $product_id = intval($_GET['add_to_cart']);
    
    $check = db_query("SELECT w.id FROM wishlist w WHERE w.user_id = ? AND w.product_id = ?", [$user_id, $product_id]);
    
    if ($check && $check->num_rows > 0) {
        
        $cart_check = db_query("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?", [$user_id, $product_id]);
        
        if ($cart_check->num_rows > 0) {
          
            $item = $cart_check->fetch_assoc();
            db_query("UPDATE cart SET quantity = quantity + 1 WHERE id = ?", [$item['id']]);
            $success_msg = 'Товар добавлен в корзину';
        } else {
           
            db_query("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)", [$user_id, $product_id]);
            $success_msg = 'Товар добавлен в корзину';
        }
    }
}
?>

<div class="main-content">
    <h1>Мой список желаний</h1>
    
    <?php if(isset($success_msg)): ?>
        <div style="background: #e6ffe6; color: #006600; padding: 10px; border-radius: 5px; margin: 10px 0;">
            <?php echo htmlspecialchars($success_msg); ?>
        </div>
    <?php endif; ?>
    
    <?php if(!$result || $result->num_rows == 0): ?>
        <div style="text-align: center; padding: 40px; background: #f9f9f9; border-radius: 5px; margin: 30px 0;">
            <p style="font-size: 18px; color: #666;">Ваш список желаний пуст</p>
            <a href="shop.php" style="display: inline-block; margin-top: 15px; padding: 10px 20px; background: #2d5016; color: white; text-decoration: none; border-radius: 3px;">
                Перейти в каталог
            </a>
        </div>
    <?php else: ?>
        <p>Найдено товаров: <?php echo $result->num_rows; ?></p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin: 30px 0;">
            <?php while($item = $result->fetch_assoc()): ?>
                <div style="border: 1px solid #ddd; padding: 20px; border-radius: 5px; position: relative; background: white; box-shadow: 0 3px 10px rgba(0,0,0,0.05);">
                   
                    <a href="wishlist.php?remove=<?php echo $item['wish_id']; ?>" 
                       style="position: absolute; top: 10px; right: 10px; color: #ff6666; text-decoration: none; font-size: 20px; background: white; border-radius: 50%; width: 30px; height: 30px; text-align: center; line-height: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);"
                       onclick="return confirm('Удалить из списка желаний?')"
                       title="Удалить из списка">
                       ✕
                    </a>
                    
                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                    <p style="color: #666; margin: 5px 0;">Категория: <?php echo htmlspecialchars($item['cat_name'] ?? 'Без категории'); ?></p>
                    
                    <div style="font-size: 22px; color: #c17a1f; font-weight: bold; margin: 15px 0;">
                        <?php echo htmlspecialchars($item['price']); ?> руб.
                    </div>
                    
                    <p style="margin: 10px 0;">Вес: <?php echo htmlspecialchars($item['base_weight']); ?> г</p>
                    
                    <p style="margin: 10px 0; color: <?php echo $item['stock'] > 0 ? '#2d7a2d' : '#cc0000'; ?>;">
                        <?php echo $item['stock'] > 0 ? '✓ В наличии' : '✗ Нет в наличии'; ?>
                    </p>
                    
                    <div style="margin-top: 20px; display: flex; gap: 10px;">
                        <a href="item.php?id=<?php echo $item['id']; ?>" 
                           style="flex: 1; text-align: center; padding: 8px; background: #2d5016; color: white; text-decoration: none; border-radius: 3px;">
                            Подробнее
                        </a>
                        <?php if($item['stock'] > 0): ?>
                            <a href="wishlist.php?add_to_cart=<?php echo $item['id']; ?>" 
                               onclick="return confirm('Добавить этот товар в корзину?')"
                               style="flex: 1; text-align: center; padding: 8px; background: #c17a1f; color: white; text-decoration: none; border-radius: 3px;">
                                В корзину
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>