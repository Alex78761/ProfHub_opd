<?php
session_start();

// Подключение к базе данных
require_once "db-connect.php";

// Если пользователь уже вошел в систему, перенаправляем его на главную страницу
if(isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

// Проверяем, если форма регистрации отправлена
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $confirm_password = md5($_POST['confirm_password']);
    
    // Проверяем, существует ли пользователь
    $check_query = "SELECT id FROM users WHERE username = ?";
    $check_stmt = $mysqli->prepare($check_query);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $error = "Пользователь с таким именем уже существует";
    } elseif ($password !== $confirm_password) {
        $error = "Пароли не совпадают";
    } else {
        // Создаем нового пользователя
        $query = "INSERT INTO users (username, password, role) VALUES (?, ?, 'user')";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ss", $username, $password);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Регистрация успешна! Теперь вы можете войти.";
            header("Location: login.php");
            exit();
        } else {
            $error = "Ошибка при регистрации. Попробуйте позже.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - ProfHub</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><i class="fas fa-brain"></i> ProfHub</h1>
                <p>Создайте новый аккаунт</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="">
                <div class="form-group">
                    <label for="username">Имя пользователя</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Подтвердите пароль</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Зарегистрироваться
                </button>
            </form>

            <div class="auth-footer">
                <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
            </div>
        </div>
    </div>
</body>
</html>
