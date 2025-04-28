<?php
session_start();
require_once "db-connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Получаем ID консультанта
$consultant_query = "SELECT id FROM users WHERE role = 'expert' LIMIT 1";
$consultant_result = $conn->query($consultant_query);
$consultant = $consultant_result->fetch_assoc();
$consultant_id = $consultant['id'];

// Получаем список всех пользователей только для консультанта
$users_list = [];
if ($user_role === 'expert') {
    $users_query = "SELECT DISTINCT u.id, u.username 
                   FROM users u 
                   WHERE u.role IN ('user', 'admin', 'respondent')
                   ORDER BY u.username";
    $users_result = $conn->query($users_query);
    while ($user = $users_result->fetch_assoc()) {
        $users_list[] = $user;
    }
}

// Получаем ID активного чата
$active_chat_user = isset($_GET['user_id']) ? intval($_GET['user_id']) : ($users_list[0]['id'] ?? null);

// Обработка отправки сообщения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    
    if (!empty($message)) {
        if ($user_role === 'expert') {
            // Консультант отправляет сообщение выбранному пользователю
            $receiver_id = $active_chat_user;
        } else {
            // Пользователь или админ отправляет сообщение консультанту
            $receiver_id = $consultant_id;
        }
        
        if (isset($receiver_id)) {
            $stmt = $conn->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $user_id, $receiver_id, $message);
            $stmt->execute();
        }
    }
}

// Получаем историю сообщений
if ($user_role === 'expert') {
    // Для консультанта показываем переписку с выбранным пользователем
    $messages_query = "SELECT m.*, u.username as sender_name, u.role as sender_role
                      FROM chat_messages m 
                      JOIN users u ON m.sender_id = u.id 
                      WHERE (sender_id = ? AND receiver_id = ?) 
                      OR (sender_id = ? AND receiver_id = ?) 
                      ORDER BY created_at ASC";
    $stmt = $conn->prepare($messages_query);
    $stmt->bind_param("iiii", $user_id, $active_chat_user, $active_chat_user, $user_id);
} else {
    // Для обычного пользователя или админа показываем переписку только с консультантом
    $messages_query = "SELECT m.*, u.username as sender_name, u.role as sender_role
                      FROM chat_messages m 
                      JOIN users u ON m.sender_id = u.id 
                      WHERE (sender_id = ? AND receiver_id = ?) 
                      OR (sender_id = ? AND receiver_id = ?) 
                      ORDER BY created_at ASC";
    $stmt = $conn->prepare($messages_query);
    $stmt->bind_param("iiii", $user_id, $consultant_id, $consultant_id, $user_id);
}

$stmt->execute();
$messages_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Чат - ProfHub</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/background.css">
    <link rel="stylesheet" href="css/chat.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="chat-container <?php echo $user_role === 'expert' ? 'with-sidebar' : ''; ?>">
        <?php if ($user_role === 'expert' && !empty($users_list)): ?>
        <div class="chat-sidebar">
            <h2>Пользователи</h2>
            <div class="users-list">
                <?php foreach ($users_list as $chat_user): ?>
                    <a href="?user_id=<?php echo $chat_user['id']; ?>" 
                       class="user-item <?php echo $chat_user['id'] === $active_chat_user ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($chat_user['username']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="chat-main">
            <div class="chat-header">
                <h1>
                    <?php if ($user_role === 'expert'): ?>
                        Чат с <?php 
                        $active_user = array_filter($users_list, function($u) use ($active_chat_user) {
                            return $u['id'] === $active_chat_user;
                        });
                        echo htmlspecialchars(reset($active_user)['username'] ?? 'пользователем');
                        ?>
                    <?php else: ?>
                        Чат с консультантом
                    <?php endif; ?>
                </h1>
            </div>

            <div class="chat-messages" id="chat-messages">
                <div class="loading-indicator" style="display: none;">
                    <div class="spinner"></div>
                </div>
                <?php while ($message = $messages_result->fetch_assoc()): ?>
                    <div class="message <?php echo $message['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                        <div class="message-content">
                            <div class="message-header">
                                <span class="sender">
                                    <?php 
                                    echo htmlspecialchars($message['sender_name']);
                                    if ($message['sender_role'] === 'expert') {
                                        echo ' (Консультант)';
                                    }
                                    ?>
                                </span>
                                <span class="time"><?php echo date('H:i', strtotime($message['created_at'])); ?></span>
                            </div>
                            <p><?php echo htmlspecialchars($message['message']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="chat-input">
                <form id="message-form">
                    <input type="hidden" name="receiver_id" value="<?php echo $user_role === 'expert' ? $active_chat_user : $consultant_id; ?>">
                    <input type="text" name="message" id="message-input" placeholder="Введите сообщение..." required>
                    <button type="submit" id="send-button">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <audio id="notification-sound" src="sounds/notification.mp3" preload="auto"></audio>
    <script>
        // Передаем необходимые PHP переменные в JavaScript
        const userRole = '<?php echo $user_role; ?>';
        const userId = <?php echo $user_id; ?>;
        const activeChatUser = <?php echo $active_chat_user ?? 'null'; ?>;
        const consultantId = <?php echo $consultant_id; ?>;
    </script>
    <script src="js/chat.js"></script>
</body>
</html> 