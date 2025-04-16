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
$tests_query = "SELECT t.test_name, tr.result, tr.test_date 
                FROM test_results tr 
                JOIN tests t ON tr.test_id = t.id 
                WHERE tr.user_id = ? 
                ORDER BY tr.test_date DESC";
$tests_stmt = $conn->prepare($tests_query);
$tests_stmt->bind_param("i", $user_id);
$tests_stmt->execute();
$tests_result = $tests_stmt->get_result();

// Получаем рейтинги профессий пользователя
$ratings_query = "SELECT r.rating, r.created_at, p.name 
                  FROM ratings r 
                  JOIN professions p ON r.profession_id = p.id 
                  WHERE r.user_id = ? 
                  ORDER BY r.id DESC";
$ratings_stmt = $conn->prepare($ratings_query);
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
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/background.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($username); ?></h1>
                <p><?php 
                    switch($role) {
                        case 'admin':
                            echo 'Администратор';
                            break;
                        case 'expert':
                            echo 'Эксперт';
                            break;
                        case 'respondent':
                            echo 'Респондент';
                            break;
                        default:
                            echo 'Пользователь';
                    }
                ?></p>
            </div>
        </div>

        <div class="profile-stats">
            <div class="stat-card">
                <h3><i class="fas fa-clipboard-check"></i> Результаты тестов</h3>
                <?php if ($tests_result && $tests_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="test-results-table">
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
                                        <td><?php echo htmlspecialchars($test['test_name']); ?></td>
                                        <td><?php echo $test['result']; ?>%</td>
                                        <td><?php echo date('d.m.Y', strtotime($test['test_date'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>Вы еще не проходили тесты</p>
                <?php endif; ?>
            </div>

            <div class="stat-card">
                <h3><i class="fas fa-star"></i> Ваши оценки профессий</h3>
                <?php if ($ratings_result && $ratings_result->num_rows > 0): ?>
                    <div class="ratings-list">
                        <?php while ($rating = $ratings_result->fetch_assoc()): ?>
                            <div class="rating-item">
                                <h4><?php echo htmlspecialchars($rating['name']); ?></h4>
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $rating['rating'] ? 'active' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>Вы еще не оценивали профессии</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($tests_result && $tests_result->num_rows > 0): ?>
        <div class="progress-chart">
            <h3><i class="fas fa-chart-line"></i> График прогресса</h3>
            <div class="chart-container">
                <canvas id="progressChart"></canvas>
            </div>
        </div>
        <?php endif; ?>

        <div class="profile-actions">
            <a href="tests.php" class="action-button">
                <i class="fas fa-tasks"></i>
                Пройти тесты
            </a>
            <a href="professions.php" class="action-button">
                <i class="fas fa-briefcase"></i>
                Изучить профессии
            </a>
            <?php if ($role === 'admin'): ?>
            <a href="admin.php" class="action-button">
                <i class="fas fa-cog"></i>
                Панель управления
            </a>
            <?php endif; ?>
        </div>
    </div>

    <script>
    <?php if ($tests_result && $tests_result->num_rows > 0): ?>
    // Инициализация графика прогресса
    const ctx = document.getElementById('progressChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php 
                $tests_result->data_seek(0);
                $dates = [];
                while ($test = $tests_result->fetch_assoc()) {
                    $dates[] = date('d.m.Y', strtotime($test['test_date']));
                }
                echo json_encode(array_reverse($dates));
            ?>,
            datasets: [{
                label: 'Результаты тестов',
                data: <?php 
                    $tests_result->data_seek(0);
                    $scores = [];
                    while ($test = $tests_result->fetch_assoc()) {
                        $scores[] = $test['result'];
                    }
                    echo json_encode(array_reverse($scores));
                ?>,
                borderColor: '#2196F3',
                backgroundColor: 'rgba(33, 150, 243, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.7)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.7)'
                    }
                }
            }
        }
    });
    <?php endif; ?>
    </script>
</body>
</html> 