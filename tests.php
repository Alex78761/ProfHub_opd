<?php
session_start();
require_once 'db_connect.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Получение списка тестов с группировкой по типу
$query = "SELECT 
    tests.*, 
    COUNT(DISTINCT test_results.user_id) as attempts_count,
    IFNULL(AVG(test_results.result), 0) as avg_score
FROM tests
LEFT JOIN test_results ON tests.id = test_results.test_id
GROUP BY tests.id, tests.test_type
ORDER BY tests.test_type, tests.test_name";

$result = $conn->query($query);
$tests_by_type = array();

if ($result) {
    while ($test = $result->fetch_assoc()) {
        $tests_by_type[$test['test_type']][] = $test;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тесты - ProfHub</title>
    <link rel="stylesheet" href="css/main.css">
    <style>
        .tests-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .test-type-section {
            margin-bottom: 40px;
        }
        
        .test-type-title {
            color: #fff;
            font-size: 24px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        .tests-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .test-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .test-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .test-name {
            color: #fff;
            font-size: 18px;
            margin-bottom: 15px;
        }
        
        .test-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
        }
        
        .test-button {
            display: block;
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: #fff;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s ease;
        }
        
        .test-button:hover {
            background: #0056b3;
        }
        
        .no-tests {
            color: #fff;
            text-align: center;
            padding: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="tests-container">
        <?php if (empty($tests_by_type)): ?>
            <div class="no-tests">
                <h2>Тесты пока не добавлены</h2>
                <p>Пожалуйста, зайдите позже.</p>
            </div>
        <?php else: ?>
            <?php foreach ($tests_by_type as $type => $tests): ?>
                <div class="test-type-section">
                    <h2 class="test-type-title"><?php echo htmlspecialchars($type); ?></h2>
                    <div class="tests-grid">
                        <?php foreach ($tests as $test): ?>
                            <div class="test-card">
                                <h3 class="test-name"><?php echo htmlspecialchars($test['test_name']); ?></h3>
                                <div class="test-stats">
                                    <span>Попыток: <?php echo $test['attempts_count']; ?></span>
                                    <span>Средний балл: <?php echo number_format($test['avg_score'], 1); ?></span>
                                </div>
                                <a href="<?php echo htmlspecialchars($test['file_path']); ?>" class="test-button" target="_blank">
                                    Начать тест
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html> 