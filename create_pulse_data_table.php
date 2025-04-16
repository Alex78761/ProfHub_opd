<?php
require_once "db-connect.php";

// SQL-запрос для создания таблицы pulse_data
$sql = "CREATE TABLE IF NOT EXISTS pulse_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    max_pulse INT NOT NULL,
    min_pulse INT NOT NULL,
    avg_pulse INT NOT NULL,
    stress_level FLOAT NOT NULL,
    time_recorded INT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Таблица pulse_data успешно создана\n";
} else {
    echo "Ошибка при создании таблицы: " . $conn->error;
}

$conn->close();
?> 