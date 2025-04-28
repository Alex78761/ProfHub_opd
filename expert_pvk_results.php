<?php
session_start();
require_once "db-connect.php";

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$role = $conn->query("SELECT role FROM users WHERE id = $user_id")->fetch_assoc()['role'] ?? '';
if ($role !== 'expert') {
    die("Доступ только для экспертов.");
}

// Получаем список профессий
$professions = $conn->query("SELECT * FROM professions ORDER BY name");

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Результаты экспертных оценок</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/background.css">
    <style>
        body { background: #181a1b; color: #f5f6fa; font-family: 'Roboto', Arial, sans-serif; }
        .container { max-width: 900px; margin: 4rem auto; background: rgba(255,255,255,0.07); border-radius: 18px; padding: 2rem; box-shadow: 0 8px 32px rgba(0,0,0,0.18); }
        h1, h2 { color: #ffd700; }
        .profession-block { margin-bottom: 2.5rem; }
        .pvk-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .pvk-table th, .pvk-table td { padding: 8px 12px; border-bottom: 1px solid #333; }
        .pvk-table th { background: #222; color: #ffd700; }
        .pvk-table td { background: rgba(255,255,255,0.03); }
        .my-rating { color: #8ecfff; font-weight: bold; }
        .avg-rating { color: #ffd700; font-weight: bold; }
        .soglas { color: #4caf50; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <h1>Результаты экспертных оценок</h1>
    <?php while ($prof = $professions->fetch_assoc()):
        $pid = $prof['id'];
        // Описание профессии
        echo "<div class='profession-block'>";
        echo "<h2>".htmlspecialchars($prof['name'])."</h2>";
        echo "<div>".nl2br(htmlspecialchars($prof['description']))."</div>";
        // Получаем ПВК, которые оценивались экспертами для этой профессии
        $pvk_res = $conn->query("SELECT pvk.id, pvk.name FROM expert_pvk_ratings JOIN pvk ON pvk.id = expert_pvk_ratings.pvk_id WHERE expert_pvk_ratings.profession_id = $pid GROUP BY pvk.id, pvk.name");
        if ($pvk_res->num_rows == 0) { echo "<p>Нет оценок.</p></div>"; continue; }
        echo "<table class='pvk-table'><tr><th>ПВК</th><th>Средний рейтинг</th><th>Моя оценка</th><th>Согласованность</th></tr>";
        while ($pvk = $pvk_res->fetch_assoc()) {
            $pvk_id = $pvk['id'];
            // Средний рейтинг
            $avg = $conn->query("SELECT AVG(rating) as avg_rating FROM expert_pvk_ratings WHERE profession_id = $pid AND pvk_id = $pvk_id")->fetch_assoc()['avg_rating'];
            // Моя оценка
            $my = $conn->query("SELECT rating FROM expert_pvk_ratings WHERE profession_id = $pid AND pvk_id = $pvk_id AND expert_id = $user_id")->fetch_assoc()['rating'] ?? '-';
            // Согласованность (среднее отклонение)
            $soglas = '-';
            $all = $conn->query("SELECT rating FROM expert_pvk_ratings WHERE profession_id = $pid AND pvk_id = $pvk_id AND expert_id != $user_id");
            $diffs = [];
            while ($row = $all->fetch_assoc()) {
                $diffs[] = abs($my - $row['rating']);
            }
            if (count($diffs)) $soglas = number_format(array_sum($diffs)/count($diffs), 2);
            echo "<tr><td>".htmlspecialchars($pvk['name'])."</td><td class='avg-rating'>".number_format($avg,2)."</td><td class='my-rating'>".htmlspecialchars($my)."</td><td class='soglas'>".htmlspecialchars($soglas)."</td></tr>";
        }
        echo "</table></div>";
    endwhile; ?>
</div>
</body>
</html> 