<?php
session_start();
require_once "db-connect.php";

function get_pvk_name($conn, $pvk_id) {
    $sql = "SELECT name FROM pvk WHERE id = $pvk_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['name'];
    } else {
        return "Неизвестное ПВК";
    }
}

function calculate_suitability($conn, $user_id, $profession_id) {
    // Получаем критерии для профессии
    $criteria_sql = "SELECT * FROM evaluation_criteria WHERE profession_id = $profession_id";
    $criteria_result = $conn->query($criteria_sql);
    $criteria = [];
    while ($row = $criteria_result->fetch_assoc()) {
        $criteria[] = $row;
    }

    if (empty($criteria)) {
        return ["pvk_data" => [], "overall_score" => 0];
    }

    $total_score = 0;
    $total_weight = 0;
    $pvk_data = [];

    foreach ($criteria as $crit) {
        $crit_id = $crit['id'];
        $crit_name = $crit['name'];
        $crit_weight = isset($crit['weight']) ? $crit['weight'] : 1;

        // Получаем связанные ПВК
        $pvk_sql = "SELECT pvk_id FROM criteria_pvk WHERE criteria_id = $crit_id";
        $pvk_result = $conn->query($pvk_sql);
        $pvk_ids = [];
        while ($row = $pvk_result->fetch_assoc()) {
            $pvk_ids[] = $row['pvk_id'];
        }

        // Получаем связанные тесты и параметры
        $test_sql = "SELECT * FROM test_criteria WHERE criteria_name = '" . $conn->real_escape_string($crit_name) . "'";
        $test_result = $conn->query($test_sql);
        $tests = [];
        while ($row = $test_result->fetch_assoc()) {
            $tests[] = $row;
        }

        // Получаем результаты пользователя по этим тестам
        $test_ids = array_column($tests, 'test_id');
        if (empty($test_ids)) continue;
        $test_ids_str = implode(',', $test_ids);
        $results_sql = "SELECT test_id, result FROM test_results WHERE user_id = $user_id AND test_id IN ($test_ids_str)";
        $results_result = $conn->query($results_sql);
        $user_results = [];
        while ($row = $results_result->fetch_assoc()) {
            $user_results[$row['test_id']] = $row['result'];
        }

        $crit_score = 0;
        $crit_weight_sum = 0;
        $cutoff_failed = false;

        foreach ($tests as $test) {
            $test_id = $test['test_id'];
            $weight = isset($test['weight']) ? floatval($test['weight']) : 1;
            $direction = isset($test['direction']) ? $test['direction'] : 'asc';
            $cutoff = isset($test['cutoff']) ? $test['cutoff'] : null;
            if (!isset($user_results[$test_id])) continue;
            $value = floatval($user_results[$test_id]);
            $score = ($direction === 'desc') ? (100 - $value) : $value;
            // Проверка порога (cutoff)
            if ($cutoff !== null) {
                if ($direction === 'desc' && $value > $cutoff) $cutoff_failed = true;
                if ($direction === 'asc' && $value < $cutoff) $cutoff_failed = true;
            }
            $crit_score += $score * $weight;
            $crit_weight_sum += $weight;
        }
        if ($crit_weight_sum > 0) {
            $crit_score = $crit_score / $crit_weight_sum;
        } else {
            $crit_score = 0;
        }
        if ($cutoff_failed) $crit_score = 0;
        // Для вывода — берём название ПВК (если есть связь)
        $pvk_name = '';
        if (!empty($pvk_ids)) {
            $pvk_id = $pvk_ids[0];
            $pvk_name_sql = "SELECT name FROM pvk WHERE id = $pvk_id";
            $pvk_name_result = $conn->query($pvk_name_sql);
            if ($pvk_name_result->num_rows > 0) {
                $pvk_name = $pvk_name_result->fetch_assoc()['name'];
            } else {
                $pvk_name = $crit_name;
            }
        } else {
            $pvk_name = $crit_name;
        }
        $rating_color_class = $crit_score < 40 ? 'low' : ($crit_score < 70 ? 'medium' : 'high');
        $pvk_data[] = [
            "name" => $pvk_name,
            "pvk_id" => $pvk_ids[0] ?? null,
            "average_rating" => round($crit_score, 2),
            "rating_color_class" => $rating_color_class
        ];
        $total_score += $crit_score * $crit_weight;
        $total_weight += $crit_weight;
    }
    usort($pvk_data, function ($a, $b) {
        return $b['average_rating'] <=> $a['average_rating'];
    });
    $overall_score = $total_weight > 0 ? round($total_score / $total_weight, 2) : 0;
    return ["pvk_data" => $pvk_data, "overall_score" => $overall_score];
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT role, respondent_id FROM users WHERE id = $user_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $role = $row['role'];
    $respondent_id = $row['respondent_id'];
} else {
    die("Пользователь не найден.");
}

