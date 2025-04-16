<?php
session_start();
require_once "db-connect.php";

// Получаем user_id и роль из сессии
$user_id = $_SESSION['user_id'];
$is_expert = false;
$is_respondent = false;

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
    }
}

// Если пользователь эксперт, получаем данные всех респондентов
$expert_respondent_data = [];
if ($is_expert) {
    $query_respondents = "SELECT name, age, user_id FROM respondents";
    $result_respondents = $conn->query($query_respondents);

    // Получаем данные всех респондентов
    while ($row_respondent = $result_respondents->fetch_assoc()) {
        $expert_respondent_data[] = $row_respondent;
    }
}

// Если пользователь респондент, получаем его данные
$respondent_data = [];
if ($is_respondent) {
    $query_respondent = "SELECT name, age FROM respondents WHERE user_id = ?";
    $stmt_respondent = $conn->prepare($query_respondent);
    $stmt_respondent->bind_param("i", $user_id);
    $stmt_respondent->execute();
    $result_respondent = $stmt_respondent->get_result();

    // Получаем данные респондента
    $respondent_data = $result_respondent->fetch_assoc();

    $stmt_respondent->close();
}

// Если пользователь эксперт, выполняем запрос для извлечения результатов тестов для всех респондентов
// Если пользователь респондент, выполняем запрос только для его результатов тестов
if ($is_expert) {
    $query = "SELECT tr.test_id, t.test_type, t.test_name, tr.result, tr.score, tr.created_at, r.name AS respondent_name, r.age AS respondent_age
              FROM test_results tr 
              INNER JOIN tests t ON tr.test_id = t.id 
              INNER JOIN respondents r ON tr.user_id = r.user_id
              WHERE EXISTS (
                  SELECT 1 FROM test_results tr2 WHERE tr2.user_id = r.user_id
              )
              ORDER BY r.name";
} elseif ($is_respondent) {
    $query = "SELECT tr.test_id, t.test_type, t.test_name, tr.result, tr.score, tr.created_at
              FROM test_results tr 
              INNER JOIN tests t ON tr.test_id = t.id 
              WHERE tr.user_id = ?
              ORDER BY tr.created_at DESC";
}

$stmt = $conn->prepare($query);
if ($is_respondent) {
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
    echo '<div class="container">';
    echo '<h2>Результаты тестов</h2>';
    echo '<p>У вас пока нет результатов тестов.</p>';
    echo '</div>';
    exit;
}

