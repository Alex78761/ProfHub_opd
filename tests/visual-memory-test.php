<?php
session_start();
include('../db-connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $finalResult = $_POST['finalResult'];
    $user_id = $_SESSION['user_id'] ?? null;

    // Получение test_id по test_type и test_name
    $sql = "SELECT id FROM tests WHERE test_type = 'Оценка памяти' AND test_name = 'зрительная'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $test_id = $row['id'];

        // Сохранение результата в test_results
        $test_name = 'зрительная';
        $stmt = $conn->prepare("INSERT INTO test_results (user_id, test_id, test_name, result) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisd", $user_id, $test_id, $test_name, $finalResult);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Result saved successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error saving result."]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Test not found."]);
    }

    $conn->close();
    exit;
}
?>
<?php include '../header.php'; ?>
<link rel="stylesheet" href="../css/header.css">
<style>
  main.container {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 80vh;
  }
  .card {
    background: rgba(34, 40, 49, 0.98);
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(52,152,219,0.13), 0 1.5px 6px rgba(0,0,0,0.07);
    padding: 2.7rem 2.5rem 2.2rem 2.5rem;
    margin: 2.5rem auto;
    color: var(--text-color, #fff);
    max-width: 600px;
    width: 100%;
    transition: box-shadow 0.3s, transform 0.3s;
  }
  .card:hover {
    box-shadow: 0 12px 36px rgba(52,152,219,0.18), 0 2px 8px rgba(0,0,0,0.10);
    transform: translateY(-3px) scale(1.01);
  }
  .card h1 {
    font-size: 2.2rem;
    font-weight: 800;
    color: #39aaff;
    margin-bottom: 1.1rem;
    text-align: center;
    letter-spacing: 1px;
  }
  .card p {
    color: #bfc9d1;
    font-size: 1.13rem;
    margin-bottom: 1.1em;
    text-align: center;
  }
  .test-controls, .card > div[style*='display:flex'] {
    display: flex;
    justify-content: center;
    gap: 1.5em;
    margin: 1.7em 0 1.2em 0;
  }
  .button, .button-primary, .button-secondary {
    min-width: 140px;
    font-size: 1.13rem;
    font-weight: 700;
    border: none;
    outline: none;
    cursor: pointer;
    padding: 0.85em 1.7em;
    border-radius: 12px;
    transition: background 0.2s, color 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 8px rgba(52,152,219,0.08);
  }
  .button-primary {
    background: linear-gradient(90deg, #3498db 60%, #21d397 100%);
    color: #fff;
  }
  .button-primary:hover {
    background: linear-gradient(90deg, #217dbb 60%, #1abc9c 100%);
    box-shadow: 0 4px 16px rgba(52,152,219,0.18);
  }
  .button-secondary {
    background: #393e46;
    color: #e0e0e0;
  }
  .button-secondary:hover {
    background: #222831;
    color: #fff;
  }
  .input {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1.5px solid #2d9cdb;
    background: rgba(0,0,0,0.22);
    color: #fff;
    font-size: 1.08rem;
    margin-bottom: 1.2em;
    box-sizing: border-box;
    transition: border 0.2s, box-shadow 0.2s;
  }
  .input:focus {
    outline: none;
    border-color: #39aaff;
    box-shadow: 0 0 0 2px #39aaff33;
  }
  form {
    background: transparent;
    border-radius: 12px;
    padding: 0;
    margin-bottom: 1.7em;
  }
  #test-area {
    margin-top: 2em;
  }
  .numbers {
    font-size: 1.6em;
    text-align: center;
    margin-bottom: 22px;
    color: #fff;
    letter-spacing: 3px;
    font-weight: 600;
  }
  .result {
    font-size: 1.13em;
    margin-top: 1.1em;
    text-align: center;
    color: #ffd700;
    font-weight: 600;
  }
  .correct-count, .countdown {
    text-align: center;
    margin-top: 0.7em;
    color: #39aaff;
    font-size: 1.1em;
    font-weight: 600;
  }
  .hidden { display: none !important; }
</style>
<main class="container">
  <div class="card">
    <h1>Тест на зрительную память</h1>
    <p>В этом тесте вы должны будете запомнить и воспроизвести числа.</p>
    <p>Нажмите <b>Далее</b>, чтобы выбрать уровень сложности и время прохождения теста.</p>
    <div class="test-controls">
      <button type="button" class="button button-primary" id="next">Далее</button>
      <button type="button" class="button button-secondary" id="back"><a href="tests.php" id="back-button" style="color:inherit;text-decoration:none;">Назад</a></button>
    </div>
    <form class="hidden" id="settings-form">
        <label for="difficulty">Выберите уровень сложности:</label>
        <select id="difficulty" name="difficulty" onchange="updateTimeOptions()" class="input">
            <option value="easy">Лёгкий</option>
            <option value="medium">Средний</option>
            <option value="hard">Сложный</option>
            <option value="random">Случайный</option>
            <option value="order">Порядок</option>
        </select>
        <label for="time">Выберите время прохождения:</label>
        <select id="time" name="time" class="input"></select>
        <label for="customTime">Или введите свое время (в секундах):</label>
        <input type="text" id="customTime" name="customTime" class="input">
        <br>
        <button type="button" class="button button-primary" id="start">Начать тест</button>
    </form>
    <div id="test-area" class="hidden">
        <div class="numbers hidden"></div>
        <label for="answer" class="hidden">Введите числа в порядке по умолчанию через пробел.</label>
        <input type="text" id="answer" name="answer" class="input hidden" required>
        <button type="button" class="button button-primary hidden" id="check-answer">Проверить</button>
        <p class="result hidden"></p>
        <p class="correct-count">Всего правильных ответов: <span id="correct">0</span></p>
        <p class="countdown"></p>
        <button type="button" class="button button-secondary" id="cancel">Отменить тест</button>
    </div>
  </div>
</main>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
const numbersDiv = document.querySelector('.numbers');
const countdownEl = document.querySelector('.countdown');
const answerInput = document.querySelector('#answer');
const resultP = document.querySelector('.result');
const form = document.querySelector('#settings-form');
const startBtn = document.querySelector('#start');
const cancelBtn = document.querySelector('#cancel');
const nextBtn = document.querySelector('#next');
const backBtn = document.querySelector('#back');
const testArea = document.getElementById('test-area');
const correctCount = document.getElementById("correct");
const checkAnswerBtn = document.querySelector('#check-answer');

let reverse;
let timerId;
let countdownTimer;
let testDuration;
let correctAnswers = 0;
let resultTimes = [];
let correctRes = [];
let test_id = 11;
let testActive = false;
let answerAccepted = false;
let displayTime = 5000;
let difficulty = "easy";
let startTime;

const timeOptions = {
    easy: [30000, 60000, 120000],
    medium: [60000, 120000, 180000],
    hard: [120000, 180000, 240000],
    random: [60000, 120000, 240000],
    order: [60000, 120000, 180000]
};

const difficultyScores = {
    easy: 1,
    medium: 2,
    hard: 3,
    random: 2,
    order: 2
};

document.addEventListener('visibilitychange', () => {
    if (document.hidden && testActive) {
        cancelTest();
        alert('Тест был сброшен из-за смены вкладки или окна.');
    }
});
window.addEventListener('blur', () => {
    if (testActive) {
        cancelTest();
        alert('Тест был сброшен из-за перехода на другую вкладку.');
    }
});

function getRandomNumber() {
    return Math.random() < 0.5 ? 0 : 1;
}

function generateNumbers() {
    const numbers = [];
    let count;
    if (difficulty === "easy") {
        count = 2;
        while (numbers.length < count) {
            numbers.push(Math.floor(Math.random() * 9) + 1);
        }
    } else if (difficulty === "medium") {
        count = 2;
        let randomChoice = Math.random();
        if (randomChoice < 0.5) {
            numbers.push(Math.floor(Math.random() * 9) + 1);
            numbers.push(Math.floor(Math.random() * 90) + 10);
        } else {
            while (numbers.length < count) {
                numbers.push(Math.floor(Math.random() * 90) + 10);
            }
        }
    } else if (difficulty === "hard") {
        count = 3;
        while (numbers.length < count) {
            numbers.push(Math.floor(Math.random() * 90) + 10);
        }
    } else if (difficulty === "order") {
        count = Math.floor(Math.random() * 3) + 1;
        for (let i = 0; i < count; i++) {
            const randomNum = Math.random() < 0.5 ? Math.floor(Math.random() * 9) + 1 : Math.floor(Math.random() * 90) + 10;
            numbers.push(randomNum);
        }
    } else if (difficulty === "random") {
        count = Math.floor(Math.random() * 3) + 2;
        while (numbers.length < count) {
            numbers.push(Math.floor(Math.random() * 100));
        }
    }
    return numbers;
}

function displayNumbers(numbers) {
    numbersDiv.textContent = numbers.join(' ');
    numbersDiv.classList.remove('hidden');
    answerInput.classList.add('hidden');
    checkAnswerBtn.classList.add('hidden');
    document.querySelector('label[for="answer"]').classList.add('hidden');
}

function updateTimeOptions() {
    difficulty = document.getElementById("difficulty").value;
    const timeSelect = document.getElementById("time");
    timeSelect.innerHTML = '';
    timeOptions[difficulty].forEach(time => {
        const option = document.createElement('option');
        option.value = time;
        option.textContent = `${time / 1000} секунд`;
        timeSelect.appendChild(option);
    });
    displayTime = parseInt(timeSelect.value);
}

function startCountdown(duration, display, callback) {
    let timer = duration,
        minutes, seconds;
    countdownTimer = setInterval(() => {
        minutes = parseInt(timer / 60, 10);
        seconds = parseInt(timer % 60, 10);
        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;
        display.textContent = minutes + ":" + seconds;
        if (--timer < 0) {
            clearInterval(countdownTimer);
            callback();
        }
    }, 1000);
}

function startLearnNumbers() {
    if (!testActive) return;
    let numbers = generateNumbers();
    displayNumbers(numbers);
    answerAccepted = false;
    numbersDiv.style.display = 'block';
    numbersDiv.classList.remove('hidden');
    answerInput.classList.add('hidden');
    checkAnswerBtn.classList.add('hidden');
    document.querySelector('label[for="answer"]').classList.add('hidden');
    clearTimeout(timerId);
    timerId = setTimeout(() => {
        startGeneralTest();
    }, 5000);
}

function startGeneralTest() {
    if (!testActive) return;
    numbersDiv.classList.add('hidden');
    answerInput.classList.remove('hidden');
    checkAnswerBtn.classList.remove('hidden');
    document.querySelector('label[for="answer"]').classList.remove('hidden');
    countdownEl.textContent = "";
    startTime = Date.now();
    reverse = getRandomNumber();
    let label = document.querySelector('label[for="answer"]');
    if (reverse === 0) {
        label.textContent = "Введите числа в порядке по умолчанию через пробел.";
    } else {
        label.textContent = "Введите числа в обратном порядке через пробел.";
    }
    answerInput.value = '';
    answerInput.focus();
    clearTimeout(timerId);
    timerId = setTimeout(() => {
        checkAnswer();
    }, 5000);
}

function checkAnswer() {
    if (!testActive || answerAccepted) return;
    answerAccepted = true;
    clearTimeout(timerId);
    const answer = answerInput.value.trim();
    let numbers = (reverse === 0) ? numbersDiv.textContent.split(' ').map(n => parseInt(n)) : numbersDiv.textContent.split(' ').map(n => parseInt(n)).reverse();
    let correct = numbers.every((num, index) => num === parseInt(answer.split(' ')[index]));
    const time = Date.now() - startTime;
    if (correct) {
        resultP.textContent = `Верно. Вы решили задание за ${time} мс.`;
        resultP.style.color = 'green';
        correctAnswers++;
    } else {
        resultP.textContent = `Неверно.`;
        resultP.style.color = 'red';
    }
    correctCount.textContent = correctAnswers;
    resultTimes.push(time);
    correctRes.push(correct ? 1 : 0);
    resultP.classList.remove('hidden');
    if (testActive && difficulty !== "order") {
        setTimeout(startLearnNumbers, 1000);
    }
}

function cancelTest() {
    clearTimeout(timerId);
    clearTimeout(countdownTimer);
    testActive = false;
    resetTest();
    document.querySelectorAll('.text').forEach(el => el.classList.remove('hidden'));
}

function resetTest() {
    startBtn.classList.remove('hidden');
    form.classList.add('hidden');
    testArea.classList.add('hidden');
    nextBtn.classList.remove('hidden');
    backBtn.classList.remove('hidden');
    cancelBtn.classList.remove('hidden');
    correctAnswers = 0;
    correctCount.textContent = correctAnswers;
    countdownEl.textContent = "";
    resultP.textContent = "";
    clearTimeout(timerId);
    clearTimeout(countdownTimer);
    numbersDiv.classList.add('hidden');
    answerInput.classList.add('hidden');
    checkAnswerBtn.classList.add('hidden');
    document.querySelector('label[for="answer"]').classList.add('hidden');
}

function displayFinalResults() {
    const elementsToHide = testArea.querySelectorAll(':scope > :not(.result):not(.correct-count)');
    elementsToHide.forEach(element => {
        element.classList.add('hidden');
    });
    resultP.classList.remove('hidden');
    numbersDiv.style.display = 'none';
}

function calculateFinalScore() {
    const baseScore = correctAnswers * difficultyScores[difficulty];
    return (baseScore / (testDuration / 1000)).toFixed(2);
}

function displayFinalScore() {
    const score = calculateFinalScore();
    if (score > 0) {
        resultP.textContent = `Ваш финальный результат: ${score} баллов в секунду.`;
        resultP.style.color = 'blue';
        save(score);
    } else {
        resultP.textContent = `Вы не прошли тест`;
        resultP.style.color = 'red';
    }
    displayFinalResults();
    setTimeout(() => {
        document.querySelectorAll('.text').forEach(el => el.classList.remove('hidden'));
        resetTest();
    }, 10000);
}

function displayResultsDuringTest() {
    resultP.classList.remove('hidden');
    countdownEl.classList.remove('hidden');
}

nextBtn.addEventListener('click', () => {
    document.querySelectorAll('.text').forEach(el => el.classList.add('hidden'));
    nextBtn.classList.add('hidden');
    backBtn.classList.add('hidden');
    form.classList.remove('hidden');
});

startBtn.addEventListener('click', () => {
    difficulty = document.getElementById("difficulty").value;
    let customTime = document.getElementById("customTime").value;
    testDuration = customTime ? parseInt(customTime) * 1000 : parseInt(document.getElementById("time").value);
    correctAnswers = 0;
    correctCount.textContent = correctAnswers;
    form.classList.add('hidden');
    testArea.classList.remove('hidden');
    cancelBtn.classList.remove('hidden');
    displayResultsDuringTest();
    testActive = true;
    startLearnNumbers();
    startCountdown(testDuration / 1000, countdownEl, () => {
        testActive = false;
        displayFinalScore();
    });
});

cancelBtn.addEventListener('click', cancelTest);

checkAnswerBtn.addEventListener('click', (event) => {
    event.preventDefault();
    checkAnswer();
});

answerInput.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
        event.preventDefault();
        checkAnswer();
    }
});

function save(score) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if (this.status >= 200 && this.status < 300) {
            console.log('Result saved successfully:', this.responseText);
        } else {
            console.log('Failed to save result:', this.statusText);
        }
    };
    xhr.onerror = function () {
        console.log('Network error.');
    };
    xhr.send("finalResult=" + encodeURIComponent(score));
}

updateTimeOptions();
</script>
