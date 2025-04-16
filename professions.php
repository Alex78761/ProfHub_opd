<?php
session_start();
require_once "db-connect.php";

// Получаем список всех профессий
$professions_query = "SELECT * FROM professions ORDER BY name ASC";
$professions_result = $conn->query($professions_query);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профессии - ProfHub</title>
    <link href="css/header.css" rel="stylesheet">
    <link href="css/background.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #1a1a1a;
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background-color: transparent;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .navbar-brand {
            width: 40px;
            height: 40px;
        }

        .navbar-brand img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .navbar-nav {
            display: flex;
            gap: 2rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        .nav-link.active {
            color: #007bff;
        }

        .hero-section {
            background: rgba(0, 0, 0, 0.7);
            padding: 2rem;
            text-align: center;
            border-radius: 10px;
            margin: 6rem auto 2rem;
            max-width: 1200px;
            backdrop-filter: blur(10px);
        }

        .hero-section h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: normal;
        }

        .hero-section p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
        }

        .professions-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .profession-card {
            background-color: rgba(42, 42, 42, 0.8);
            border-radius: 10px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            display: flex;
            flex-direction: column;
        }

        .profession-icon {
            width: 24px;
            height: 24px;
            margin-bottom: 1rem;
            filter: invert(1);
        }

        .profession-title {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: white;
            font-weight: normal;
        }

        .profession-description {
            color: #cccccc;
            margin-bottom: 1.5rem;
            line-height: 1.5;
            flex-grow: 1;
        }

        .btn-more {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: transparent;
            border: 1px solid #007bff;
            color: #007bff;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 0.9rem;
            text-align: center;
        }

        .btn-more:hover {
            background-color: #007bff;
            color: white;
        }

        footer {
            background-color: transparent;
            padding: 2rem;
            margin-top: auto;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-logo {
            width: 40px;
            height: 40px;
        }

        .footer-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .footer-links a {
            color: #cccccc;
            text-decoration: none;
            margin-left: 2rem;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: white;
        }

        .background {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -1;
            background: linear-gradient(45deg, #1a1a1a, #2a2a2a);
        }
    </style>
</head>
<body>
    <div class="background"></div>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">
            <img src="images/logotip.jpg" alt="ProfHub">
        </a>
        <ul class="navbar-nav">
            <li><a href="professions.php" class="nav-link active">Профессии</a></li>
            <li><a href="tests.php" class="nav-link">Тесты</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="account.php" class="nav-link">Личный кабинет</a></li>
                <li><a href="logout.php" class="nav-link">Выйти</a></li>
            <?php else: ?>
                <li><a href="login.php" class="nav-link">Войти</a></li>
                <li><a href="register.php" class="nav-link">Регистрация</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="hero-section">
        <h1>Каталог IT-профессий</h1>
        <p>Исследуйте различные профессии в сфере информационных технологий и найдите свой путь в IT.</p>
    </div>

    <div class="professions-grid">
        <?php while ($profession = $professions_result->fetch_assoc()): ?>
            <div class="profession-card">
                <img src="images/document-icon.svg" alt="" class="profession-icon">
                <h2 class="profession-title"><?php echo htmlspecialchars($profession['name']); ?></h2>
                <p class="profession-description">
                    <?php echo htmlspecialchars(substr($profession['description'], 0, 150)) . '...'; ?>
                </p>
                <a href="profession.php?id=<?php echo $profession['id']; ?>" class="btn-more">Подробнее</a>
            </div>
        <?php endwhile; ?>
    </div>

    <footer>
        <div class="footer-content">
            <a href="index.php" class="footer-logo">
                <img src="images/logotip.jpg" alt="ProfHub">
            </a>
            <div class="footer-links">
                <a href="about.php">О нас</a>
                <a href="contact.php">Контакты</a>
                <a href="privacy.php">Конфиденциальность</a>
            </div>
        </div>
    </footer>
</body>
</html>
