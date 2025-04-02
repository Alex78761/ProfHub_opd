<?php
session_start();
require_once "db-connect.php";

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Получаем ID теста из URL
$test_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получаем информацию о тесте
$test_query = "SELECT * FROM tests WHERE id = ?";
$test_stmt = $mysqli->prepare($test_query);
$test_stmt->bind_param("i", $test_id);
$test_stmt->execute();
$test_result = $test_stmt->get_result();

if ($test_result->num_rows === 0) {
    header("Location: tests.php");
    exit();
}

$test = $test_result->fetch_assoc();

// Получаем последний результат пользователя
$result_query = "SELECT * FROM test_results 
                 WHERE user_id = ? AND test_id = ? 
                 ORDER BY date_taken DESC LIMIT 1";
$result_stmt = $mysqli->prepare($result_query);
$result_stmt->bind_param("ii", $_SESSION['user_id'], $test_id);
$result_stmt->execute();
$result_data = $result_stmt->get_result();

if ($result_data->num_rows === 0) {
    header("Location: take_test.php?id=" . $test_id);
    exit();
}

$result = $result_data->fetch_assoc();

// Определяем уровень результата
$level = '';
$level_description = '';
if ($result['score'] >= 90) {
    $level = 'Отлично';
    $level_description = 'Вы показали отличный результат!';
} elseif ($result['score'] >= 75) {
    $level = 'Хорошо';
    $level_description = 'Хороший результат, есть куда стремиться!';
} elseif ($result['score'] >= 60) {
    $level = 'Удовлетворительно';
    $level_description = 'Неплохой результат, но можно улучшить.';
} else {
    $level = 'Требует улучшения';
    $level_description = 'Рекомендуется повторить тест после дополнительной подготовки.';
}

// Получаем средний результат по тесту
$avg_query = "SELECT AVG(score) as avg_score, COUNT(*) as total_attempts 
              FROM test_results 
              WHERE test_id = ?";
$avg_stmt = $mysqli->prepare($avg_query);
$avg_stmt->bind_param("i", $test_id);
$avg_stmt->execute();
$avg_result = $avg_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты теста - ProfHub</title>
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
                            <a class="nav-link active" href="tests.php">Тесты</a>
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
                <h1>Результаты теста</h1>
                <p><?php echo htmlspecialchars($test['title']); ?></p>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card profession-card fade-in">
                        <div class="card-body text-center">
                            <div class="result-score mb-4">
                                <h2 class="display-1"><?php echo $result['score']; ?>%</h2>
                                <h3 class="text-primary"><?php echo $level; ?></h3>
                            </div>
                            
                            <p class="lead mb-4"><?php echo $level_description; ?></p>

                            <div class="result-details mb-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="detail-item">
                                            <i class="fas fa-calendar"></i>
                                            <span>Дата прохождения:</span>
                                            <strong><?php echo date('d.m.Y H:i', strtotime($result['date_taken'])); ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-item">
                                            <i class="fas fa-users"></i>
                                            <span>Всего попыток:</span>
                                            <strong><?php echo $avg_result['total_attempts']; ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-item">
                                            <i class="fas fa-chart-line"></i>
                                            <span>Средний результат:</span>
                                            <strong><?php echo round($avg_result['avg_score'], 1); ?>%</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-item">
                                            <i class="fas fa-clock"></i>
                                            <span>Время на тест:</span>
                                            <strong><?php echo $test['duration']; ?> минут</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($result['score'] < 60): ?>
                                <div class="alert alert-warning">
                                    <h5><i class="fas fa-exclamation-triangle"></i> Рекомендации по улучшению:</h5>
                                    <ul class="mb-0">
                                        <li>Повторите материал по темам теста</li>
                                        <li>Используйте дополнительные учебные ресурсы</li>
                                        <li>Практикуйтесь в решении подобных задач</li>
                                        <li>Попробуйте пройти тест снова после подготовки</li>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <div class="mt-4">
                                <a href="tests.php" class="btn btn-outline me-2">
                                    <i class="fas fa-list"></i> К списку тестов
                                </a>
                                <a href="take_test.php?id=<?php echo $test_id; ?>" class="btn btn-primary">
                                    <i class="fas fa-redo"></i> Пройти тест снова
                                </a>
                            </div>
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