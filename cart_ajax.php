<?php
// cart.php - страница корзины
session_start();
require_once 'config/db.php';

// Только для авторизованных
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

$page_title = 'Корзина';
include 'header.php';
?>

<div class="main-content">
    <h1>🛒 Корзина покупок</h1>
    
    <div id="cart-container">
        <!-- Здесь будет загружена корзина через AJAX -->
        <div class="loading-state">
            <div class="spinner"></div>
            <p>Загрузка корзины...</p>
        </div>
    </div>
</div>

<style>
/* Стили для корзины */
.loading-state {
    text-align: center;
    padding: 60px 20px;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #2d5016;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Стили корзины */
.cart-empty {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    margin: 30px 0;
}

.cart-empty h3 {
    color: #666;
    margin-bottom: 20px;
    font-size: 24px;
}

.cart-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    margin: 25px 0;
    flex-wrap: wrap;
    gap: 20px;
}

.cart-summary-info {
    flex: 1;
    min-width: 250px;
}

.cart-summary-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.cart-items {
    display: grid;
    gap: 25px;
    margin: 30px 0;
}

.cart-item {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    display: flex;
    gap: 25px;
    align-items: center;
    transition: transform 0.3s, box-shadow 0.3s;
    position: relative;
}

.cart-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.cart-item-image {
    width: 140px;
    height: 140px;
    border-radius: 10px;
    overflow: hidden;
    background: #f9f3e9;
    flex-shrink: 0;
}

.cart-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    font-size: 50px;
    color: #c17a1f;
}

.cart-item-info {
    flex: 1;
    min-width: 0;
}

.cart-item-name {
    color: #2d5016;
    font-size: 18px;
    font-weight: bold;
    margin: 0 0 10px 0;
    line-height: 1.4;
}

.cart-item-price {
    color: #c17a1f;
    font-size: 20px;
    font-weight: bold;
    margin: 10px 0;
}

.cart-item-weight {
    color: #666;
    font-size: 14px;
    margin: 5px 0;
}

.cart-item-stock {
    font-size: 14px;
    font-weight: bold;
    margin: 10px 0;
}

.in-stock {
    color: #2d7a2d;
}

.out-of-stock {
    color: #cc0000;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 15px 0;
}

.quantity-btn {
    width: 36px;
    height: 36px;
    background: #f0f0f0;
    border: none;
    border-radius: 5px;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s;
}

.quantity-btn:hover {
    background: #2d5016;
    color: white;
}

.quantity-input {
    width: 70px;
    padding: 8px;
    border: 2px solid #ddd;
    border-radius: 5px;
    text-align: center;
    font-size: 16px;
    font-weight: bold;
}

.quantity-input:focus {
    border-color: #2d5016;
    outline: none;
}

.cart-item-total {
    text-align: right;
    font-size: 24px;
    color: #c17a1f;
    font-weight: bold;
    margin-bottom: 15px;
}

.cart-item-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    font-size: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-primary {
    background: #2d5016;
    color: white;
}

.btn-primary:hover {
    background: #1a3310;
    transform: translateY(-2px);
}

.btn-secondary {
    background: #c17a1f;
    color: white;
}

.btn-secondary:hover {
    background: #a36618;
    transform: translateY(-2px);
}

.btn-danger {
    background: #ff4757;
    color: white;
}

.btn-danger:hover {
    background: #ff3742;
    transform: translateY(-2px);
}

.btn-neutral {
    background: #666;
    color: white;
}

.btn-neutral:hover {
    background: #555;
    transform: translateY(-2px);
}

.btn-small {
    padding: 8px 16px;
    font-size: 13px;
}

.remove-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 36px;
    height: 36px;
    background: rgba(255, 71, 87, 0.1);
    color: #ff4757;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}

.remove-btn:hover {
    background: #ff4757;
    color: white;
    transform: scale(1.1);
}

.cart-total {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    margin-top: 40px;
    text-align: right;
}

.total-label {
    color: #666;
    font-size: 18px;
    margin-bottom: 10px;
}

.total-price {
    font-size: 42px;
    color: #c17a1f;
    font-weight: bold;
    margin: 10px 0 25px 0;
}

