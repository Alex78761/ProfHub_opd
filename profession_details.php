<?php
session_start();
require_once "db-connect.php";

$profession_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получаем информацию о профессии
$profession_query = "SELECT p.*, 
                    COUNT(r.id) as rating_count,
                    AVG(r.rating) as avg_rating
                    FROM professions p
                    LEFT JOIN ratings r ON p.id = r.profession_id
                    WHERE p.id = ?
                    GROUP BY p.id";
$profession_stmt = $mysqli->prepare($profession_query);
$profession_stmt->bind_param("i", $profession_id);
$profession_stmt->execute();
$profession_result = $profession_stmt->get_result();

if ($profession_result->num_rows === 0) {
    header("Location: professions.php");
    exit();
}

$profession = $profession_result->fetch_assoc();

// Получаем ПВК для профессии
$pvk_query = "SELECT pvk.* FROM pvk 
              JOIN profession_pvk pp ON pvk.id = pp.pvk_id 
              WHERE pp.profession_id = ?";
$pvk_stmt = $mysqli->prepare($pvk_query);
$pvk_stmt->bind_param("i", $profession_id);
$pvk_stmt->execute();
$pvk_result = $pvk_stmt->get_result();

// Получаем последние отзывы
$reviews_query = "SELECT r.*, u.username 
                 FROM ratings r 
                 JOIN users u ON r.user_id = u.id 
                 WHERE r.profession_id = ? 
                 ORDER BY r.date_rated DESC 
                 LIMIT 5";
$reviews_stmt = $mysqli->prepare($reviews_query);
$reviews_stmt->bind_param("i", $profession_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();

// Проверяем, оценивал ли текущий пользователь эту профессию
$user_rating = null;
if (isset($_SESSION['user_id'])) {
    $user_rating_query = "SELECT * FROM ratings WHERE user_id = ? AND profession_id = ?";
    $user_rating_stmt = $mysqli->prepare($user_rating_query);
    $user_rating_stmt->bind_param("ii", $_SESSION['user_id'], $profession_id);
    $user_rating_stmt->execute();
    $user_rating_result = $user_rating_stmt->get_result();
    if ($user_rating_result->num_rows > 0) {
        $user_rating = $user_rating_result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profession['name']); ?> - ProfHub</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <i class="fas fa-brain"></i>
                    <span>ProfHub</span>
                </a>
                <nav class="nav-menu">
                    <a href="index.php">Главная</a>
                    <a href="professions.php">Профессии</a>
                    <a href="tests.php">Тесты</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="profile.php">Личный кабинет</a>
                        <a href="logout.php" class="btn btn-outline">Выйти</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline">Войти</a>
                        <a href="register.php" class="btn btn-primary">Регистрация</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="profession-details">
                <div class="profession-header">
                    <h1><?php echo htmlspecialchars($profession['name']); ?></h1>
                    <div class="profession-rating">
                        <?php
                        $rating = round($profession['avg_rating'] ?? 0);
                        for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $rating ? 'text-warning' : 'text-muted'; ?>"></i>
                        <?php endfor; ?>
                        <span class="rating-count">(<?php echo $profession['rating_count']; ?> оценок)</span>
                    </div>
                </div>

                <div class="profession-content">
                    <div class="profession-description">
                        <h2>Описание профессии</h2>
                        <p><?php echo nl2br(htmlspecialchars($profession['description'])); ?></p>
                    </div>

                    <div class="profession-pvk">
                        <h2>Профессионально важные качества</h2>
                        <ul class="pvk-list">
                            <?php while ($pvk = $pvk_result->fetch_assoc()): ?>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <?php echo htmlspecialchars($pvk['name']); ?>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="profession-actions">
                            <?php if ($user_rating): ?>
                                <a href="rate_profession.php?id=<?php echo $profession_id; ?>" class="btn btn-outline">
                                    <i class="fas fa-edit"></i> Изменить оценку
                                </a>
                            <?php else: ?>
                                <a href="rate_profession.php?id=<?php echo $profession_id; ?>" class="btn btn-primary">
                                    <i class="fas fa-star"></i> Оценить профессию
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="profession-reviews">
                        <h2>Отзывы</h2>
                        <?php if ($reviews_result->num_rows > 0): ?>
                            <div class="reviews-list">
                                <?php while ($review = $reviews_result->fetch_assoc()): ?>
                                    <div class="review-item">
                                        <div class="review-header">
                                            <span class="review-author"><?php echo htmlspecialchars($review['username']); ?></span>
                                            <span class="review-date"><?php echo date('d.m.Y', strtotime($review['date_rated'])); ?></span>
                                        </div>
                                        <div class="review-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <?php if ($review['comment']): ?>
                                            <p class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Пока нет отзывов. Будьте первым!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <i class="fas fa-brain"></i>
                    <span>ProfHub</span>
                </div>
                <div class="footer-links">
                    <a href="#"><i class="fab fa-vk"></i></a>
                    <a href="#"><i class="fab fa-telegram"></i></a>
                </div>
                <p>&copy; 2024 ProfHub. Все права защищены.</p>
            </div>
        </div>
    </footer>
</body>
</html> 