$respondent_name = "";
if ($role == 'respondent') {
    $sql = "SELECT name FROM respondents WHERE id = $respondent_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $respondent_name = $result->fetch_assoc()['name'];
    } else {
        die("Респондент не найден.");
    }
} elseif ($role == 'expert') {
    $sql = "SELECT id, name, gender, age FROM respondents";
    $result = $conn->query($sql);
    $respondents = [];
    while ($row = $result->fetch_assoc()) {
        $respondents[$row['id']] = $row;
    }

    $sql = "SELECT name FROM experts WHERE id = (SELECT expert_id FROM users WHERE id = $user_id)";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $expert_name = $result->fetch_assoc()['name'];
    }
}

$professions = [
    1 => 'Аналитик',
    10 => 'Frontend-разработчик',
    11 => 'Backend-разработчик'
];

function get_all_professions_suitability($conn, $user_id) {
    global $professions;
    $profession_data = [];

    foreach ($professions as $profession_id => $profession_name) {
        $suitability_data = calculate_suitability($conn, $user_id, $profession_id);
        $profession_data[] = [
            "name" => $profession_name,
            "overall_score" => $suitability_data['overall_score'],
            "pvk_data" => $suitability_data['pvk_data']
        ];
    }

    usort($profession_data, function ($a, $b) {
        return $b['overall_score'] <=> $a['overall_score'];
    });

    return $profession_data;
}

function get_age_categories($ages) {
    $categories = [];
    foreach ($ages as $age) {
        $min_age = floor($age / 5) * 5;
        $max_age = $min_age + 4;
        $category = "$min_age-$max_age";
        if (!in_array($category, $categories)) {
            $categories[] = $category;
        }
    }
    return $categories;
}

$respondents = $respondents ?? [];

