<?php
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'Вход';


if (isset($_GET['logout'])) {
    
    $_SESSION = array();
    
   
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    
    session_destroy();
    
   
    header('Location: index.php');
    exit();
}


if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Заполните все поля';
    } else {
        // Ищем пользователя
        $user = db_fetch_one("SELECT * FROM users WHERE username = ?", [$username]);
        
        if ($user) {
            // Проверяем пароль
            if (password_verify($password, $user['password']) || $user['password'] === $password) {
                // Успешный вход
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                // Если пароль был в открытом виде - хешируем его
                if (!password_needs_rehash($user['password'], PASSWORD_DEFAULT) && $user['password'] !== $password) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    db_query("UPDATE users SET password = ? WHERE id = ?", [$hashed, $user['id']]);
                }
                
                header('Location: index.php');
                exit();
            } else {
                $error = 'Неверный пароль';
            }
        } else {
            $error = 'Пользователь не найден';
        }
    }
}

include 'header.php';
?>

<div class="container" style="max-width: 400px; margin: 50px auto;">
    <h2>Вход в систему</h2>
    
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" class="auth-form">
        <div class="form-group">
            <label>Логин:</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label>Пароль:</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">Войти</button>
    </form>
    
    <div class="test-accounts" style="margin-top: 30px; padding: 15px; background: #f5f5f5; border-radius: 5px;">
        <h4>Тестовые аккаунты:</h4>
        <ul>
            <li>Логин: <strong>user1</strong> | Пароль: <strong>password123</strong></li>
            <li>Логин: <strong>tea_lover</strong> | Пароль: <strong>ilovetea</strong></li>
            <li>Логин: <strong>Qwerty</strong> | Пароль: <strong>123456</strong></li>
            <li>Логин: <strong>adm</strong> | Пароль: <strong>admin123</strong></li>
        </ul>
    </div>
    
    <div class="auth-links" style="text-align: center; margin-top: 20px;">
        <p>Нет аккаунта? <a href="register.php">Зарегистрируйтесь</a></p>
    </div>
</div>

<style>
.alert-danger {
    background: #f8d7da;
    color: #721c24;
    padding: 12px;
    border-radius: 5px;
    margin-bottom: 20px;
    border: 1px solid #f5c6cb;
}
</style>

<?php include 'footer.php'; ?>