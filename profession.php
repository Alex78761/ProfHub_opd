<?php
session_start();
require_once "db-connect.php";

// Получаем ID профессии из URL
$profession_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получаем информацию о профессии
$profession_query = "SELECT * FROM professions WHERE id = ?";
$profession_stmt = $conn->prepare($profession_query);
$profession_stmt->bind_param("i", $profession_id);
$profession_stmt->execute();
$profession_result = $profession_stmt->get_result();

if ($profession_result->num_rows === 0) {
    header("Location: professions.php");
    exit();
}

$profession = $profession_result->fetch_assoc();

// Получаем средний рейтинг профессии
$rating_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as rating_count FROM ratings WHERE profession_id = ?";
$rating_stmt = $conn->prepare($rating_query);
$rating_stmt->bind_param("i", $profession_id);
$rating_stmt->execute();
$rating_result = $rating_stmt->get_result();
$rating_data = $rating_result->fetch_assoc();

// Получаем последние отзывы
$reviews_query = "SELECT r.*, u.username FROM ratings r 
                  JOIN users u ON r.user_id = u.id 
                  WHERE r.profession_id = ? 
                  ORDER BY r.created_at DESC LIMIT 5";
$reviews_stmt = $conn->prepare($reviews_query);
$reviews_stmt->bind_param("i", $profession_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();

// Получаем средние экспертные оценки
$expert_query = "SELECT 
    ROUND(AVG(relevance), 2) as avg_relevance, 
    ROUND(AVG(demand), 2) as avg_demand, 
    ROUND(AVG(prospects), 2) as avg_prospects, 
    COUNT(*) as expert_count 
FROM expert_evaluations 
WHERE profession_id = ?";
$expert_stmt = $conn->prepare($expert_query);
$expert_stmt->bind_param("i", $profession_id);
$expert_stmt->execute();
$expert_result = $expert_stmt->get_result();
$expert_data = $expert_result->fetch_assoc();

// Отладочная информация
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!-- Debug info:
Session user_id: " . ($_SESSION['user_id'] ?? 'not set') . "
Session role: " . ($_SESSION['role'] ?? 'not set') . "
Profession ID: $profession_id
Expert count: " . ($expert_data['expert_count'] ?? 'not set') . "
-->";

// Получаем оценку текущего эксперта, если он авторизован
$current_expert_evaluation = null;
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'expert') {
    $expert_eval_query = "SELECT * FROM expert_evaluations WHERE expert_id = ? AND profession_id = ?";
    $expert_eval_stmt = $conn->prepare($expert_eval_query);
    $expert_eval_stmt->bind_param("ii", $_SESSION['user_id'], $profession_id);
    $expert_eval_stmt->execute();
    $current_expert_evaluation = $expert_eval_stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profession['name']); ?> - ProfHub</title>
    <link href="css/header.css" rel="stylesheet">
    <link href="css/background.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Roboto', Arial, sans-serif;
            background-color: #181a1b;
            color: #f5f6fa;
            min-height: 100vh;
        }
        .main-content {
            margin-top: 7rem;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 60vh;
        }
        .profession-simple-card {
            background: rgba(255,255,255,0.07);
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            padding: 2.5rem 2rem 2rem 2rem;
            min-width: 320px;
            max-width: 500px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            border: 1.5px solid rgba(0,123,255,0.08);
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.7s cubic-bezier(.39,.575,.56,1) both;
        }
        .profession-title {
            color: #fff;
            font-size: 2.1rem;
            font-weight: 700;
            margin-bottom: 1.2rem;
            letter-spacing: 0.5px;
        }
        .profession-salary {
            color: #ffd700;
            font-weight: 600;
            font-size: 1.15rem;
            margin-bottom: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .profession-description {
            color: #e0e0e0;
            margin-bottom: 1.2rem;
            font-size: 1.08rem;
            line-height: 1.6;
        }
        .profession-req {
            color: #8ecfff;
            font-size: 1.01rem;
            margin-bottom: 0.7rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .profession-req i {
            color: #007bff;
        }
        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: none; }
        }
        @media (max-width: 600px) {
            .main-content {
                margin-top: 5rem;
                padding: 0 0.2rem;
        }
            .profession-simple-card {
                padding: 1.2rem 0.7rem 1.2rem 0.7rem;
                min-width: unset;
                max-width: 100vw;
            }
            .profession-title {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="background.css"></div>
    <div class="main-content">
        <div class="profession-simple-card">
            <div class="profession-title"><?php echo htmlspecialchars($profession['name']); ?></div>
            <div class="profession-salary"><i class="fas fa-coins"></i> <?php echo htmlspecialchars($profession['salary']); ?> ₽</div>
            <div class="profession-description"><?php echo nl2br(htmlspecialchars($profession['description'])); ?></div>
            <div class="profession-req"><i class="fas fa-list"></i> <?php echo nl2br(htmlspecialchars($profession['requirements'])); ?></div>
            <?php if ($expert_data['expert_count'] > 0): ?>
                <div style="margin: 1.2rem 0 0.7rem 0; padding: 1rem; background: rgba(255,215,0,0.08); border-radius: 10px; color: #ffd700; font-size: 1.08rem; font-weight: 500;">
                    <span style="color:#ffd700;font-weight:700;">Экспертная оценка профессии:</span><br>
                    Актуальность: <b><?php echo number_format($expert_data['avg_relevance'], 2); ?></b> / 5<br>
                    Востребованность: <b><?php echo number_format($expert_data['avg_demand'], 2); ?></b> / 5<br>
                    Перспективы: <b><?php echo number_format($expert_data['avg_prospects'], 2); ?></b> / 5<br>
                    <span style="color:#8ecfff;font-size:0.98em;">(Оценок: <?php echo $expert_data['expert_count']; ?>)</span>
                </div>
                <!-- Средние оценки ПВК от экспертов -->
                <div style="margin: 0.7rem 0 1.2rem 0; padding: 0.7rem 1rem; background: rgba(33,211,151,0.07); border-radius: 10px; color: #fff; font-size: 1.01rem;">
                    <span style="color:#21d397;font-weight:600;">Средние оценки ПВК (эксперты):</span><br>
                    <?php
                    $pvk_query = "SELECT pvk.id, pvk.name, AVG(ratings.rating) as avg_rating FROM pvk LEFT JOIN ratings ON pvk.id = ratings.pvk_id AND ratings.profession_id = ? AND ratings.user_id IN (SELECT id FROM users WHERE role = 'expert') GROUP BY pvk.id, pvk.name HAVING avg_rating IS NOT NULL ORDER BY pvk.id";
                    $pvk_stmt = $conn->prepare($pvk_query);
                    $pvk_stmt->bind_param("i", $profession_id);
                    $pvk_stmt->execute();
                    $pvk_result = $pvk_stmt->get_result();
                    if ($pvk_result->num_rows > 0) {
                        while ($pvk = $pvk_result->fetch_assoc()) {
                            echo '<div style="margin-bottom:0.3em;">';
                            echo '<b style="color:#8ecfff;">' . htmlspecialchars($pvk['name']) . '</b>: ';
                            echo '<span style="color:#ffd700;">' . number_format($pvk['avg_rating'], 2) . '</span>';
                            echo '</div>';
                        }
                    } else {
                        echo '<span style="color:#888;">Нет оценок ПВК от экспертов</span>';
                    }
                    ?>
                </div>
                <!-- Индивидуальные оценки ПВК эксперта -->
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'expert'): ?>
                <div style="margin: 0.7rem 0 1.2rem 0; padding: 0.7rem 1rem; background: rgba(52,152,219,0.07); border-radius: 10px; color: #fff; font-size: 1.01rem;">
                    <span style="color:#3498db;font-weight:600;">Ваши оценки ПВК:</span><br>
                    <?php
                    $user_id = $_SESSION['user_id'];
                    $pvk_user_query = "SELECT pvk.name, ratings.rating FROM ratings LEFT JOIN pvk ON ratings.pvk_id = pvk.id WHERE ratings.profession_id = ? AND ratings.user_id = ? ORDER BY pvk.id";
                    $pvk_user_stmt = $conn->prepare($pvk_user_query);
                    $pvk_user_stmt->bind_param("ii", $profession_id, $user_id);
                    $pvk_user_stmt->execute();
                    $pvk_user_result = $pvk_user_stmt->get_result();
                    if ($pvk_user_result->num_rows > 0) {
                        while ($pvk = $pvk_user_result->fetch_assoc()) {
                            echo '<div style="margin-bottom:0.3em;">';
                            echo '<b style="color:#8ecfff;">' . htmlspecialchars($pvk['name']) . '</b>: ';
                            echo '<span style="color:#f39c12;">' . $pvk['rating'] . '</span>';
                            echo '</div>';
                        }
                    } else {
                        echo '<span style="color:#888;">Вы не оценивали ПВК</span>';
                    }
                    ?>
                </div>
                <?php endif; ?>
                <!-- Список экспертов -->
                <div style="margin: 0.7rem 0 1.2rem 0; padding: 0.7rem 1rem; background: rgba(255,255,255,0.04); border-radius: 10px; color: #fff; font-size: 1.01rem;">
                    <span style="color:#ffd700;font-weight:600;">Эксперты, оценившие профессию:</span><br>
                    <?php
                    $experts_query = "SELECT u.username, e.relevance, e.demand, e.prospects, e.comment FROM expert_evaluations e JOIN users u ON e.expert_id = u.id WHERE e.profession_id = ?";
                    $experts_stmt = $conn->prepare($experts_query);
                    $experts_stmt->bind_param("i", $profession_id);
                    $experts_stmt->execute();
                    $experts_result = $experts_stmt->get_result();
                    if ($experts_result->num_rows > 0) {
                        while ($expert = $experts_result->fetch_assoc()) {
                            echo '<div style="margin-bottom:0.5em; padding-bottom:0.5em; border-bottom:1px solid rgba(255,255,255,0.07);">';
                            echo '<b style="color:#8ecfff;">' . htmlspecialchars($expert['username']) . '</b>: ';
                            echo 'Актуальность <b>' . $expert['relevance'] . '</b>, ';
                            echo 'Востребованность <b>' . $expert['demand'] . '</b>, ';
                            echo 'Перспективы <b>' . $expert['prospects'] . '</b>';
                            if ($expert['comment']) {
                                echo '<br><span style="color:#b0b0b0; font-size:0.97em;">Комментарий: ' . nl2br(htmlspecialchars($expert['comment'])) . '</span>';
                            }
                            echo '</div>';
                        }
                    } else {
                        echo '<span style="color:#888;">Нет данных</span>';
                    }
                    ?>
                </div>
                <?php if ($current_expert_evaluation): ?>
                <div style="margin: 1.2rem 0 0.7rem 0; padding: 1rem; background: rgba(0,123,255,0.08); border-radius: 10px; color: #8ecfff; font-size: 1.08rem; font-weight: 500;">
                    <span style="color:#8ecfff;font-weight:700;">Ваша оценка:</span><br>
                    Актуальность: <b><?php echo $current_expert_evaluation['relevance']; ?></b> / 5<br>
                    Востребованность: <b><?php echo $current_expert_evaluation['demand']; ?></b> / 5<br>
                    Перспективы: <b><?php echo $current_expert_evaluation['prospects']; ?></b> / 5<br>
                    <?php if ($current_expert_evaluation['comment']): ?>
                    <div style="margin-top: 0.5rem; color: #e0e0e0; font-size: 0.95em;">
                        Комментарий: <?php echo nl2br(htmlspecialchars($current_expert_evaluation['comment'])); ?>
                            </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div style="margin: 1.2rem 0 0.7rem 0; color: #ffd700; font-size: 1.01rem;">Экспертная оценка пока отсутствует</div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Экспертные оценки ПВК -->
    <div class="main-content">
        <div class="profession-simple-card" style="background:rgba(33,211,151,0.07); border:1.5px solid #21d397;">
            <div style="color:#21d397;font-weight:700; font-size:1.15em; margin-bottom:0.7em;">Экспертные оценки ПВК</div>
            <?php
            $pvk_expert_query = "SELECT u.username, pvk.name as pvk_name, epr.rating FROM expert_pvk_ratings epr JOIN users u ON epr.expert_id = u.id JOIN pvk ON epr.pvk_id = pvk.id WHERE epr.profession_id = ? ORDER BY u.username, pvk.id";
            $pvk_expert_stmt = $conn->prepare($pvk_expert_query);
            $pvk_expert_stmt->bind_param("i", $profession_id);
            $pvk_expert_stmt->execute();
            $pvk_expert_result = $pvk_expert_stmt->get_result();
            if ($pvk_expert_result->num_rows > 0) {
                while ($row = $pvk_expert_result->fetch_assoc()) {
                    echo '<div style="margin-bottom:0.5em; padding-bottom:0.5em; border-bottom:1px solid rgba(255,255,255,0.07);">';
                    echo '<b style="color:#8ecfff;">' . htmlspecialchars($row['username']) . '</b>: ';
                    echo '<span style="color:#fff;">' . htmlspecialchars($row['pvk_name']) . '</span> — ';
                    echo '<span style="color:#ffd700;">' . $row['rating'] . '</span>';
                    echo '</div>';
                }
            } else {
                echo '<span style="color:#888;">Нет экспертных оценок ПВК для этой профессии</span>';
            }
            ?>
        </div>
    </div>
</body>
</html> 