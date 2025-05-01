<?php
require_once "db-connect.php";

// Увеличиваем длину test_type, если нужно
$conn->query("ALTER TABLE tests MODIFY COLUMN test_type VARCHAR(255) NOT NULL");

// Проверяем, есть ли столбец file_path
$check = $conn->query("SHOW COLUMNS FROM tests LIKE 'file_path'");
if ($check->num_rows == 0) {
    $conn->query("ALTER TABLE tests ADD COLUMN file_path VARCHAR(255) NOT NULL AFTER test_name");
    echo "Столбец file_path добавлен.<br>";
}

// Очищаем таблицу
$conn->query("DELETE FROM tests");

// Вставляем тесты
$tests = [
    ['Оценка простых сенсомоторных реакций человека', 'реакция на свет', 'simple_color_test.php'],
    ['Оценка простых сенсомоторных реакций человека', 'реакция на звук', 'tests/sound_reaction_test_interface.php'],
    ['Оценка сложных сенсомоторных реакций человека', 'оценка скорости реакции на разные цвета', 'advanced_color_test.php'],
    ['Оценка сложных сенсомоторных реакций человека', 'оценка скорости реакции на сложный звуковой сигнал – сложение в уме ', 'audio_count_test.php'],
    ['Оценка сложных сенсомоторных реакций человека', 'оценка скорости реакции на сложение в уме (чет/нечет) - визуально', 'visual_count_test.php'],
    ['Оценка простой реакции человека на движущийся объект', 'реакция на движение', 'movement_test.php'],
    ['Оценка сложной реакции человека на движущиеся объекты', 'реакция на множество движущихся объектов', 'advanced_movement_test.php'],
    // Слежение
    ['Слежение', 'реакция на изменение направления движения', 'analog_test.php'],
    ['Слежение', 'слежение за объектом', 'chaseTest.php'],
    // Мышление
    ['Оценка мышления', 'обобщение', 'generalization.php'],
    ['Оценка мышления', 'классификация', 'classification.php'],
    ['Оценка мышления', 'анализ', 'analysis.php'],
    ['Оценка мышления', 'сравнение', 'comparison.php'],
    // Память
    ['Оценка памяти', 'зрительная', 'visual-memory-test.php'],
    // Внимание
    ['Оценка внимания', 'переключаемость', 'switch-test.php'],
    ['Оценка внимания', 'объем', 'capacity-test.php'],
    // Добавляю тест на кратковременную память
    ['Оценка памяти', 'кратковременная', 'short-term-memory-test.php']
];

$stmt = $conn->prepare("INSERT INTO tests (test_type, test_name, file_path) VALUES (?, ?, ?)");
foreach ($tests as $test) {
    $stmt->bind_param("sss", $test[0], $test[1], $test[2]);
    $stmt->execute();
}

$stmt->close();
$conn->close();
echo "Все тесты успешно восстановлены!"; 