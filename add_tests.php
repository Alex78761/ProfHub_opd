<?php
require_once "db-connect.php";

// Массив с тестами
$tests = [
    ['test_name' => 'Тест на цветовое восприятие', 'test_type' => 'Цветовое восприятие', 'file_path' => 'simple_color_test.php'],
    ['test_name' => 'Тест на визуальную память', 'test_type' => 'Память', 'file_path' => 'visual-memory-test.php'],
    ['test_name' => 'Тест на визуальный счет', 'test_type' => 'Счет', 'file_path' => 'visual_count_test.php'],
    ['test_name' => 'Тест на звуковую реакцию', 'test_type' => 'Реакция', 'file_path' => 'tests/sound_reaction_test_interface.php'],
    ['test_name' => 'Тест на стрессоустойчивость', 'test_type' => 'Стресс', 'file_path' => 'stress.php'],
    ['test_name' => 'Тест на переключение внимания', 'test_type' => 'Внимание', 'file_path' => 'switch-test.php'],
    ['test_name' => 'Тест на движение', 'test_type' => 'Движение', 'file_path' => 'movement_test.php'],
    ['test_name' => 'Тест на аудиальный счет', 'test_type' => 'Счет', 'file_path' => 'audio_count_test.php'],
    ['test_name' => 'Тест на объем внимания', 'test_type' => 'Внимание', 'file_path' => 'capacity-test.php'],
    ['test_name' => 'Тест на преследование', 'test_type' => 'Реакция', 'file_path' => 'chaseTest.php'],
    ['test_name' => 'Тест на классификацию', 'test_type' => 'Мышление', 'file_path' => 'classification.php'],
    ['test_name' => 'Тест на сложное движение', 'test_type' => 'Движение', 'file_path' => 'advanced_movement_test.php'],
    ['test_name' => 'Тест на аналогии', 'test_type' => 'Мышление', 'file_path' => 'analog_test.php'],
    ['test_name' => 'Тест на анализ', 'test_type' => 'Мышление', 'file_path' => 'analysis.php'],
    ['test_name' => 'Тест на сложное цветовое восприятие', 'test_type' => 'Цветовое восприятие', 'file_path' => 'advanced_color_test.php'],
    ['test_name' => 'Тест на скорость сложения в уме', 'test_type' => 'Реакция', 'file_path' => 'visual_count_test.php'],
];

// Подготавливаем и выполняем запрос для добавления тестов
$stmt = $conn->prepare("INSERT INTO tests (test_name, test_type, file_path) VALUES (?, ?, ?)");

foreach ($tests as $test) {
    $stmt->bind_param("sss", $test['test_name'], $test['test_type'], $test['file_path']);
    $stmt->execute();
}

echo "Тесты успешно добавлены в базу данных";

$conn->close();
?> 