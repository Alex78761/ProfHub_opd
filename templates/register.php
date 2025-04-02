<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Имя пользователя обязательно";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Введите корректный email";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Пароль должен быть не менее 6 символов";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Пароли не совпадают";
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password]);
            
            header("Location: /?page=login&registered=1");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = "Пользователь с таким именем или email уже существует";
            } else {
                $errors[] = "Ошибка при регистрации. Попробуйте позже.";
            }
        }
    }
}
?>

<div class="form-container">
    <h2 class="text-center mb-4">Регистрация</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="/?page=register" id="registerForm">
        <div class="mb-3">
            <label for="username" class="form-label">Имя пользователя</label>
            <input type="text" class="form-control" id="username" name="username" required
                   value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
        </div>
        
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required
                   value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Пароль</label>
            <input type="password" class="form-control" id="password" name="password" required
                   minlength="6">
        </div>
        
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Подтверждение пароля</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                   required minlength="6">
        </div>
        
        <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
    </form>
    
    <p class="text-center mt-3">
        Уже есть аккаунт? <a href="/?page=login">Войти</a>
    </p>
</div> 