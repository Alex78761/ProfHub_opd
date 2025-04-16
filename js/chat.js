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
        item.addEventListener('click', () => {
            const userId = item.dataset.userId;
            activeUserId = userId;
            receiverInput.value = userId;
            
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
            formData.append('recipient_id', userRole === 'expert' ? activeChatUser : consultantId);

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

            if (userRole === 'expert' && activeChatUser) {
                params.append('user_id', activeChatUser);
            }

            const response = await fetch(`get_messages.php?${params}`);
            const data = await response.json();

            if (data.success && data.messages && data.messages.length > 0) {
                data.messages.forEach(message => {
                    const messageElement = createMessageElement(message);
                    chatMessages.appendChild(messageElement);
                    lastMessageId = Math.max(lastMessageId, message.id);
                });

                scrollToBottom();

                // Проигрываем звук для новых сообщений
                if (!isWindowFocused && notificationSound) {
                    notificationSound.play().catch(() => {});
                }
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