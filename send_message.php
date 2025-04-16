<?php
session_start();
require_once 'db-connect.php';

header('Content-Type: application/json');

// Логирование входящих данных
error_log('POST data: ' . print_r($_POST, true));
error_log('Session data: ' . print_r($_SESSION, true));

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if (!isset($_POST['content']) || !isset($_POST['recipient_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields', 'received' => $_POST]);
    exit;
}

$sender_id = $_SESSION['user_id'];
$recipient_id = intval($_POST['recipient_id']);
$message = trim($_POST['content']);

if (empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
    exit;
}

try {
    // Проверяем существование пользователя
    $check_user = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $check_user->bind_param("i", $recipient_id);
    $check_user->execute();
    $result = $check_user->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Recipient not found']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $sender_id, $recipient_id, $message);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message_id' => $conn->insert_id,
            'debug' => [
                'sender_id' => $sender_id,
                'recipient_id' => $recipient_id,
                'message' => $message
            ]
        ]);
    } else {
        error_log('MySQL Error: ' . $stmt->error);
        echo json_encode(['success' => false, 'error' => 'Failed to send message', 'sql_error' => $stmt->error]);
    }
} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error', 'details' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?> 