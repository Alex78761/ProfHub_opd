<?php
session_start();
require_once "db-connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $age = $_POST['age'];

    // Получаем user_id из сессии
    $user_id = $_SESSION['user_id'];

    // Проверяем, существует ли уже анкета для данного пользователя
    $query_check = "SELECT id FROM respondents WHERE user_id = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // Если анкета уже существует, обновляем её
        $query_update = "UPDATE respondents SET name = ?, gender = ?, age = ? WHERE user_id = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("sssi", $name, $gender, $age, $user_id);
        
        if ($stmt_update->execute()) {
            // После успешного обновления анкеты, присваиваем роль "respondent" пользователю
            $query_assign_role = "UPDATE users SET role = 'respondent' WHERE id = ?";
            $stmt_assign_role = $conn->prepare($query_assign_role);
            $stmt_assign_role->bind_param("i", $user_id);
            $stmt_assign_role->execute();
            $stmt_assign_role->close();

            $_SESSION['role'] = 'respondent';
            header("Location: tests/tests.php");
            exit;
        } else {
            echo "Ошибка при обновлении данных респондента: " . $conn->error;
        }
        $stmt_update->close();
    } else {
        $query_insert = "INSERT INTO respondents (user_id, name, gender, age) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($query_insert);
        $stmt_insert->bind_param("isss", $user_id, $name, $gender, $age);

        if ($stmt_insert->execute()) {
            $respondent_id = $stmt_insert->insert_id; // Получаем respondent_id после вставки

            // После успешной вставки новой анкеты, присваиваем роль "respondent" пользователю
            $query_assign_role = "UPDATE users SET role = 'respondent', respondent_id = ? WHERE id = ?";
            $stmt_assign_role = $conn->prepare($query_assign_role);
            $stmt_assign_role->bind_param("ii", $respondent_id, $user_id);
            $stmt_assign_role->execute();
            $stmt_assign_role->close();

            $_SESSION['role'] = 'respondent';
            $_SESSION['respondent_id'] = $respondent_id;
            header("Location: tests/tests.php");
            exit;
        } else {
            echo "Ошибка при регистрации респондента: " . $conn->error;
        }
        $stmt_insert->close();
    }
    $stmt_check->close();
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация респондента</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/header.css" rel="stylesheet">
    <link href="css/background.css" rel="stylesheet">
    <style>
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .btn-submit {
            background-color: #00796b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        .btn-submit:hover {
            background-color: #00695c;
        }
    </style>
</head>
<body>
    <div class="background"></div>
    <header>
        <p><a href="index.php">Домой</a></p>
        <?php if (isset($_SESSION['username'])): ?>
            <p><a href="account.php">Личный кабинет</a></p>
        <?php endif; ?>
    </header>
    <div class="container">
        <h2 class="text-center mb-4">Регистрация респондента</h2>
        <form method="post" action="">
            <div class="form-group">
                <label for="name" class="form-label">Имя:</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="gender" class="form-label">Пол:</label>
                <select class="form-select" id="gender" name="gender">
                    <option value="Male">Мужской</option>
                    <option value="Female">Женский</option>
                    <option value="Other">Другой</option>
                </select>
            </div>
            <div class="form-group">
                <label for="age" class="form-label">Возраст:</label>
                <input type="number" class="form-control" id="age" name="age" required min="14" max="100">
            </div>
            <button type="submit" name="register" class="btn-submit">Зарегистрироваться</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
