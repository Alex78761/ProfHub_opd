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

// Получаем список всех тестов
$tests = [];
$tests_result = $conn->query("SELECT id, test_name FROM tests ORDER BY test_name");
while ($row = $tests_result->fetch_assoc()) {
    $tests[] = $row;
}

// Получаем список всех пользователей (user и respondent)
$users = [];
$users_result = $conn->query("SELECT id, username FROM users WHERE role IN ('user', 'respondent') ORDER BY username");
while ($row = $users_result->fetch_assoc()) {
    $users[] = $row;
}

// Определяем режим
$mode = $_GET['mode'] ?? 'all';
$selected_test = $_GET['test_id'] ?? '';
$selected_user = $_GET['user_id'] ?? '';

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты тестирования — Эксперт</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/background.css">
    <style>
        .container { max-width: 1200px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 10px; }
        h2 { margin-bottom: 20px; }
        .tabs { margin-bottom: 20px; }
        .tabs a { margin-right: 15px; text-decoration: none; color: #00796b; font-weight: bold; }
        .tabs a.active { color: #FFD700; }
        select { margin: 0 10px 20px 0; padding: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #222; color: #FFD700; }
        tr:hover { background: #f5f5f5; }
        .chart-container { width: 100%; height: 400px; margin-bottom: 30px; }
    </style>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <h2>Результаты тестирования (Эксперт)</h2>
    <div class="tabs">
        <a href="?mode=all" class="<?= $mode=='all'?'active':'' ?>">Все тесты</a>
        <a href="?mode=test" class="<?= $mode=='test'?'active':'' ?>">Отдельный тест</a>
        <a href="?mode=dynamics" class="<?= $mode=='dynamics'?'active':'' ?>">Динамика пользователя</a>
    </div>

    <?php if ($mode == 'all'): ?>
        <!-- 5.6.3. Все тесты -->
        <?php
        $query = "SELECT u.username, t.test_name, t.test_type, tr.result, tr.score, tr.created_at
                  FROM test_results tr
                  INNER JOIN users u ON tr.user_id = u.id
                  INNER JOIN tests t ON tr.test_id = t.id
                  WHERE u.role IN ('user', 'respondent')
                  ORDER BY u.username, tr.created_at DESC";
        $result = $conn->query($query);
        ?>
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
    <?php elseif ($mode == 'test'): ?>
        <!-- 5.6.1. Результаты отдельного теста -->
        <form method="get" style="margin-bottom:20px;">
            <input type="hidden" name="mode" value="test">
            <label for="test_id">Выберите тест:</label>
            <select name="test_id" id="test_id" onchange="this.form.submit()">
                <option value="">-- Все тесты --</option>
                <?php foreach ($tests as $test): ?>
                    <option value="<?= $test['id'] ?>" <?= $selected_test==$test['id']?'selected':'' ?>><?= htmlspecialchars($test['test_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php
        $where = "u.role IN ('user', 'respondent')";
        if ($selected_test) {
            $where .= " AND t.id = " . intval($selected_test);
        }
        $query = "SELECT u.username, t.test_name, t.test_type, tr.result, tr.score, tr.created_at
                  FROM test_results tr
                  INNER JOIN users u ON tr.user_id = u.id
                  INNER JOIN tests t ON tr.test_id = t.id
                  WHERE $where
                  ORDER BY tr.created_at DESC";
        $result = $conn->query($query);
        // Для диаграммы: собираем данные по пользователям
        $chart_data = [];
        if ($selected_test && $result && $result->num_rows > 0) {
            foreach ($result as $row) {
                $chart_data[$row['username']][] = floatval($row['result']);
            }
        }
        ?>
        <?php if ($selected_test && !empty($chart_data)): ?>
            <div class="chart-container" id="test_chart"></div>
            <script>
                google.charts.load('current', {'packages':['corechart']});
                google.charts.setOnLoadCallback(drawChart);
                function drawChart() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', 'Пользователь');
                    data.addColumn('number', 'Средний результат');
                    <?php foreach ($chart_data as $username => $results): ?>
                        data.addRow(['<?= addslashes($username) ?>', <?= round(array_sum($results)/count($results), 2) ?>]);
                    <?php endforeach; ?>
                    var options = {
                        title: 'Средний результат по пользователям',
                        hAxis: {title: 'Пользователь'},
                        vAxis: {title: 'Результат'},
                        legend: { position: 'none' },
                        backgroundColor: '#fff',
                        colors: ['#00796b']
                    };
                    var chart = new google.visualization.ColumnChart(document.getElementById('test_chart'));
                    chart.draw(data, options);
                }
            </script>
        <?php endif; ?>
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
                <?php foreach ($result as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['test_type']) ?></td>
                        <td><?= htmlspecialchars($row['test_name']) ?></td>
                        <td><?= round(floatval($row['result']), 2) ?></td>
                        <td><?= isset($row['score']) ? round(floatval($row['score']), 2) : '-' ?></td>
                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">Нет данных для отображения.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    <?php elseif ($mode == 'dynamics'): ?>
        <!-- 5.6.2. Динамика одного вида теста -->
        <form method="get" style="margin-bottom:20px;">
            <input type="hidden" name="mode" value="dynamics">
            <label for="user_id">Пользователь:</label>
            <select name="user_id" id="user_id">
                <option value="">-- Выберите --</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= $selected_user==$user['id']?'selected':'' ?>><?= htmlspecialchars($user['username']) ?></option>
                <?php endforeach; ?>
            </select>
            <label for="test_id">Тест:</label>
            <select name="test_id" id="test_id">
                <option value="">-- Выберите --</option>
                <?php foreach ($tests as $test): ?>
                    <option value="<?= $test['id'] ?>" <?= $selected_test==$test['id']?'selected':'' ?>><?= htmlspecialchars($test['test_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Показать</button>
        </form>
        <?php
        $data = [];
        if ($selected_user && $selected_test) {
            $query = "SELECT tr.result, tr.score, tr.created_at
                      FROM test_results tr
                      WHERE tr.user_id = ? AND tr.test_id = ?
                      ORDER BY tr.created_at ASC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $selected_user, $selected_test);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $data[] = $row;
            }
            $stmt->close();
        }
        ?>
        <?php if ($selected_user && $selected_test): ?>
            <div class="chart-container" id="dynamics_chart"></div>
            <script>
                google.charts.load('current', {'packages':['corechart']});
                google.charts.setOnLoadCallback(drawChart);
                function drawChart() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', 'Дата');
                    data.addColumn('number', 'Результат');
                    <?php foreach ($data as $row): ?>
                        data.addRow(['<?= $row['created_at'] ?>', <?= floatval($row['result']) ?>]);
                    <?php endforeach; ?>
                    var options = {
                        title: 'Динамика результатов',
                        hAxis: {title: 'Дата'},
                        vAxis: {title: 'Результат'},
                        legend: { position: 'none' },
                        backgroundColor: '#fff',
                        colors: ['#FFD700']
                    };
                    var chart = new google.visualization.LineChart(document.getElementById('dynamics_chart'));
                    chart.draw(data, options);
                }
            </script>
        <?php elseif ($selected_user || $selected_test): ?>
            <p>Выберите и пользователя, и тест для просмотра динамики.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
<?php $conn->close(); ?> 