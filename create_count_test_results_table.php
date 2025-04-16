<?php
require_once "db-connect.php";

$sql = "CREATE TABLE IF NOT EXISTS count_test_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    actual_count INT NOT NULL,
    user_count INT NOT NULL,
    test_duration INT NOT NULL,
    difficulty VARCHAR(20) NOT NULL,
    accuracy DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Таблица count_test_results успешно создана";
} else {
    echo "Ошибка при создании таблицы: " . $conn->error;
}

$conn->close();
?> 
 
 
 
 
 
 
 
 
 