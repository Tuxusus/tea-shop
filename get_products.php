[file name]: get_products.php
[file content begin]
<?php
// get_products.php - универсальная версия
session_start();
require_once 'config/db.php';

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$stock = $_GET['stock'] ?? '';
$popular = $_GET['popular'] ?? '';

// Строим запрос
$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND p.name LIKE ?";
    $params[] = "%$search%";
}

if ($category) {
    $sql .= " AND c.slug = ?";
    $params[] = $category;
}

if ($stock === '1') {
    $sql .= " AND p.stock > 0";
}

if ($popular === '1') {
    $sql .= " AND p.is_popular = 1";
}

$sql .= " ORDER BY p.name ASC LIMIT 100";

$products = db_fetch_all($sql, $params);

if (empty($products)): ?>
    <div class="empty-state">
        <h3>Товары не найдены</h3>
        <p>Попробуйте изменить параметры поиска</p>
    </div>
<?php else: ?>
    <div class="products-grid">
        <?php foreach($products as $product): ?>
        <div class="product-card">
            <div class="product-image">
                <?php if(!empty($product['image']) && file_exists('uploads/' . $product['image'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <?php else: ?>
                    <div class="no-image">🍃</div>
                <?php endif; ?>
            </div>
            
            <div class="product-content">
                <div class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Без категории'); ?></div>
                <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                
                <p class="product-description">
                    <?php 
                    $desc = strip_tags($product['description'] ?? '');
                    echo mb_strlen($desc) > 80 ? mb_substr($desc, 0, 80) . '...' : $desc;
                    ?>
                </p>
                
                <div class="product-price"><?php echo htmlspecialchars($product['price']); ?> руб.</div>
                <p class="product-weight">Вес: <?php echo htmlspecialchars($product['base_weight']); ?> г</p>
                
                <p class="product-stock <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                    <?php echo $product['stock'] > 0 ? '✓ В наличии' : '✗ Нет в наличии'; ?>
                </p>
                
                <div class="product-actions">
                    <a href="item.php?id=<?php echo $product['id']; ?>" class="btn btn-details">Подробнее</a>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($product['stock'] > 0): ?>
                            <button class="btn btn-cart add-to-cart-btn" 
                                    data-product-id="<?php echo $product['id']; ?>"
                                    onclick="window.addToCart(<?php echo $product['id']; ?>, this)">
                                🛒
                            </button>
                        <?php else: ?>
                            <button disabled class="btn btn-cart" style="background: #ccc; cursor: not-allowed;">🛒</button>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="auth.php" class="btn btn-cart" title="Войдите чтобы добавить в корзину">🔒</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <script>
    // Инициализация кнопок корзины после загрузки контента
    document.addEventListener('DOMContentLoaded', function() {
        const cartButtons = document.querySelectorAll('.add-to-cart-btn');
        cartButtons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                if (!this.disabled) {
                    this.style.transform = 'scale(1.1)';
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
<?php endif; ?>

<style>
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.product-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}

.product-card:hover {
    transform: translateY(-5px);
}

.product-image {
    height: 200px;
    background: #f9f3e9;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.product-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.no-image {
    font-size: 60px;
    color: #c17a1f;
}

.product-content {
    padding: 15px;
}

.product-category {
    color: #666;
    font-size: 12px;
    margin-bottom: 5px;
}

.product-name {
    color: #2d5016;
    margin: 0 0 10px 0;
    font-size: 16px;
    line-height: 1.4;
}

.product-description {
    color: #666;
    font-size: 14px;
    margin: 0 0 10px 0;
    line-height: 1.4;
}

.product-price {
    color: #c17a1f;
    font-size: 18px;
    font-weight: bold;
    margin: 10px 0;
}

.product-weight {
    color: #666;
    font-size: 14px;
    margin: 5px 0;
}

.in-stock {
    color: #2d7a2d;
    font-weight: bold;
}

.out-of-stock {
    color: #cc0000;
    font-weight: bold;
}

.product-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.btn {
    padding: 8px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    font-weight: bold;
    transition: all 0.3s;
}

.btn-details {
    background: #2d5016;
    color: white;
    flex: 1;
}

.btn-details:hover {
    background: #1a3310;
    transform: translateY(-2px);
}

.btn-cart {
    background: #c17a1f;
    color: white;
    width: 40px;
}

.btn-cart:hover:not(:disabled) {
    background: #a36618;
    transform: translateY(-2px);
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #666;
}

.btn-cart:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none !important;
}
</style>
[file content end]