const startButton = document.getElementById('startButton');
const cancelButton = document.getElementById('cancelButton');
const buttonContainer = document.querySelector('.button-container');
const evenButton = document.getElementById('evenButton');
const oddButton = document.getElementById('oddButton');
const resultDisplay = document.getElementById('result');
const timerDisplay = document.getElementById('timer');
const previousReactionTimeDisplay = document.getElementById('previousReactionTime');
const currentReactionTimeDisplay = document.getElementById('currentReactionTime');
const averageReactionTimeDisplay = document.getElementById('averageReactionTime');
const questionDiv = document.getElementById('question');

let reactionTimes = [];
let questions = [];
let currentSum;
let questionsRemaining = 10;
let questionStartTime;
let previousReactionTime = null;
let testActive = false;

function generateQuestions() {
    questions = [];
    for (let i = 0; i < 10; i++) {
        const num1 = Math.floor(Math.random() * 100);
        const num2 = Math.floor(Math.random() * 100);
        const sum = num1 + num2;
        questions.push({ question: `${num1} + ${num2} = ?`, sum: sum });
    }
}

function startTest() {
    testActive = true;
    startButton.style.display = 'none';
    cancelButton.style.display = 'inline-block';
    resultDisplay.style.display = 'none';
    buttonContainer.style.display = 'flex';
    previousReactionTimeDisplay.style.display = 'block';
    averageReactionTimeDisplay.style.display = 'block';
    currentReactionTimeDisplay.style.display = 'block';
    timerDisplay.style.display = 'block';
    questionsRemaining = 10;
    reactionTimes = [];
    previousReactionTime = null;
    generateQuestions();
    showNextQuestion();
}

function showNextQuestion() {
    if (questionsRemaining > 0) {
        const { question, sum } = questions[10 - questionsRemaining];
        questionDiv.textContent = question;
        questionDiv.style.display = 'block';
        currentSum = sum;
        questionStartTime = Date.now();
        questionsRemaining--;
        updateQuestionsRemaining();
    } else {
        endTest();
    }
}

evenButton.onclick = function() { checkAnswer(true); };
oddButton.onclick = function() { checkAnswer(false); };

function checkAnswer(isEven) {
    if (!testActive) return;
    const isSumEven = currentSum % 2 === 0;
    const isCorrect = (isEven && isSumEven) || (!isEven && !isSumEven);
    const reactionTime = (Date.now() - questionStartTime) / 1000;
    reactionTimes.push(reactionTime);
    previousReactionTime = reactionTime;
    updateReactionTimeDisplays(reactionTime);
    if (isCorrect) {
        showNextQuestion();
    } else {
        resultDisplay.textContent = 'Неправильно!';
        resultDisplay.style.display = 'block';
        setTimeout(() => {
            resultDisplay.style.display = 'none';
            showNextQuestion();
        }, 1000);
    }
}

function updateQuestionsRemaining() {
    timerDisplay.textContent = `Осталось вопросов: ${questionsRemaining}`;
}

function updateReactionTimeDisplays(reactionTime) {
    currentReactionTimeDisplay.textContent = `Текущее время реакции: ${reactionTime !== null ? reactionTime.toFixed(2) : '—'}`;
    previousReactionTimeDisplay.textContent = `Предыдущее время реакции: ${previousReactionTime !== null ? previousReactionTime.toFixed(2) : '—'}`;
    if (reactionTimes.length > 0) {
        const avg = reactionTimes.reduce((a, b) => a + b, 0) / reactionTimes.length;
        averageReactionTimeDisplay.textContent = `Среднее время реакции: ${avg.toFixed(2)}`;
    } else {
        averageReactionTimeDisplay.textContent = '';
    }
}

function endTest() {
    testActive = false;
    startButton.style.display = 'block';
    cancelButton.style.display = 'none';
    buttonContainer.style.display = 'none';
    questionDiv.style.display = 'none';
    timerDisplay.style.display = 'none';
    currentReactionTimeDisplay.style.display = 'none';
    previousReactionTimeDisplay.style.display = 'none';
    averageReactionTimeDisplay.style.display = 'none';
    if (reactionTimes.length > 0) {
        const avg = reactionTimes.reduce((a, b) => a + b, 0) / reactionTimes.length;
        resultDisplay.textContent = `Тест завершён! Среднее время реакции: ${avg.toFixed(2)} секунд.`;
        resultDisplay.style.display = 'block';
        saveAvgReactionTime(avg);
    }
}

function saveAvgReactionTime(avg) {
    const formData = new FormData();
    formData.append('avgReactionTime', avg);
    fetch('save_visual_count_test.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log(data);
    })
    .catch(error => {
        console.error('Ошибка при сохранении результата:', error);
    });
}

cancelButton.onclick = function() {
    testActive = false;
    startButton.style.display = 'block';
    cancelButton.style.display = 'none';
    buttonContainer.style.display = 'none';
    questionDiv.style.display = 'none';
    timerDisplay.style.display = 'none';
    currentReactionTimeDisplay.style.display = 'none';
    previousReactionTimeDisplay.style.display = 'none';
    averageReactionTimeDisplay.style.display = 'none';
    resultDisplay.style.display = 'none';
}; 