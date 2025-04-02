<?php
session_start();
require_once "db-connect.php";

// Получаем ID профессии из URL
$profession_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получаем информацию о профессии
$profession_query = "SELECT * FROM professions WHERE id = ?";
$profession_stmt = $mysqli->prepare($profession_query);
$profession_stmt->bind_param("i", $profession_id);
$profession_stmt->execute();
$profession_result = $profession_stmt->get_result();

if ($profession_result->num_rows === 0) {
    header("Location: professions.php");
    exit();
}

$profession = $profession_result->fetch_assoc();

// Получаем средний рейтинг профессии
$rating_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as rating_count FROM ratings WHERE profession_id = ?";
$rating_stmt = $mysqli->prepare($rating_query);
$rating_stmt->bind_param("i", $profession_id);
$rating_stmt->execute();
$rating_result = $rating_stmt->get_result();
$rating_data = $rating_result->fetch_assoc();

// Получаем последние отзывы
$reviews_query = "SELECT r.*, u.username FROM ratings r 
                  JOIN users u ON r.user_id = u.id 
                  WHERE r.profession_id = ? 
                  ORDER BY r.date_rated DESC LIMIT 5";
$reviews_stmt = $mysqli->prepare($reviews_query);
$reviews_stmt->bind_param("i", $profession_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profession['name']); ?> - ProfHub</title>
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
                <h1><?php echo htmlspecialchars($profession['name']); ?></h1>
                <div class="rating mb-3">
                    <?php
                    $rating = round($rating_data['avg_rating'] ?? 0);
                    for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star <?php echo $i <= $rating ? 'text-warning' : 'text-muted'; ?>"></i>
                    <?php endfor; ?>
                    <span class="ms-2">(<?php echo $rating_data['rating_count']; ?> оценок)</span>
                </div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="rate_profession.php?id=<?php echo $profession_id; ?>" class="btn btn-outline">
                        <i class="fas fa-star"></i> Оценить профессию
                    </a>
                <?php endif; ?>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card profession-card fade-in">
                        <div class="card-body">
                            <h5 class="card-title">Описание профессии</h5>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($profession['description'])); ?></p>
                        </div>
                    </div>

                    <div class="card profession-card fade-in mt-4">
                        <div class="card-body">
                            <h5 class="card-title">Требования к специалисту</h5>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($profession['requirements'])); ?></p>
                        </div>
                    </div>

                    <div class="card profession-card fade-in mt-4">
                        <div class="card-body">
                            <h5 class="card-title">Перспективы развития</h5>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($profession['prospects'])); ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card profession-card fade-in">
                        <div class="card-body">
                            <h5 class="card-title">Средняя зарплата</h5>
                            <p class="card-text"><?php echo number_format($profession['avg_salary'], 0, ',', ' '); ?> ₽</p>
                        </div>
                    </div>

                    <div class="card profession-card fade-in mt-4">
                        <div class="card-body">
                            <h5 class="card-title">Последние отзывы</h5>
                            <?php if ($reviews_result->num_rows > 0): ?>
                                <?php while ($review = $reviews_result->fetch_assoc()): ?>
                                    <div class="review mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong><?php echo htmlspecialchars($review['username']); ?></strong>
                                            <small class="text-muted">
                                                <?php echo date('d.m.Y', strtotime($review['date_rated'])); ?>
                                            </small>
                                        </div>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="mb-0"><?php echo htmlspecialchars($review['comment']); ?></p>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-muted">Пока нет отзывов</p>
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