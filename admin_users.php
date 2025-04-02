<?php
session_start();
require_once "db-connect.php";

// Проверяем, авторизован ли пользователь и является ли он администратором
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Обработка действий с пользователями
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $user_id = (int)$_POST['user_id'];
        
        switch ($_POST['action']) {
            case 'delete':
                // Удаляем пользователя
                $delete_query = "DELETE FROM users WHERE id = ? AND role != 'admin'";
                $delete_stmt = $mysqli->prepare($delete_query);
                $delete_stmt->bind_param("i", $user_id);
                $delete_stmt->execute();
                break;
                
            case 'change_role':
                $new_role = $_POST['role'];
                // Изменяем роль пользователя
                $role_query = "UPDATE users SET role = ? WHERE id = ? AND role != 'admin'";
                $role_stmt = $mysqli->prepare($role_query);
                $role_stmt->bind_param("si", $new_role, $user_id);
                $role_stmt->execute();
                break;
        }
        
        header("Location: admin_users.php");
        exit();
    }
}

// Получаем список пользователей
$users_query = "SELECT * FROM users ORDER BY username";
$users_result = $mysqli->query($users_query);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями - ProfHub</title>
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
                    <a href="admin.php" class="active">Панель управления</a>
                    <a href="logout.php" class="btn btn-outline">Выйти</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="admin-container">
                <div class="admin-header">
                    <h1>Управление пользователями</h1>
                    <p>Управление учетными записями пользователей</p>
                </div>

                <div class="admin-content">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Имя пользователя</th>
                                    <th>Роль</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $users_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td>
                                            <?php if ($user['role'] === 'admin'): ?>
                                                <span class="badge badge-primary">Администратор</span>
                                            <?php elseif ($user['role'] === 'expert'): ?>
                                                <span class="badge badge-success">Эксперт</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Пользователь</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($user['role'] !== 'admin'): ?>
                                                <div class="action-buttons">
                                                    <form method="POST" class="inline-form">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <input type="hidden" name="action" value="change_role">
                                                        <select name="role" class="form-control" onchange="this.form.submit()">
                                                            <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Пользователь</option>
                                                            <option value="expert" <?php echo $user['role'] === 'expert' ? 'selected' : ''; ?>>Эксперт</option>
                                                        </select>
                                                    </form>
                                                    <form method="POST" class="inline-form" onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя?');">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
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