$ages = array_unique(array_column($respondents, 'age'));
sort($ages);
$age_categories = get_age_categories($ages);

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Пригодность респондента</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', 'Roboto', 'Arial', sans-serif;
            background: #f4f8fb;
            color: #222;
        }
        main.container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 0 60px 0;
        }
        .card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(52,152,219,0.07), 0 1.5px 6px rgba(0,0,0,0.03);
            padding: 2.5rem 2.5rem 2rem 2.5rem;
            margin-bottom: 2.5rem;
        }
        h1, h2, h3, h4 {
            font-weight: 700;
            color: #3498db;
            margin-bottom: 1.2rem;
        }
        h1 { font-size: 2.3rem; }
        h2 { font-size: 1.7rem; }
        h3 { font-size: 1.3rem; }
        h4 { font-size: 1.1rem; }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 1.5rem;
            background: #fafdff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(52,152,219,0.04);
        }
        th, td {
            padding: 1rem 1.2rem;
            text-align: left;
        }
        th {
            background: #eaf6fb;
            color: #3498db;
            font-weight: 600;
            border-bottom: 2px solid #d0e6f7;
        }
        tr:not(:last-child) td {
            border-bottom: 1px solid #f0f4f8;
        }
        .rating-badge {
            display: inline-block;
            min-width: 48px;
            padding: 0.4em 0.9em;
            border-radius: 1.2em;
            font-weight: 600;
            font-size: 1.1em;
            text-align: center;
            background: #eaf6fb;
            color: #3498db;
            box-shadow: 0 1px 4px rgba(52,152,219,0.08);
        }
        .rating-badge.low { background: #ffeaea; color: #e74c3c; }
        .rating-badge.medium { background: #fffbe5; color: #f1c40f; }
        .rating-badge.high { background: #eafbe7; color: #27ae60; }
        .overall-score {
            font-size: 1.5em;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(90deg, #3498db 60%, #27ae60 100%);
            border-radius: 1.2em;
            padding: 0.5em 1.5em;
            display: inline-block;
            margin: 1.2em 0 0.5em 0;
            box-shadow: 0 2px 8px rgba(52,152,219,0.10);
        }
        .profession-block {
            margin-bottom: 2.5rem;
        }
        .pvk-title {
            font-weight: 600;
            color: #222;
        }
        .pvk-table th, .pvk-table td {
            padding: 0.8em 1em;
        }
        .pvk-table th {
            background: #f0f7fa;
        }
        .pvk-table tr:nth-child(even) td {
            background: #f8fbfd;
        }
        .info-section {
            background: #fafdff;
            border-left: 5px solid #3498db;
            padding: 1.5em 2em;
            border-radius: 12px;
            margin-bottom: 2.5rem;
        }
        .fa-info-circle {
            color: #3498db;
            margin-right: 0.5em;
        }
        @media (max-width: 900px) {
            main.container { padding: 1rem; }
            .card { padding: 1.2rem; }
            th, td { padding: 0.7rem 0.5rem; }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<main class="container">
<div class="info-section">
  <i class="fa fa-info-circle"></i>
  <b>Как работает система:</b> <br>
  <ul style="margin-top:0.7em;">
    <li>Проходите тесты — ваши результаты автоматически сохраняются.</li>
    <li>Анализ пригодности — вы видите, насколько ваши качества соответствуют разным профессиям. Для каждой профессии учитываются важные критерии (ПВК), ваши тесты, веса. Всё рассчитывается автоматически и показывается в виде таблиц и графиков.</li>
    <li>Анализ стресса — на отдельной странице можно видеть коэффициент стресса на основе биосигналов (пульса).</li>
    <li>Личный кабинет — здесь вы можете посмотреть свою статистику, результаты тестов и биосигналов.</li>
  </ul>
</div>
<h1>Пригодность для различных профессий</h1>
<?php if ($role == 'respondent'): ?>
    <h2>Респондент: <?php echo $respondent_name; ?></h2>
    <?php
    $profession_data = get_all_professions_suitability($conn, $user_id);
    foreach ($profession_data as $profession):
    ?>
    <div class="card profession-block">
        <h2><i class="fa fa-briefcase"></i> <?php echo $profession['name']; ?></h2>
        <table class="pvk-table">
            <thead>
                <tr>
                    <th>Критерии личных качеств (ПВК)</th>
                    <th>Рейтинг</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (empty($profession['pvk_data'])) {
                    echo "<tr><td colspan='2'>Ни один тест не пройден.</td></tr>";
                } else {
                    foreach ($profession['pvk_data'] as $data) {
                        $rating = $data['average_rating'];
                        $rating_color_class = $data['rating_color_class'];
                        echo "<tr>
                                <td class='pvk-title'>{$data['name']}</td>
                                <td><span class='rating-badge {$rating_color_class}'>".number_format($rating,2)."</span></td>
                              </tr>";
                    }
                }
                ?>
            </tbody>
        </table>
        <div class="overall-score"><i class="fa fa-star"></i> Общий показатель пригодности: <?php echo $profession['overall_score']; ?></div>
    </div>
    <?php endforeach; ?>
<?php elseif ($role == 'expert'): ?>
    <h2>Эксперт: <?php echo $expert_name; ?></h2>
    <?php
    $profession_data = get_all_professions_suitability($conn, $user_id);
    foreach ($profession_data as $profession):
    ?>
    <div class="card profession-block">
        <h2><i class="fa fa-briefcase"></i> <?php echo $profession['name']; ?></h2>
        <table class="pvk-table">
            <thead>
                <tr>
                    <th>Критерии личных качеств (ПВК)</th>
                    <th>Рейтинг</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (empty($profession['pvk_data'])) {
                    echo "<tr><td colspan='2'>Ни один тест не пройден.</td></tr>";
                } else {
                    foreach ($profession['pvk_data'] as $data) {
                        $rating = $data['average_rating'];
                        $rating_color_class = $data['rating_color_class'];
                        echo "<tr>
                                <td class='pvk-title'>{$data['name']}</td>
                                <td><span class='rating-badge {$rating_color_class}'>".number_format($rating,2)."</span></td>
                              </tr>";
                    }
                }
                ?>
            </tbody>
        </table>
        <div class="overall-score"><i class="fa fa-star"></i> Общий показатель пригодности: <?php echo $profession['overall_score']; ?></div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="card" style="margin:2rem 0; padding:2rem; background:#f8f8f8; border-radius:12px;">
  <h2>Как вычисляются рейтинги и баллы</h2>
  <ul>
    <li><b>Рейтинг по каждому ПВК</b> — это средневзвешенное значение ваших результатов по тестам, которые связаны с этим ПВК для выбранной профессии. Если тестов несколько, их результаты усредняются с учётом веса.</li>
    <li><b>Формула:</b> <br>
      <code>Рейтинг ПВК = (результат_теста1 × вес1 + результат_теста2 × вес2 + ...) / (вес1 + вес2 + ...)</code>
    </li>
    <li><b>Общий показатель пригодности</b> — это средневзвешенное всех рейтингов ПВК для профессии с учётом их важности (веса):<br>
      <code>Общий балл = (рейтинг_ПВК1 × вес1 + рейтинг_ПВК2 × вес2 + ...) / (вес1 + вес2 + ...)</code>
    </li>
    <li><b>Если по какому-то ПВК нет результата</b> — он не обнуляет общий балл, а просто не учитывается в среднем.</li>
    <li><b>Используемые тесты для каждой профессии:</b></li>
  </ul>
  <ul>
    <li><b>Аналитик:</b> Тест на анализ, тест на внимание, тест на креативность, тест на переключаемость, тест на зрительную память, тест на обобщение, тест на кратковременную память.</li>
    <li><b>Frontend-разработчик:</b> Тест на креативность, тест на переключаемость, тест на зрительную память, тест на анализ, тест на кратковременную память, тест на обобщение.</li>
    <li><b>Backend-разработчик:</b> Тест на переключаемость, тест на анализ, тест на обобщение, тест на креативность, тест на кратковременную память, тест на зрительную память.</li>
  </ul>
  <p>Если вы хотите узнать, какой тест влияет на конкретный ПВК — наведите курсор на название ПВК или обратитесь к администратору.</p>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Выберите",
            allowClear: true
        });

        $('input[name="respondent_gender[]"], input[name="respondent_age[]"]').on('change', function() {
            var genders = $('input[name="respondent_gender[]"]:checked').map(function() {
                return this.value;
            }).get();

            var ages = $('input[name="respondent_age[]"]:checked').map(function() {
                return this.value;
            }).get();

            if (genders.length > 0 || ages.length > 0) {
                $('#respondent-select-container').show();
            } else {
                $('#respondent-select-container').hide();
            }

            $('#respondent_select option').each(function() {
                var show = true;

                if (genders.length > 0 && !genders.includes($(this).data('gender'))) {
                    show = false;
                }

                if (ages.length > 0) {
                    var ageRange = ages.map(function(age) {
                        return age.split('-');
                    });

                    var respondentAge = $(this).data('age');
                    var inRange = ageRange.some(function(range) {
                        return respondentAge >= range[0] && respondentAge <= range[1];
                    });

                    if (!inRange) {
                        show = false;
                    }
                }

                $(this).toggle(show);
            });

            $('#respondent_select').trigger('change');
        });

        $('#respondent_select').on('change', function() {
            if ($(this).val().length > 0) {
                $('#view-results-btn').show();
            } else {
                $('#view-results-btn').hide();
            }
        });
    });
</script>
</main>
</body>
</html>

<?php
$conn->close();
?>
