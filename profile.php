<?php
session_start();
require_once "db-connect.php";

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Получаем результаты тестов пользователя
$tests_query = "SELECT t.title, tr.score, tr.date_taken 
                FROM test_results tr 
                JOIN tests t ON tr.test_id = t.id 
                WHERE tr.user_id = ? 
                ORDER BY tr.date_taken DESC";
$tests_stmt = $mysqli->prepare($tests_query);
$tests_stmt->bind_param("i", $user_id);
$tests_stmt->execute();
$tests_result = $tests_stmt->get_result();

// Получаем рейтинги профессий пользователя
$ratings_query = "SELECT p.name, r.rating, r.comment 
                 FROM ratings r 
                 JOIN professions p ON r.profession_id = p.id 
                 WHERE r.user_id = ? 
                 ORDER BY r.date_rated DESC";
$ratings_stmt = $mysqli->prepare($ratings_query);
$ratings_stmt->bind_param("i", $user_id);
$ratings_stmt->execute();
$ratings_result = $ratings_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - ProfHub</title>
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
                    <?php if ($role === 'admin'): ?>
                        <a href="admin.php">Панель управления</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-outline">Выйти</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="profile-header">
                <h1>Личный кабинет</h1>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <div>
                        <h2><?php echo htmlspecialchars($username); ?></h2>
                        <p><?php echo $role === 'admin' ? 'Администратор' : 'Пользователь'; ?></p>
                    </div>
                </div>
            </div>

            <div class="profile-grid">
                <div class="profile-card">
                    <h3><i class="fas fa-clipboard-check"></i> Результаты тестов</h3>
                    <?php if ($tests_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Тест</th>
                                        <th>Результат</th>
                                        <th>Дата</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($test = $tests_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($test['title']); ?></td>
                                            <td><?php echo $test['score']; ?>%</td>
                                            <td><?php echo date('d.m.Y', strtotime($test['date_taken'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Вы еще не проходили тесты</p>
                    <?php endif; ?>
                </div>

                <div class="profile-card">
                    <h3><i class="fas fa-star"></i> Ваши оценки профессий</h3>
                    <?php if ($ratings_result->num_rows > 0): ?>
                        <div class="ratings-list">
                            <?php while ($rating = $ratings_result->fetch_assoc()): ?>
                                <div class="rating-item">
                                    <h4><?php echo htmlspecialchars($rating['name']); ?></h4>
                                    <div class="rating-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $rating['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <?php if ($rating['comment']): ?>
                                        <p class="rating-comment"><?php echo htmlspecialchars($rating['comment']); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Вы еще не оценивали профессии</p>
                    <?php endif; ?>
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