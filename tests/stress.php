<?php
session_start();
require '../db_connect.php';
require '../widgets/pulse-widget/pulse-widget-include.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT role FROM users WHERE id = $user_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $role = $row['role'];
} else {
    die("Пользователь не найден.");
}

function get_pulse_data($conn, $user_id = null) {
    if ($user_id) {
        $sql = "SELECT * FROM pulse_data WHERE user_id = $user_id ORDER BY COALESCE(timestamp, '1970-01-01 00:00:00') DESC";
    } else {
        $sql = "SELECT pd.*, r.name FROM pulse_data pd JOIN respondents r ON pd.user_id = r.user_id ORDER BY COALESCE(pd.timestamp, '1970-01-01 00:00:00') DESC";
    }
    $result = $conn->query($sql);
    $pulse_data = [];
    while ($row = $result->fetch_assoc()) {
        $pulse_data[] = $row;
    }
    return $pulse_data;
}

function get_respondents($conn) {
    $sql = "SELECT u.id, r.name, r.gender, r.age FROM users u JOIN respondents r ON u.id = r.user_id";
    $result = $conn->query($sql);
    $respondents = [];
    while ($row = $result->fetch_assoc()) {
        $respondents[] = $row;
    }
    return $respondents;
}

function calculate_stress_coefficient($avg_pulse) {
    $normal_pulse = 70;
    return abs($avg_pulse - $normal_pulse);
}

function get_pulse_color($pulse) {
    if ($pulse < 60 || $pulse > 100) {
        return 'red';
    } elseif (($pulse >= 60 && $pulse <= 70) || ($pulse >= 90 && $pulse <= 100)) {
        return 'yellow';
    } else {
        return 'green';
    }
}

$pulse_data = get_pulse_data($conn, $user_id);
$respondents = get_respondents($conn);

$expert_stress = 0;
$user_stress = 0;

