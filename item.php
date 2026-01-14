[file name]: item.php
[file content begin]
<?php
session_start();
require_once 'config/db.php';

$page_title = 'Карточка товара';


$product_id = intval($_GET['id'] ?? 0);
if (!$product_id) {
    header('Location: shop.php');
    exit();
}


$sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?";
$result = db_query($sql, [$product_id]);

if (!$result || $result->num_rows == 0) {
    header('Location: shop.php');
    exit();
}

$product = $result->fetch_assoc();
$page_title = $product['name'] . ' - Чайная Гора';


$similar_sql = "SELECT * FROM products 
               WHERE category_id = ? AND id != ? 
               LIMIT 4";
$similar_products = db_query($similar_sql, [$product['category_id'], $product_id]);

// Функция для добавления в корзину прямо на этой странице
if (isset($_GET['add_to_cart']) && $_GET['add_to_cart'] == $product_id && isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    
    // Проверяем наличие в корзине
    $cart_item = db_fetch_one(
        "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?",
        [$user_id, $product_id]
    );
    
    if ($cart_item) {
        // Увеличиваем количество
        db_query(
            "UPDATE cart SET quantity = quantity + 1 WHERE id = ?",
            [$cart_item['id']]
        );
        $success_message = 'Товар добавлен в корзину (количество увеличено)';
    } else {
        // Добавляем новый товар
        db_query(
            "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)",
            [$user_id, $product_id]
        );
        $success_message = 'Товар добавлен в корзину';
    }
    
    header('Location: item.php?id=' . $product_id . '&success=1');
    exit();
}

include 'header.php';
?>

