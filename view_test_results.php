<?php
session_start();
require_once "db-connect.php";

// Получаем user_id и роль из сессии
$user_id = $_SESSION['user_id'];
$is_expert = false;
$is_respondent = false;
$is_user = false;

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];

    // Получаем информацию о пользователе
    $query_user = "SELECT username, role FROM users WHERE id = ?";
    $statement = $conn->prepare($query_user);
    $statement->bind_param("i", $user_id);
    $statement->execute();
    $result_user = $statement->get_result();

    if($result_user->num_rows == 1){
        $row_user = $result_user->fetch_assoc();
        $username = $row_user['username'];
        $role = $row_user['role'];

        // Проверяем, является ли пользователь экспертом
        $is_expert = $role === 'expert';
        // Проверяем, является ли пользователь респондентом
        $is_respondent = $role === 'respondent';
        // Проверяем, является ли пользователь пользователем
        $is_user = $role === 'user';
    }
}

// Получаем список респондентов для эксперта
$selected_respondent_id = $is_expert && isset($_GET['respondent_id']) ? intval($_GET['respondent_id']) : null;
if ($is_expert) {
    $query_respondents = "SELECT name, age, user_id FROM respondents";
    $result_respondents = $conn->query($query_respondents);
    $expert_respondent_data = [];
    while ($row_respondent = $result_respondents->fetch_assoc()) {
        $expert_respondent_data[] = $row_respondent;
    }
    // Если не выбран респондент — берем первого
    if (!$selected_respondent_id && count($expert_respondent_data)) {
        $selected_respondent_id = $expert_respondent_data[0]['user_id'];
    }
    // Получаем данные только по выбранному респонденту
    $query = "SELECT tr.test_id, t.test_type, t.test_name, tr.result, tr.score, tr.created_at FROM test_results tr INNER JOIN tests t ON tr.test_id = t.id WHERE tr.user_id = ? ORDER BY tr.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $selected_respondent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
    // Получаем имя респондента
    foreach ($expert_respondent_data as $resp) {
        if ($resp['user_id'] == $selected_respondent_id) {
            $current_respondent = $resp;
            break;
        }
    }
} elseif ($is_respondent) {
    $query = "SELECT tr.test_id, t.test_type, t.test_name, tr.result, tr.score, tr.created_at
              FROM test_results tr 
              INNER JOIN tests t ON tr.test_id = t.id 
              WHERE tr.user_id = ?
              ORDER BY tr.created_at DESC";
} elseif ($is_user) {
    $query = "SELECT tr.test_id, t.test_type, t.test_name, tr.result, tr.score, tr.created_at
              FROM test_results tr 
              INNER JOIN tests t ON tr.test_id = t.id 
              WHERE tr.user_id = ?
              ORDER BY tr.created_at DESC";
}

