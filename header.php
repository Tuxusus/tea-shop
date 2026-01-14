<?php
// header.php - с вашим логотипом i.png
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

$cart_count = 0;
if ($is_logged_in && $user_id > 0) {
    require_once 'config/db.php';
    $cart_result = db_query("SELECT COUNT(*) as count FROM cart WHERE user_id = ?", [$user_id]);
    if ($cart_result) {
        $cart = $cart_result->fetch_assoc();
        $cart_count = (int)$cart['count'];
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'Чайный магазин'); ?></title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f8f8;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .site-header {
            background: linear-gradient(135deg, #2d5016, #3d7026);
            color: white;
            padding: 10px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 5px 0;
        }
        
        .logo:hover {
            opacity: 0.9;
        }
        
        .logo-image {
            height: 50px;
            width: auto;
            border-radius: 5px;
            transition: transform 0.3s;
            object-fit: contain;
        }
        
        .logo:hover .logo-image {
            transform: scale(1.05);
        }
        
        .logo-text {
            font-size: 22px;
            font-weight: bold;
            color: white;
        }
        
        .nav-menu {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }
        
        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }
        
        .user-info {
            background: #c17a1f;
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
        
        .cart-count {
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 5px;
        }
        
        .main-content {
            flex: 1;
            max-width: 1200px;
            width: 100%;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .site-footer {
            background: #1a3310;
            color: white;
            padding: 30px 0;
            margin-top: 40px;
            text-align: center;
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #c17a1f;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
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
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background: #2d5016;
            color: white;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 1000;
            display: none;
            max-width: 400px;
        }
        
        .notification.show {
            display: block;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 15px;
                padding: 10px;
            }
            
            .logo-image {
                height: 40px;
            }
            
            .logo-text {
                font-size: 18px;
            }
            
            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px;
            }
            
            .nav-link {
                font-size: 13px;
                padding: 6px 12px;
            }
        }
        
        @media (max-width: 480px) {
            .logo {
                gap: 10px;
            }
            
            .logo-image {
                height: 35px;
            }
            
            .logo-text {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="header-container">
            <a href="index.php" class="logo">
                <?php 
                $logo_path = 'uploads/i.png';
                
                if (file_exists($logo_path)): 
                ?>
                    <img src="<?php echo $logo_path; ?>" 
                         alt="Чайная Гора" 
                         class="logo-image"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="logo-fallback" style="
                        display: none;
                        width: 50px;
                        height: 50px;
                        background: #c17a1f;
                        border-radius: 50%;
                        align-items: center;
                        justify-content: center;
                        color: white;
                        font-size: 24px;
                        font-weight: bold;
                    ">
                        ЧГ
                    </div>
                <?php else: ?>
                    <div style="
                        width: 50px;
                        height: 50px;
                        background: #c17a1f;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: white;
                        font-size: 24px;
                        font-weight: bold;
                    ">
                        ЧГ
                    </div>
                <?php endif; ?>
                <span class="logo-text">Чайная Гора</span>
            </a>
            
            <nav class="nav-menu">
                <a href="index.php" class="nav-link">Главная</a>
                <a href="shop.php" class="nav-link">Каталог</a>
                
                <?php if($is_logged_in): ?>
                    <a href="wishlist.php" class="nav-link">Желания</a>
                    <a href="cart.php" class="nav-link">Корзина
                        <?php if($cart_count > 0): ?>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="profile.php" class="nav-link user-info">
                         <?php echo htmlspecialchars($username); ?>
                    </a>
                    <a href="auth.php?logout=1" class="nav-link">Выйти</a>
                <?php else: ?>
                    <a href="auth.php" class="nav-link">Вход</a>
                    <a href="register.php" class="nav-link">Регистрация</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    
    <main class="main-content">
<?php
ob_end_flush();
?>