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
let reactionTimes = []; // Массив времен реакции
let questions = []; // Массив вопросов
let countdown;
let currentSum;
let questionsRemaining = 10; // Общее количество вопросов
let isAnsweredCorrectly = false; // Переменная для отслеживания правильности ответа
let isReactionRecorded = false; // Переменная для отслеживания фиксации времени реакции
let questionStartTime; // Переменная для хранения времени начала вопроса

// Функция для генерации вопросов
function generateQuestions() {
    for (let i = 0; i < 10; i++) {
        const num1 = Math.floor(Math.random() * 100);
        const num2 = Math.floor(Math.random() * 100);
        const sum = num1 + num2;
        questions.push({ question: `${num1} плюс ${num2} равно ?`, sum: sum });
    }
}

// Функция для начала теста
function startTest() {
    startButton.style.display = 'none';
    cancelButton.style.display = 'inline-block';
    resultDisplay.style.display = 'block';
    buttonContainer.style.display = 'flex';
    previousReactionTimeDisplay.style.display = 'block';
    averageReactionTimeDisplay.style.display = 'block';
    currentReactionTimeDisplay.style.display = 'block';
    timerDisplay.style.display = 'block';
    questionsRemaining = 10;
    reactionTimes = [];
    updateQuestionsRemaining();
    generateQuestions();
    speakNextQuestion();
    countdown = setInterval(speakNextQuestion, 6000);
}

// Функция для озвучивания следующего вопроса
function speakNextQuestion() {
    if (questionsRemaining > 0) {
        if (questions.length === 0) {
            generateQuestions();
        }
        const { question, sum } = questions.shift();
        const speechText = speakText(question, 0.05);
        currentSum = sum;
        isAnsweredCorrectly = false;
        isReactionRecorded = false;
        questionStartTime = Date.now();

        speechText.addEventListener('end', function() {
            questionStartTime = Date.now(); // Начинаем отсчет после окончания речи
        });

        questionsRemaining--;
        updateQuestionsRemaining();
    } else {
        saveAvgReactionTime();
        endTest();
    }
}

// Функция для озвучивания текста
function speakText(text, volume) {
    const speechSynthesis = window.speechSynthesis;
    const speechText = new SpeechSynthesisUtterance(text);
    speechText.volume = volume;
    speechText.lang = 'ru-RU';
    speechSynthesis.speak(speechText);
    return speechText;
}

// Функция для обработки нажатия кнопки
function handleButtonClick(isEven) {
    if (!isReactionRecorded && currentSum !== undefined) {
    const isSumEven = currentSum % 2 === 0;
    const isCorrect = (isEven && isSumEven) || (!isEven && !isSumEven);
    
        if (isCorrect) {
            const reactionTime = (Date.now() - questionStartTime) / 1000;
        reactionTimes.push(reactionTime);
            updateReactionTimeDisplays(reactionTime);
            isReactionRecorded = true;
            
            if (reactionTimes.length === 10) {
                saveAvgReactionTime();
                endTest();
            }
        } else {
            resultDisplay.textContent = 'Неправильно!';
            resultDisplay.style.display = 'block';
            setTimeout(() => {
                resultDisplay.style.display = 'none';
            }, 1000);
        }
    }
}

// Добавляем обработчики событий для кнопок
startButton.addEventListener('click', startTest);
cancelButton.addEventListener('click', cancelTest);
evenButton.addEventListener('click', () => handleButtonClick(true));
oddButton.addEventListener('click', () => handleButtonClick(false));

// Функция для обновления оставшихся вопросов
function updateQuestionsRemaining() {
    timerDisplay.textContent = `Осталось вопросов: ${questionsRemaining}`;
}

// Функция для обновления отображения времени реакции
function updateReactionTimeDisplays(reactionTime) {
    currentReactionTimeDisplay.textContent = `Текущее время реакции: ${reactionTime.toFixed(2)} сек.`;
    if (reactionTimes.length > 1) {
        const previousTime = reactionTimes[reactionTimes.length - 2];
        previousReactionTimeDisplay.textContent = `Предыдущее время реакции: ${previousTime.toFixed(2)} сек.`;
    }
    const avgTime = reactionTimes.reduce((a, b) => a + b, 0) / reactionTimes.length;
    averageReactionTimeDisplay.textContent = `Среднее время реакции: ${avgTime.toFixed(2)} сек.`;
}

// Функция для завершения теста
function endTest() {
    clearInterval(countdown);
    startButton.style.display = 'block';
    cancelButton.style.display = 'none';
    buttonContainer.style.display = 'none';
    timerDisplay.style.display = 'none';
    currentReactionTimeDisplay.style.display = 'none';
    previousReactionTimeDisplay.style.display = 'none';
    averageReactionTimeDisplay.style.display = 'none';
    
    if (reactionTimes.length > 0) {
        const avgTime = reactionTimes.reduce((a, b) => a + b, 0) / reactionTimes.length;
        resultDisplay.textContent = `Тест завершен! Среднее время реакции: ${avgTime.toFixed(2)} секунд`;
        resultDisplay.style.display = 'block';
    }
    
    reactionTimes = [];
    questions = [];
    questionsRemaining = 10;
    isAnsweredCorrectly = false;
    currentSum = undefined;
}

// Функция для сохранения среднего времени реакции
function saveAvgReactionTime() {
    if (reactionTimes.length > 0) {
    const averageReactionTime = reactionTimes.reduce((acc, val) => acc + val, 0) / reactionTimes.length;
        
        // Создаем объект с данными
        const data = new URLSearchParams();
        data.append('avgReactionTime', averageReactionTime);

    fetch('save_count_test.php', {
        method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: data
    })
        .then(response => response.text())
    .then(data => {
            console.log('Ответ сервера:', data);
            resultDisplay.textContent = `Тест завершен! Среднее время реакции: ${averageReactionTime.toFixed(2)} секунд`;
            resultDisplay.style.display = 'block';
    })
    .catch(error => {
            console.error('Ошибка при сохранении результатов:', error);
            resultDisplay.textContent = 'Произошла ошибка при сохранении результатов';
            resultDisplay.style.display = 'block';
    });
    }
}

// Функция для отмены теста
function cancelTest() {
    clearInterval(countdown);
    endTest();
}
