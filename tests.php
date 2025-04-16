<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Подключение к базе данных
require_once "db-connect.php";

// Проверка, авторизован ли пользователь
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}

// Проверяем, является ли пользователь администратором, экспертом, респондентом или обычным пользователем
$is_admin = false;
$is_expert = false;
$is_respondent = false;
$is_user = false;

// Получаем информацию о пользователе
$query_user = "SELECT u.username, u.role, u.respondent_id FROM users u WHERE u.id = ?";
$statement = $conn->prepare($query_user);
$statement->bind_param("i", $user_id);
$statement->execute();
$result_user = $statement->get_result();

if ($result_user->num_rows == 1) {
    $row_user = $result_user->fetch_assoc();
    $role = $row_user['role'];

    // Устанавливаем флаги для разных ролей
    $is_admin = $role === 'admin';
    $is_expert = $role === 'expert';
    $is_respondent = $role === 'respondent';
    $is_user = $role === 'user';

    // Если пользователь - респондент, получаем его respondent_id
    if ($is_respondent) {
        $respondent_id = $row_user['respondent_id'];
        
        // Получаем назначенные тесты для респондента
        $query_respondent_tests = "SELECT t.id, t.test_name, t.test_type 
                                 FROM tests t 
                                 JOIN respondent_tests rt ON t.id = rt.test_id 
                                 WHERE rt.respondent_id = ? 
                                 ORDER BY t.test_type, t.test_name";
        $stmt = $conn->prepare($query_respondent_tests);
        $stmt->bind_param("i", $respondent_id);
        $stmt->execute();
        $result_respondent_tests = $stmt->get_result();
        
        $respondent_tests = [];
        while ($row_test = $result_respondent_tests->fetch_assoc()) {
            $test_type = trim($row_test['test_type']);
            $respondent_tests[$test_type][] = [
                'id' => $row_test['id'],
                'test_name' => trim($row_test['test_name']),
                'file_path' => 'tests/' . strtolower(str_replace(' ', '-', trim($row_test['test_name']))) . '.php'
            ];
        }
    }
}

// Получаем все тесты
$query_all_tests = "SELECT id, test_name, test_type FROM tests ORDER BY test_type, test_name";
$result_all_tests = $conn->query($query_all_tests);

$tests = []; // Инициализируем массив

// Заполняем массив тестов, если запрос успешен
if ($result_all_tests) {
    while ($row_test = $result_all_tests->fetch_assoc()) {
        $test_type = trim($row_test['test_type']); // Убираем лишние пробелы
        $tests[$test_type][] = [
            'id' => $row_test['id'],
            'test_name' => trim($row_test['test_name']),
            'file_path' => 'tests/' . strtolower(str_replace(' ', '-', trim($row_test['test_name']))) . '.php'
        ];
    }
}

// Определение профессий
$professions = [];
$query_professions = "SELECT id, name FROM professions";
$result_professions = $conn->query($query_professions);
if ($result_professions) {
    while ($row = $result_professions->fetch_assoc()) {
        $professions[$row['id']] = $row['name'];
    }
}

// Функция для получения типа теста из его названия файла
function getTestType($filename) {
    $types = [
        'simple_color_test' => 'Сенсомоторные реакции',
        'sound_reaction_test' => 'Сенсомоторные реакции',
        'advanced_color_test' => 'Сенсомоторные реакции',
        'audio_count_test' => 'Сенсомоторные реакции',
        'visual_count_test' => 'Сенсомоторные реакции',
        'movement_test' => 'Реакция на движущийся объект',
        'advanced_movement_test' => 'Реакция на движущийся объект',
        'chase' => 'Реакция на движущийся объект',
        'stress' => 'Тесты на стрессоустойчивость',
        'visual-memory' => 'Тесты на память',
        'capacity' => 'Тесты на внимание',
        'classification' => 'Тесты на классификацию',
        'analog' => 'Аналоговые тесты',
        'comparison' => 'Тесты на сравнение',
        'generalization' => 'Тесты на обобщение',
        'switch' => 'Тесты на переключение'
    ];
    
    foreach ($types as $key => $type) {
        if (strpos(strtolower($filename), $key) !== false) {
            return $type;
        }
    }
    return 'Другие тесты';
}

// Функция для получения названия теста из его файла
function getTestName($filename) {
    $names = [
        'simple_color_test.php' => 'Тест на простую зрительную реакцию',
        'sound_reaction_test.php' => 'Тест на простую слуховую реакцию',
        'advanced_color_test.php' => 'Тест на сложную цветовую реакцию',
        'audio_count_test.php' => 'Тест на сложную слуховую реакцию (чет/нечет)',
        'visual_count_test.php' => 'Тест на сложную зрительную реакцию (чет/нечет)',
        'movement_test.php' => 'Тест на простую реакцию на движущийся объект',
        'advanced_movement_test.php' => 'Тест на сложную реакцию на движущиеся объекты',
        'chaseTest.php' => 'Тест на преследование движущегося объекта',
        'stress.php' => 'Тест на стрессоустойчивость',
        'visual-memory-test.php' => 'Тест на визуальную память',
        'capacity-test.php' => 'Тест на объем внимания',
        'classification.php' => 'Тест на классификацию',
        'analog_test.php' => 'Аналоговый тест',
        'comparison.php' => 'Тест на сравнение',
        'generalization.php' => 'Тест на обобщение',
        'switch-test.php' => 'Тест на переключение внимания'
    ];
    
    return isset($names[$filename]) ? $names[$filename] : ucfirst(str_replace(['_', '-', '.php'], [' ', ' ', ''], $filename));
}

