<?php
// Подключение к базе данных
require_once "db-connect.php";

session_start();

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Гость';

// Получаем список профессий из базы данных
$query_professions = "SELECT * FROM professions";
$result_professions = $conn->query($query_professions);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/rated.css">
    <link rel="stylesheet" href="css/header.css">
    <title>Результаты оценок профессий</title>
</head>
<body>
<header>
        <p><a href="index.php">Домой</a></p>
        <?php if (isset($_SESSION['username'])): ?>
            <p><a href="account.php">Личный кабинет</a></p>
        <?php endif; ?>
    </header>
    <h2>Результаты оценок профессий</h2>
    <p><?php echo $username; ?>, Ознакомьтесь с результатами оценок профессий:</p>
    <?php while ($row_profession = $result_professions->fetch_assoc()): ?>
        <div class="profession">
            <h3><?php echo $row_profession['name']; ?></h3>
            <p><span style="color:#888;">Описание:</span> <?php echo $row_profession['description']; ?></p>
            <div class="block-title">Оценка экспертов (средняя по ПВК)</div>
            <?php
            // Получаем среднюю оценку для каждой ПВК от экспертов
            $profession_id = $row_profession['id'];
            $query_expert_avg_ratings = "SELECT 
                                  pvk.id as pvk_id,
                                  pvk.name, 
                                  AVG(ratings.rating) as avg_rating
                                  FROM pvk
                                  LEFT JOIN ratings ON pvk.id = ratings.pvk_id
                                  WHERE ratings.profession_id = $profession_id
                                  AND ratings.user_id IN (SELECT user_id FROM users WHERE role = 'expert')
                                  GROUP BY pvk.id, pvk.name
                                  HAVING avg_rating IS NOT NULL";
            $result_expert_avg_ratings = $conn->query($query_expert_avg_ratings);
            $has_expert = false;
            while ($row_expert_avg_rating = $result_expert_avg_ratings->fetch_assoc()): $has_expert = true; ?>
                <div class="pvk">
                    <span><?php echo $row_expert_avg_rating['name']; ?></span>
                </div>
                <div class="progress-bar">
                    <div class="progress-bar-inner" style="width: <?php echo ($row_expert_avg_rating['avg_rating'] * 10); ?>%;"></div>
                </div>
                <span class="progress-label"><?php echo number_format($row_expert_avg_rating['avg_rating'], 1); ?></span>
                <!-- Список экспертов по ПВК -->
                <details style="margin-bottom:8px;">
                  <summary style="color:#8ecfff; cursor:pointer; font-size:0.98em;">Показать экспертов</summary>
                  <?php
                  $pvk_id = $row_expert_avg_rating['pvk_id'];
                  $experts_query = "SELECT u.username, r.rating FROM ratings r JOIN users u ON r.user_id = u.id WHERE r.profession_id = $profession_id AND r.pvk_id = $pvk_id AND u.role = 'expert'";
                  $experts_result = $conn->query($experts_query);
                  if ($experts_result->num_rows > 0) {
                      while ($expert = $experts_result->fetch_assoc()) {
                          echo '<div style=\'color:#fff; margin-left:1em;\'><b style=\'color:#8ecfff;\'>' . htmlspecialchars($expert['username']) . '</b>: ' . $expert['rating'] . '</div>';
                      }
                  } else {
                      echo '<div style=\'color:#888; margin-left:1em;\'>Нет данных</div>';
                  }
                  ?>
                </details>
            <?php endwhile; ?>
            <?php if (!$has_expert): ?>
                <div style="color:#888; margin-bottom:12px;">Нет оценок от экспертов</div>
            <?php endif; ?>

            <?php
            // Если пользователь гость, показываем только его оценки
            if (!isset($_SESSION['user_id'])): ?>
                <?php if (isset($_SESSION['guest_ratings'][$profession_id])): ?>
                    <div class="block-title">Ваша оценка</div>
                    <?php foreach ($_SESSION['guest_ratings'][$profession_id] as $pvk_id => $rating): ?>
                        <?php if ($rating > 0): ?>
                            <?php
                            // Получаем имя ПВК по его ID
                            $query_pvk_name = "SELECT name FROM pvk WHERE id = $pvk_id";
                            $result_pvk_name = $conn->query($query_pvk_name);
                            $pvk_name = $result_pvk_name->fetch_assoc()['name'];
                            ?>
                            <div class="pvk">
                                <span><?php echo $pvk_name; ?></span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-bar-inner" style="width: <?php echo ($rating * 10); ?>%; background: linear-gradient(90deg, #f39c12 0%, #e67e22 100%);"></div>
                            </div>
                            <span class="progress-label"><?php echo $rating; ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>

            <?php
            // Если пользователь зарегистрирован и имеет оценки, показываем их
            if (isset($_SESSION['user_id'])): ?>
                <?php
                $user_id = $_SESSION['user_id'];
                $query_user_rating = "SELECT pvk.name, ratings.rating
                                      FROM ratings
                                      LEFT JOIN pvk ON ratings.pvk_id = pvk.id
                                      WHERE ratings.profession_id = $profession_id
                                      AND ratings.user_id = $user_id";
                $result_user_rating = $conn->query($query_user_rating);
                if ($result_user_rating->num_rows > 0): ?>
                    <div class="block-title">Ваша оценка</div>
                    <?php while ($row_user_rating = $result_user_rating->fetch_assoc()): ?>
                        <div class="pvk">
                            <span><?php echo $row_user_rating['name']; ?></span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-bar-inner" style="width: <?php echo ($row_user_rating['rating'] * 10); ?>%; background: linear-gradient(90deg, #f39c12 0%, #e67e22 100%);"></div>
                        </div>
                        <span class="progress-label"><?php echo $row_user_rating['rating']; ?></span>
                    <?php endwhile; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</body>
</html>

<?php
// Закрытие соединения с базой данных
$conn->close();
?>
