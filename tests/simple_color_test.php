<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/color-test.css">
  <link rel="stylesheet" href="../css/header.css">
  <link rel="stylesheet" href="../css/background.css">
    <title>Оценка скорости реакции на цвет</title>
</head>
<body>
    <div class="background"></div>
<header id="header">
        <p><a href="../index.php">Домой</a></p>
        <?php if (isset($_SESSION['username'])): ?>
            <p><a href="../account.php">Личный кабинет</a></p>
        <?php endif; ?>
    </header>
    <div class="container">
    <h2 id="test-heading">Оценка скорости реакции на цвет</h2>
        <div id="statsBlock" style="display: none;">
            <p id="changeCounter">Смен цветов осталось: <span id="changeCounterDisplay">10</span></p>
            <p id="timer">Время реакции: <span id="reactionTimeDisplay">0</span> сек.</p>
            <p id="prevReactionTime">Прошлое время реакции: <span id="prevReactionTimeDisplay">-</span> сек.</p>
            <p id="avgReactionTime">Среднее время реакции: <span id="avgReactionTimeDisplay">-</span> сек.</p>
        </div>
        <button id="startButton">Начать</button>
        <button id="cancelButton" style="display: none;">Остановить тест</button>
    <p id="instruction" style="display: none;">Нажмите когда цвет изменится</p>
        <div id="colorBox" class="colorbox" style="display: none;"></div>
        <div id="resultText" class="result-text"></div>
    <br>
        <a href="tests.php">Вернуться</a>
    </div>
    <script src="../js/simple_color_test_script.js"></script>
</body>
</html>

