<?php
session_start();
require_once "db-connect.php";

// Проверка, что пользователь — эксперт
if (!isset($_SESSION['user_id'])) {
    echo '<div class="container"><h2>Доступ запрещён</h2><p>Вы не авторизованы.</p></div>';
    exit;
}

$user_id = $_SESSION['user_id'];
$query_role = "SELECT role FROM users WHERE id = ?";
$stmt_role = $conn->prepare($query_role);
$stmt_role->bind_param("i", $user_id);
$stmt_role->execute();
$result_role = $stmt_role->get_result();
$role = $result_role->fetch_assoc()['role'] ?? '';
$stmt_role->close();

if ($role !== 'expert') {
    echo '<div class="container"><h2>Доступ запрещён</h2><p>Только эксперт может просматривать эту страницу.</p></div>';
    exit;
}

// Получаем результаты всех пользователей с ролью 'user' или 'respondent'
$query = "SELECT u.username, tr.test_id, t.test_type, t.test_name, tr.result, tr.score, tr.created_at
          FROM test_results tr
          INNER JOIN users u ON tr.user_id = u.id
          INNER JOIN tests t ON tr.test_id = t.id
          WHERE u.role IN ('user', 'respondent')
          ORDER BY u.username, tr.created_at DESC";
$result = $conn->query($query);

// Выводим результаты
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты всех пользователей</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/background.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: rgba(255,255,255,0.95);
            border-radius: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #222;
            color: #FFD700;
        }
        tr:hover {
            background: #f5f5f5;
        }
        h2 {
            color: #222;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <h2>Результаты тестов всех пользователей (роль: user или respondent)</h2>
    <table>
        <thead>
            <tr>
                <th>Имя пользователя</th>
                <th>Тип теста</th>
                <th>Название теста</th>
                <th>Результат</th>
                <th>Оценка</th>
                <th>Дата</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['test_type']) ?></td>
                    <td><?= htmlspecialchars($row['test_name']) ?></td>
                    <td><?= round(floatval($row['result']), 2) ?></td>
                    <td><?= isset($row['score']) ? round(floatval($row['score']), 2) : '-' ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">Нет данных для отображения.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
<?php $conn->close(); ?> 