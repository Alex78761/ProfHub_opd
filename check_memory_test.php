<?php
require_once 'db_connect.php';

$test_type = "Оценка памяти";
$test_name = "зрительная";

$stmt = $conn->prepare("SELECT * FROM tests WHERE test_type = ? AND test_name = ?");
$stmt->bind_param("ss", $test_type, $test_name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Тест найден:\n";
    print_r($result->fetch_assoc());
} else {
    echo "Тест не найден. Добавляем...\n";
    
    $stmt = $conn->prepare("INSERT INTO tests (test_type, test_name) VALUES (?, ?)");
    $stmt->bind_param("ss", $test_type, $test_name);
    if ($stmt->execute()) {
        echo "Тест успешно добавлен\n";
    } else {
        echo "Ошибка при добавлении теста: " . $conn->error . "\n";
    }
}
?> 