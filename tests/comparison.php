<?php
session_start();
require '../db-connect.php';

// Проверка и обработка AJAX запроса
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['score'])) {
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId === null) {
        echo "Ошибка: идентификатор пользователя не установлен.";
        exit;
    }
    $score = floatval($_POST['score']);  // Получаем и конвертируем score из строки в число
    saveResult($userId, $score);
    exit;  // Завершаем скрипт после обработки AJAX запроса
}

function getTestId($testType, $testName) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM tests WHERE test_type = ? AND test_name = ?");
    if ($stmt === false) {
        die("Ошибка подготовки запроса: " . $conn->error);
    }
    $stmt->bind_param("ss", $testType, $testName);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['id'];
    } else {
        $stmt->close();
        return null;
    }
}

function saveResult($userId, $score) {
    global $conn;
    $testType = "Оценка мышления";
    $testName = "сравнение";
    
    $testId = getTestId($testType, $testName);
    if ($testId !== null) {
        $stmt = $conn->prepare("INSERT INTO test_results (user_id, test_id, test_name, result) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            die("Ошибка подготовки запроса: " . $conn->error);
        }
        $stmt->bind_param("iisd", $userId, $testId, $testName, $score);
        if ($stmt->execute()) {
            echo "Результат успешно сохранен.";
        } else {
            echo "Ошибка при сохранении результата: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Ошибка: тест не найден в базе данных.";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест на сравнение</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }

        .container {
            max-width: 800px;
            padding: 20px;
            display: none; /* Initially hidden */
        }

        h1 {
            font-size: 36px;
            text-align: center;
        }

        .box {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
            border: 2px solid #ccc;
            margin-bottom: 20px;
        }

        .box-notice {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            height: 200px;
            border: 2px solid #ccc;
            margin-bottom: 20px;
            padding: 20px;
        }

        .box-notice p {
            margin: 10px 0;
            padding: 8px;
            border-bottom: 1px solid #ccc;
        }

        .box-notice p:last-child {
            border-bottom: none;
        }

        .big-letter {
            font-size: 72px;
            font-weight: bold;
        }

        .button-container {
            display: flex;
            justify-content: center;
        }

        button {
            padding: 10px 20px;
            margin: 0 10px;
            font-size: 16px;
            cursor: pointer;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .modal {
            display: block;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fefefe;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            text-align: center;
        }

        .result {
            font-size: 24px;
            margin-top: 20px;
        }

        .feedback {
            font-size: 24px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<!-- Модальное окно -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <h1>Тест на сравнение</h1>
        <h3>Вам будут показаны пары слов. Определите, какое слово больше по значению.</h3>
        <h2>Введите время теста:</h2>
        <input type="number" id="countdownInput" placeholder="Введите время (секунды)...">
        <button onclick="startCountdown()">Начать</button>
        <button onclick="goBack()">Назад</button>
        <h3>*После ввода времени браузер начнет отсчет от 3 до 0 для начала теста</h3>
    </div>
</div>

<div class="container" id="testContainer">
    <div class="box" id="boxContainer">
        <div class="big-letter" id="bigLetter">Загрузка...</div>
    </div>

    <div class="box-notice" id="boxNoticeContainer">
        <p id="feedback" class="feedback"></p>
        <p id="turn">Количество отвеченных вопросов: 0</p>
        <p id="countdownDisplay">Осталось...</p>
        <p id="correct_streak">Всего правильных ответов: 0</p>
    </div>

    <div class="button-container" id="buttonContainer">
        <button id="button1" onclick="chooseWord('left')">Левое слово больше</button>
        <button id="button2" onclick="chooseWord('right')">Правое слово больше</button>
        <button onclick="cancelTest()">Отменить тест</button>
    </div>

    <div class="result" id="resultDisplay"></div>
</div>

    <script>
    let countdown;
    let countdownDisplay = document.getElementById('countdownDisplay');
    let turnDisplay = document.getElementById('turn');
    let correctStreakDisplay = document.getElementById('correct_streak');
    let bigLetter = document.getElementById('bigLetter');
    let feedback = document.getElementById('feedback');
    let resultDisplay = document.getElementById('resultDisplay');
    let correctStreak = 0;
    let turns = 0;
    let testContainer = document.getElementById('testContainer');
    let modal = document.getElementById('myModal');
    let boxContainer = document.getElementById('boxContainer');
    let boxNoticeContainer = document.getElementById('boxNoticeContainer');
    let buttonContainer = document.getElementById('buttonContainer');
    let currentPair = -1;
    let interval;
    let responseReceived = false;
    let gameStarted = false;

    const wordPairs = [
        { left: "дом", right: "квартира", correct: "right" },
        { left: "река", right: "океан", correct: "right" },
        { left: "гора", right: "холм", correct: "left" },
        { left: "книга", right: "журнал", correct: "left" },
        { left: "автомобиль", right: "велосипед", correct: "left" },
        { left: "самолет", right: "вертолет", correct: "left" },
        { left: "город", right: "деревня", correct: "left" },
        { left: "море", right: "озеро", correct: "left" },
        { left: "лес", right: "парк", correct: "left" },
        { left: "школа", right: "университет", correct: "right" }
    ];

    function startCountdown() {
        let time = document.getElementById('countdownInput').value;
        if (!time || time <= 0) {
            alert('Пожалуйста, введите время для теста.');
            return;
        }
        countdown = 3;
        modal.style.display = 'none';
        testContainer.style.display = 'block';
        gameStarted = false;
        let countdownInterval = setInterval(() => {
            bigLetter.textContent = countdown;
            countdown--;
            if (countdown < 0) {
                clearInterval(countdownInterval);
                bigLetter.textContent = 'Начали!';
                setTimeout(() => {
                    startGame(time);
                }, 1000);
            }
        }, 1000);
    }

    function startGame(time) {
        countdown = time;
        gameStarted = true;
        interval = setInterval(function() {
            countdown--;
            countdownDisplay.textContent = 'Осталось ' + countdown + ' секунд';
            if (countdown <= 0) {
                clearInterval(interval);
                displayFinalResult();
            }
        }, 1000);
        changeWordPair();
    }

    function changeWordPair() {
        if (countdown <= 0) {
            return;
        }

        currentPair = Math.floor(Math.random() * wordPairs.length);
        let pair = wordPairs[currentPair];
        bigLetter.textContent = pair.left + " - " + pair.right;
        feedback.textContent = '';
        responseReceived = false;

        setTimeout(changeWordPair, 3000);
    }

    function chooseWord(side) {
        if (!gameStarted || responseReceived || countdown <= 0) {
            return;
        }
        responseReceived = true;
        
        let pair = wordPairs[currentPair];
        if (side === pair.correct) {
            correctStreak++;
            showFeedback(true);
        } else {
            showFeedback(false);
        }

        turns++;
        correctStreakDisplay.textContent = 'Всего правильных ответов: ' + correctStreak;
        turnDisplay.textContent = 'Количество отвеченных вопросов: ' + turns;
    }

    function showFeedback(isCorrect) {
        if (isCorrect) {
            feedback.textContent = 'Правильно';
            feedback.style.color = '#00FF00';
        } else {
            feedback.textContent = 'Неправильно';
            feedback.style.color = '#FF0000';
        }
    }

    function displayFinalResult() {
        let score = (correctStreak / turns) || 0;
        if (score === 0) {
            resultDisplay.innerHTML = 'Вы не прошли тест';
        } else {
            resultDisplay.innerHTML = `Всего правильных ответов: ${correctStreak}<br>Процент правильных ответов: ${(score * 100).toFixed(2)}%`;
            saveResult(score);
        }

        boxContainer.style.display = 'none';
        boxNoticeContainer.style.display = 'none';
        buttonContainer.style.display = 'none';
        
        setTimeout(() => {
            modal.style.display = 'flex';
            resetTest();
        }, 10000);
    }

    function saveResult(score) {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
        if (this.status >= 200 && this.status < 300) {
            console.log('Result saved successfully:', this.responseText);
        } else {
            console.log('Failed to save result:', this.statusText);
        }
    };
        xhr.onerror = function() {
        console.log('Network error.');
    };
        xhr.send("score=" + score);
    }

    function resetTest() {
        correctStreak = 0;
        turns = 0;
        correctStreakDisplay.textContent = 'Всего правильных ответов: 0';
        turnDisplay.textContent = 'Количество отвеченных вопросов: 0';
        bigLetter.textContent = 'Загрузка...';
        resultDisplay.innerHTML = '';
        boxContainer.style.display = 'block';
        boxNoticeContainer.style.display = 'block';
        buttonContainer.style.display = 'flex';
        document.getElementById('countdownInput').value = '';
        testContainer.style.display = 'none';
        modal.style.display = 'flex';
    }

function cancelTest() {
        clearInterval(interval);
        resetTest();
    }

    function goBack() {
        window.location.href = "tests.php";
    }

    function program() {
        modal.style.display = 'flex';
        testContainer.style.display = 'none';
    }

    program();
    </script>

</body>
</html>
<?php $conn->close(); ?>
