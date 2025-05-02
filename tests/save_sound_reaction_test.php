<?php
session_start();
header('Content-Type: application/json');

// Включаем вывод всех ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключение к базе данных
require_once "../db-connect.php";

// Логирование входящего запроса и сессии
error_log("=== Debug Information ===");
error_log("POST data: " . print_r($_POST, true));
error_log("Session data: " . print_r($_SESSION, true));
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Database connection: " . ($conn->connect_error ? "Error: " . $conn->connect_error : "Success"));

// Проверяем, была ли отправлена форма для сохранения результатов теста
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['avgReactionTime'])) {
    try {
    // Получаем среднее время реакций из POST-данных
        $avgReactionTime = floatval($_POST['avgReactionTime']);
        error_log("Processing avgReactionTime: " . $avgReactionTime);

    // Проверяем, авторизован ли пользователь
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
            error_log("User ID from session: " . $user_id);

            // Проверяем существование пользователя
            $check_user = $conn->prepare("SELECT id FROM users WHERE id = ?");
            if (!$check_user) {
                throw new Exception("Ошибка подготовки запроса проверки пользователя: " . $conn->error);
            }
            
            $check_user->bind_param("i", $user_id);
            if (!$check_user->execute()) {
                throw new Exception("Ошибка выполнения запроса проверки пользователя: " . $check_user->error);
            }
            
            $user_result = $check_user->get_result();
            if ($user_result->num_rows === 0) {
                throw new Exception("Пользователь не найден в базе данных");
            }
            $check_user->close();

            // Используем известный ID теста
            $test_id = 73;
        $test_name = "реакция на звук";

            // Проверяем существование теста
            $check_test = $conn->prepare("SELECT id FROM tests WHERE id = ?");
            if (!$check_test) {
                throw new Exception("Ошибка подготовки запроса проверки теста: " . $conn->error);
            }
            
            $check_test->bind_param("i", $test_id);
            if (!$check_test->execute()) {
                throw new Exception("Ошибка выполнения запроса проверки теста: " . $check_test->error);
            }
            
            $test_result = $check_test->get_result();
            if ($test_result->num_rows === 0) {
                throw new Exception("Тест не найден в базе данных");
            }
            $check_test->close();

            // Подготовка и выполнение запроса на вставку результатов теста
            $stmt = $conn->prepare("INSERT INTO test_results (user_id, test_id, test_name, result) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Ошибка подготовки запроса вставки: " . $conn->error);
            }

            $stmt->bind_param("iisd", $user_id, $test_id, $test_name, $avgReactionTime);
            if (!$stmt->execute()) {
                throw new Exception("Ошибка выполнения запроса вставки: " . $stmt->error);
            }

            error_log("Successfully saved result for user_id: " . $user_id);
            echo json_encode([
                'status' => 'success',
                'message' => 'Результаты успешно сохранены'
            ]);

            $stmt->close();
        } else {
            error_log("User not authenticated, saving to session");
        $_SESSION['guest_avg_reaction_time_sound'] = $avgReactionTime;
            echo json_encode([
                'status' => 'success',
                'message' => 'Результаты успешно сохранены в сессии'
            ]);
        }
    } catch (Exception $e) {
        error_log("Error in save_sound_reaction_test.php: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
            'details' => [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]
        ]);
    }
} else {
    error_log("Invalid request: " . print_r($_SERVER, true));
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Нет данных о реакционном времени',
        'details' => [
            'method' => $_SERVER['REQUEST_METHOD'],
            'post_data' => $_POST
        ]
    ]);
}
?>
