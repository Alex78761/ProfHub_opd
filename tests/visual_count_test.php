<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/count-test.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/background.css">
    <title>Сложение</title>
    <style>
        
    </style>
</head>
<body>
<div class="background"></div>
<?php include '../header.php'; ?>
<main class="container">
  <div class="card">
    <h2>Оценка скорости сложения в уме</h2>
    <br>
    <button id="startButton" onclick="startTest()">Начать тест</button>
    <div id="question" style="display: none;"></div>
    <div class="button-container" style="display: none;">
        <button id="evenButton" onclick="checkAnswer(true)">Четное</button>
        <button id="oddButton" onclick="checkAnswer(false)">Нечетное</button>
    </div>
    <br>
    <br>
    <p id="previousReactionTime" style="display: none;"></p>
    <p id="currentReactionTime" style="display: none;"></p>
    <p id="averageReactionTime" style="display: none;"></p>
    <p id="result" style="display: none;"></p>
    <p id="timer" style="display: none;"></p>
    <button id="cancelButton" onclick="cancelTest()" style="display: none;">Отмена</button>
    <br>
    <br>
    <a href="tests.php">Назад</a> 
    <br>
    <br>
    <a href="../index.php">Домой</a>
  </div>
</main>
<script src="../js/visual_count_test.js"></script>
</body>
</html>
