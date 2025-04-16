<?php
session_start();
require_once "db-connect.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

// Получаем ID консультанта
$consultant_query = "SELECT id FROM users WHERE role = 'expert' LIMIT 1";
$consultant_result = $conn->query($consultant_query);
$consultant = $consultant_result->fetch_assoc();
$consultant_id = $consultant['id'];

try {
    if ($user_role === 'expert') {
        // Для консультанта: получаем сообщения с конкретным пользователем
        $user_id_to_chat = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        
        if (!$user_id_to_chat) {
            echo json_encode(['success' => false, 'error' => 'No user selected']);
            exit;
        }

        $query = "SELECT m.*, u.username as sender_name, u.role as sender_role,
                        CASE WHEN m.sender_id = ? THEN 1 ELSE 0 END as is_sent
                 FROM chat_messages m
                 JOIN users u ON m.sender_id = u.id
                 WHERE ((m.sender_id = ? AND m.receiver_id = ?) 
                    OR (m.sender_id = ? AND m.receiver_id = ?))
                 AND m.id > ?
                 ORDER BY m.created_at ASC";
                 
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiiii", $user_id, $user_id, $user_id_to_chat, $user_id_to_chat, $user_id, $last_id);
    } else {
        // Для пользователя или админа: получаем сообщения только с консультантом
        $query = "SELECT m.*, u.username as sender_name, u.role as sender_role,
                        CASE WHEN m.sender_id = ? THEN 1 ELSE 0 END as is_sent
                 FROM chat_messages m
                 JOIN users u ON m.sender_id = u.id
                 WHERE ((m.sender_id = ? AND m.receiver_id = ?) 
                    OR (m.sender_id = ? AND m.receiver_id = ?))
                 AND m.id > ?
                 ORDER BY m.created_at ASC";
                 
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiiii", $user_id, $user_id, $consultant_id, $consultant_id, $user_id, $last_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'id' => $row['id'],
            'content' => $row['message'],
            'sender_name' => $row['sender_name'],
            'sender_role' => $row['sender_role'],
            'is_sent' => $row['is_sent'] == 1,
            'created_at' => $row['created_at']
        ];
    }

    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);

} catch (Exception $e) {
    error_log('Error in get_messages.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch messages',
        'details' => $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?> 