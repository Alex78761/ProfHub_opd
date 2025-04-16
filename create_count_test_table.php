<?php
require_once "db-connect.php";

$sql = "CREATE TABLE IF NOT EXISTS count_test_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    avg_reaction_time FLOAT NOT NULL,
    test_date DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

try {
    if ($conn->query($sql) === TRUE) {
        echo "Таблица count_test_results успешно создана\n";
    } else {
        echo "Ошибка при создании таблицы: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "Произошла ошибка: " . $e->getMessage() . "\n";
}

$conn->close();
?> 
require_once "db-connect.php";

$sql = "CREATE TABLE IF NOT EXISTS count_test_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    avg_reaction_time FLOAT NOT NULL,
    test_date DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

try {
    if ($conn->query($sql) === TRUE) {
        echo "Таблица count_test_results успешно создана\n";
    } else {
        echo "Ошибка при создании таблицы: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "Произошла ошибка: " . $e->getMessage() . "\n";
}

$conn->close();
?> 
 
 
 
 
require_once "db-connect.php";

$sql = "CREATE TABLE IF NOT EXISTS count_test_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    avg_reaction_time FLOAT NOT NULL,
    test_date DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

try {
    if ($conn->query($sql) === TRUE) {
        echo "Таблица count_test_results успешно создана\n";
    } else {
        echo "Ошибка при создании таблицы: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "Произошла ошибка: " . $e->getMessage() . "\n";
}

$conn->close();
?> 
require_once "db-connect.php";

$sql = "CREATE TABLE IF NOT EXISTS count_test_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    avg_reaction_time FLOAT NOT NULL,
    test_date DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

try {
    if ($conn->query($sql) === TRUE) {
        echo "Таблица count_test_results успешно создана\n";
    } else {
        echo "Ошибка при создании таблицы: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "Произошла ошибка: " . $e->getMessage() . "\n";
}

$conn->close();
?> 
 
 
 
 
 
 
 