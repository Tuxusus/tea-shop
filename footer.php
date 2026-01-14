[file name]: footer.php
[file content begin]
    </main>
    
    <footer class="site-footer">
        <div class="footer-container">
            <p style="font-size: 18px; margin-bottom: 15px; color: #fff;">Чайная Гора</p>
            <p style="color: #aaa; max-width: 600px; margin: 0 auto 20px; line-height: 1.6;">
                Магазин элитного китайского чая. Только натуральные продукты из экологически чистых регионов.
            </p>
            
            <div class="footer-links">
                <a href="index.php">Главная</a>
                <a href="shop.php">Каталог</a>
                <a href="contact.php">Контакты</a>
                <a href="auth.php">Вход</a>
                <a href="register.php">Регистрация</a>
            </div>
            
            <div style="margin: 25px 0; color: #888; font-size: 14px;">
                <p>Телефон: +7 (999) 123-45-67</p>
                <p>Email: info@tea-mountain.ru</p>
                <p>Адрес: Москва, ул. Чайная, 15</p>
            </div>
            
            <div class="copyright">
                <p>© <?php echo date('Y'); ?> Чайная Гора. Все права защищены.</p>
                <p style="margin-top: 5px;">Этот сайт создан для учебных целей</p>
            </div>
        </div>
    </footer>
    
    <div id="notification" class="notification"></div>
    
    <script>
    // Функция для показа уведомлений
    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.className = 'notification';
        
        if (type === 'success') {
            notification.style.background = '#2d5016';
        } else if (type === 'error') {
            notification.style.background = '#d9534f';
        } else if (type === 'warning') {
            notification.style.background = '#f0ad4e';
        }
        
        notification.classList.add('show');
        
        setTimeout(() => {
            notification.classList.remove('show');
        }, 4000);
    }
    
    // Функция обновления счетчика корзины
    function updateCartCount() {
        fetch('get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                const cartCountElements = document.querySelectorAll('.cart-count');
                cartCountElements.forEach(element => {
                    if (data.count > 0) {
                        element.textContent = data.count;
                        element.style.display = 'flex';
                    } else {
                        element.style.display = 'none';
                    }
                });
            })
            .catch(error => console.error('Error updating cart count:', error));
    }
    
    // ГЛОБАЛЬНАЯ функция добавления в корзину (доступна везде)
    window.addToCart = function(productId, button = null) {
        console.log('Добавление товара ID:', productId);
        
        // Сохраняем исходное состояние кнопки
        let originalHTML = '';
        let originalBackground = '';
        let originalColor = '';
        
        if (button) {
            originalHTML = button.innerHTML;
            originalBackground = button.style.background;
            originalColor = button.style.color;
            button.innerHTML = '...';
            button.disabled = true;
        }
        
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
                console.log('Данные ответа:', data);
                
                if (data && data.success === true) {
                    // УСПЕХ
                    showNotification(data.message || 'Товар добавлен в корзину', 'success');
                    updateCartCount();
                    
                    if (button) {
                        // Меняем кнопку на успешную
                        button.innerHTML = '✓';
                        button.style.background = '#2d5016';
                        button.style.color = 'white';
                        
                        // Через 1.5 секунды возвращаем исходное состояние
                        setTimeout(() => {
                            button.innerHTML = originalHTML;
                            button.style.background = originalBackground;
                            button.style.color = originalColor;
                            button.disabled = false;
                        }, 1500);
                    }
                } else {
                    // ОШИБКА от сервера
                    showNotification(data.message || 'Ошибка при добавлении в корзину', 'error');
                    
                    if (button) {
                        button.innerHTML = originalHTML;
                        button.style.background = originalBackground;
                        button.style.color = originalColor;
                        button.disabled = false;
                    }
                }
            })
            .catch(error => {
                console.error('Ошибка запроса:', error);
                
                // Проверяем, это ошибка авторизации или сетевые проблемы
                if (error.message !== 'Not authorized') {
                    showNotification('Ошибка соединения с сервером', 'error');
                }
                
                if (button) {
                    button.innerHTML = originalHTML;
                    button.style.background = originalBackground;
                    button.style.color = originalColor;
                    button.disabled = false;
                }
            });
    };
    
    // Делегирование событий для кнопок корзины в динамически загруженном контенте
    document.addEventListener('click', function(event) {
        // Проверяем, нажата ли кнопка корзины
        if (event.target.classList.contains('btn-cart') && 
            !event.target.classList.contains('btn-cart[href]') && 
            !event.target.disabled) {
            
            // Ищем ближайший data-атрибут с ID товара
            let productId = event.target.getAttribute('data-product-id');
            
            // Если нет data-атрибута, ищем в onclick
            if (!productId && event.target.hasAttribute('onclick')) {
                const onclick = event.target.getAttribute('onclick');
                const match = onclick.match(/addToCart\((\d+)/);
                if (match) {
                    productId = match[1];
                }
            }
            
            // Если нашли ID, вызываем addToCart
            if (productId) {
                event.preventDefault();
                event.stopPropagation();
                window.addToCart(productId, event.target);
            }
        }
    });
    
    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        // Добавляем data-атрибуты к существующим кнопкам
        const cartButtons = document.querySelectorAll('button[onclick*="addToCart"]:not([data-product-id])');
        cartButtons.forEach(button => {
            const onclick = button.getAttribute('onclick');
            const match = onclick.match(/addToCart\((\d+)/);
            if (match) {
                button.setAttribute('data-product-id', match[1]);
            }
        });
    });
    
    // Анимация для кнопок
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .cart-loading {
            animation: spin 1s linear infinite;
        }
        
        .cart-success {
            background: #2d5016 !important;
            color: white !important;
            animation: pulse 0.5s;
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>
[file content end]