if (isset($query) && $query) {
    $stmt = $conn->prepare($query);
    if ($is_respondent || $is_user) {
        $stmt->bind_param("i", $user_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Создаем массив данных для таблицы "История выполнений тестов"
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();
} else {
    $data = [];
}

// Создаем массив данных для диаграммы прогресса
$progress_data = [];
foreach ($data as $row) {
    $test_name = $row['test_name'];
    $result = floatval($row['result']); // Преобразуем результат в число
    if (!isset($progress_data[$test_name])) {
        $progress_data[$test_name] = [
            'test_name' => $test_name,
            'total_attempts' => 0,
            'total_time' => 0,
        ];
    }
    $progress_data[$test_name]['total_attempts']++;
    $progress_data[$test_name]['total_time'] += $result;
}

// Добавляем проверку на наличие данных
if (empty($data)) {
    echo '<div class="results-table-container">';
    echo '<div class="results-container">';
    echo '<h2>Результаты тестов</h2>';
    echo '<div class="no-data">\n<img src="https://img.icons8.com/ios-filled/50/888888/sad.png" alt="Нет данных" style="width:48px;opacity:0.5;"><br>Нет данных для отображения.</div>';
    echo '</div></div>';
    exit;
}

// Секция общей статистики
$total_tests = count($data);
$avg_score = 0;
$score_count = 0;
$best_score = null;
foreach ($data as $row) {
    if (isset($row['score'])) {
        $avg_score += floatval($row['score']);
        $score_count++;
        if ($best_score === null || floatval($row['score']) > $best_score) {
            $best_score = floatval($row['score']);
        }
    }
}
$avg_score = $score_count ? round($avg_score / $score_count, 2) : '-';
$best_score = $best_score !== null ? round($best_score, 2) : '-';

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/test_results.css">
    <link rel="stylesheet" href="css/background.css">
    <title>Результаты тестов</title>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <style>
        body, html { background: linear-gradient(135deg, #1a1c20 0%, #0f1012 100%) !important; background-color: #0f1012 !important; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="results-table-container">
  <div class="results-container">
    <div class="header-section">
      <h1>Результаты тестов</h1>
      <div class="stats-row">
        <div class="stat-card">Всего попыток: <b><?= $total_tests ?></b></div>
        <div class="stat-card">Средняя оценка: <b><?= $avg_score ?></b></div>
        <div class="stat-card">Лучший результат: <b><?= $best_score ?></b></div>
      </div>
      <?php if ($is_expert): ?>
        <form class="respondent-select" method="get">
          <label for="respondent_id" style="color:#3498db;font-weight:500;margin-right:10px;">Респондент:</label>
          <select name="respondent_id" id="respondent_id" onchange="this.form.submit()">
            <?php foreach ($expert_respondent_data as $resp): ?>
              <option value="<?= $resp['user_id'] ?>" <?= $resp['user_id']==$selected_respondent_id?'selected':'' ?>><?= htmlspecialchars($resp['name']) ?> (<?= $resp['age'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </form>
        <div style="text-align:center;color:#888;margin-bottom:16px;">Показаны результаты для: <b><?= htmlspecialchars($current_respondent['name']) ?></b></div>
      <?php endif; ?>
    </div>
    <table class="results-table sticky-header">
      <thead><tr><th>Тип теста</th><th>Название теста</th><th>Результат (мс)</th><th>Оценка</th><th>Дата</th></tr></thead>
      <tbody>
      <?php foreach ($data as $row):
        $score_class = '';
        if (isset($row['score'])) {
          if ($row['score'] < 40) $score_class = 'low';
          elseif ($row['score'] < 70) $score_class = 'medium';
        }
      ?>
        <tr>
          <td><?= htmlspecialchars($row['test_type']) ?></td>
          <td><?= htmlspecialchars($row['test_name']) ?></td>
          <td><?= round(floatval($row['result']), 2) ?></td>
          <td class="test-score <?= $score_class ?>"><?= isset($row['score']) ? round(floatval($row['score']), 2) : '-' ?></td>
          <td><?= htmlspecialchars($row['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <h2 class="charts-title">Графики по последним 5 попыткам каждого теста</h2>
    <div class="charts-grid">
    <?php
    $tests_by_name = [];
    foreach ($data as $row) {
        $tests_by_name[$row['test_name']][] = $row;
    }
    $chart_idx = 0;
    foreach ($tests_by_name as $test_name => $attempts):
        usort($attempts, function($a, $b) {
            return strtotime($a['created_at']) - strtotime($b['created_at']);
        });
        $last_attempts = array_slice($attempts, -5);
    ?>
        <div class="chart-type-container">
            <h3><?= htmlspecialchars($test_name) ?></h3>
            <div class="chart-container" id="progress_chart_div_<?= $chart_idx ?>"></div>
        </div>
    <?php $chart_idx++; endforeach; ?>
    </div>
    <script>
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawAllCharts);
    function drawAllCharts() {
    <?php
    $chart_idx = 0;
    foreach ($tests_by_name as $test_name => $attempts):
        usort($attempts, function($a, $b) {
            return strtotime($a['created_at']) - strtotime($b['created_at']);
        });
        $last_attempts = array_slice($attempts, -5);
    ?>
        var data<?= $chart_idx ?> = new google.visualization.DataTable();
        data<?= $chart_idx ?>.addColumn('string', 'Дата');
        data<?= $chart_idx ?>.addColumn('number', 'Результат');
        <?php foreach ($last_attempts as $row): ?>
            data<?= $chart_idx ?>.addRow(['<?= $row['created_at'] ?>', <?= round($row['result']*100,2) ?>]);
        <?php endforeach; ?>
        var options<?= $chart_idx ?> = {
            title: '',
            hAxis: { title: 'Дата', titleTextStyle: {color: '#3498db'}, textStyle: {color: '#e1e1e1'} },
            vAxis: { title: 'Результат (%)', titleTextStyle: {color: '#3498db'}, textStyle: {color: '#e1e1e1'} },
            legend: {position: 'none'},
            backgroundColor: 'transparent',
            colors: ['#f1c40f'],
            pointSize: 6,
            curveType: 'function',
            chartArea: {left:50,top:30,width:'80%',height:'60%'},
            animation: {startup:true,duration:900,easing:'out'},
            lineWidth: 4,
            tooltip: {textStyle:{color:'#fff'}, showColorCode:true}
        };
        var chart<?= $chart_idx ?> = new google.visualization.LineChart(document.getElementById('progress_chart_div_<?= $chart_idx ?>'));
        chart<?= $chart_idx ?>.draw(data<?= $chart_idx ?>, options<?= $chart_idx ?>);
    <?php $chart_idx++; endforeach; ?>
    }
    </script>
  </div>
</div>
</body>
</html>
<?php $conn->close(); ?>
