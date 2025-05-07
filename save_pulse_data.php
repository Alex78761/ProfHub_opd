<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "db-connect.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Не авторизован']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Неверный формат данных']);
    exit();
}

$user_id = $_SESSION['user_id'];
$max_pulse = (int)$data['max_pulse'];
$min_pulse = (int)$data['min_pulse'];
$avg_pulse = (int)round($data['avg_pulse']);
$time_recorded = (int)$data['time_recorded'];
$stress_level = 0; // по умолчанию

$sql = "INSERT INTO pulse_data (user_id, max_pulse, min_pulse, avg_pulse, stress_level, time_recorded) 
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiiii", $user_id, $max_pulse, $min_pulse, $avg_pulse, $stress_level, $time_recorded);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка при сохранении данных']);
}

$stmt->close();
$conn->close();
?> 