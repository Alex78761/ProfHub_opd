<?php
session_start();
require_once "db-connect.php";

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
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

// Получаем вопросы теста
$questions_query = "SELECT * FROM questions WHERE test_id = ? ORDER BY question_order";
$questions_stmt = $mysqli->prepare($questions_query);
$questions_stmt->bind_param("i", $test_id);
$questions_stmt->execute();
$questions_result = $questions_stmt->get_result();

// Обработка отправки формы
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $score = 0;
    $total_questions = $questions_result->num_rows;
    
    // Проверяем ответы
    while ($question = $questions_result->fetch_assoc()) {
        $answer = isset($_POST['answer_' . $question['id']]) ? (int)$_POST['answer_' . $question['id']] : 0;
        if ($answer === $question['correct_answer']) {
            $score++;
        }
    }
    
    // Вычисляем процент правильных ответов
    $score_percentage = round(($score / $total_questions) * 100);
    
    // Сохраняем результат
    $result_query = "INSERT INTO test_results (user_id, test_id, score, date_taken) VALUES (?, ?, ?, NOW())";
    $result_stmt = $mysqli->prepare($result_query);
    $result_stmt->bind_param("iid", $user_id, $test_id, $score_percentage);
    $result_stmt->execute();
    
    // Перенаправляем на страницу результатов
    header("Location: test_result.php?id=" . $test_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($test['title']); ?> - ProfHub</title>
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
                    <a href="profile.php">Личный кабинет</a>
                    <a href="logout.php" class="btn btn-outline">Выйти</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="test-container">
                <div class="test-header">
                    <h1><?php echo htmlspecialchars($test['title']); ?></h1>
                    <div class="test-info">
                        <span><i class="fas fa-clock"></i> <?php echo $test['duration']; ?> минут</span>
                        <span><i class="fas fa-question-circle"></i> <?php echo $questions_result->num_rows; ?> вопросов</span>
                    </div>
                </div>

                <form method="POST" class="test-form">
                    <?php 
                    $questions_result->data_seek(0); // Сбрасываем указатель результата
                    $question_number = 1;
                    while ($question = $questions_result->fetch_assoc()): 
                    ?>
                        <div class="question-card">
                            <div class="question-header">
                                <span class="question-number">Вопрос <?php echo $question_number; ?></span>
                            </div>
                            <div class="question-content">
                                <p class="question-text"><?php echo htmlspecialchars($question['question_text']); ?></p>
                                <div class="answer-options">
                                    <?php for ($i = 1; $i <= 4; $i++): ?>
                                        <div class="answer-option">
                                            <input type="radio" 
                                                   name="answer_<?php echo $question['id']; ?>" 
                                                   id="answer_<?php echo $question['id']; ?>_<?php echo $i; ?>" 
                                                   value="<?php echo $i; ?>" 
                                                   required>
                                            <label for="answer_<?php echo $question['id']; ?>_<?php echo $i; ?>">
                                                <?php echo htmlspecialchars($question['answer_' . $i]); ?>
                                            </label>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    <?php 
                    $question_number++;
                    endwhile; 
                    ?>

                    <div class="test-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Завершить тест
                        </button>
                    </div>
                </form>
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

    <script>
        // Добавляем таймер
        let timeLeft = <?php echo $test['duration'] * 60; ?>;
        const timerElement = document.querySelector('.test-info span:first-child');
        
        const timer = setInterval(() => {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.innerHTML = `<i class="fas fa-clock"></i> ${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                document.querySelector('.test-form').submit();
            }
            
            timeLeft--;
        }, 1000);
    </script>
</body>
</html> 