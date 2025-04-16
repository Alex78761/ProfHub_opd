<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "db-connect.php";

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Получение данных из POST-запроса
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['test_id']) || !isset($data['test_name']) || !isset($data['result_data'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

$user_id = $_SESSION['user_id'];
$test_id = $data['test_id'];
$test_name = $data['test_name'];
$result_data = json_encode($data['result_data']);
$score = isset($data['score']) ? $data['score'] : null;

// Сохранение результата теста
$query = "INSERT INTO test_results (user_id, test_id, test_name, result_data, score) 
          VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iissd", $user_id, $test_id, $test_name, $result_data, $score);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Результат теста сохранен']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save test result']);
}

$stmt->close();
$conn->close();
?> 