<?php
session_start();
include '../db-connect.php';

$user_id = $_SESSION['user_id'] ?? null;
$result = null;
$level = isset($_POST['level']) ? (int)$_POST['level'] : 1;
$attempts = isset($_POST['attempts']) ? (int)$_POST['attempts'] : 0;
$total_score = isset($_POST['total_score']) ? (float)$_POST['total_score'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_sequence'])) {
    $user_sequence = strtoupper(trim($_POST['user_sequence']));
    $original_sequence = strtoupper(trim($_POST['original_sequence']));
    
    // Вычисляем частичный результат
    $correct_chars = 0;
    $min_length = min(strlen($user_sequence), strlen($original_sequence));
    for ($i = 0; $i < $min_length; $i++) {
        if ($user_sequence[$i] === $original_sequence[$i]) {
            $correct_chars++;
        }
    }
    $attempt_score = $correct_chars / strlen($original_sequence);
    $total_score += $attempt_score;
    $attempts++;
    
    // Если это последняя попытка или пользователь достиг максимального уровня
    if ($attempts >= 3 || $level >= 5) {
        $final_score = $total_score / $attempts;
        
        // Сохраняем результат
        $test_type = 'Оценка памяти';
        $test_name = 'кратковременная';
        if ($user_id !== null) {
            $stmt = $conn->prepare("SELECT id FROM tests WHERE test_type = ? AND test_name = ?");
            $stmt->bind_param("ss", $test_type, $test_name);
            $stmt->execute();
            $stmt->bind_result($test_id);
            $stmt->fetch();
            $stmt->close();
            if ($test_id !== null) {
                $stmt = $conn->prepare("INSERT INTO test_results (user_id, test_id, test_name, result) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iisd", $user_id, $test_id, $test_name, $final_score);
                $stmt->execute();
                $stmt->close();
            }
        }
        $result = "Тест завершен! Ваш результат: " . round($final_score * 100) . "%";
    } else {
        // Переходим на следующий уровень
        $level++;
        $result = "Попытка $attempts завершена. Переход на уровень $level";
    }
}

function generateSequence($length) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $seq = '';
    for ($i = 0; $i < $length; $i++) {
        $seq .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $seq;
}

// Определяем длину последовательности в зависимости от уровня
$sequence_length = 4 + ($level - 1) * 2; // Начинаем с 4 символов, увеличиваем на 2 каждый уровень
$sequence = generateSequence($sequence_length);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест на кратковременную память</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #fff;
        }
        .container {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        .sequence {
            font-size: 24px;
            margin: 20px 0;
            padding: 10px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            text-align: center;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        button {
            background: #ffd700;
            color: #000;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #ffc000;
        }
        .result {
            margin-top: 20px;
            padding: 10px;
            background: rgba(0, 255, 0, 0.1);
            border-radius: 5px;
            text-align: center;
        }
        .level-info {
            margin-bottom: 10px;
            color: #ffd700;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Тест на кратковременную память</h1>
        <div class="level-info">
            Уровень: <?php echo $level; ?> (<?php echo $sequence_length; ?> символов)
        </div>
        <?php if ($result): ?>
            <div class="result"><?php echo $result; ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="sequence" id="sequence"><?php echo $sequence; ?></div>
            <input type="text" name="user_sequence" placeholder="Введите запомненную последовательность" required>
            <input type="hidden" name="original_sequence" value="<?php echo $sequence; ?>">
            <input type="hidden" name="level" value="<?php echo $level; ?>">
            <input type="hidden" name="attempts" value="<?php echo $attempts; ?>">
            <input type="hidden" name="total_score" value="<?php echo $total_score; ?>">
            <button type="submit">Проверить</button>
        </form>
    </div>
    <script>
        // Скрываем последовательность через 5 секунд
        setTimeout(() => {
            document.getElementById('sequence').style.display = 'none';
        }, 5000);
    </script>
</body>
</html>
<?php $conn->close(); ?> 