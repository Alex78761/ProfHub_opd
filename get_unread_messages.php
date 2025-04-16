<?php
session_start();
require_once "db-connect.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role === 'expert') {
    // Для консультанта - считаем все непрочитанные сообщения от пользователей
    $query = "SELECT COUNT(*) as unread FROM chat_messages 
              WHERE receiver_id = ? 
              AND is_read = 0 
              AND sender_id IN (SELECT id FROM users WHERE role IN ('user', 'respondent'))";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo json_encode(['unread' => $row['unread']]);
} else {
    echo json_encode(['unread' => 0]);
}
?> 