class PulseWidget {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.pulseValue = 0;
        this.isRecording = false;
        this.recordedData = [];
        this.startTime = null;
        this.initialize();
    }

    initialize() {
        this.createUI();
        this.setupEventListeners();
        makeDraggable(this.container.querySelector('.pulse-widget'));
    }

    createUI() {
        this.container.innerHTML = `
            <div class="pulse-widget">
                <div class="pulse-header">Пульс</div>
                <div class="pulse-display">
                    <div class="pulse-value">--</div>
                    <div class="pulse-graph"></div>
                </div>
                <div class="pulse-controls">
                    <button class="start-btn">Начать запись</button>
                    <button class="stop-btn" disabled>Остановить</button>
                </div>
                <div class="pulse-stats">
                    <div>MAX:<span class="max-pulse">--</span></div>
                    <div>MIN:<span class="min-pulse">--</span></div>
                    <div>MID:<span class="avg-pulse">--</span></div>
                </div>
            </div>
        `;

        this.pulseValueElement = this.container.querySelector('.pulse-value');
        this.startButton = this.container.querySelector('.start-btn');
        this.stopButton = this.container.querySelector('.stop-btn');
        this.maxPulseElement = this.container.querySelector('.max-pulse');
        this.minPulseElement = this.container.querySelector('.min-pulse');
        this.avgPulseElement = this.container.querySelector('.avg-pulse');
        this.graphElement = this.container.querySelector('.pulse-graph');
    }

    setupEventListeners() {
        this.startButton.addEventListener('click', () => this.startRecording());
        this.stopButton.addEventListener('click', () => this.stopRecording());
    }

    startRecording() {
        this.isRecording = true;
        this.recordedData = [];
        this.startTime = Date.now();
        this.startButton.disabled = true;
        this.stopButton.disabled = false;
        
        // Имитация получения данных пульса
        this.simulatePulse();
    }

    stopRecording() {
        this.isRecording = false;
        this.startButton.disabled = false;
        this.stopButton.disabled = true;
        
        // Отправка данных на сервер
        this.saveData();
    }

    simulatePulse() {
        if (!this.isRecording) return;

        // Генерация случайного значения пульса (60-100)
        this.pulseValue = Math.floor(Math.random() * 41) + 60;
        this.recordedData.push({
            value: this.pulseValue,
            timestamp: Date.now() - this.startTime
        });

        this.updateDisplay();
        this.updateGraph();

        // Продолжаем запись каждую секунду
        setTimeout(() => this.simulatePulse(), 1000);
    }

    updateDisplay() {
        this.pulseValueElement.textContent = this.pulseValue;
        
        if (this.recordedData.length > 0) {
            const values = this.recordedData.map(d => d.value);
            this.maxPulseElement.textContent = Math.max(...values);
            this.minPulseElement.textContent = Math.min(...values);
            this.avgPulseElement.textContent = Math.round(values.reduce((a, b) => a + b) / values.length);
        }
    }

    updateGraph() {
        // Здесь можно добавить визуализацию графика
        // Например, используя Chart.js
    }

    saveData() {
        if (this.recordedData.length === 0) return;

        const values = this.recordedData.map(d => d.value);
        const data = {
            max_pulse: Math.max(...values),
            min_pulse: Math.min(...values),
            avg_pulse: Math.round(values.reduce((a, b) => a + b) / values.length),
            time_recorded: Math.floor((Date.now() - this.startTime) / 1000)
        };

        fetch('/save_pulse_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            console.log('Данные сохранены:', data);
        })
        .catch(error => {
            console.error('Ошибка при сохранении данных:', error);
        });
    }
}

// Добавляем стили
const style = document.createElement('style');
style.textContent = `
    .pulse-widget {
        background: var(--card-bg, #181a1b);
        border-radius: var(--border-radius, 18px);
        padding: 2rem 1.5rem 1.5rem 1.5rem;
        box-shadow: 0 4px 24px rgba(52,152,219,0.07), 0 1.5px 6px rgba(0,0,0,0.03);
        max-width: 260px;
        min-width: 180px;
        margin: 0 auto;
        border: 1px solid var(--border-color, #23272a);
        position: fixed;
        top: 120px;
        right: 40px;
        z-index: 9999;
        font-family: 'Segoe UI', 'Roboto', 'Arial', sans-serif;
        color: var(--text-color, #e0e0e0);
    }
    .pulse-header {
        background: var(--header-bg, #23272a);
        border-radius: 12px 12px 0 0;
        padding: 10px 0 6px 0;
        text-align: center;
        font-size: 1.1rem;
        font-weight: bold;
        color: var(--accent-color, #61dafb);
        margin-bottom: 1rem;
    }
    .pulse-value {
        font-size: 2.1rem;
        font-weight: bold;
        color: #ff4d4d;
        margin: 0.5rem 0;
        text-shadow: 0 0 8px rgba(255, 77, 77, 0.2);
        animation: pulse-blink 1s infinite alternate;
    }
    @keyframes pulse-blink {
        from { opacity: 1; }
        to { opacity: 0.7; }
    }
    .pulse-controls {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        gap: 0.5em;
    }
    .pulse-controls button {
        font-family: inherit;
        font-size: 1rem;
        border-radius: 8px;
        border: none;
        padding: 0.7em 0;
        font-weight: 600;
        transition: background 0.2s, color 0.2s;
        flex: 1;
    }
    .start-btn {
        background: var(--btn-green, #2ecc71);
        color: #fff;
    }
    .start-btn:hover {
        background: #27ae60;
    }
    .stop-btn {
        background: var(--btn-grey, #7f8c8d);
        color: #fff;
    }
    .stop-btn:enabled:hover {
        background: #e74c3c;
    }
    .stop-btn:disabled {
        background: #444;
        color: #bbb;
    }
    .pulse-stats {
        display: flex;
        justify-content: space-between;
        margin-top: 1rem;
        gap: 0.5em;
    }
    .pulse-stats div {
        background: var(--header-bg, #23272a);
        border-radius: 8px;
        padding: 0.5em 0.7em;
        text-align: center;
        min-width: 60px;
        color: #e74c3c;
        font-weight: 600;
        font-size: 1rem;
    }
    .pulse-graph {
        height: 40px;
        background: var(--primary-color, #222c36);
        border-radius: 8px;
        margin: 0.5em 0 1em 0;
        border: 1px solid var(--border-color, #23272a);
        position: relative;
        overflow: hidden;
        opacity: 0.7;
    }
`;
document.head.appendChild(style);

// ... DRAG & DROP ...
function makeDraggable(widget) {
    const header = widget.querySelector('.pulse-header');
    let offsetX = 0, offsetY = 0, isDragging = false;

    header.addEventListener('mousedown', (e) => {
        isDragging = true;
        offsetX = e.clientX - widget.getBoundingClientRect().left;
        offsetY = e.clientY - widget.getBoundingClientRect().top;
        widget.classList.add('dragging');
        document.body.style.userSelect = 'none';
    });
    document.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        widget.style.left = (e.clientX - offsetX) + 'px';
        widget.style.top = (e.clientY - offsetY) + 'px';
        widget.style.right = 'auto';
        widget.style.bottom = 'auto';
    });
    document.addEventListener('mouseup', () => {
        if (isDragging) {
            isDragging = false;
            widget.classList.remove('dragging');
            document.body.style.userSelect = '';
        }
    });
} 