const colorBox = document.getElementById('colorBox');
const instruction = document.getElementById('instruction');
const timerDisplay = document.getElementById('reactionTimeDisplay');
const prevReactionTimeDisplay = document.getElementById('prevReactionTimeDisplay');
const avgReactionTimeDisplay = document.getElementById('avgReactionTimeDisplay');
const changeCounterDisplay = document.getElementById('changeCounterDisplay');
const resultText = document.getElementById('resultText');
const statsBlock = document.getElementById('statsBlock');
const startButton = document.getElementById('startButton');
const cancelButton = document.getElementById('cancelButton');

let startTime, endTime, prevReactionTime;
let reactionTimes = [];
let greenAppearances = 0;
let isTestActive = false;
let greenClickHandled = false;
let colorTimeout;

// Функция для обработки клика
function handleClick() {
    if (!isTestActive || colorBox.style.backgroundColor !== 'green' || greenClickHandled) return;

    endTime = new Date();
    const reactionTime = (endTime - startTime) / 1000;
    
    // Обновляем отображение времени реакции
    timerDisplay.textContent = reactionTime.toFixed(3);
    
    // Сохраняем время реакции и обновляем предыдущее время
    reactionTimes.push(reactionTime);
    if (prevReactionTime !== undefined) {
        prevReactionTimeDisplay.textContent = prevReactionTime.toFixed(3);
    }
    prevReactionTime = reactionTime;

    // Обновляем среднее время
    const avgReactionTime = reactionTimes.reduce((a, b) => a + b, 0) / reactionTimes.length;
    avgReactionTimeDisplay.textContent = avgReactionTime.toFixed(3);

    greenClickHandled = true;
    colorBox.style.backgroundColor = 'black';
    greenAppearances++;
    updateChangeCounter();
    
    if (greenAppearances < 10) {
        setTimeout(alternateColors, 1000);
    } else {
        finishTest();
    }
}

// Функция для установки зеленого цвета
function setGreenColor() {
    if (!isTestActive) return;

    colorBox.style.backgroundColor = 'green';
    startTime = new Date();
    greenClickHandled = false;

    // Генерируем случайную задержку от 2 до 5 секунд
    const delay = Math.floor(Math.random() * (5000 - 2000 + 1)) + 2000;
    
    colorTimeout = setTimeout(() => {
        if (!isTestActive) return;
        
        colorBox.style.backgroundColor = 'black';
        greenAppearances++;
        updateChangeCounter();
        
        if (greenAppearances < 10) {
            setTimeout(alternateColors, 1000);
        } else {
            finishTest();
        }
    }, delay);
}

// Функция для чередования цветов
function alternateColors() {
    if (!isTestActive) return;

    if (greenAppearances < 10) {
        setGreenColor();
    } else {
        finishTest();
    }
}

// Функция для обновления счетчика
function updateChangeCounter() {
    changeCounterDisplay.textContent = 10 - greenAppearances;
}

// Функция для начала теста
function startTest() {
    isTestActive = true;
    reactionTimes = [];
    greenAppearances = 0;
    prevReactionTime = undefined;
    
    // Обновляем UI
    startButton.style.display = 'none';
    instruction.style.display = 'block';
    colorBox.style.display = 'block';
    statsBlock.style.display = 'block';
    cancelButton.style.display = 'inline-block';
    resultText.textContent = '';
    
    // Добавляем обработчик клика
    colorBox.addEventListener('click', handleClick);
    
    // Начинаем тест
    updateChangeCounter();
    alternateColors();
}

// Функция для завершения теста
function finishTest() {
    isTestActive = false;
    clearTimeout(colorTimeout);
    
    const avgReactionTime = reactionTimes.reduce((a, b) => a + b, 0) / reactionTimes.length;
    
    // Сохраняем результат
    saveReactionTime(avgReactionTime);
    
    // Обновляем UI
    colorBox.style.backgroundColor = 'black';
    colorBox.style.display = 'none';
    instruction.style.display = 'none';
    cancelButton.style.display = 'none';
    startButton.style.display = 'block';
    
    // Отображаем результат
    resultText.innerHTML = `
        <h3>Тест завершен!</h3>
        <p>Ваше среднее время реакции: ${avgReactionTime.toFixed(3)} сек.</p>
        <p>Лучшее время: ${Math.min(...reactionTimes).toFixed(3)} сек.</p>
        <p>Худшее время: ${Math.max(...reactionTimes).toFixed(3)} сек.</p>
    `;
    
    // Удаляем обработчик клика
    colorBox.removeEventListener('click', handleClick);
}

// Функция для отмены теста
function cancelTest() {
    isTestActive = false;
    clearTimeout(colorTimeout);
    
    // Обновляем UI
    colorBox.style.backgroundColor = 'black';
    colorBox.style.display = 'none';
    instruction.style.display = 'none';
    statsBlock.style.display = 'none';
    cancelButton.style.display = 'none';
    startButton.style.display = 'block';
    resultText.textContent = 'Тест был прерван';
    
    // Удаляем обработчик клика
    colorBox.removeEventListener('click', handleClick);
}

// Функция для сохранения результата
function saveReactionTime(avgReactionTime) {
    const formData = new FormData();
    formData.append('avgReactionTime', avgReactionTime);

    fetch('../tests/save_simple_color_test.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(data => {
        console.log('Результат сохранен:', data);
    })
    .catch(error => {
        console.error('Ошибка при сохранении результата:', error);
        resultText.innerHTML += '<p style="color: #ff6b6b;">Ошибка при сохранении результата</p>';
    });
}

// Добавляем обработчики событий
startButton.addEventListener('click', startTest);
cancelButton.addEventListener('click', cancelTest);

