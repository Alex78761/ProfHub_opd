<?php
session_start();
require_once "db-connect.php";

// Проверяем, авторизован ли пользователь и является ли он администратором
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Получаем статистику
$stats_query = "SELECT 
                (SELECT COUNT(*) FROM users) as total_users,
                (SELECT COUNT(*) FROM professions) as total_professions,
                (SELECT COUNT(*) FROM tests) as total_tests,
                (SELECT COUNT(*) FROM test_results) as total_results,
                (SELECT COUNT(*) FROM ratings) as total_ratings";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Получаем последние результаты тестов
$recent_results_query = "SELECT tr.*, t.test_name as test_title, u.username 
                        FROM test_results tr 
                        JOIN tests t ON tr.test_id = t.id 
                        JOIN users u ON tr.user_id = u.id 
                        ORDER BY tr.test_date DESC 
                        LIMIT 5";
$recent_results = $conn->query($recent_results_query);

// Получаем последние оценки профессий
$recent_ratings_query = "SELECT r.*, p.name as profession_name, u.username 
                        FROM ratings r 
                        JOIN professions p ON r.profession_id = p.id 
                        JOIN users u ON r.user_id = u.id 
                        ORDER BY r.id DESC 
                        LIMIT 5";
$recent_ratings = $conn->query($recent_ratings_query);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель управления - ProfHub</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/background.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="main">
        <div class="container">
            <div class="admin-container">
                <div class="admin-header">
                    <h1>Панель управления</h1>
                    <p>Управление контентом и пользователями</p>
                </div>

                <div class="admin-grid">
                    <div class="admin-card">
                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Пользователи</h3>
                        <p class="card-value"><?php echo $stats['total_users']; ?></p>
                        <a href="admin_users.php" class="btn btn-outline">
                            <i class="fas fa-cog"></i> Управление
                        </a>
                    </div>

                    <div class="admin-card">
                        <div class="card-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h3>Профессии</h3>
                        <p class="card-value"><?php echo $stats['total_professions']; ?></p>
                        <a href="admin_professions.php" class="btn btn-outline">
                            <i class="fas fa-cog"></i> Управление
                        </a>
                    </div>

                    <div class="admin-card">
                        <div class="card-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h3>Тесты</h3>
                        <p class="card-value"><?php echo $stats['total_tests']; ?></p>
                        <a href="admin_tests.php" class="btn btn-outline">
                            <i class="fas fa-cog"></i> Управление
                        </a>
                    </div>

                    <div class="admin-card">
                        <div class="card-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h3>Результаты</h3>
                        <p class="card-value"><?php echo $stats['total_results']; ?></p>
                        <a href="admin_results.php" class="btn btn-outline">
                            <i class="fas fa-cog"></i> Управление
                        </a>
                    </div>
                </div>

                <div class="admin-recent">
                    <div class="recent-section">
                        <h2>Последние результаты тестов</h2>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Пользователь</th>
                                        <th>Тест</th>
                                        <th>Результат</th>
                                        <th>Дата</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_results && $recent_results->num_rows > 0): ?>
                                        <?php while ($result = $recent_results->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($result['username']); ?></td>
                                                <td><?php echo htmlspecialchars($result['test_title']); ?></td>
                                                <td><?php echo $result['result']; ?>%</td>
                                                <td><?php echo date('d.m.Y', strtotime($result['test_date'])); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Нет результатов тестов</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="recent-section">
                        <h2>Последние оценки профессий</h2>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Пользователь</th>
                                        <th>Профессия</th>
                                        <th>Оценка</th>
                                        <th>Дата</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_ratings && $recent_ratings->num_rows > 0): ?>
                                        <?php while ($rating = $recent_ratings->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($rating['username']); ?></td>
                                                <td><?php echo htmlspecialchars($rating['profession_name']); ?></td>
                                                <td>
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $rating['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                    <?php endfor; ?>
                                                </td>
                                                <td><?php echo date('d.m.Y', strtotime($rating['id'])); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Нет оценок профессий</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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