// Получаем список тестов из директории
$test_files = array_filter(glob(__DIR__ . '/tests/*.php'), function($file) {
    $filename = basename($file);
    // Исключаем служебные файлы
    $exclude_files = ['index.php', 'process_tests.php', 'view_test_results.php', 'tests.php'];
    return !in_array($filename, $exclude_files) && !str_starts_with($filename, 'save_');
});

$tests = [];

foreach ($test_files as $file) {
    $filename = basename($file);
    $test_type = getTestType($filename);
    if (!isset($tests[$test_type])) {
        $tests[$test_type] = [];
    }
    $tests[$test_type][] = [
        'name' => getTestName($filename),
        'file' => $filename
    ];
}

// Добавляем тесты по познавательным способностям
$tests['Познавательные способности'][] = [
    'name' => 'Тест на переключение внимания',
    'file' => 'switch-test.php'
];
$tests['Познавательные способности'][] = [
    'name' => 'Тест на объем внимания',
    'file' => 'capacity-test.php'
];
$tests['Познавательные способности'][] = [
    'name' => 'Тест на визуальную память',
    'file' => 'visual-memory-test.php'
];
$tests['Познавательные способности'][] = [
    'name' => 'Тест на сравнение',
    'file' => 'comparison.php'
];
$tests['Познавательные способности'][] = [
    'name' => 'Тест на анализ',
    'file' => 'analysis.php'
];
$tests['Познавательные способности'][] = [
    'name' => 'Тест на классификацию',
    'file' => 'classification.php'
];
$tests['Познавательные способности'][] = [
    'name' => 'Тест на обобщение',
    'file' => 'generalization.php'
];
$tests['Познавательные способности'][] = [
    'name' => 'Тест на кратковременную память',
    'file' => 'short-term-memory-test.php'
];

// Добавляю вручную категорию 'Слежение' с двумя тестами
$tests['Слежение'][] = [
    'name' => 'Тест аналогового слежения',
    'file' => 'analog_test.php'
];
$tests['Слежение'][] = [
    'name' => 'Тест на преследование движущегося объекта',
    'file' => 'chaseTest.php'
];

// Сортируем категории и тесты по алфавиту
ksort($tests);
foreach ($tests as &$category_tests) {
    usort($category_tests, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
}
unset($category_tests);

// Оставляем только нужные категории
$allowed_categories = [
    'Сенсомоторные реакции',
    'Реакция на движущийся объект',
    'Слежение',
    'Познавательные способности'
];
foreach (array_keys($tests) as $category) {
    if (!in_array($category, $allowed_categories)) {
        unset($tests[$category]);
    }
}

// Удаляю тест на преследование движущегося объекта из категории 'Реакция на движущийся объект'
if (isset($tests['Реакция на движущийся объект'])) {
    $tests['Реакция на движущийся объект'] = array_filter(
        $tests['Реакция на движущийся объект'],
        function($test) {
            return $test['name'] !== 'Тест на преследование движущегося объекта';
        }
    );
    // Переиндексация массива
    $tests['Реакция на движущийся объект'] = array_values($tests['Реакция на движущийся объект']);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тесты - ProfHub</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/background.css">
    <link rel="stylesheet" href="css/tests.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="tests-container">
        <h1>Доступные тесты</h1>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="message">
                <p>Для прохождения тестов необходимо войти в систему</p>
                <a href="login.php">Войти</a>
            </div>
        <?php else: ?>
            <?php if (empty($tests)): ?>
                <div class="message">
                    <p>Тесты не найдены</p>
                    <p>Директория поиска: <?php echo __DIR__ . '/tests/'; ?></p>
                    <p>Найдено файлов: <?php echo count($test_files); ?></p>
                </div>
            <?php else: ?>
                <?php foreach ($tests as $category => $category_tests): ?>
                    <div class="test-category">
                        <h2 class="category-title"><?php echo htmlspecialchars($category); ?></h2>
                        <div class="tests-grid">
                            <?php foreach ($category_tests as $test): ?>
                                <div class="test-card">
                                    <div class="test-name"><?php echo htmlspecialchars($test['name']); ?></div>
                                    <a href="tests/<?php echo htmlspecialchars($test['file']); ?>" class="start-test-btn">
                                        Начать тест
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php $conn->close(); ?>
</body>
</html> 