.checkout-btn {
    padding: 18px 50px;
    font-size: 18px;
    background: linear-gradient(135deg, #2d5016, #3d7026);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: bold;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;
    box-shadow: 0 5px 15px rgba(45, 80, 22, 0.3);
}

.checkout-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(45, 80, 22, 0.4);
    background: linear-gradient(135deg, #1a3310, #2d5016);
}

/* Адаптивность */
@media (max-width: 768px) {
    .cart-item {
        flex-direction: column;
        text-align: center;
        gap: 20px;
    }
    
    .cart-item-image {
        width: 100%;
        height: 200px;
    }
    
    .cart-item-actions {
        justify-content: center;
    }
    
    .cart-summary {
        flex-direction: column;
        text-align: center;
    }
    
    .cart-summary-actions {
        justify-content: center;
    }
    
    .checkout-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// Функция загрузки корзины
function loadCart() {
    const container = document.getElementById('cart-container');
    container.innerHTML = `
        <div class="loading-state">
            <div class="spinner"></div>
            <p>Загрузка корзины...</p>
        </div>
    `;
    
    fetch('get_cart_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #cc0000;">
                        <h3>${data.error}</h3>
                        <a href="auth.php" class="btn btn-primary">Войти</a>
                    </div>
                `;
                return;
            }
            
            if (!data.items || data.items.length === 0) {
                container.innerHTML = `
                    <div class="cart-empty">
                        <h3>😔 Ваша корзина пуста</h3>
                        <p style="color: #666; margin-bottom: 30px;">Добавьте товары из каталога</p>
                        <a href="shop.php" class="btn btn-primary" style="padding: 15px 40px; font-size: 16px;">
                            🛍️ Перейти в каталог
                        </a>
                    </div>
                `;
                updateCartCount(0);
                return;
            }
            
            renderCart(data);
            updateCartCount(data.total_items);
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = `
                <div style="text-align: center; padding: 40px; color: #cc0000;">
                    <h3>Ошибка загрузки корзины</h3>
                    <p>Попробуйте обновить страницу</p>
                    <button onclick="loadCart()" class="btn btn-primary">Обновить</button>
                </div>
            `;
        });
}

// Функция отрисовки корзины
function renderCart(data) {
    let html = `
        <div class="cart-summary">
            <div class="cart-summary-info">
                <h3 style="margin: 0 0 10px 0; color: #2d5016;">Итого в корзине:</h3>
                <p style="font-size: 18px; margin: 5px 0;">Товаров: <strong>${data.total_items} шт.</strong></p>
                <p style="font-size: 18px; margin: 5px 0;">Сумма: <strong>${data.formatted_total}</strong></p>
            </div>
            <div class="cart-summary-actions">
                <button onclick="clearCart()" class="btn btn-neutral">
                    🗑️ Очистить корзину
                </button>
                <a href="shop.php" class="btn btn-secondary">
                    🛍️ Продолжить покупки
                </a>
            </div>
        </div>
        
        <div class="cart-items">
    `;
    
    data.items.forEach(item => {
        const isLowStock = item.stock < 5 && item.stock > 0;
        const stockMessage = item.stock > 0 
            ? `✓ В наличии ${isLowStock ? '<span style="color: #ffa502;">(осталось мало!)</span>' : ''}`
            : '✗ Нет в наличии';
        
        html += `
            <div class="cart-item" id="cart-item-${item.cart_id}">
                <button class="remove-btn" onclick="removeItem(${item.cart_id})" title="Удалить">
                    ✕
                </button>
                
                <div class="cart-item-image">
                    ${item.image_url 
                        ? `<img src="${item.image_url}" alt="${item.name}" loading="lazy">`
                        : '<div class="no-image">🍃</div>'
                    }
                </div>
                
                <div class="cart-item-info">
                    <h3 class="cart-item-name">${escapeHtml(item.name)}</h3>
                    
                    <div class="cart-item-price">${item.price} руб. / шт.</div>
                    <div class="cart-item-weight">Вес: ${item.weight} г</div>
                    
                    <div class="cart-item-stock ${item.stock > 0 ? 'in-stock' : 'out-of-stock'}">
                        ${stockMessage}
                    </div>
                    
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateQuantity(${item.cart_id}, ${item.quantity - 1})">−</button>
                        <input type="number" 
                               id="quantity-${item.cart_id}"
                               class="quantity-input" 
                               value="${item.quantity}"
                               min="1"
                               max="${item.max_quantity}"
                               onchange="updateQuantity(${item.cart_id}, this.value)">
                        <button class="quantity-btn" onclick="updateQuantity(${item.cart_id}, ${item.quantity + 1})">+</button>
                        
                        <button onclick="updateQuantity(${item.cart_id}, document.getElementById('quantity-${item.cart_id}').value)" 
                                class="btn btn-small" style="margin-left: 10px;">
                            Обновить
                        </button>
                    </div>
                </div>
                
                <div style="text-align: right; flex: 0 0 200px;">
                    <div class="cart-item-total">${(item.price * item.quantity).toFixed(2)} руб.</div>
                    <div class="cart-item-actions">
                        <a href="item.php?id=${item.product_id}" class="btn btn-small btn-primary">
                            📖 Подробнее
                        </a>
                        <button onclick="removeItem(${item.cart_id})" class="btn btn-small btn-danger">
                            🗑️ Удалить
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += `
        </div>
        
        <div class="cart-total">
            <div class="total-label">Итого к оплате:</div>
            <div class="total-price">${data.formatted_total}</div>
            <button class="checkout-btn" onclick="window.location.href='checkout.php'">
                💳 Перейти к оформлению
            </button>
        </div>
    `;
    
    document.getElementById('cart-container').innerHTML = html;
}

// Функция обновления количества
function updateQuantity(cartId, quantity) {
    quantity = parseInt(quantity);
    if (isNaN(quantity)) return;
    
    // Находим элемент для анимации
    const itemElement = document.getElementById(`cart-item-${cartId}`);
    if (itemElement) {
        itemElement.style.opacity = '0.7';
        itemElement.style.pointerEvents = 'none';
    }
    
    // Отправляем запрос
    const formData = new FormData();
    formData.append('action', 'update_quantity');
    formData.append('cart_id', cartId);
    formData.append('quantity', quantity);
    
    fetch('update_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            
            // Если товар удален (количество = 0)
            if (data.removed) {
                if (itemElement) {
                    itemElement.style.transition = 'all 0.5s';
                    itemElement.style.transform = 'translateX(100%)';
                    itemElement.style.opacity = '0';
                    
                    setTimeout(() => {
                        loadCart(); // Перезагружаем корзину
                    }, 500);
                } else {
                    loadCart();
                }
            } else {
                // Просто обновляем корзину
                loadCart();
            }
        } else {
            showNotification(data.message, 'error');
            if (itemElement) {
                itemElement.style.opacity = '1';
                itemElement.style.pointerEvents = 'auto';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка соединения', 'error');
        if (itemElement) {
            itemElement.style.opacity = '1';
            itemElement.style.pointerEvents = 'auto';
        }
    });
}

// Функция удаления товара
function removeItem(cartId) {
    if (!confirm('Удалить товар из корзины?')) return;
    
    const itemElement = document.getElementById(`cart-item-${cartId}`);
    if (itemElement) {
        itemElement.style.transition = 'all 0.5s';
        itemElement.style.transform = 'translateX(100%)';
        itemElement.style.opacity = '0';
    }
    
    const formData = new FormData();
    formData.append('action', 'remove_item');
    formData.append('cart_id', cartId);
    
    fetch('update_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => {
                loadCart(); // Перезагружаем после анимации
            }, 500);
        } else {
            showNotification(data.message, 'error');
            if (itemElement) {
                itemElement.style.transform = '';
                itemElement.style.opacity = '1';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка соединения', 'error');
    });
}

// Функция очистки корзины
function clearCart() {
    if (!confirm('Очистить всю корзину?')) return;
    
    const formData = new FormData();
    formData.append('action', 'clear_cart');
    
    fetch('update_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            // Анимация очистки
            const items = document.querySelectorAll('.cart-item');
            items.forEach((item, index) => {
                item.style.transitionDelay = `${index * 0.1}s`;
                item.style.transform = 'translateX(100%)';
                item.style.opacity = '0';
            });
            
            setTimeout(() => {
                loadCart(); // Перезагружаем корзину
            }, 500 + (items.length * 100));
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка соединения', 'error');
    });
}

// Функция обновления счетчика в шапке
function updateCartCount(count) {
    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(element => {
        if (count > 0) {
            element.textContent = count;
            element.style.display = 'flex';
        } else {
            element.style.display = 'none';
        }
    });
}

// Вспомогательная функция экранирования HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Загружаем корзину при загрузке страницы
document.addEventListener('DOMContentLoaded', loadCart);

// Обновляем корзину каждые 30 секунд
setInterval(loadCart, 30000);
</script>

<?php include 'footer.php'; ?>