<?php
session_start();
require 'db-connect.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Получение всех результатов для пользователя
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT tr.*, t.test_name, t.test_type 
                       FROM test_results tr 
                       JOIN tests t ON tr.test_id = t.id 
                       WHERE tr.user_id = ? 
                       ORDER BY tr.created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Получение всех тестов
$testsStmt = $conn->prepare("SELECT * FROM tests ORDER BY test_type, test_name");
$testsStmt->execute();
$testsResult = $testsStmt->get_result();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты тестов</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/background.css">
    <link rel="stylesheet" href="css/header.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        th {
            background: rgba(255, 255, 255, 0.2);
            color: #FFD700;
        }
        tr:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        .section-title {
            color: #FFD700;
            margin: 30px 0 20px;
            font-size: 24px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1 class="section-title">Результаты тестов</h1>
        
        <h2 class="section-title">Ваши результаты</h2>
        <table>
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Тест</th>
                    <th>Тип теста</th>
                    <th>Результат</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['created_at'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['test_name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['test_type'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['result'] ?? ''); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h2 class="section-title">Доступные тесты</h2>
        <table>
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Тип</th>
                    <th>Описание</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($test = $testsResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($test['test_name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($test['test_type'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($test['test_description'] ?? ''); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php
$stmt->close();
$testsStmt->close();
$conn->close();
?> 