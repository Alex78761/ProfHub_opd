<?php
session_start();
require_once "db-connect.php";

// Получаем список всех профессий
$professions_query = "SELECT * FROM professions ORDER BY name ASC";
$professions_result = mysqli_query($mysqli, $professions_query);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профессии - ProfHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light">
                <a class="navbar-brand" href="index.php">
                    <img src="images/logo.png" alt="ProfHub" height="40">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="professions.php">Профессии</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="tests.php">Тесты</a>
                        </li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="profile.php">Личный кабинет</a>
                            </li>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="admin.php">Панель управления</a>
                                </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a class="nav-link" href="logout.php">Выйти</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="login.php">Войти</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="register.php">Регистрация</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="jumbotron fade-in">
                <h1>Каталог IT-профессий</h1>
                <p>Исследуйте различные профессии в сфере информационных технологий и найдите свой путь в IT.</p>
            </div>

            <div class="row">
                <?php while ($profession = mysqli_fetch_assoc($professions_result)): ?>
                    <div class="col-md-4">
                        <div class="card profession-card fade-in">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-briefcase"></i>
                                    <?php echo htmlspecialchars($profession['name']); ?>
                                </h5>
                                <p class="card-text">
                                    <?php echo htmlspecialchars(substr($profession['description'], 0, 150)) . '...'; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="profession.php?id=<?php echo $profession['id']; ?>" class="btn btn-outline">
                                        Подробнее
                                    </a>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <a href="rate_profession.php?id=<?php echo $profession['id']; ?>" class="btn btn-outline">
                                            <i class="fas fa-star"></i> Оценить
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="images/logo.png" alt="ProfHub" height="30">
                </div>
                <div class="footer-links">
                    <a href="about.php">О нас</a>
                    <a href="contact.php">Контакты</a>
                    <a href="privacy.php">Конфиденциальность</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
