<?php
session_start();
require_once "db-connect.php";

// Проверяем, является ли пользователь админом
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Обработка добавления новой профессии
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $requirements = trim($_POST['requirements']);
        $salary = trim($_POST['salary']);
        
        if (!empty($name)) {
            $stmt = $conn->prepare("INSERT INTO professions (name, description, requirements, salary) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $description, $requirements, $salary);
            $stmt->execute();
        }
    } 
    elseif ($_POST['action'] === 'edit') {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $requirements = trim($_POST['requirements']);
        $salary = trim($_POST['salary']);
        
        if (!empty($name)) {
            $stmt = $conn->prepare("UPDATE professions SET name = ?, description = ?, requirements = ?, salary = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $description, $requirements, $salary, $id);
            $stmt->execute();
        }
    }
    elseif ($_POST['action'] === 'delete') {
        $id = intval($_POST['id']);
        // Сначала удаляем все связанные оценки ПВК
        $stmt = $conn->prepare("DELETE FROM expert_pvk_ratings WHERE profession_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        // Теперь удаляем профессию
        $stmt = $conn->prepare("DELETE FROM professions WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    
    header("Location: admin_professions.php");
    exit;
}

// Получаем список всех профессий
$professions_query = "SELECT * FROM professions ORDER BY name ASC";
$professions_result = $conn->query($professions_query);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление профессиями - ProfHub</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/background.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 100px auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.23);
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }

        .professions-list {
            margin-top: 20px;
        }

        .profession-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
        }

        .profession-info {
            flex-grow: 1;
        }

        .profession-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .add-profession-form {
            margin-bottom: 20px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            background: rgba(0, 0, 0, 0.5);
            color: white;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="admin-container">
        <h1>Управление профессиями</h1>

        <div class="add-profession-form">
            <h2>Добавить новую профессию</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="name">Название профессии:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="description">Описание:</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                <div class="form-group">
                    <label for="requirements">Требования:</label>
                    <textarea id="requirements" name="requirements" required></textarea>
                </div>
                <div class="form-group">
                    <label for="salary">Зарплата:</label>
                    <input type="text" id="salary" name="salary" required>
                </div>
                <button type="submit" class="btn btn-primary">Добавить</button>
            </form>
        </div>

        <div class="professions-list">
            <h2>Список профессий</h2>
            <?php while ($profession = $professions_result->fetch_assoc()): ?>
                <div class="profession-item">
                    <div class="profession-info">
                        <h3><?php echo htmlspecialchars($profession['name']); ?></h3>
                        <p><?php echo htmlspecialchars($profession['description']); ?></p>
                    </div>
                    <div class="profession-actions">
                        <button class="btn btn-primary edit-btn" 
                                data-id="<?php echo $profession['id']; ?>"
                                data-name="<?php echo htmlspecialchars($profession['name']); ?>"
                                data-description="<?php echo htmlspecialchars($profession['description']); ?>"
                                data-requirements="<?php echo htmlspecialchars($profession['requirements']); ?>"
                                data-salary="<?php echo htmlspecialchars($profession['salary']); ?>">
                            <i class="fas fa-edit"></i> Редактировать
                        </button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $profession['id']; ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Вы уверены, что хотите удалить эту профессию?')">
                                <i class="fas fa-trash"></i> Удалить
                            </button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const description = this.dataset.description;
                const requirements = this.dataset.requirements;
                const salary = this.dataset.salary;

                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="${id}">
                    <div class="form-group">
                        <label for="edit-name">Название профессии:</label>
                        <input type="text" id="edit-name" name="name" value="${name}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-description">Описание:</label>
                        <textarea id="edit-description" name="description">${description}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit-requirements">Требования:</label>
                        <textarea id="edit-requirements" name="requirements" required>${requirements}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit-salary">Зарплата:</label>
                        <input type="text" id="edit-salary" name="salary" value="${salary}" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                `;

                const professionItem = this.closest('.profession-item');
                professionItem.innerHTML = '';
                professionItem.appendChild(form);
            });
        });
    </script>
</body>
</html> 