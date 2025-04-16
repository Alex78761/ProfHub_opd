<?php
require_once "db-connect.php";

// Удаляем существующую таблицу, если она есть
$conn->query("DROP TABLE IF EXISTS test_results");

$sql = "CREATE TABLE test_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    test_id INT NOT NULL,
    test_name VARCHAR(255) NOT NULL,
    result DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Таблица test_results успешно создана";
} else {
    echo "Ошибка при создании таблицы: " . $conn->error;
}

$conn->close();
?> 