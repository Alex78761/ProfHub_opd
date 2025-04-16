<?php
require_once "db-connect.php";

// SQL-запрос для создания таблицы tests
$sql = "CREATE TABLE IF NOT EXISTS tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_name VARCHAR(255) NOT NULL,
    test_type VARCHAR(100) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Таблица tests успешно создана\n";
    
    // Теперь добавим тесты
    require_once "add_tests.php";
} else {
    echo "Ошибка при создании таблицы: " . $conn->error;
    $conn->close();
}
?> 