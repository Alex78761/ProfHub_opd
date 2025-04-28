<?php
session_start();
require_once "db-connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Получаем данные эксперта
$expert = $conn->query("SELECT e.*, u.username FROM experts e JOIN users u ON u.expert_id = e.id WHERE u.id = $user_id")->fetch_assoc();
if (!$expert) {
    die("Профиль эксперта не найден.");
}

// Количество оценённых профессий
$count_professions = $conn->query("SELECT COUNT(DISTINCT profession_id) as cnt FROM expert_pvk_ratings WHERE expert_id = $user_id")->fetch_assoc()['cnt'];

// Согласованность с другими экспертами (среднее отклонение по ПВК)
$soglas = '-';
$res = $conn->query("SELECT profession_id, pvk_id, rating FROM expert_pvk_ratings WHERE expert_id = $user_id");
$my_ratings = [];
while ($row = $res->fetch_assoc()) {
    $my_ratings[$row['profession_id']][$row['pvk_id']] = $row['rating'];
}
$other_res = $conn->query("SELECT profession_id, pvk_id, AVG(rating) as avg_rating FROM expert_pvk_ratings WHERE expert_id != $user_id GROUP BY profession_id, pvk_id");
$total_diff = 0; $count = 0;
while ($row = $other_res->fetch_assoc()) {
    $pid = $row['profession_id']; $pvk = $row['pvk_id'];
    if (isset($my_ratings[$pid][$pvk])) {
        $total_diff += abs($my_ratings[$pid][$pvk] - $row['avg_rating']);
        $count++;
    }
}
if ($count > 0) $soglas = number_format($total_diff / $count, 2);

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Профиль эксперта</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/background.css">
    <style>
        body { background: #181a1b; color: #f5f6fa; font-family: 'Roboto', Arial, sans-serif; }
        .container { max-width: 600px; margin: 4rem auto; background: rgba(255,255,255,0.07); border-radius: 18px; padding: 2rem; box-shadow: 0 8px 32px rgba(0,0,0,0.18); }
        h1 { color: #ffd700; }
        .profile-info { margin-bottom: 2rem; }
        .profile-info div { margin-bottom: 0.7rem; }
        .stat { color: #8ecfff; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <h1>Профиль эксперта</h1>
    <div class="profile-info">
        <div><b>Имя:</b> <?= htmlspecialchars($expert['name']) ?></div>
        <div><b>Группа:</b> <?= htmlspecialchars($expert['sgroup']) ?></div>
        <div><b>Код:</b> <?= htmlspecialchars($expert['code']) ?></div>
        <div><b>Пользователь:</b> <?= htmlspecialchars($expert['username']) ?></div>
    </div>
    <div class="stat"><b>Оценено профессий:</b> <?= $count_professions ?></div>
    <div class="stat"><b>Среднее отклонение от других экспертов (согласованность):</b> <?= $soglas ?></div>
</div>
</body>
</html> 