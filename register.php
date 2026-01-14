<?php
// register.php - СУПЕР ПРОСТОЙ ВАРИАНТ

// ВКЛЮЧАЕМ ОШИБКИ
error_reporting(E_ALL);
ini_set('display_errors', 1);

// СТАРТУЕМ СЕССИЮ
session_start();

// ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'tea_shop_db';

// Подключаемся к MySQL
$conn = new mysqli($host, $user, $password, $database);

// Проверяем подключение
if ($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}

// Устанавливаем кодировку
$conn->set_charset("utf8mb4");

// ЕСЛИ УЖЕ ВОШЛИ - ПЕРЕХОДИМ НА ГЛАВНУЮ
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// ПЕРЕМЕННЫЕ
$error_message = '';
$success_message = '';

// ЕСЛИ ФОРМА ОТПРАВЛЕНА
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Берем данные из формы
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // ПРОСТАЯ ПРОВЕРКА
    if (empty($username) || empty($email) || empty($password)) {
        $error_message = 'Заполните все поля!';
    } 
    elseif ($password !== $confirm_password) {
        $error_message = 'Пароли не совпадают!';
    }
    elseif (strlen($password) < 6) {
        $error_message = 'Пароль должен быть не менее 6 символов!';
    }
    else {
        // Проверяем, нет ли уже такого пользователя
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error_message = 'Пользователь с таким логином или email уже существует!';
            $check_stmt->close();
        } else {
            $check_stmt->close();
            
            // Хешируем пароль
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // СОЗДАЕМ ПОЛЬЗОВАТЕЛЯ
            $insert_sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($insert_stmt->execute()) {
                // УСПЕХ! ПОЛУЧАЕМ ID НОВОГО ПОЛЬЗОВАТЕЛЯ
                $new_user_id = $insert_stmt->insert_id;
                $insert_stmt->close();
                
                // СОХРАНЯЕМ В СЕССИЮ
                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                
                // ЗАКРЫВАЕМ СОЕДИНЕНИЕ
                $conn->close();
                
                // НЕМЕДЛЕННЫЙ ПЕРЕХОД НА ГЛАВНУЮ
                header("Location: index.php");
                exit(); // ВАЖНО: ВЫХОДИМ ИЗ СКРИПТА
                
            } else {
                $error_message = 'Ошибка при регистрации: ' . $insert_stmt->error;
                $insert_stmt->close();
            }
        }
    }
}

// ЕСЛИ МЫ ЗДЕСЬ, ЗНАЧИТ БЫЛА ОШИБКА ИЛИ ЭТО ПЕРВАЯ ЗАГРУЗКА
// Закрываем соединение если еще не закрыли
if (isset($conn) && $conn) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .register-box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        
        h1 {
            color: #2d5016;
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
        }
        
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #ffcdd2;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }
        
        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        input:focus {
            border-color: #2d5016;
            outline: none;
        }
        
        button {
            width: 100%;
            padding: 14px;
            background: #2d5016;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        
        button:hover {
            background: #1a3310;
        }
        
        .links {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .links a {
            color: #2d5016;
            text-decoration: none;
            font-weight: bold;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
        .success-note {
            text-align: center;
            color: #2d5016;
            font-style: italic;
            margin-top: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="register-box">
        <h1>Создание аккаунта</h1>
        
        <?php if ($error_message): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Логин:</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       placeholder="Введите имя пользователя">
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       placeholder="example@mail.ru">
            </div>
            
            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Не менее 6 символов">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Повторите пароль:</label>
                <input type="password" id="confirm_password" name="confirm_password" required 
                       placeholder="Повторите пароль">
            </div>
            
            <button type="submit">Создать аккаунт</button>
        </form>
        
        <div class="success-note">
            После регистрации вы будете автоматически авторизованы
        </div>
        
        <div class="links">
            <p>Уже есть аккаунт? <a href="auth.php">Войти</a></p>
            <p style="margin-top: 10px;"><a href="index.php">← На главную</a></p>
        </div>
    </div>
    
    <script>
    // Простая проверка совпадения паролей
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function checkPasswords() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.style.borderColor = '#ff4757';
            return false;
        } else {
            confirmPassword.style.borderColor = '#2d5016';
            return true;
        }
    }
    
    confirmPassword.addEventListener('input', checkPasswords);
    password.addEventListener('input', checkPasswords);
    
   
    document.querySelector('form').addEventListener('submit', function(e) {
        if (!checkPasswords()) {
            e.preventDefault();
            alert('Пароли не совпадают!');
            return false;
        }
        
        if (password.value.length < 6) {
            e.preventDefault();
            alert('Пароль должен быть не менее 6 символов!');
            return false;
        }
        
        return true;
    });
    </script>
</body>
</html>