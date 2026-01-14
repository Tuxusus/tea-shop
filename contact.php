<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once 'config/db.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

$page_title = 'Обратная связь';
$success = '';
$error = '';

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Валидация
    if (empty($subject) || empty($message)) {
        $error = 'Заполните все поля';
    } elseif (strlen($subject) > 255) {
        $error = 'Тема слишком длинная (максимум 255 символов)';
    } else {
        $user_id = (int)$_SESSION['user_id'];
        
        // Проверяем таблицу messages
        $table_check = db_query("SHOW TABLES LIKE 'messages'");
        $table_exists = $table_check && $table_check->num_rows > 0;
        
        if (!$table_exists) {
            // Создаем таблицу если её нет
            $create_sql = "CREATE TABLE IF NOT EXISTS messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                subject VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                status ENUM('new', 'read', 'answered') DEFAULT 'new',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            db_query($create_sql);
        }
        
        // Подготавливаем данные
        $subject_clean = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
        $message_clean = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        
        // Пробуем отправить сообщение
        try {
            // Используем прямое подключение к MySQL для надежности
            global $mysqli;
            
            $stmt = $mysqli->prepare("INSERT INTO messages (user_id, subject, message) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $subject_clean, $message_clean);
            
            if ($stmt->execute()) {
                $success = '✅ Ваше сообщение отправлено! Мы ответим вам в течение 24 часов.';
                // Очищаем форму
                $_POST['subject'] = '';
                $_POST['message'] = '';
            } else {
                $error = '❌ Ошибка при отправке сообщения. Попробуйте позже.';
            }
            $stmt->close();
            
        } catch (Exception $e) {
            $error = '❌ Ошибка: ' . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<div class="main-content">
    <h1>Обратная связь</h1>
    <p>Здесь вы можете задать вопрос или оставить отзыв о нашей работе.</p>
    
    <?php if($success): ?>
        <div style="background: #e6ffe6; color: #006600; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #b3e6b3;">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div style="background: #ffe6e6; color: #cc0000; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #ffb3b3;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <div style="max-width: 600px; margin: 30px 0;">
        <form method="POST" id="contactForm">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #2d5016;">
                    Тема сообщения *
                </label>
                <input type="text" 
                       name="subject" 
                       id="subject"
                       style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-size: 16px;"
                       value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>"
                       required
                       maxlength="255"
                       placeholder="Например: Вопрос о доставке">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #2d5016;">
                    Сообщение *
                </label>
                <textarea name="message" 
                          id="message"
                          style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-size: 16px; height: 200px; resize: vertical;"
                          required
                          placeholder="Опишите ваш вопрос или предложение..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
            </div>
            
            <div style="margin-bottom: 25px; padding: 15px; background: #f9f9f9; border-radius: 5px;">
                <p><strong>Отправитель:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <button type="submit" 
                        style="padding: 12px 30px; background: #2d5016; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px;">
                    Отправить сообщение
                </button>
                <a href="profile.php" 
                   style="padding: 12px 30px; background: #666; color: white; text-decoration: none; border-radius: 5px; display: flex; align-items: center;">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Простая валидация
document.getElementById('contactForm').addEventListener('submit', function(event) {
    const subject = document.getElementById('subject').value.trim();
    const message = document.getElementById('message').value.trim();
    
    if (subject.length < 3) {
        event.preventDefault();
        alert('Тема сообщения должна содержать минимум 3 символа');
        return false;
    }
    
    if (message.length < 10) {
        event.preventDefault();
        alert('Сообщение должно содержать минимум 10 символов');
        return false;
    }
    
    return true;
});
</script>

<?php include 'footer.php'; ?>