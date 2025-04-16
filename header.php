<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "db-connect.php";

// Принудительно проверяем роль из базы данных
if (isset($_SESSION['user_id'])) {
    $check_role_stmt = $conn->prepare("SELECT u.id, u.username, u.role FROM users u WHERE u.id = ?");
    $check_role_stmt->bind_param("i", $_SESSION['user_id']);
    $check_role_stmt->execute();
    $check_role_result = $check_role_stmt->get_result();
    if ($check_role_row = $check_role_result->fetch_assoc()) {
        $_SESSION['role'] = $check_role_row['role'];
    }
    $check_role_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/header.css">
</head>
<body>
    <div class="video-background">
        <video autoplay muted loop>
            <source src="images/fontop.mp4" type="video/mp4">
        </video>
        <div class="overlay"></div>
    </div>

    <header>
        <div class="header-container">
            <nav>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="nav-group">
                        <a href="view_test_results.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'view_test_results.php') ? 'active' : ''; ?>">
                            <i class="fas fa-chart-bar"></i> Результаты тестов
                        </a>
                        <a href="tests.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'tests.php') ? 'active' : ''; ?>">
                            <i class="fas fa-tasks"></i> Тесты
                        </a>
                        <a href="professions.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'professions.php') ? 'active' : ''; ?>">
                            <i class="fas fa-briefcase"></i> Профессии
                        </a>
                    </div>
                    <div class="nav-group">
                        <a href="profile.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>">
                            <i class="fas fa-user"></i> Личный кабинет
                        </a>
                        <a href="chat.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'chat.php') ? 'active' : ''; ?>">
                            <i class="fas fa-comments"></i> Чат
                        </a>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="admin.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin.php') ? 'active' : ''; ?>">
                                <i class="fas fa-cog"></i> Панель управления
                            </a>
                        <?php endif; ?>
                        <a href="logout.php" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Выйти
                        </a>
                    </div>
                <?php else: ?>
                    <div class="nav-group auth-buttons">
                        <a href="login.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'login.php') ? 'active' : ''; ?>">
                            <i class="fas fa-sign-in-alt"></i> Войти
                        </a>
                        <a href="register.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'register.php') ? 'active' : ''; ?>">
                            <i class="fas fa-user-plus"></i> Регистрация
                        </a>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <?php if (isset($_SESSION['user_id'])): ?>
    <script>
    function updateUnreadMessages() {
        fetch('get_unread_messages.php')
            .then(response => response.json())
            .then(data => {
                const chatLink = document.querySelector('a[href="chat.php"]');
                if (chatLink) {
                    const existingBadge = chatLink.querySelector('.message-badge');
                    
                    if (data.unread > 0) {
                        if (existingBadge) {
                            existingBadge.textContent = data.unread;
                        } else {
                            const badge = document.createElement('span');
                            badge.className = 'message-badge';
                            badge.textContent = data.unread;
                            chatLink.appendChild(badge);
                        }
                    } else if (existingBadge) {
                        existingBadge.remove();
                    }
                }
            })
            .catch(error => console.error('Ошибка при получении непрочитанных сообщений:', error));
    }

    setInterval(updateUnreadMessages, 30000);
    updateUnreadMessages();
    </script>
    <?php endif; ?>
</body>
</html> 