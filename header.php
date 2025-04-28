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
    <title>ProfHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/header.css">
    <?php if (basename($_SERVER['PHP_SELF']) === 'index.php'): ?>
    <style>
    .main-page-nav {
        margin-top: 1rem;
        padding: 0.5rem 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 8px;
    }
    .main-page-nav .nav-link {
        color: rgba(255, 255, 255, 0.8);
        padding: 0.5rem 1rem;
        margin: 0 0.25rem;
        border-radius: 5px;
        transition: all 0.3s ease;
    }
    .main-page-nav .nav-link:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.1);
        transform: translateY(-2px);
    }
    .main-page-nav .nav-link i {
        margin-right: 0.5rem;
    }
    </style>
    <?php endif; ?>
</head>
<body class="bg-dark">
    <div class="video-background">
        <video autoplay muted loop playsinline>
            <source src="images/fontop.mp4" type="video/mp4">
        </video>
        <div class="overlay"></div>
    </div>

    <header class="header">
        <nav class="navbar navbar-expand-lg navbar-dark h-100">
            <div class="container h-100">
                <a class="navbar-brand d-flex align-items-center h-100" href="index.php">
                    <i class="fas fa-graduation-cap me-2"></i>
                    <span class="brand-text">ProfHub</span>
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <ul class="navbar-nav me-auto">
                            <!-- Профессии -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="professionsDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-briefcase"></i> Профессии
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark">
                                    <li><a class="dropdown-item" href="professions.php">Все профессии</a></li>
                                    <li><a class="dropdown-item" href="rated_professions.php">Оцененные профессии</a></li>
                                    <li><a class="dropdown-item" href="profession_details.php">Детали профессий</a></li>
                                </ul>
                            </li>

                            <!-- Тесты -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="testsDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-tasks"></i> Тесты
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark">
                                    <li><a class="dropdown-item" href="tests.php">Все тесты</a></li>
                                    <li><a class="dropdown-item" href="view_test_results.php">Результаты тестов</a></li>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                        <li><a class="dropdown-item" href="add_tests.php">Добавить тест</a></li>
                                    <?php endif; ?>
                                </ul>
                            </li>

                            <!-- Обучение -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="learningDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-book"></i> Обучение
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark">
                                    <li><a class="dropdown-item" href="progress.php">Мой прогресс</a></li>
                                    <li><a class="dropdown-item" href="suitability.php">Профпригодность</a></li>
                                    <li><a class="dropdown-item" href="recommendations.php">Рекомендации</a></li>
                                </ul>
                            </li>

                            <!-- Сообщество -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="communityDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-users"></i> Сообщество
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark">
                                    <li><a class="dropdown-item" href="chat.php">Чат</a></li>
                                    <li><a class="dropdown-item" href="experts.php">Эксперты</a></li>
                                    <li><a class="dropdown-item" href="forum.php">Форум</a></li>
                                </ul>
                            </li>
                        </ul>

                        <ul class="navbar-nav align-items-center">
                            <!-- Уведомления -->
                            <li class="nav-item me-2">
                                <a class="nav-link position-relative p-1" href="notifications.php">
                                    <i class="fas fa-bell"></i>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <span id="notifications-count">0</span>
                                    </span>
                                </a>
                            </li>

                            <!-- Профиль -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle p-1" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                                    <li><a class="dropdown-item" href="profile.php">Мой профиль</a></li>
                                    <li><a class="dropdown-item" href="my_results.php">Мои результаты</a></li>
                                    <li><a class="dropdown-item" href="settings.php">Настройки</a></li>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="admin.php">Панель управления</a></li>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'expert'): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="expert_panel.php">Оценка профессий</a></li>
                                        <li><a class="dropdown-item" href="expert_pvk_results.php">Результаты эксперта</a></li>
                                        <li><a class="dropdown-item" href="expert_profile.php">Профиль эксперта</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="logout.php">Выйти</a></li>
                                </ul>
                            </li>
                        </ul>
                    <?php else: ?>
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item">
                                <a class="nav-link btn btn-primary" href="login.php">
                                    <i class="fas fa-sign-in-alt"></i> Войти
                                </a>
                            </li>
                            <li class="nav-item ms-2">
                                <a class="nav-link btn btn-outline-light" href="register.php">
                                    <i class="fas fa-user-plus"></i> Регистрация
                                </a>
                            </li>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <?php if (basename($_SERVER['PHP_SELF']) === 'index.php'): ?>
    <div class="container">
        <nav class="main-page-nav">
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="professions.php" class="nav-link">
                    <i class="fas fa-briefcase"></i>Каталог профессий
                </a>
                <a href="tests.php" class="nav-link">
                    <i class="fas fa-tasks"></i>Тестирование
                </a>
                <a href="experts.php" class="nav-link">
                    <i class="fas fa-user-tie"></i>Консультации
                </a>
                <a href="forum.php" class="nav-link">
                    <i class="fas fa-comments"></i>Форум
                </a>
                <a href="progress.php" class="nav-link">
                    <i class="fas fa-chart-line"></i>Отслеживание прогресса
                </a>
                <a href="recommendations.php" class="nav-link">
                    <i class="fas fa-lightbulb"></i>Рекомендации
                </a>
                <a href="suitability.php" class="nav-link">
                    <i class="fas fa-check-circle"></i>Профпригодность
                </a>
                <a href="chat.php" class="nav-link">
                    <i class="fas fa-comment-dots"></i>Чат с экспертами
                </a>
            </div>
        </nav>
    </div>
    <?php endif; ?>

    <!-- Bootstrap и другие скрипты -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
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