<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db-connect.php';

// Включаем вывод всех ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Проверка соединения с базой данных
if (!isset($conn) || $conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed',
        'details' => $conn->connect_error ?? 'Unknown error'
    ]);
    exit;
}

// Логирование для отладки
file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Request: " . print_r([
    'method' => $_SERVER['REQUEST_METHOD'],
    'post' => $_POST,
    'session' => $_SESSION,
    'server' => $_SERVER
], true) . "\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Only POST requests allowed'
    ]);
    exit;
}

if (!isset($_POST['avgReactionTime'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing avgReactionTime parameter'
    ]);
    exit;
}

try {
    $avgTime = (float)$_POST['avgReactionTime'];
    $testId = 73; // Исправленный ID теста "реакция на звук"
    $userId = $_SESSION['user_id'] ?? null;
    
    error_log("Processing request - User ID: " . $userId . ", Test ID: " . $testId . ", Avg Time: " . $avgTime);
    
    if (!$userId) {
        $_SESSION['guest_results']['sound_test'] = $avgTime;
        echo json_encode([
            'status' => 'success',
            'message' => 'Результат сохранен для гостя'
        ]);
        exit;
    }

    // Проверяем существование пользователя
    $check_user = $conn->prepare("SELECT id FROM users WHERE id = ?");
    if ($check_user === false) {
        throw new Exception("Ошибка подготовки запроса проверки пользователя: " . $conn->error);
    }
    
    $check_user->bind_param("i", $userId);
    if (!$check_user->execute()) {
        throw new Exception("Ошибка выполнения запроса проверки пользователя: " . $check_user->error);
    }
    
    $user_result = $check_user->get_result();
    if ($user_result->num_rows === 0) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Пользователь не найден',
            'details' => "User ID: $userId not found in database"
        ]);
        exit;
    }
    $check_user->close();

    // Проверяем существование теста
    $check_test = $conn->prepare("SELECT id FROM tests WHERE id = ?");
    if ($check_test === false) {
        throw new Exception("Ошибка подготовки запроса проверки теста: " . $conn->error);
    }
    
    $check_test->bind_param("i", $testId);
    if (!$check_test->execute()) {
        throw new Exception("Ошибка выполнения запроса проверки теста: " . $check_test->error);
    }
    
    $test_result = $check_test->get_result();
    if ($test_result->num_rows === 0) {
        throw new Exception("Тест не найден в базе данных");
    }
    $check_test->close();

    // Сохраняем результат
    $stmt = $conn->prepare("INSERT INTO test_results (user_id, test_id, test_name, result) VALUES (?, ?, ?, ?)");
    if ($stmt === false) {
        throw new Exception("Ошибка подготовки запроса сохранения результата: " . $conn->error);
    }
    
    $test_name = "реакция на звук";
    $stmt->bind_param("iisd", $userId, $testId, $test_name, $avgTime);
    if (!$stmt->execute()) {
        throw new Exception("Ошибка выполнения запроса сохранения результата: " . $stmt->error);
    }
    
    $stmt->close();
    echo json_encode([
        'status' => 'success',
        'message' => 'Результат успешно сохранен'
    ]);
    
} catch (Exception $e) {
    error_log("Database error in sound_reaction_test.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Ошибка при сохранении результата',
        'details' => $e->getMessage()
    ]);
}
?>