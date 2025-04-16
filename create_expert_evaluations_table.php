<?php
require_once 'db-connect.php';

$sql = "DROP TABLE IF EXISTS expert_evaluations;
CREATE TABLE expert_evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT NOT NULL,
    profession_id INT NOT NULL,
    relevance INT NOT NULL,
    demand INT NOT NULL,
    prospects INT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (expert_id) REFERENCES users(id),
    FOREIGN KEY (profession_id) REFERENCES professions(id),
    UNIQUE KEY unique_evaluation (expert_id, profession_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->multi_query($sql)) {
    echo "Таблица expert_evaluations успешно создана!";
} else {
    echo "Ошибка при создании таблицы: " . $conn->error;
}

$conn->close();
?> 
require_once 'db-connect.php';

$sql = "DROP TABLE IF EXISTS expert_evaluations;
CREATE TABLE expert_evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT NOT NULL,
    profession_id INT NOT NULL,
    relevance INT NOT NULL,
    demand INT NOT NULL,
    prospects INT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (expert_id) REFERENCES users(id),
    FOREIGN KEY (profession_id) REFERENCES professions(id),
    UNIQUE KEY unique_evaluation (expert_id, profession_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->multi_query($sql)) {
    echo "Таблица expert_evaluations успешно создана!";
} else {
    echo "Ошибка при создании таблицы: " . $conn->error;
}

$conn->close();
?> 
 
 
 
 
require_once 'db-connect.php';

$sql = "DROP TABLE IF EXISTS expert_evaluations;
CREATE TABLE expert_evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT NOT NULL,
    profession_id INT NOT NULL,
    relevance INT NOT NULL,
    demand INT NOT NULL,
    prospects INT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (expert_id) REFERENCES users(id),
    FOREIGN KEY (profession_id) REFERENCES professions(id),
    UNIQUE KEY unique_evaluation (expert_id, profession_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->multi_query($sql)) {
    echo "Таблица expert_evaluations успешно создана!";
} else {
    echo "Ошибка при создании таблицы: " . $conn->error;
}

$conn->close();
?> 
require_once 'db-connect.php';

$sql = "DROP TABLE IF EXISTS expert_evaluations;
CREATE TABLE expert_evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expert_id INT NOT NULL,
    profession_id INT NOT NULL,
    relevance INT NOT NULL,
    demand INT NOT NULL,
    prospects INT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (expert_id) REFERENCES users(id),
    FOREIGN KEY (profession_id) REFERENCES professions(id),
    UNIQUE KEY unique_evaluation (expert_id, profession_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->multi_query($sql)) {
    echo "Таблица expert_evaluations успешно создана!";
} else {
    echo "Ошибка при создании таблицы: " . $conn->error;
}

$conn->close();
?> 
 
 
 
 
 
 
 