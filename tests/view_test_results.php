<?php
session_start();
require_once "../db-connect.php";
require_once "../header.php";

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
        
        // Отладочная информация
        echo "User role: " . $role . "<br>";
        echo "Is expert: " . ($is_expert ? 'true' : 'false') . "<br>";
        echo "Is respondent: " . ($is_respondent ? 'true' : 'false') . "<br>";
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
    $query = "SELECT tr.user_id, tr.result, tr.created_at, t.test_type, t.test_name, r.name AS respondent_name, r.age AS respondent_age
              FROM test_results tr 
              INNER JOIN tests t ON tr.test_id = t.id 
              INNER JOIN respondents r ON tr.user_id = r.user_id
              WHERE EXISTS (
                  SELECT 1 FROM test_results tr2 WHERE tr2.user_id = r.user_id
              )
              ORDER BY r.name";
} elseif ($is_respondent) {
    $query = "SELECT tr.result, tr.created_at, t.test_type, t.test_name
              FROM test_results tr 
              INNER JOIN tests t ON tr.test_id = t.id 
              WHERE tr.user_id = ?
              ORDER BY tr.created_at DESC";
}

// Проверяем, определена ли переменная $query
if (!isset($query)) {
    die("Ошибка: Не удалось определить тип пользователя или запрос не сформирован.");
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
    $result = $row['result'];
    if (!isset($progress_data[$test_name])) {
        $progress_data[$test_name] = [
            'test_name' => $test_name,
            'total_attempts' => 0,
            'total_time' => 0,
        ];
    }
    $progress_data[$test_name]['total_attempts']++;
    $progress_data[$test_name]['total_time'] += $result; // Суммируем время реакции
}
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
        }
     
        .container {
            margin-top: 60px; /* Отступ для контейнера */
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #000; /* Черный фон для заголовка таблицы */
            color: white; /* Белый текст для заголовка таблицы */
        }
        h2, h3, h4 {
            margin-top: 20px;
        }
        .chart-container {
            width: 900px;
            height: 500px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<header>
    <p><a href="tests.php">Назад</a></p>
    <p><a href="../index.php" style="color: white;">Домой</a></p>
    <?php if (isset($_SESSION['username'])): ?>
        <p><a href="../account.php" style="color: white;">Личный кабинет</a></p>
    <?php endif; ?>
</header>
<div class="container">
<?php if (!isset($_SESSION['user_id']) && isset($_SESSION['guest_results']) && !empty($_SESSION['guest_results'])): ?>
    <h2>Ваши результаты (гость)</h2>
    <table>
        <thead>
            <tr>
                <th>Название теста</th>
                <th>Результат</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($_SESSION['guest_results'] as $test => $result): ?>
            <tr>
                <td><?= htmlspecialchars($test) ?></td>
                <td><?= htmlspecialchars($result) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (isset($_SESSION['guest_visual_count_test'])): ?>
            <tr>
                <td>Тест на скорость сложения в уме</td>
                <td><?= htmlspecialchars($_SESSION['guest_visual_count_test']) ?> сек.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>

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
                    <td><?= $row_respondent_results['result'] ?></td>
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
                    'result' => $row_respondent_results['result']
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
            <td><?= $row['result'] ?></td>
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
                ?>
                        data.addRow(['<?= $row['created_at'] ?>', <?= $row['result'] ?>]);
                <?php
                    endif;
                endforeach;
                ?>

                var options = {
                    title: '<?= $test['test_name'] ?>:',
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

<script type="text/javascript">
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawCharts);

    function drawCharts() {
        // Создаем данные для диаграммы прогресса
        var progressData = new google.visualization.DataTable();
        progressData.addColumn('string', 'Тест');
        progressData.addColumn('number', 'Среднее время реакции (сек)');

        <?php
        foreach ($progress_data as $test) {
            $avg_time = $test['total_time'] / $test['total_attempts'];
            $test_name = str_replace('visual-count-test', 'Тест на скорость сложения в уме', $test['test_name']);
            echo "progressData.addRow(['".addslashes($test_name)."', ".$avg_time."]);";
        }
        ?>

        var options = {
            title: 'Среднее время реакции по тестам',
            hAxis: {title: 'Тест'},
            vAxis: {title: 'Время (сек)'},
            legend: 'none'
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('progress_chart'));
        chart.draw(progressData, options);
    }
</script>
</div>
</body>
</html>
