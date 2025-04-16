<?php
session_start();
require_once "db-connect.php";

// Получаем ID профессии из URL
$profession_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получаем информацию о профессии
$profession_query = "SELECT * FROM professions WHERE id = ?";
$profession_stmt = $conn->prepare($profession_query);
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
$rating_stmt = $conn->prepare($rating_query);
$rating_stmt->bind_param("i", $profession_id);
$rating_stmt->execute();
$rating_result = $rating_stmt->get_result();
$rating_data = $rating_result->fetch_assoc();

// Получаем последние отзывы
$reviews_query = "SELECT r.*, u.username FROM ratings r 
                  JOIN users u ON r.user_id = u.id 
                  WHERE r.profession_id = ? 
                  ORDER BY r.created_at DESC LIMIT 5";
$reviews_stmt = $conn->prepare($reviews_query);
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

        .main-content {
            margin-top: 6rem;
            padding: 2rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .profession-header {
            background: rgba(0, 0, 0, 0.7);
            padding: 2rem;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            margin-bottom: 2rem;
        }

        .profession-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: normal;
        }

        .rating {
            color: #ffc107;
            margin-bottom: 1rem;
        }

        .rating .far {
            color: #4a4a4a;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .card {
            background-color: rgba(42, 42, 42, 0.8);
            border-radius: 10px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            margin-bottom: 2rem;
        }

        .card-title {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: white;
            font-weight: normal;
        }

        .card-text {
            color: #cccccc;
            line-height: 1.6;
            margin-bottom: 0;
        }

        .btn-rate {
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
        }

        .btn-rate:hover {
            background-color: #007bff;
            color: white;
        }

        .review {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .review:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .review-date {
            color: #666;
            font-size: 0.9rem;
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="background"></div>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">
            <img src="images/logotip.jpg" alt="ProfHub">
        </a>
        <ul class="navbar-nav">
            <li><a href="professions.php" class="nav-link">Профессии</a></li>
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

    <div class="main-content">
        <div class="profession-header">
            <h1><?php echo htmlspecialchars($profession['name']); ?></h1>
            <div class="rating">
                <?php
                $rating = round($rating_data['avg_rating'] ?? 0);
                for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fa<?php echo $i <= $rating ? 's' : 'r'; ?> fa-star"></i>
                <?php endfor; ?>
                <span style="color: #cccccc; margin-left: 0.5rem;">(<?php echo $rating_data['rating_count']; ?> оценок)</span>
            </div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="rate_profession.php?id=<?php echo $profession_id; ?>" class="btn-rate">
                    <i class="fas fa-star"></i> Оценить профессию
                </a>
            <?php endif; ?>
        </div>

        <div class="content-grid">
            <div class="main-info">
                <div class="card">
                    <h2 class="card-title">Описание профессии</h2>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($profession['description'])); ?></p>
                </div>

                <div class="card">
                    <h2 class="card-title">Требования к специалисту</h2>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($profession['requirements'])); ?></p>
                </div>

                <div class="card">
                    <h2 class="card-title">Перспективы развития</h2>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($profession['prospects'])); ?></p>
                </div>
            </div>

            <div class="side-info">
                <div class="card">
                    <h2 class="card-title">Средняя зарплата</h2>
                    <p class="card-text"><?php echo number_format($profession['avg_salary'], 0, ',', ' '); ?> ₽</p>
                </div>

                <div class="card">
                    <h2 class="card-title">Последние отзывы</h2>
                    <?php if ($reviews_result->num_rows > 0): ?>
                        <?php while ($review = $reviews_result->fetch_assoc()): ?>
                            <div class="review">
                                <div class="review-header">
                                    <strong><?php echo htmlspecialchars($review['username']); ?></strong>
                                    <span class="review-date">
                                        <?php echo date('d.m.Y', strtotime($review['created_at'])); ?>
                                    </span>
                                </div>
                                <div class="rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa<?php echo $i <= $review['rating'] ? 's' : 'r'; ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="card-text"><?php echo htmlspecialchars($review['comment']); ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="card-text">Пока нет отзывов</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
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