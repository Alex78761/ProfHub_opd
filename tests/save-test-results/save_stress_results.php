<?php
session_start();
require_once "../db-connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;
    $result = $_POST['result'] ?? null;
    $test_type = 'Оценка стрессоустойчивости';
    $test_name = 'стрессоустойчивость';

    if ($user_id !== null && $result !== null) {
        $stmt = $conn->prepare("SELECT id FROM tests WHERE test_type = ? AND test_name = ?");
        $stmt->bind_param("ss", $test_type, $test_name);
        $stmt->execute();
        $stmt->bind_result($test_id);
        $stmt->fetch();
        $stmt->close();

        if ($test_id !== null) {
            $stmt = $conn->prepare("INSERT INTO test_results (user_id, test_id, test_name, result) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iisd", $user_id, $test_id, $test_name, $result);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Результат успешно сохранён!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ошибка при сохранении результата: ' . $conn->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Не найден test_id для теста']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Не переданы необходимые данные!']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
}
$conn->close();
?> 