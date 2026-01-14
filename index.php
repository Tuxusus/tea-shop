<?php
$page_title = 'Главная - Магазин китайского чая';
require_once 'config/db.php';
include 'header.php';
?>

<h1 style="color: #2d5016; text-align: center; margin: 30px 0;">Добро пожаловать в мир китайского чая</h1>

<div style="text-align: center; max-width: 800px; margin: 0 auto 40px;">
    <p style="font-size: 18px; color: #666;">
        Откройте для себя удивительный мир китайского чая. У нас только лучшие сорта, 
        собранные в экологически чистых регионах Китая.
    </p>
</div>

<?php

$products = db_query("SELECT * FROM products WHERE is_popular = 1 LIMIT 4");
if ($products && $products->num_rows > 0):
?>
<div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin: 30px 0;">
    <h2 style="color: #2d5016; margin-bottom: 25px; text-align: center;">🔥 Популярные товары</h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px;">
        <?php while($product = $products->fetch_assoc()): ?>
        <div style="border: 1px solid #e0e0e0; border-radius: 10px; overflow: hidden; transition: transform 0.3s; background: white;">
            <div style="height: 200px; overflow: hidden; background: #f9f3e9;">
                <?php if(!empty($product['image']) && file_exists('uploads/' . $product['image'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 60px; color: #c17a1f;">
                        🍵
                    </div>
                <?php endif; ?>
            </div>
            
            <div style="padding: 20px;">
                <h3 style="margin: 0 0 10px 0; color: #2d5016; font-size: 18px;">
                    <?php echo htmlspecialchars($product['name']); ?>
                </h3>
                
                <p style="color: #c17a1f; font-size: 22px; font-weight: bold; margin: 10px 0;">
                    <?php echo htmlspecialchars($product['price']); ?> руб.
                </p>
                
                <div style="margin: 15px 0; font-size: 14px; color: #666;">
                    <p>Вес: <?php echo htmlspecialchars($product['base_weight']); ?> г</p>
                    <p style="color: <?php echo $product['stock'] > 0 ? '#2d7a2d' : '#ff4757'; ?>;">
                        <?php echo $product['stock'] > 0 ? '✓ В наличии' : '✗ Нет в наличии'; ?>
                    </p>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <a href="item.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-primary" style="flex: 2; text-decoration: none; text-align: center;">
                        Подробнее
                    </a>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($product['stock'] > 0): ?>
                            <button onclick="addToCart(<?php echo (int)$product['id']; ?>, this)" 
                                    class="btn" 
                                    style="flex: 1;"
                                    title="Добавить в корзину">
                                🛒
                            </button>
                        <?php else: ?>
                            <button disabled class="btn" 
                                    style="flex: 1; background: #ccc; cursor: not-allowed;"
                                    title="Нет в наличии">
                                🛒
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="auth.php" class="btn" style="flex: 1; text-decoration: none; text-align: center;" title="Войдите чтобы добавить в корзину">
                            🔒
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 40px 0;">
    <div style="background: #e8f5e8; padding: 25px; border-radius: 10px;">
        <h3 style="color: #2d5016; margin-bottom: 15px;">🍵 Категории чая</h3>
        <ul style="list-style: none; padding: 0;">
            <?php
            $categories = db_query("SELECT * FROM categories WHERE parent_id IS NOT NULL LIMIT 4");
            while($cat = $categories->fetch_assoc()): ?>
                <li style="margin: 8px 0;">
                    <a href="shop.php?category=<?php echo urlencode($cat['slug']); ?>" 
                       style="color: #2d5016; text-decoration: none; display: block; padding: 5px 10px; border-radius: 3px;">
                        → <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                </li>
            <?php endwhile; ?>
            <li style="margin: 8px 0;">
                <a href="shop.php" style="color: #2d5016; text-decoration: none; display: block; padding: 5px 10px; border-radius: 3px; font-weight: bold;">
                    → Все категории
                </a>
            </li>
        </ul>
    </div>
    
    <div style="background: #f9f3e9; padding: 25px; border-radius: 10px;">
        <h3 style="color: #2d5016; margin-bottom: 15px;">⭐ Почему мы?</h3>
        <ul style="list-style: none; padding: 0;">
            <li style="margin: 8px 0; padding-left: 20px; position: relative;">✅ Гарантия качества</li>
            <li style="margin: 8px 0; padding-left: 20px; position: relative;">✅ Натуральные продукты</li>
            <li style="margin: 8px 0; padding-left: 20px; position: relative;">✅ Быстрая доставка</li>
            <li style="margin: 8px 0; padding-left: 20px; position: relative;">✅ Экспертная консультация</li>
        </ul>
    </div>
    
    <div style="background: #e8e5f5; padding: 25px; border-radius: 10px;">
        <h3 style="color: #2d5016; margin-bottom: 15px;">📞 Контакты</h3>
        <p>Телефон: +7 (999) 123-45-67</p>
        <p>Email: info@tea-mountain.ru</p>
        <p>Адрес: Москва, ул. Чайная, 15</p>
        <p>Время работы: 10:00 - 20:00</p>
    </div>
</div>

<script>
// Проверяем, все ли кнопки корзины работают
document.addEventListener('DOMContentLoaded', function() {
    console.log('Страница загружена, проверяем кнопки корзины...');
    
    // Добавляем обработчики для всех кнопок корзины
    const cartButtons = document.querySelectorAll('button[onclick*="addToCart"]');
    cartButtons.forEach(button => {
        console.log('Найдена кнопка корзины:', button);
        
        // Добавляем стиль при наведении
        button.addEventListener('mouseenter', function() {
            if (!this.disabled) {
                this.style.transform = 'scale(1.05)';
                this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
            }
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });
});
</script>

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
    transition: all 0.3s;
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

/* Анимация для кнопок */
@keyframes successPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.success-animation {
    animation: successPulse 0.5s ease;
}
</style>

<?php include 'footer.php'; ?>