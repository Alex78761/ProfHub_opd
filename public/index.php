<?php
session_start();
require_once '../config/database.php';

// Маршрутизация
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$allowed_pages = ['home', 'professions', 'about', 'register', 'login', 'profile', 'admin', 'expert', 'consultant'];

if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}

// Заголовок страницы
$titles = [
    'home' => 'Главная',
    'professions' => 'ИТ-профессии',
    'about' => 'О портале',
    'register' => 'Регистрация',
    'login' => 'Вход',
    'profile' => 'Личный кабинет',
    'admin' => 'Панель администратора',
    'expert' => 'Панель эксперта',
    'consultant' => 'Панель консультанта'
];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titles[$page]; ?> - Портал ИТ-профессий</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">Портал ИТ-профессий</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Главная</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/?page=professions">ИТ-профессии</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/?page=about">О портале</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/?page=profile">Личный кабинет</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/logout.php">Выход</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/?page=login">Вход</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/?page=register">Регистрация</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-4">
        <?php include "../templates/{$page}.php"; ?>
    </main>

    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Портал ИТ-профессий</h5>
                    <p>Ваш путеводитель в мире информационных технологий</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; <?php echo date('Y'); ?> Все права защищены</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html> 