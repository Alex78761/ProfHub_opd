<?php
session_start();

// Подключение к базе данных
require_once "db-connect.php";

// Если пользователь уже вошел в систему, перенаправляем его на главную страницу
if(isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

$errors = [];

// Проверяем, если форма регистрации отправлена
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Валидация
    if (empty($username)) {
        $errors[] = "Введите имя пользователя";
    } elseif (strlen($username) < 3) {
        $errors[] = "Имя пользователя должно содержать минимум 3 символа";
    }
    
    if (empty($password)) {
        $errors[] = "Введите пароль";
    } elseif (strlen($password) < 6) {
        $errors[] = "Пароль должен содержать минимум 6 символов";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Пароли не совпадают";
    }
    
    // Проверяем, существует ли пользователь
    if (empty($errors)) {
        $check_query = "SELECT id FROM users WHERE username = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Пользователь с таким именем уже существует";
        } else {
            // Создаем нового пользователя с безопасным хешированием пароля
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, password, role) VALUES (?, ?, 'user')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $username, $hashed_password);
            
            if ($stmt->execute()) {
                $new_user_id = $stmt->insert_id;
                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'user';
                header("Location: index.php");
                exit();
            } else {
                $errors[] = "Ошибка при регистрации. Попробуйте позже.";
            }
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
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/background.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Регистрация</h1>
                <p>Создайте новый аккаунт для доступа к тестам</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="" novalidate>
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Имя пользователя
                    </label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-control" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                           required 
                           minlength="3"
                           placeholder="Минимум 3 символа">
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Пароль
                    </label>
                    <div class="password-input">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               required 
                               minlength="6"
                               placeholder="Минимум 6 символов">
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-lock"></i>
                        Подтвердите пароль
                    </label>
                    <div class="password-input">
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               class="form-control" 
                               required
                               placeholder="Повторите пароль">
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
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

    <script>
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const icon = input.nextElementSibling.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    </script>
</body>
</html>
