<?php
session_start();
require_once "db-connect.php";

// Получаем последние добавленные профессии
$latest_professions_query = "SELECT * FROM professions ORDER BY id DESC LIMIT 3";
$latest_professions_result = mysqli_query($mysqli, $latest_professions_query);

// Получаем статистику
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM professions) as total_professions,
    (SELECT COUNT(*) FROM tests) as total_tests,
    (SELECT COUNT(*) FROM users) as total_users";
$stats_result = mysqli_query($mysqli, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProfHub - Портал IT-профессий</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
</head>
<body>
    <div class="video-background">
        <video autoplay muted loop id="myVideo">
            <source src="images/fontop.mp4" type="video/mp4">
        </video>
    </div>
    
    <header class="header">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light">
                <a class="navbar-brand" href="index.php">
                    <img src="images/logotip.jpg" alt="ProfHub" height="40">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="professions.php">Профессии</a>
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
                <h1>Добро пожаловать в ProfHub</h1>
                <p>Ваш путеводитель в мире IT-профессий. Исследуйте, тестируйте и развивайтесь вместе с нами.</p>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="register.php" class="btn btn-primary">Начать путешествие</a>
                <?php endif; ?>
            </div>

            <div class="row mb-5">
                <div class="col-md-4">
                    <div class="card profession-card fade-in">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-briefcase"></i> Каталог профессий
                            </h5>
                            <p class="card-text">Исследуйте различные IT-профессии и узнайте, какая из них подходит именно вам.</p>
                            <a href="professions.php" class="btn btn-outline">Подробнее</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card profession-card fade-in">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-tasks"></i> Профессиональные тесты
                            </h5>
                            <p class="card-text">Пройдите тесты, чтобы оценить свои навыки и определить уровень подготовки.</p>
                            <a href="tests.php" class="btn btn-outline">Подробнее</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card profession-card fade-in">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-star"></i> Экспертные оценки
                            </h5>
                            <p class="card-text">Получите оценку от профессионалов и рекомендации по развитию.</p>
                            <a href="ratings.php" class="btn btn-outline">Подробнее</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-md-6">
                    <div class="card profession-card fade-in">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-info-circle"></i> О ProfHub
                            </h5>
                            <p class="card-text">ProfHub - это инновационная платформа, созданная для помощи в выборе и развитии IT-профессии. Мы предоставляем:</p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i> Подробные описания IT-профессий</li>
                                <li><i class="fas fa-check text-success me-2"></i> Профессиональные тесты для оценки навыков</li>
                                <li><i class="fas fa-check text-success me-2"></i> Экспертные оценки и рекомендации</li>
                                <li><i class="fas fa-check text-success me-2"></i> Сообщество единомышленников</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card profession-card fade-in">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-chart-line"></i> Статистика платформы
                            </h5>
                            <div class="row text-center">
                                <div class="col-4">
                                    <h3 class="text-primary"><?php echo $stats['total_professions']; ?></h3>
                                    <p>Профессий</p>
                                </div>
                                <div class="col-4">
                                    <h3 class="text-primary"><?php echo $stats['total_tests']; ?></h3>
                                    <p>Тестов</p>
                                </div>
                                <div class="col-4">
                                    <h3 class="text-primary"><?php echo $stats['total_users']; ?></h3>
                                    <p>Пользователей</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <section class="mb-5">
                <h2 class="text-center mb-4">Последние добавленные профессии</h2>
                <div class="row">
                    <?php while ($profession = mysqli_fetch_assoc($latest_professions_result)): ?>
                        <div class="col-md-4">
                            <div class="card profession-card fade-in">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($profession['name']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars(substr($profession['description'], 0, 150)) . '...'; ?></p>
                                    <a href="profession.php?id=<?php echo $profession['id']; ?>" class="btn btn-outline">Подробнее</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>

            <div class="row mb-5">
                <div class="col-12">
                    <div class="card profession-card fade-in">
                        <div class="card-body text-center">
                            <h5 class="card-title">
                                <i class="fas fa-rocket"></i> Начните свой путь в IT
                            </h5>
                            <p class="card-text">Присоединяйтесь к нашему сообществу и развивайтесь вместе с нами!</p>
                            <?php if (!isset($_SESSION['user_id'])): ?>
                                <a href="register.php" class="btn btn-primary">Зарегистрироваться</a>
                            <?php else: ?>
                                <a href="professions.php" class="btn btn-primary">Исследовать профессии</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="images/logotip.jpg" alt="ProfHub" height="30">
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
