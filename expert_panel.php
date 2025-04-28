<?php
session_start();
require_once "db-connect.php";

// Проверка авторизации и роли
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$role_query = $conn->prepare("SELECT role FROM users WHERE id = ?");
$role_query->bind_param("i", $user_id);
$role_query->execute();
$role_result = $role_query->get_result();
$role = $role_result->fetch_assoc()['role'] ?? '';
if ($role !== 'expert') {
    die("Доступ только для экспертов.");
}

// Получаем список профессий
$professions = [];
$res = $conn->query("SELECT id, name FROM professions ORDER BY name");
while ($row = $res->fetch_assoc()) {
    $professions[] = $row;
}

$success = $error = '';
$selected_profession_id = 0;

// Если отправлена форма выбора профессии
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profession_id']) && !isset($_POST['save_ratings'])) {
    $selected_profession_id = (int)$_POST['profession_id'];
}

// Сохранение оценок
if (isset($_POST['save_ratings']) && isset($_POST['profession_id'], $_POST['pvk_ids'], $_POST['ratings'])) {
    $profession_id = (int)$_POST['profession_id'];
    $pvk_ids = $_POST['pvk_ids'];
    $ratings = $_POST['ratings'];
    if (count($pvk_ids) < 5 || count($pvk_ids) > 10) {
        $error = "Выберите от 5 до 10 ПВК.";
        $selected_profession_id = $profession_id;
    } else {
        foreach ($pvk_ids as $pvk_id) {
            $pvk_id = (int)$pvk_id;
            $rating = (int)($ratings[$pvk_id] ?? 0);
            if ($rating < 1 || $rating > 10) continue;
            $stmt = $conn->prepare("REPLACE INTO expert_pvk_ratings (expert_id, profession_id, pvk_id, rating) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiii", $user_id, $profession_id, $pvk_id, $rating);
            $stmt->execute();
            $stmt->close();
        }
        $success = "Оценки сохранены!";
        $selected_profession_id = 0;
    }
}

// Получаем ПВК только если выбрана профессия
$pvk = [];
if ($selected_profession_id) {
    $res = $conn->query("SELECT id, category, name FROM pvk ORDER BY category, name");
    while ($row = $res->fetch_assoc()) {
        $pvk[] = $row;
    }
    // Группируем ПВК по категориям
    $pvk_by_cat = [];
    foreach ($pvk as $item) {
        $pvk_by_cat[$item['category']][] = $item;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Экспертная оценка профессий</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/background.css">
    <style>
        body { background: #181a1b; color: #f5f6fa; font-family: 'Roboto', Arial, sans-serif; }
        .container { max-width: 700px; margin: 4rem auto; background: rgba(255,255,255,0.07); border-radius: 18px; padding: 2rem; box-shadow: 0 8px 32px rgba(0,0,0,0.18); }
        h1, h2 { color: #ffd700; }
        .pvk-list { columns: 2; }
        .pvk-item { margin-bottom: 0.5rem; }
        .ratings { margin-top: 1.5rem; }
        .ratings input[type=number] { width: 60px; }
        .success { color: #4caf50; }
        .error { color: #ff5252; }
        .category { margin-top: 1.2rem; font-weight: bold; color: #8ecfff; }
        .btn { background: #007bff; color: #fff; border: none; padding: 0.7rem 1.5rem; border-radius: 7px; cursor: pointer; margin-top: 1.2rem; }
        .btn:hover { background: #0056b3; }
    </style>
    <script>
        function limitCheckboxes() {
            const checkboxes = document.querySelectorAll('.pvk-checkbox');
            checkboxes.forEach(cb => cb.addEventListener('change', function() {
                let checked = Array.from(checkboxes).filter(c => c.checked);
                if (checked.length > 10) {
                    this.checked = false;
                    alert('Можно выбрать не более 10 ПВК!');
                }
            }));
        }
        window.onload = limitCheckboxes;
    </script>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <h1>Экспертная оценка профессий</h1>
    <?php if ($success) echo "<div class='success'>$success</div>"; ?>
    <?php if ($error) echo "<div class='error'>$error</div>"; ?>
    <?php if (!$selected_profession_id): ?>
        <form method="post">
            <label for="profession_id">Профессия:</label>
            <select name="profession_id" id="profession_id" required>
                <option value="">Выберите профессию</option>
                <?php foreach ($professions as $prof): ?>
                    <option value="<?= $prof['id'] ?>"><?= htmlspecialchars($prof['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn" type="submit">Выбрать</button>
        </form>
    <?php else: ?>
        <?php if ($selected_profession_id && count($pvk) === 0): ?>
            <div style="color:#ff5252;">Для выбранной профессии не найдено ни одного ПВК!</div>
        <?php endif; ?>
        <div style="color:#ffd700; margin-bottom:10px;">[debug] Выбрана профессия ID: <?= $selected_profession_id ?>, ПВК найдено: <?= count($pvk) ?> </div>
        <form method="post">
            <input type="hidden" name="profession_id" value="<?= $selected_profession_id ?>">
            <div class="pvk-list">
                <?php if (count($pvk) > 0): ?>
                    <?php foreach ($pvk_by_cat as $cat => $pvks): ?>
                        <div class="category"><?= htmlspecialchars($cat) ?></div>
                        <?php foreach ($pvks as $item): ?>
                            <div class="pvk-item">
                                <label>
                                    <input type="checkbox" class="pvk-checkbox" name="pvk_ids[]" value="<?= $item['id'] ?>">
                                    <?= htmlspecialchars($item['name']) ?>
                                </label>
                                <input type="number" name="ratings[<?= $item['id'] ?>]" min="1" max="10" placeholder="Рейтинг" required>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button class="btn" type="submit" name="save_ratings">Сохранить оценки</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html> 