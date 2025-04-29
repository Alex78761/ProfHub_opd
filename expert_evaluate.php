<?php
session_start();
require_once "db-connect.php";

// Проверка авторизации
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Массивы с опциями для выпадающих списков
$relevance_options = [
    1 => '1 - Низкая',
    2 => '2 - Ниже среднего',
    3 => '3 - Средняя',
    4 => '4 - Выше среднего',
    5 => '5 - Высокая'
];

$demand_options = [
    1 => '1 - Низкая',
    2 => '2 - Ниже среднего',
    3 => '3 - Средняя',
    4 => '4 - Выше среднего',
    5 => '5 - Высокая'
];

$prospects_options = [
    1 => '1 - Низкие',
    2 => '2 - Ниже среднего',
    3 => '3 - Средние',
    4 => '4 - Выше среднего',
    5 => '5 - Высокие'
];

// Получаем список всех профессий и их оценок для текущего эксперта
$query = "SELECT p.*, e.relevance, e.demand, e.prospects, e.comment 
          FROM professions p 
          LEFT JOIN expert_evaluations e ON p.id = e.profession_id AND e.expert_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$professions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profession_id = filter_input(INPUT_POST, 'profession_id', FILTER_VALIDATE_INT);
    $relevance = filter_input(INPUT_POST, 'relevance', FILTER_VALIDATE_INT);
    $demand = filter_input(INPUT_POST, 'demand', FILTER_VALIDATE_INT);
    $prospects = filter_input(INPUT_POST, 'prospects', FILTER_VALIDATE_INT);
    $comment = htmlspecialchars(trim($_POST['comment'] ?? ''), ENT_QUOTES, 'UTF-8');
    
    if ($profession_id && $relevance && $demand && $prospects) {
        try {
            // Проверка существующей оценки
            $check_stmt = $conn->prepare("SELECT id FROM expert_evaluations WHERE expert_id = ? AND profession_id = ?");
            $check_stmt->bind_param("ii", $user_id, $profession_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Обновление существующей оценки
                $stmt = $conn->prepare("
                    UPDATE expert_evaluations 
                    SET relevance = ?, demand = ?, prospects = ?, comment = ?
                    WHERE expert_id = ? AND profession_id = ?
                ");
                $stmt->bind_param("iiiisi", $relevance, $demand, $prospects, $comment, $user_id, $profession_id);
            } else {
                // Создание новой оценки
                $stmt = $conn->prepare("
                    INSERT INTO expert_evaluations (expert_id, profession_id, relevance, demand, prospects, comment) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("iiiisi", $user_id, $profession_id, $relevance, $demand, $prospects, $comment);
            }
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Оценка успешно сохранена!';
                header('Location: profession.php?id=' . $profession_id);
                exit();
            } else {
                $error = 'Произошла ошибка при сохранении оценки: ' . $stmt->error;
            }
        } catch (Exception $e) {
            $error = 'Произошла ошибка при сохранении оценки: ' . $e->getMessage();
        }
    } else {
        $error = 'Пожалуйста, заполните все обязательные поля.';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оценка профессий</title>
    <link rel="stylesheet" href="css/evaluate.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1>Оценка профессий</h1>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (empty($professions)): ?>
            <div class="no-professions">
                <p>Нет профессий для оценки</p>
                <a href="professions.php" class="back-link">Вернуться к списку профессий</a>
            </div>
        <?php else: ?>
            <?php foreach ($professions as $profession): ?>
                <div class="profession-card">
                    <h2 class="profession-name"><?php echo htmlspecialchars($profession['name']); ?></h2>
                    <div class="profession-description">
                        <?php echo nl2br(htmlspecialchars($profession['description'])); ?>
                    </div>
                    
                    <form class="evaluation-form" method="POST" action="">
                        <input type="hidden" name="profession_id" value="<?php echo $profession['id']; ?>">
                        
                        <div class="criteria-group">
                            <h3>Критерии оценки</h3>
                            
                            <div class="criteria-item">
                                <label for="relevance_<?php echo $profession['id']; ?>">Актуальность профессии:</label>
                                <select name="relevance" id="relevance_<?php echo $profession['id']; ?>" required>
                                    <option value="">Выберите оценку</option>
                                    <?php foreach ($relevance_options as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" <?php echo ($profession['relevance'] == $value) ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="criteria-item">
                                <label for="demand_<?php echo $profession['id']; ?>">Востребованность на рынке:</label>
                                <select name="demand" id="demand_<?php echo $profession['id']; ?>" required>
                                    <option value="">Выберите оценку</option>
                                    <?php foreach ($demand_options as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" <?php echo ($profession['demand'] == $value) ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="criteria-item">
                                <label for="prospects_<?php echo $profession['id']; ?>">Перспективы развития:</label>
                                <select name="prospects" id="prospects_<?php echo $profession['id']; ?>" required>
                                    <option value="">Выберите оценку</option>
                                    <?php foreach ($prospects_options as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" <?php echo ($profession['prospects'] == $value) ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="comment-group">
                            <label for="comment_<?php echo $profession['id']; ?>">Комментарий:</label>
                            <textarea 
                                name="comment" 
                                id="comment_<?php echo $profession['id']; ?>" 
                                placeholder="Введите ваш комментарий..."
                            ><?php echo isset($profession['comment']) ? htmlspecialchars($profession['comment']) : ''; ?></textarea>
                        </div>

                        <button type="submit" class="submit-btn">
                            <?php echo isset($profession['relevance']) ? 'Обновить оценку' : 'Сохранить оценку'; ?>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html> 