<?php
session_start();
require_once "db-connect.php";

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

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

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    
    if ($rating >= 1 && $rating <= 5) {
        // Проверяем, не оценивал ли пользователь эту профессию ранее
        $check_query = "SELECT id FROM ratings WHERE user_id = ? AND profession_id = ?";
        $check_stmt = $mysqli->prepare($check_query);
        $check_stmt->bind_param("ii", $_SESSION['user_id'], $profession_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Обновляем существующую оценку
            $update_query = "UPDATE ratings SET rating = ?, comment = ? WHERE user_id = ? AND profession_id = ?";
            $update_stmt = $mysqli->prepare($update_query);
            $update_stmt->bind_param("isii", $rating, $comment, $_SESSION['user_id'], $profession_id);
            $update_stmt->execute();
        } else {
            // Добавляем новую оценку
            $insert_query = "INSERT INTO ratings (user_id, profession_id, rating, comment) VALUES (?, ?, ?, ?)";
            $insert_stmt = $mysqli->prepare($insert_query);
            $insert_stmt->bind_param("iiis", $_SESSION['user_id'], $profession_id, $rating, $comment);
            $insert_stmt->execute();
        }
        
        header("Location: profession.php?id=" . $profession_id);
        exit();
    }
    
    $error = "Пожалуйста, выберите оценку от 1 до 5 звезд.";
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оценка профессии - ProfHub</title>
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
                <h1>Оценка профессии</h1>
                <p>Поделитесь своим мнением о профессии "<?php echo htmlspecialchars($profession['name']); ?>"</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card profession-card fade-in">
                        <div class="card-body">
                            <form method="POST" action="">
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endif; ?>

                                <div class="mb-4">
                                    <label class="form-label">Ваша оценка</label>
                                    <div class="rating-input">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" class="d-none">
                                            <label for="star<?php echo $i; ?>" class="star-label">
                                                <i class="fas fa-star"></i>
                                            </label>
                                        <?php endfor; ?>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="comment" class="form-label">Ваш отзыв</label>
                                    <textarea class="form-control" id="comment" name="comment" rows="4" required></textarea>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="profession.php?id=<?php echo $profession_id; ?>" class="btn btn-outline">
                                        Отмена
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        Отправить оценку
                                    </button>
                                </div>
                            </form>
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
    <script>
        // Анимация звезд при наведении
        document.querySelectorAll('.star-label').forEach(label => {
            label.addEventListener('mouseover', function() {
                const stars = document.querySelectorAll('.star-label');
                const currentStar = this;
                stars.forEach(star => {
                    if (star === currentStar || star.previousElementSibling === currentStar) {
                        star.querySelector('i').classList.add('text-warning');
                    } else {
                        star.querySelector('i').classList.remove('text-warning');
                    }
                });
            });
        });

        // Сброс анимации при уходе мыши
        document.querySelector('.rating-input').addEventListener('mouseleave', function() {
            document.querySelectorAll('.star-label i').forEach(star => {
                star.classList.remove('text-warning');
            });
        });
    </script>
</body>
</html> 