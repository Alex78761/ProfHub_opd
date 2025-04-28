let lastMessageId = 0;
let isWindowFocused = true;
let notificationPermission = false;
let activeUserId = null;

document.addEventListener('DOMContentLoaded', () => {
    // Запрашиваем разрешение на уведомления
    if ("Notification" in window) {
        Notification.requestPermission().then(permission => {
            notificationPermission = permission === "granted";
        });
    }

    // Отслеживаем фокус окна
    window.addEventListener('focus', () => isWindowFocused = true);
    window.addEventListener('blur', () => isWindowFocused = false);

    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const chatMessages = document.getElementById('chat-messages');
    const loadingIndicator = document.querySelector('.loading-indicator');
    const notificationSound = document.getElementById('notification-sound');
    const receiverInput = document.querySelector('input[name="receiver_id"]');
    const userItems = document.querySelectorAll('.user-item');

    // Инициализация activeUserId из скрытого поля
    if (receiverInput) {
        activeUserId = receiverInput.value || null;
    }

    // Обработка клика по пользователю в списке
    userItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const userId = item.getAttribute('href').split('=')[1];
            activeUserId = userId;
            
            // Визуальное выделение активного пользователя
            userItems.forEach(u => u.classList.remove('active'));
            item.classList.add('active');
            
            // Очистка истории сообщений и загрузка новых
            lastMessageId = 0;
            chatMessages.innerHTML = '';
            loadNewMessages();
        });
    });

    // Автоматическая прокрутка вниз
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Показать/скрыть индикатор загрузки
    function toggleLoading(show) {
        if (loadingIndicator) {
            loadingIndicator.style.display = show ? 'flex' : 'none';
        }
    }

    // Функция для отправки сообщения
    async function sendMessage(message) {
        try {
            const formData = new FormData();
            formData.append('content', message);
            formData.append('recipient_id', activeUserId);

            const response = await fetch('send_message.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                messageInput.value = '';
                await loadNewMessages();
            } else {
                console.error('Error sending message:', data.error);
                alert('Ошибка при отправке сообщения: ' + (data.error || 'Неизвестная ошибка'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Произошла ошибка при отправке сообщения');
        }
    }

    // Функция для загрузки новых сообщений
    async function loadNewMessages() {
        try {
            toggleLoading(true);
            
            const params = new URLSearchParams({
                last_id: lastMessageId
            });

            if (activeUserId) {
                params.append('user_id', activeUserId);
            }

            const response = await fetch(`get_messages.php?${params.toString()}`);
            const data = await response.json();

            if (data.success) {
                data.messages.forEach(message => {
                    if (!document.querySelector(`[data-message-id="${message.id}"]`)) {
                        appendMessage(message);
                    lastMessageId = Math.max(lastMessageId, message.id);
                        
                        // Воспроизводим звук уведомления для новых входящих сообщений
                        if (!message.is_sent && !isWindowFocused && notificationPermission) {
                            notificationSound.play();
                        }
                    }
                });

                scrollToBottom();
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        } finally {
            toggleLoading(false);
        }
    }

    // Функция для создания элемента сообщения
    function createMessageElement(message) {
        const div = document.createElement('div');
        div.className = `message ${message.is_sent ? 'sent' : 'received'}`;
        
        const content = document.createElement('div');
        content.className = 'message-content';
        
        const header = document.createElement('div');
        header.className = 'message-header';
        
        const sender = document.createElement('span');
        sender.className = 'sender';
        sender.textContent = message.sender_name;
        if (message.sender_role === 'expert') {
            sender.textContent += ' (Консультант)';
        }
        
        const time = document.createElement('span');
        time.className = 'time';
        time.textContent = new Date(message.created_at).toLocaleTimeString();
        
        const text = document.createElement('p');
        text.textContent = message.content;
        
        header.appendChild(sender);
        header.appendChild(time);
        content.appendChild(header);
        content.appendChild(text);
        div.appendChild(content);
        
        return div;
    }

    // Функция для добавления сообщения в чат
    function appendMessage(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${message.is_sent ? 'sent' : 'received'}`;
        messageDiv.setAttribute('data-message-id', message.id);

        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';

        const headerDiv = document.createElement('div');
        headerDiv.className = 'message-header';

        const senderSpan = document.createElement('span');
        senderSpan.className = 'sender';
        senderSpan.textContent = message.sender_name + (message.sender_role === 'expert' ? ' (Консультант)' : '');

        const timeSpan = document.createElement('span');
        timeSpan.className = 'time';
        timeSpan.textContent = new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        const messageP = document.createElement('p');
        messageP.textContent = message.content;

        headerDiv.appendChild(senderSpan);
        headerDiv.appendChild(timeSpan);
        contentDiv.appendChild(headerDiv);
        contentDiv.appendChild(messageP);
        messageDiv.appendChild(contentDiv);

        chatMessages.appendChild(messageDiv);
    }

    // Обработчик отправки формы
    messageForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const message = messageInput.value.trim();
        if (message) {
            await sendMessage(message);
        }
    });

    // Автоматическое обновление чата каждые 3 секунды
    setInterval(loadNewMessages, 3000);

    // Загрузка начальных сообщений
    loadNewMessages();

    // Фокус на поле ввода
    messageInput.focus();
}); 