if ($role === 'expert') {
    $total_stress = 0;
    $count = 0;
    foreach ($pulse_data as $data) {
        $total_stress += calculate_stress_coefficient($data['avg_pulse']);
        $count++;
    }
    if ($count > 0) {
        $expert_stress = $total_stress / $count;
    }
} else {
    $total_stress = 0;
    $count = 0;
    foreach ($pulse_data as $data) {
        $total_stress += calculate_stress_coefficient($data['avg_pulse']);
        $count++;
    }
    if ($count > 0) {
        $user_stress = $total_stress / $count;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Данные Пульса</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../widgets/pulse-widget/pulse-widget.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #181e22 0%, #23282e 100%);
            color: #f3f3f3;
            font-family: 'Segoe UI', 'Roboto', 'Arial', sans-serif;
            min-height: 100vh;
        }
        main.container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 0 60px 0;
        }
        .card {
            background: rgba(34, 39, 46, 0.65);
            border-radius: 28px;
            box-shadow: 0 8px 40px 0 rgba(0,0,0,0.25), 0 1.5px 8px 0 rgba(127,215,255,0.04);
            padding: 38px 34px 28px 34px;
            margin-bottom: 2.8rem;
            backdrop-filter: blur(8px);
            border: 1.5px solid rgba(127,215,255,0.10);
        }
        h1, h2, h3 {
            color: #7fd7ff;
            font-weight: 800;
            margin-bottom: 1.2rem;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 1.5rem;
            background: rgba(0, 0, 0, 0.92);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 2px 16px rgba(0,0,0,0.13);
            font-size: 1.08em;
        }
        th, td {
            padding: 16px 12px;
            text-align: center;
        }
        th {
            background: #1a232b;
            color: #7fd7ff;
            font-weight: 700;
            font-size: 1.13em;
        }
        tr:nth-child(even) td {
            background: #23282e;
        }
        tr:nth-child(odd) td {
            background: #181e22;
        }
        tr:hover td {
            background: #2a2f38;
            transition: background 0.18s;
        }
        .red { color: #ff5c5c; font-weight: 700; }
        .yellow { color: #ffe066; font-weight: 700; }
        .green { color: #7fff7f; font-weight: 700; }
        input[type="submit"], button {
            background: linear-gradient(90deg,#7fd7ff 60%,#7fff7f 100%);
            color: #222;
            font-weight: 700;
            border: none;
            border-radius: 1.2em;
            padding: 0.7em 2.2em;
            font-size: 1.15em;
            box-shadow: 0 2px 16px #7fd7ff44;
            transition: background 0.2s, box-shadow 0.2s;
            margin-top: 1em;
        }
        input[type="submit"]:hover, button:hover {
            background: linear-gradient(90deg,#7fff7f 60%,#7fd7ff 100%);
            color: #111;
            box-shadow: 0 4px 24px #7fff7f44;
        }
        select, input[type="number"] {
            background: #23282e;
            color: #f3f3f3;
            border: 2px solid #7fd7ff;
            border-radius: 1em;
            padding: 0.5em 1em;
            font-size: 1.1em;
            margin-right: 1em;
            margin-bottom: 1em;
        }
        .checkbox-container {
            display: inline-block;
            margin: 0.5em 1em 0.5em 0;
            background: rgba(34,39,46,0.85);
            border-radius: 1em;
            padding: 0.5em 1.2em;
            color: #7fd7ff;
            font-weight: 600;
            box-shadow: 0 2px 8px #7fd7ff22;
        }
        @media (max-width: 700px) {
            .card { padding: 1.2rem; }
            th, td { padding: 0.7rem 0.5rem; }
        }
    </style>
</head>
<body>
<?php include '../header.php'; ?>
<main class="container">
  <div class="card">
    <div id="pulse-widget-container"></div>
<div class="card" style="margin-bottom:2rem;">
  <h2>Как работает анализ стресса</h2>
  <ul>
    <li><b>Виджет пульса</b> — на любой странице можно запустить виджет, который будет собирать и отображать ваш пульс в реальном времени, а также сохранять результаты для анализа.</li>
    <li><b>Анализ стресса</b> — на этой странице вы видите свой коэффициент стресса на основе биосигналов (пульса), а также можете сравнить себя с другими респондентами. Эксперты могут фильтровать и сравнивать результаты по полу и возрасту.</li>
    <li><b>Личный кабинет</b> — здесь вы можете посмотреть свою статистику, результаты тестов и биосигналов.</li>
    <li><b>Навигация</b> — используйте меню сверху для перехода между разделами.</li>
  </ul>
</div>
    <div class="container">
        <h1>Данные Пульса</h1>
        <?php if ($role === 'expert'): ?>
            <h2>Результаты Эксперта</h2>
            <?php if (empty($pulse_data)): ?>
                <p>Нет данных для отображения.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Максимальный Пульс</th>
                            <th>Минимальный Пульс</th>
                            <th>Коэффициент стресса</th>
                            <th>Время записи (сек)</th>
                            <th>Дата Записи</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pulse_data as $data): ?>
                            <tr>
                                <td class="<?php echo get_pulse_color($data['max_pulse']); ?>"><?php echo htmlspecialchars($data['max_pulse']); ?></td>
                                <td class="<?php echo get_pulse_color($data['min_pulse']); ?>"><?php echo htmlspecialchars($data['min_pulse']); ?></td>
                                <td class="<?php echo get_pulse_color($data['avg_pulse']); ?>"><?php echo htmlspecialchars(calculate_stress_coefficient($data['avg_pulse'])); ?></td>
                                <td><?php echo htmlspecialchars($data['time_recorded']); ?></td>
                                <td><?php echo htmlspecialchars($data['timestamp']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p>Коэффициент стресса: <?php echo htmlspecialchars(round($expert_stress, 2)); ?></p>
            <?php endif; ?>

            <h2>Фильтрация респондентов</h2>
            <form id="filter-form">
                <label for="gender">Пол:</label>
                <select id="gender" name="gender">
                    <option value="">Все</option>
                    <option value="Male">Мужской</option>
                    <option value="Female">Женский</option>
                </select>

                <label for="age">Возраст:</label>
                <select id="age" name="age">
                    <option value="">Все</option>
                    <?php for ($i = 18; $i <= 65; $i += 5): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?> - <?php echo $i + 4; ?></option>
                    <?php endfor; ?>
                </select>
            </form>

            <h2>Выберите респондентов для просмотра результатов:</h2>
            <form id="respondent-form" method="post" action="">
                <div id="respondent-select"></div>
                <input type="submit" value="Посмотреть результаты">
            </form>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['respondent_ids'])) {
                $selected_respondent_ids = $_POST['respondent_ids'];
                if (count($selected_respondent_ids) > 0) {
                    $selected_data = [];
                    $stress_coefficients = [];
                    foreach ($selected_respondent_ids as $selected_respondent_id) {
                        $respondent_data = get_pulse_data($conn, $selected_respondent_id);
                        $selected_data[$selected_respondent_id] = $respondent_data;
                        $total_stress = 0;
                        $count = 0;
                        foreach ($respondent_data as $data) {
                            $total_stress += calculate_stress_coefficient($data['avg_pulse']);
                            $count++;
                        }
                        if ($count > 0) {
                            $stress_coefficient = $total_stress / $count;
                        } else {
                            $stress_coefficient = 0;
                        }
                        $stress_coefficients[] = [
                            'id' => $selected_respondent_id,
                            'name' => $respondents[array_search($selected_respondent_id, array_column($respondents, 'id'))]['name'],
                            'stress_coefficient' => $stress_coefficient,
                        ];
                    }

                    // Sort selected respondents by stress coefficient
                    usort($stress_coefficients, function($a, $b) {
                        return $a['stress_coefficient'] <=> $b['stress_coefficient'];
                    });

                    if (count($selected_respondent_ids) > 1) {
                    ?>
                    <h2>Топ респондентов по психической устойчивости</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Имя</th>
                                <th>Коэффициент стресса</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stress_coefficients as $respondent): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($respondent['name']); ?></td>
                                    <td><?php echo htmlspecialchars(round($respondent['stress_coefficient'], 2)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php
                    }
                    ?>

                    <h2>Результаты респондентов</h2>
                    <?php foreach ($selected_data as $respondent_id => $data): ?>
                        <h3><?php echo htmlspecialchars($respondents[array_search($respondent_id, array_column($respondents, 'id'))]['name']); ?></h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Максимальный Пульс</th>
                                    <th>Минимальный Пульс</th>
                                    <th>Коэффициент стресса</th>
                                    <th>Время записи (сек)</th>
                                    <th>Дата Записи</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $pulse): ?>
                                    <tr>
                                        <td class="<?php echo get_pulse_color($pulse['max_pulse']); ?>"><?php echo htmlspecialchars($pulse['max_pulse']); ?></td>
                                        <td class="<?php echo get_pulse_color($pulse['min_pulse']); ?>"><?php echo htmlspecialchars($pulse['min_pulse']); ?></td>
                                        <td class="<?php echo get_pulse_color($pulse['avg_pulse']); ?>"><?php echo htmlspecialchars(calculate_stress_coefficient($pulse['avg_pulse'])); ?></td>
                                        <td><?php echo htmlspecialchars($pulse['time_recorded']); ?></td>
                                        <td><?php echo htmlspecialchars($pulse['timestamp']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p>Коэффициент стресса: <?php echo htmlspecialchars(round(array_column($stress_coefficients, 'stress_coefficient', 'id')[$respondent_id], 2)); ?></p>
                    <?php endforeach; ?>

                    <?php if (count($selected_respondent_ids) > 1): ?>
                        <h2>Сравнение респондентов</h2>
                        <div class="chart-container">
                            <canvas id="comparisonChart"></canvas>
                        </div>
                        <script>
                            const ctx = document.getElementById('comparisonChart').getContext('2d');
                            const comparisonChart = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: <?php echo json_encode(array_column($stress_coefficients, 'name')); ?>,
                                    datasets: [{
                                        label: 'Коэффициент стресса',
                                        data: <?php echo json_encode(array_column($stress_coefficients, 'stress_coefficient')); ?>,
                                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                        borderColor: 'rgba(75, 192, 192, 1)',
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    scales: {
                                        y: {
                                            beginAtZero: true
                                        }
                                    }
                                }
                            });
                        </script>
                    <?php endif; ?>
                <?php } else { ?>
                    <p>Пожалуйста, выберите хотя бы одного респондента.</p>
                <?php } ?>
            <?php } ?>
        <?php else: ?>
            <h2>Ваши данные пульса</h2>
            <?php if (empty($pulse_data)): ?>
                <p>Нет данных для отображения.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Максимальный Пульс</th>
                            <th>Минимальный Пульс</th>
                            <th>Коэффициент стресса</th>
                            <th>Время записи (сек)</th>
                            <th>Дата Записи</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pulse_data as $data): ?>
                            <tr>
                                <td class="<?php echo get_pulse_color($data['max_pulse']); ?>"><?php echo htmlspecialchars($data['max_pulse']); ?></td>
                                <td class="<?php echo get_pulse_color($data['min_pulse']); ?>"><?php echo htmlspecialchars($data['min_pulse']); ?></td>
                                <td class="<?php echo get_pulse_color($data['avg_pulse']); ?>"><?php echo htmlspecialchars(calculate_stress_coefficient($data['avg_pulse'])); ?></td>
                                <td><?php echo htmlspecialchars($data['time_recorded']); ?></td>
                                <td><?php echo htmlspecialchars($data['timestamp']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p>Коэффициент стресса: <?php echo htmlspecialchars(round($user_stress, 2)); ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        const respondents = <?php echo json_encode($respondents); ?>;
        const respondentSelect = document.getElementById('respondent-select');

        function filterRespondents() {
            const genderElem = document.getElementById('gender');
            const ageElem = document.getElementById('age');
            const gender = genderElem ? genderElem.value : '';
            const age = ageElem ? ageElem.value : '';
            if (!respondentSelect) return;
            const filteredRespondents = respondents.filter(respondent => {
                let genderMatch = !gender || respondent.gender === gender;
                let ageMatch = !age || (respondent.age >= age && respondent.age < parseInt(age) + 5);
                return genderMatch && ageMatch;
            });
            respondentSelect.innerHTML = '';
            if (filteredRespondents.length > 0) {
                const selectAllDiv = document.createElement('div');
                selectAllDiv.classList.add('checkbox-container');
                const selectAllCheckbox = document.createElement('input');
                selectAllCheckbox.type = 'checkbox';
                selectAllCheckbox.id = 'select_all';
                selectAllCheckbox.onclick = selectAllRespondents;
                const selectAllLabel = document.createElement('label');
                selectAllLabel.htmlFor = 'select_all';
                selectAllLabel.textContent = 'Выбрать всех';
                selectAllDiv.appendChild(selectAllCheckbox);
                selectAllDiv.appendChild(selectAllLabel);
                respondentSelect.appendChild(selectAllDiv);
            }
            filteredRespondents.forEach(respondent => {
                const checkboxContainer = document.createElement('div');
                checkboxContainer.classList.add('checkbox-container');
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'respondent_ids[]';
                checkbox.value = respondent.id;
                checkbox.id = 'respondent_' + respondent.id;
                const label = document.createElement('label');
                label.htmlFor = 'respondent_' + respondent.id;
                label.textContent = respondent.name;
                checkboxContainer.appendChild(checkbox);
                checkboxContainer.appendChild(label);
                respondentSelect.appendChild(checkboxContainer);
            });
        }

        function selectAllRespondents() {
            const selectAllCheckbox = document.getElementById('select_all');
            const checkboxes = document.querySelectorAll('#respondent-select input[type="checkbox"]:not(#select_all)');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }

        const filterForm = document.getElementById('filter-form');
        if (filterForm) {
            filterForm.addEventListener('change', function(e) {
            e.preventDefault();
            filterRespondents();
        });
        }

        document.addEventListener('DOMContentLoaded', filterRespondents);
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new PulseWidget('pulse-widget-container');
        });
    </script>
  </div>
</main>
</body>
</html>

<?php
$conn->close();
?>
