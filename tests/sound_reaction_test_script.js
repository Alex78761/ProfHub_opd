let reactionTimes = [];
let soundPlayCount = 0;
let maxSoundPlays = 10;
let testStarted = false;
let startTime;
let soundInterval;
let soundButton = document.getElementById('soundButton');
let startButton = document.getElementById('startButton');
let cancelButton = document.getElementById('cancelButton');
let instruction = document.getElementById('instruction');
let timer = document.getElementById('timer');
let prevReactionTime = document.getElementById('prevReactionTime');
let avgReactionTime = document.getElementById('avgReactionTime');
let changeCounter = document.getElementById('changeCounter');
let sound = document.getElementById('sound');
let resultText = document.getElementById('resultText');

function startTest() {
    testStarted = true;
    soundPlayCount = 0;
    reactionTimes = [];
    startButton.style.display = 'none';
    instruction.style.display = 'block';
    soundButton.style.display = 'block';
    timer.style.display = 'block';
    prevReactionTime.style.display = 'block';
    avgReactionTime.style.display = 'block';
    changeCounter.style.display = 'block';
    cancelButton.style.display = 'block';
    document.getElementById('changeCounterDisplay').textContent = maxSoundPlays;
    
    // Начать первый звук через случайный интервал (1-5 секунд)
    let firstDelay = Math.random() * 4000 + 1000;
    soundInterval = setTimeout(playSound, firstDelay);
}

function playSound() {
    if (!testStarted || soundPlayCount >= maxSoundPlays) return;
    
    sound.currentTime = 0;
    sound.play();
    startTime = new Date().getTime();
    soundButton.disabled = false;
    soundPlayCount++;
    document.getElementById('changeCounterDisplay').textContent = maxSoundPlays - soundPlayCount;
    
    // Если это был последний звук, отключить кнопку через 3 секунды
    if (soundPlayCount === maxSoundPlays) {
        setTimeout(() => {
            soundButton.disabled = true;
            endTest();
        }, 3000);
    }
}

soundButton.addEventListener('click', function() {
    if (!testStarted || soundButton.disabled) return;
    
    let endTime = new Date().getTime();
    let reactionTime = (endTime - startTime) / 1000;
    reactionTimes.push(reactionTime);
    
    // Обновить отображение
    document.getElementById('reactionTimeDisplay').textContent = reactionTime.toFixed(3);
    document.getElementById('prevReactionTimeDisplay').textContent = reactionTime.toFixed(3);
    
    if (reactionTimes.length > 1) {
        let avg = reactionTimes.reduce((a, b) => a + b, 0) / reactionTimes.length;
        document.getElementById('avgReactionTimeDisplay').textContent = avg.toFixed(3);
    }
    
    soundButton.disabled = true;
    
    // Запланировать следующий звук через случайный интервал (1-5 секунд)
    if (soundPlayCount < maxSoundPlays) {
        clearTimeout(soundInterval);
        let nextDelay = Math.random() * 4000 + 1000;
        soundInterval = setTimeout(playSound, nextDelay);
    }
});

function cancelTest() {
    clearTimeout(soundInterval);
    resetTest();
    resultText.textContent = "Тест отменен";
}

function endTest() {
    testStarted = false;
    let avgTime = reactionTimes.reduce((a, b) => a + b, 0) / reactionTimes.length;
    resultText.textContent = `Тест завершен. Среднее время реакции: ${avgTime.toFixed(3)} секунд`;
    
    // Отправить результаты на сервер
    saveResults(avgTime);
    
    // Сбросить тест через 5 секунд
    setTimeout(resetTest, 5000);
}

function resetTest() {
    testStarted = false;
    clearTimeout(soundInterval);
    startButton.style.display = 'block';
    instruction.style.display = 'none';
    soundButton.style.display = 'none';
    timer.style.display = 'none';
    prevReactionTime.style.display = 'none';
    avgReactionTime.style.display = 'none';
    changeCounter.style.display = 'none';
    cancelButton.style.display = 'none';
    soundButton.disabled = false;
    resultText.textContent = "";
}

function saveResults(avgTime) {
    // Проверка значения перед отправкой
    console.log("Sending avgTime:", avgTime); 
    fetch('sound_reaction_test.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `avgReactionTime=${encodeURIComponent(avgTime)}`
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || err.details || `HTTP error ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log("Success:", data);
        if (data.status === 'error') {
            throw new Error(data.message || data.details || 'Unknown error');
        }
        alert("Результаты сохранены: " + data.message);
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Ошибка при сохранении результатов: " + error.message);
    });
}

// Инициализация обработчиков после загрузки DOM
window.addEventListener('DOMContentLoaded', function() {
    if (startButton) startButton.addEventListener('click', startTest);
    if (cancelButton) cancelButton.addEventListener('click', cancelTest);
});