// Отображаем результаты тестов
echo '<div class="container">';
echo '<h2>Результаты тестов</h2>';
echo '<table>';
echo '<tr><th>Тип теста</th><th>Название теста</th><th>Результат (мс)</th><th>Дата</th></tr>';
foreach ($data as $row) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($row['test_type']) . '</td>';
    echo '<td>' . htmlspecialchars($row['test_name']) . '</td>';
    echo '<td>' . round(floatval($row['result']), 2) . '</td>';
    echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';
    echo '</tr>';
}
echo '</table>';
echo '</div>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/view_test_results.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/background.css">
    <title>Результаты тестов</title>
    <!-- Подключаем библиотеку Google Charts -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #1a1a1a 0%, #0a0a0a 100%);
            color: white;
        }

        header {
            background: rgba(0, 0, 0, 0.8);
            padding: 15px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo a {
            color: #ffd700;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
            transition: color 0.3s;
        }

        .logo a:hover {
            color: #fff;
        }

        nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        nav ul li {
            margin-left: 20px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s;
        }

        nav ul li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffd700;
        }

        .container {
            margin-top: 60px;
            padding: 20px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        th {
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            font-weight: bold;
        }

        tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        h2, h3, h4 {
            margin-top: 30px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        h2 {
            font-size: 2em;
            margin-bottom: 20px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 10px;
        }

        h3 {
            font-size: 1.5em;
            color: #ffd700;
        }

        h4 {
            font-size: 1.2em;
            color: #ddd;
        }

        .chart-container {
            width: 100%;
            height: 400px;
            margin-bottom: 40px;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .no-results {
            text-align: center;
            padding: 50px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            margin: 20px 0;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .chart-container {
                height: 300px;
            }

            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
<header>
    <div class="header-container">
        <div class="logo">
            <a href="../index.php">OPDLab</a>
        </div>
        <nav>
            <ul>
                <li><a href="../index.php">Главная</a></li>
                <li><a href="../tests.php">Тесты</a></li>
                <li><a href="../account.php">Личный кабинет</a></li>
                <?php if (isset($_SESSION['username'])): ?>
                    <li><a href="../logout.php">Выйти</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<div class="container">
<?php if ($is_expert && !empty($expert_respondent_data)): ?>
    <?php foreach ($expert_respondent_data as $respondent): ?>
        <?php
        // Получаем имя пользователя из таблицы пользователей
        $query_username = "SELECT username FROM users WHERE id = ?";
        $stmt_username = $conn->prepare($query_username);
        $stmt_username->bind_param("i", $respondent['user_id']);
        $stmt_username->execute();
        $result_username = $stmt_username->get_result();
        $row_username = $result_username->fetch_assoc();
        $username = $row_username['username'];
        $stmt_username->close();

        // Запрос для получения результатов тестов для данного респондента
        $query_respondent_results = "SELECT tr.test_id, t.test_type, t.test_name, tr.result, tr.created_at
                                     FROM test_results tr 
                                     INNER JOIN tests t ON tr.test_id = t.id 
                                     WHERE tr.user_id = ?";
        $stmt_respondent_results = $conn->prepare($query_respondent_results);
        $stmt_respondent_results->bind_param("i", $respondent['user_id']);
        $stmt_respondent_results->execute();
        $result_respondent_results = $stmt_respondent_results->get_result();
        ?>
        <h4>Имя пользователя: <?= $username ?>
            <br>Имя респондента: <?= $respondent['name'] ?>
            <br>Возраст респондента: <?= $respondent['age'] ?></h4>
        <h2>История выполнений тестов</h2>
        <br>
        <table>
            <thead>
            <tr>
                <th>Тип теста</th>
                <th>Название теста</th>
                <th>Результат</th>
                <th>Дата выполнения</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row_respondent_results = $result_respondent_results->fetch_assoc()): ?>
                <tr>
                    <td><?= $row_respondent_results['test_type'] ?></td>
                    <td><?= $row_respondent_results['test_name'] ?></td>
                    <td><?= round($row_respondent_results['result'] * 100) . '%' ?></td>
                    <td><?= $row_respondent_results['created_at'] ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php 
        // Создадим массив, чтобы хранить результаты для каждого теста
        $test_results = array();
        $result_respondent_results->data_seek(0); // Сбрасываем указатель результата запроса
        while ($row_respondent_results = $result_respondent_results->fetch_assoc()): 
            // Если у теста есть результат, сохраняем его
            if ($row_respondent_results['result'] !== null) {
                $test_results[$row_respondent_results['test_name']][] = array(
                    'date' => $row_respondent_results['created_at'],
                    'result' => $row_respondent_results['result'] * 100
                );
            }
        endwhile; ?>

        <?php 
        // Для каждого теста, у которого есть результат, строим график
        foreach ($test_results as $test_name => $test_data): ?>
            <h3><?= $test_name ?></h3>
            <div id="progress_chart_div_<?= md5($test_name . $respondent['name']) ?>" class="chart-container"></div>
            <script>
                google.charts.load('current', {'packages':['corechart']});
                google.charts.setOnLoadCallback(drawChart<?= md5($test_name . $respondent['name']) ?>);

                function drawChart<?= md5($test_name . $respondent['name']) ?>() {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', 'Дата выполнения');
                    data.addColumn('number', 'Результат');
                    
                    <?php foreach ($test_data as $result): ?>
                        data.addRow(['<?= $result['date'] ?>', <?= $result['result'] ?>]);
                    <?php endforeach; ?>

                    var options = {
                        title: '<?= $test_name ?>',
                        titleTextStyle: {color: 'white', fontSize: 20, bold: true},
                        hAxis: {
                            title: 'Дата выполнения',
                            titleTextStyle: {color: 'white'},
                            textStyle: {color: 'white', fontSize: 16}
                        },
                        vAxis: {
                            title: 'Результат',
                            titleTextStyle: {color: 'white'},
                            textStyle: {color: 'white', fontSize: 16}
                        },
                        legend: {position: 'none'},
                        backgroundColor: 'transparent',
                        colors: ['yellow']
                    };
                    var chartDiv = document.getElementById('progress_chart_div_<?= md5($test_name . $respondent['name']) ?>');
                    chartDiv.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
                    chartDiv.style.backdropFilter = 'blur(10px)';
                    chartDiv.style.borderRadius = '10px';

                    var chart = new google.visualization.LineChart(chartDiv);
                    chart.draw(data, options);
                }
            </script>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php else: ?>
    <p>Нет доступных результатов тестов для отображения.</p>
<?php endif; ?>


<?php if ($is_respondent): ?>
<h2>История выполнений тестов</h2>
<br>
<table>
    <thead>
    <tr>
        <th>Тип теста</th>
        <th>Название теста</th>
        <th>Результат</th>
        <th>Дата выполнения</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($data as $row): ?>
        <tr>
            <td><?= $row['test_type'] ?></td>
            <td><?= $row['test_name'] ?></td>
            <td><?= round($row['result'] * 100) . '%' ?></td>
            <td><?= $row['created_at'] ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<br>
<h2>Прогресс выполнения тестов по имени</h2>
<br>
<?php
// Группировка данных по категориям тестов с проверкой наличия пройденных тестов
$tests_by_category = [];
foreach ($data as $row) {
    $test_type = $row['test_type'];
    if (!isset($tests_by_category[$test_type])) {
        $tests_by_category[$test_type] = [];
    }
    $tests_by_category[$test_type][] = $row;
}
?>
    <?php foreach ($tests_by_category as $test_type => $tests): ?>
    <?php
    // Проверка наличия пройденных тестов в текущей категории
    $has_passed_tests = false;
    foreach ($tests as $test) {
        if ($test['result'] !== null) {
            $has_passed_tests = true;
            break;
        }
    }
    if (!$has_passed_tests) {
        continue; // Пропустить вывод категории, если нет пройденных тестов
    }
    ?>
    <h3><?= $test_type ?></h3>
    <?php
    // Массив для отслеживания уже выведенных тестов
    $printed_tests = [];
    foreach ($tests as $test):
    
    if ($test['result'] !== null && !in_array($test['test_name'], $printed_tests)):
        // Добавляем название теста в массив выведенных тестов
        $printed_tests[] = $test['test_name'];
        ?>
        <div id="progress_chart_div_<?= $test['test_id'] ?>" class="chart-container"></div>

        <script>
            google.charts.load('current', {'packages':['corechart']});
            google.charts.setOnLoadCallback(drawChart<?= $test['test_id'] ?>);

            function drawChart<?= $test['test_id'] ?>() {
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Дата выполнения');
                data.addColumn('number', 'Результат');

                <?php
                // Добавляем только данные для текущего теста
                foreach ($tests as $row):
                    if ($row['test_name'] === $test['test_name']):
                        $result_value = $row['result'] * 100;
                ?>
                        data.addRow(['<?= $row['created_at'] ?>', <?= $result_value ?>]);
                <?php
                    endif;
                endforeach;
                ?>

                var options = {
                    title: '<?= $test['test_name'] ?>',
                    titleTextStyle: {color: 'white', fontSize: 20, bold: true},
                    hAxis: {
                        title: 'Дата выполнения',
                        titleTextStyle: {color: 'white'},
                        textStyle: {color: 'white', fontSize: 16}
                    },
                    vAxis: {
                        title: 'Результат',
                        titleTextStyle: {color: 'white'},
                        textStyle: {color: 'white', fontSize: 16}
                    },
                    legend: {position: 'none'},
                    backgroundColor: 'transparent',
                    colors: ['yellow']
                };
                var chartDiv = document.getElementById('progress_chart_div_<?= $test['test_id'] ?>');
                chartDiv.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
                chartDiv.style.backdropFilter = 'blur(10px)';
                chartDiv.style.borderRadius = '10px';

                var chart = new google.visualization.LineChart(chartDiv);
                chart.draw(data, options);
            }
        </script>
<?php
    endif;
endforeach;
?>

<?php endforeach; ?>
<?php endif; ?>
</div>
</body>
</html>

<?php
$conn->close();
?>
