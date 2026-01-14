<?php
require_once 'config/db.php';

echo "<h1>Создание таблиц базы данных</h1>";

$queries = [
    // Таблица cart если её нет
    "CREATE TABLE IF NOT EXISTS cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT DEFAULT 1,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        INDEX idx_user_id (user_id),
        INDEX idx_product_id (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    
    // Таблица wishlist если её нет
    "CREATE TABLE IF NOT EXISTS wishlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY unique_wishlist (user_id, product_id),
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    
    // Таблица messages если её нет
    "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('new', 'read', 'answered') DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

foreach ($queries as $query) {
    echo "<h3>Выполнение запроса:</h3>";
    echo "<pre>" . htmlspecialchars($query) . "</pre>";
    
    $result = db_query($query);
    
    if ($result) {
        echo "<p style='color: green;'>✅ Успешно</p>";
    } else {
        echo "<p style='color: red;'>❌ Ошибка</p>";
    }
    echo "<hr>";
}

echo "<h2>Проверка существования таблиц:</h2>";

$tables = ['users', 'products', 'categories', 'cart', 'wishlist', 'messages'];

foreach ($tables as $table) {
    $result = db_query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<p>✅ Таблица <strong>$table</strong> существует</p>";
        
       
        $structure = db_query("DESCRIBE $table");
        echo "<table border='1' cellpadding='5' style='margin-bottom: 20px;'>";
        echo "<tr><th colspan='5'>Структура таблицы $table</th></tr>";
        echo "<tr><th>Поле</th><th>Тип</th><th>NULL</th><th>Ключ</th><th>По умолчанию</th></tr>";
        while($row = $structure->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ Таблица <strong>$table</strong> не найдена</p>";
    }
}

echo "<h2>Проверка данных:</h2>";

$users = db_query("SELECT COUNT(*) as count FROM users");
if ($users) {
    $count = $users->fetch_assoc();
    echo "<p>Пользователей: " . $count['count'] . "</p>";
}


$products = db_query("SELECT COUNT(*) as count FROM products");
if ($products) {
    $count = $products->fetch_assoc();
    echo "<p>Товаров: " . $count['count'] . "</p>";
}


$cart = db_query("SELECT COUNT(*) as count FROM cart");
if ($cart) {
    $count = $cart->fetch_assoc();
    echo "<p>Записей в корзине: " . $count['count'] . "</p>";
}

echo "<p><a href='index.php'>Вернуться на сайт</a></p>";
?>