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
        .container { max-width: 900px; margin: 3rem auto; background: rgba(255,255,255,0.10); border-radius: 22px; padding: 2.5rem 2rem; box-shadow: 0 8px 32px rgba(0,0,0,0.18); }
        h1, h2 { color: #ffd700; text-align: center; margin-bottom: 2rem; display: flex; align-items: center; justify-content: center; gap: 0.7em; text-shadow: 0 4px 24px #000, 0 1px 0 #fff2; }
        .icon-prof { font-size: 2.2em; color: #ffd700; filter: drop-shadow(0 2px 8px #ffd70044); }
        .start-form { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1.5rem; padding: 2.5rem 2rem; background: linear-gradient(120deg, rgba(0,0,0,0.25) 60%, rgba(0,123,255,0.10) 100%); border-radius: 18px; box-shadow: 0 8px 32px rgba(0,0,0,0.18); backdrop-filter: blur(12px); animation: fadeIn 0.7s; }
        .start-form label { font-size: 1.18em; color: #ccc; margin-bottom: 0.5em; letter-spacing: 0.5px; }
        .start-form select {
            font-size: 1.22em; padding: 0.8em 1.5em; border-radius: 10px; border: 2px solid #ffd700; background: #23272e; color: #ffd700; min-width: 280px; margin-bottom: 1em; transition: border 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 12px #ffd70022;
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="%23ffd700" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 1.2em center;
            background-size: 1.5em;
        }
        .start-form select:focus { border: 2px solid #007bff; outline: none; box-shadow: 0 0 0 3px #007bff33; }
        .btn {
            background: linear-gradient(90deg, #ffd700 0%, #ffb300 100%);
            color: #222;
            border: none;
            padding: 1.1rem 2.5rem;
            border-radius: 12px;
            cursor: pointer;
            margin-top: 2.2rem;
            font-size: 1.18em;
            font-weight: 600;
            box-shadow: 0 4px 16px #ffd70033, 0 2px 8px rgba(0,0,0,0.10);
            transition: background 0.2s, box-shadow 0.2s, transform 0.1s;
        }
        .btn:hover {
            background: linear-gradient(90deg, #ffb300 0%, #ffd700 100%);
            box-shadow: 0 6px 24px #ffd70055, 0 2px 8px rgba(0,0,0,0.13);
            transform: translateY(-2px) scale(1.03);
        }
        .btn:active {
            background: #ffd700;
            box-shadow: 0 2px 8px #ffd70022;
            transform: scale(0.98);
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(30px);} to { opacity: 1; transform: none; } }
        .pvk-list { display: flex; flex-wrap: wrap; gap: 1.5rem; margin-top: 1.5rem; }
        .pvk-card {
            background: rgba(255,255,255,0.18);
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.10);
            padding: 1.2rem 1.2rem 1.2rem 1.5rem;
            min-width: 320px;
            flex: 1 1 320px;
            display: flex;
            align-items: center;
            gap: 1.2rem;
            transition: box-shadow 0.2s, background 0.2s;
            border: 2px solid transparent;
        }
        .pvk-card.selected {
            border: 2px solid #007bff;
            background: rgba(0,123,255,0.08);
        }
        .pvk-checkbox {
            width: 22px; height: 22px; accent-color: #007bff; margin-right: 10px;
        }
        .pvk-name { font-size: 1.15em; font-weight: 500; color: #fff; }
        .pvk-rating {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .pvk-rating input[type=number] {
            width: 70px;
            font-size: 1.1em;
            border-radius: 8px;
            border: 1px solid #bbb;
            padding: 6px 10px;
            background: #fff;
            color: #222;
        }
        .category {
            width: 100%;
            margin-top: 2.2rem;
            margin-bottom: 0.7rem;
            font-weight: bold;
            color: #8ecfff;
            font-size: 1.18em;
            letter-spacing: 0.5px;
            border-left: 5px solid #8ecfff;
            padding-left: 10px;
        }
        .success { color: #4caf50; text-align: center; font-size: 1.1em; margin-bottom: 1.2rem; }
        .error { color: #ff5252; text-align: center; font-size: 1.1em; margin-bottom: 1.2rem; }
        @media (max-width: 900px) {
            .container { padding: 1rem; }
            .pvk-list { flex-direction: column; gap: 1rem; }
            .pvk-card { min-width: 0; width: 100%; }
        }
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
                // Визуальное выделение карточки
                checkboxes.forEach(c => c.closest('.pvk-card').classList.toggle('selected', c.checked));
            }));
        }
        window.onload = limitCheckboxes;
    </script>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <h1><span class="icon-prof"><i class="fas fa-briefcase"></i></span>Экспертная оценка профессий</h1>
    <?php if ($success) echo "<div class='success'>$success</div>"; ?>
    <?php if ($error) echo "<div class='error'>$error</div>"; ?>
    <?php if (!$selected_profession_id): ?>
        <form method="post" class="start-form">
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
                            <div class="pvk-card">
                                <input type="checkbox" class="pvk-checkbox" name="pvk_ids[]" value="<?= $item['id'] ?>" onchange="document.getElementById('rating_<?= $item['id'] ?>').required = this.checked; document.getElementById('rating_<?= $item['id'] ?>').disabled = !this.checked;">
                                <span class="pvk-name"><?= htmlspecialchars($item['name']) ?></span>
                                <span class="pvk-rating">
                                    <label for="rating_<?= $item['id'] ?>" style="color:#bbb;font-size:0.95em;">Рейтинг:</label>
                                    <input type="number" id="rating_<?= $item['id'] ?>" name="ratings[<?= $item['id'] ?>]" min="1" max="10" placeholder="1-10" disabled>
                                </span>
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