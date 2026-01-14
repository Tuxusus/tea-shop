<?php
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'Каталог';
include 'header.php';


$categories = db_fetch_all("SELECT * FROM categories WHERE parent_id IS NOT NULL ORDER BY name");
?>

<h1>Каталог товаров</h1>

<div style="display: flex; gap: 30px; margin-top: 20px;">
   
    <aside style="width: 250px;">
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px;">
            <h3>Категории</h3>
            <a href="shop.php" style="display: block; padding: 8px; background: #f0f0f0; margin: 5px 0; border-radius: 4px; text-decoration: none; color: #333;">Все товары</a>
            <?php foreach($categories as $cat): ?>
            <a href="shop.php?category=<?php echo urlencode($cat['slug']); ?>" 
               style="display: block; padding: 8px; background: <?php echo ($_GET['category'] ?? '') == $cat['slug'] ? '#2d5016' : '#f0f0f0'; ?>; margin: 5px 0; border-radius: 4px; text-decoration: none; color: <?php echo ($_GET['category'] ?? '') == $cat['slug'] ? 'white' : '#333'; ?>;">
                <?php echo htmlspecialchars($cat['name']); ?>
            </a>
            <?php endforeach; ?>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h3>Фильтры</h3>
            <a href="shop.php?stock=1" style="display: block; padding: 8px; background: <?php echo isset($_GET['stock']) ? '#2d5016' : '#f0f0f0'; ?>; margin: 5px 0; border-radius: 4px; text-decoration: none; color: <?php echo isset($_GET['stock']) ? 'white' : '#333'; ?>;">
                Только в наличии
            </a>
            <a href="shop.php?popular=1" style="display: block; padding: 8px; background: <?php echo isset($_GET['popular']) ? '#2d5016' : '#f0f0f0'; ?>; margin: 5px 0; border-radius: 4px; text-decoration: none; color: <?php echo isset($_GET['popular']) ? 'white' : '#333'; ?>;">
                Популярные товары
            </a>
        </div>
    </aside>
    
    <!-- Товары -->
    <div style="flex: 1;">
        <!-- Поиск -->
        <div style="margin-bottom: 20px;">
            <input type="text" 
                   id="searchInput" 
                   placeholder="Поиск товаров..." 
                   style="width: 100%; padding: 12px; border: 2px solid #c17a1f; border-radius: 5px; font-size: 16px;">
        </div>
        
        <!-- Контейнер для товаров -->
        <div id="productsContainer">
            <div style="text-align: center; padding: 40px;">
                <div class="spinner"></div>
                <p>Загрузка товаров...</p>
            </div>
        </div>
    </div>
</div>

<script>
// Функция загрузки товаров
function loadProducts() {
    const container = document.getElementById('productsContainer');
    const search = document.getElementById('searchInput').value;
    
    // Показываем загрузку
    container.innerHTML = '<div style="text-align: center; padding: 40px;"><div class="spinner"></div><p>Загрузка товаров...</p></div>';
    
    // Собираем параметры
    const params = new URLSearchParams(window.location.search);
    if (search) params.set('search', search);
    
    // Загружаем товары
    fetch('get_products.php?' + params.toString())
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(error => {
            container.innerHTML = '<div class="empty-state"><h3>Ошибка загрузки</h3><p>Попробуйте обновить страницу</p></div>';
            console.error(error);
        });
}

// Обработчик кликов по кнопкам корзины через делегирование
document.addEventListener('click', function(event) {
    
    if (event.target.classList.contains('add-to-cart-btn')) {
        event.preventDefault();
        event.stopPropagation();
        
        const button = event.target;
        const productId = button.getAttribute('data-product-id');
        const productName = button.getAttribute('data-product-name');
        
        if (!productId) return;
        
        console.log('Добавление товара из каталога:', productId);
        
        // Сохраняем исходное состояние
        const originalHTML = button.innerHTML;
        const originalBackground = button.style.background;
        
        // Показываем индикатор загрузки
        button.innerHTML = '...';
        button.disabled = true;
        button.classList.add('cart-loading');
        
        // Отправляем запрос
        fetch('add_to_cart.php?id=' + productId)
            .then(response => {
                console.log('Статус ответа:', response.status);
                if (response.status === 401) {
                    // Не авторизован
                    showNotification('Для добавления в корзину необходимо войти в систему', 'error');
                    setTimeout(() => {
                        window.location.href = 'auth.php';
                    }, 1500);
                    throw new Error('Not authorized');
                }
                return response.json();
            })
            .then(data => {
                console.log('Ответ сервера:', data);
                
                if (data && data.success === true) {
                    // Успех
                    showNotification(data.message || 'Товар "' + productName + '" добавлен в корзину', 'success');
                    
                    // Обновляем счетчик корзины
                    updateCartCount();
                    
                    // Меняем кнопку на успешную
                    button.innerHTML = '✓';
                    button.style.background = '#2d5016';
                    button.style.color = 'white';
                    button.classList.remove('cart-loading');
                    
                    // Возвращаем исходный вид через 1.5 секунды
                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.style.background = originalBackground;
                        button.style.color = '';
                        button.disabled = false;
                    }, 1500);
                } else {
                    // Ошибка от сервера
                    showNotification(data.message || 'Ошибка при добавлении в корзину', 'error');
                    button.innerHTML = originalHTML;
                    button.style.background = originalBackground;
                    button.classList.remove('cart-loading');
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Ошибка запроса:', error);
                
                // Проверяем, это ошибка авторизации или сетевые проблемы
                if (error.message !== 'Not authorized') {
                    showNotification('Ошибка соединения с сервером', 'error');
                }
                
                button.innerHTML = originalHTML;
                button.style.background = originalBackground;
                button.classList.remove('cart-loading');
                button.disabled = false;
            });
    }
});

// Поиск с задержкой
let searchTimer;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(loadProducts, 500);
});

// Загружаем при старте
document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
    
    // Добавляем стили
    const style = document.createElement('style');
    style.textContent = `
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #2d5016;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .cart-success {
            background: #2d5016 !important;
            animation: pulse 0.5s;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
    `;
    document.head.appendChild(style);
});
</script>

<?php include 'footer.php'; ?>