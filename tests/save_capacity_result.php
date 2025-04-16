<?php
session_start();
include '../db-connect.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
$accuracy = isset($_POST['accuracy']) ? round($_POST['accuracy'], 2) : null;
$test_type = 'Оценка внимания';
$test_name = 'объем';

if ($user_id !== null && $accuracy !== null) {
    $stmt = $conn->prepare("SELECT id FROM tests WHERE test_type = ? AND test_name = ?");
    $stmt->bind_param("ss", $test_type, $test_name);
    $stmt->execute();
    $stmt->bind_result($test_id);
    $stmt->fetch();
    $stmt->close();

    if ($test_id !== null) {
        $stmt = $conn->prepare("INSERT INTO test_results (user_id, test_id, test_name, result) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisd", $user_id, $test_id, $test_name, $accuracy);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Результат успешно сохранён!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ошибка при сохранении результата: ' . $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Не найден test_id для test_type=' . $test_type . ' и test_name=' . $test_name]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Не передано значение accuracy!']);
}
$conn->close(); 