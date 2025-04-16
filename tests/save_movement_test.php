<?php
session_start();
require '../db-connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Ошибка: пользователь не авторизован");
}

if (!isset($_POST['res'])) {
    die("Ошибка: результат не получен");
}

$userId = $_SESSION['user_id'];
$result = floatval($_POST['res']);

// Получаем ID теста
$stmt = $conn->prepare("SELECT id FROM tests WHERE test_type = ? AND test_name = ?");
$testType = "Оценка простой реакции человека на движущийся объект";
$testName = "реакция на движение";
$stmt->bind_param("ss", $testType, $testName);
$stmt->execute();
$testResult = $stmt->get_result();

if ($testResult->num_rows > 0) {
    $row = $testResult->fetch_assoc();
    $testId = $row['id'];
    
    // Сохраняем результат
    $stmt = $conn->prepare("INSERT INTO test_results (user_id, test_id, test_name, result) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisd", $userId, $testId, $testName, $result);
    
    if ($stmt->execute()) {
        echo "Результат успешно сохранен";
    } else {
        echo "Ошибка при сохранении результата: " . $conn->error;
    }
} else {
    echo "Ошибка: тест не найден";
}

$stmt->close();
$conn->close();
?>
