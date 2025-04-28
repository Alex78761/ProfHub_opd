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
$expert_query = "SELECT AVG(relevance) as avg_relevance, AVG(demand) as avg_demand, AVG(prospects) as avg_prospects, COUNT(*) as expert_count FROM expert_evaluations WHERE profession_id = ?";
$expert_stmt = $conn->prepare($expert_query);
$expert_stmt->bind_param("i", $profession_id);
$expert_stmt->execute();
$expert_result = $expert_stmt->get_result();
$expert_data = $expert_result->fetch_assoc();
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
    <div class="background"></div>
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
            <?php else: ?>
                <div style="margin: 1.2rem 0 0.7rem 0; color: #ffd700; font-size: 1.01rem;">Экспертная оценка пока отсутствует</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 