<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <!-- Хлебные крошки -->
    <div style="margin-bottom: 20px; font-size: 14px; color: #666;">
        <a href="index.php">Главная</a> > 
        <a href="shop.php">Каталог</a> > 
        <?php if($product['category_slug']): ?>
            <a href="shop.php?category=<?php echo urlencode($product['category_slug']); ?>">
                <?php echo htmlspecialchars($product['category_name']); ?>
            </a> > 
        <?php endif; ?>
        <span><?php echo htmlspecialchars($product['name']); ?></span>
    </div>
    
    <?php if(isset($_GET['success']) && $_GET['success'] == '1'): ?>
        <div style="background: #e6ffe6; color: #006600; padding: 10px; border-radius: 5px; margin: 10px 0; text-align: center;">
            ✅ Товар добавлен в корзину!
        </div>
    <?php endif; ?>
    
    <!-- Основная информация о товаре -->
    <div style="display: flex; gap: 40px; margin-bottom: 40px; flex-wrap: wrap;">
        <!-- Изображение товара -->
        <div style="flex: 1; min-width: 300px;">
            <div style="background: #f9f3e9; border-radius: 10px; padding: 20px; text-align: center;">
                <?php 
                $image_path = 'uploads/' . htmlspecialchars($product['image'] ?? '');
                if(!empty($product['image']) && file_exists($image_path)): 
                ?>
                    <img src="<?php echo $image_path; ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         style="max-width: 100%; max-height: 400px; border-radius: 5px;">
                <?php else: ?>
                    <div style="font-size: 100px; color: #c17a1f; padding: 40px;">
                        🍵
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Информация о товаре -->
        <div style="flex: 2; min-width: 300px;">
            <h1 style="color: #2d5016; margin-bottom: 15px;">
                <?php echo htmlspecialchars($product['name']); ?>
            </h1>
            
            <?php if($product['category_name']): ?>
                <p style="color: #666; margin-bottom: 15px;">
                    Категория: 
                    <a href="shop.php?category=<?php echo urlencode($product['category_slug']); ?>" 
                       style="color: #2d5016;">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </a>
                </p>
            <?php endif; ?>
            
            <div style="font-size: 32px; color: #c17a1f; font-weight: bold; margin: 20px 0;">
                <?php echo htmlspecialchars($product['price']); ?> руб.
            </div>
            
            <div style="margin: 20px 0;">
                <p><strong>Вес:</strong> <?php echo htmlspecialchars($product['base_weight']); ?> г</p>
                <p style="color: <?php echo $product['stock'] > 0 ? '#2d7a2d' : '#cc0000'; ?>;">
                    <strong>Наличие:</strong> 
                    <?php echo $product['stock'] > 0 ? '✓ В наличии (' . $product['stock'] . ' шт.)' : '✗ Нет в наличии'; ?>
                </p>
            </div>
            
            <!-- Описание -->
            <div style="margin: 30px 0;">
                <h3 style="color: #2d5016; margin-bottom: 10px;">Описание</h3>
                <p style="line-height: 1.6;">
                    <?php echo nl2br(htmlspecialchars($product['description'] ?? 'Описание отсутствует')); ?>
                </p>
            </div>
            
            <!-- Кнопки действий -->
            <div style="display: flex; gap: 15px; margin-top: 30px;">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($product['stock'] > 0): ?>
                        <a href="item.php?id=<?php echo $product_id; ?>&add_to_cart=<?php echo $product_id; ?>" 
                           class="btn btn-primary"
                           style="padding: 15px 30px; font-size: 16px; text-decoration: none; text-align: center;">
                            🛒 Добавить в корзину
                        </a>
                    <?php else: ?>
                        <button disabled class="btn"
                                style="padding: 15px 30px; font-size: 16px; background: #ccc; cursor: not-allowed;">
                            Нет в наличии
                        </button>
                    <?php endif; ?>
                    
                    <a href="wishlist.php?add=<?php echo $product_id; ?>" 
                       class="btn btn-secondary"
                       style="padding: 15px 30px; text-decoration: none; text-align: center;"
                       onclick="return confirm('Добавить в список желаний?')">
                        ♡ В список желаний
                    </a>
                <?php else: ?>
                    <a href="auth.php" class="btn btn-primary" style="padding: 15px 30px; font-size: 16px; text-decoration: none;">
                        🔑 Войдите, чтобы купить
                    </a>
                <?php endif; ?>
                
                <a href="shop.php" class="btn" style="padding: 15px 30px; text-decoration: none;">
                    ← Назад в каталог
                </a>
            </div>
        </div>
    </div>
    
    <!-- Похожие товары -->
    <?php if($similar_products && $similar_products->num_rows > 0): ?>
    <div style="margin-top: 60px;">
        <h2 style="color: #2d5016; margin-bottom: 25px; text-align: center;">Похожие товары</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
            <?php while($similar = $similar_products->fetch_assoc()): ?>
            <div style="border: 1px solid #e0e0e0; border-radius: 10px; overflow: hidden; transition: transform 0.3s; background: white;">
                <div style="height: 180px; overflow: hidden; background: #f9f3e9;">
                    <?php 
                    $similar_image = 'uploads/' . htmlspecialchars($similar['image'] ?? '');
                    if(!empty($similar['image']) && file_exists($similar_image)): 
                    ?>
                        <img src="<?php echo $similar_image; ?>" 
                             alt="<?php echo htmlspecialchars($similar['name']); ?>"
                             style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 50px; color: #c17a1f;">
                            🍃
                        </div>
                    <?php endif; ?>
                </div>
                
                <div style="padding: 15px;">
                    <h3 style="margin: 0 0 10px 0; color: #2d5016; font-size: 16px;">
                        <a href="item.php?id=<?php echo $similar['id']; ?>" style="color: inherit; text-decoration: none;">
                            <?php echo htmlspecialchars($similar['name']); ?>
                        </a>
                    </h3>
                    
                    <div style="color: #c17a1f; font-size: 18px; font-weight: bold; margin: 10px 0;">
                        <?php echo htmlspecialchars($similar['price']); ?> руб.
                    </div>
                    
                    <div style="margin-top: 10px; display: flex; gap: 8px;">
                        <a href="item.php?id=<?php echo $similar['id']; ?>" 
                           class="btn btn-primary"
                           style="flex: 1; padding: 8px; font-size: 14px; text-decoration: none; text-align: center;">
                            Подробнее
                        </a>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="item.php?id=<?php echo $similar['id']; ?>&add_to_cart=<?php echo $similar['id']; ?>" 
                               class="btn"
                               style="padding: 8px 12px; text-decoration: none; text-align: center;"
                               title="В корзину">
                                🛒
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.btn {
    display: inline-block;
    padding: 10px 20px;
    background: #c17a1f;
    color: white;
    text-decoration: none;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s;
    text-align: center;
}

.btn:hover {
    background: #a36618;
}

.btn-primary {
    background: #2d5016;
}

.btn-primary:hover {
    background: #1a3310;
}

.btn-secondary {
    background: #666;
}

.btn-secondary:hover {
    background: #555;
}

.no-image {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    font-size: 40px;
    color: #c17a1f;
}
</style>

<?php include 'footer.php'; ?>
[file content end]