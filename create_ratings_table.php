<?php
require_once 'db-connect.php';

// Создаем таблицу ratings
$sql = "CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    profession_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (profession_id) REFERENCES professions(id),
    UNIQUE KEY unique_rating (user_id, profession_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql)) {
    echo "Таблица ratings успешно создана или обновлена!\n";
} else {
    echo "Ошибка при создании таблицы: " . $conn->error . "\n";
}

// Проверяем наличие колонки comment
$check_column = $conn->query("SHOW COLUMNS FROM ratings LIKE 'comment'");
if ($check_column->num_rows == 0) {
    $alter_sql = "ALTER TABLE ratings ADD COLUMN comment TEXT AFTER rating";
    if ($conn->query($alter_sql)) {
        echo "Колонка comment успешно добавлена!\n";
    } else {
        echo "Ошибка при добавлении колонки comment: " . $conn->error . "\n";
    }
}

$conn->close();
?> 
require_once 'db-connect.php';

// Создаем таблицу ratings
$sql = "CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    profession_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (profession_id) REFERENCES professions(id),
    UNIQUE KEY unique_rating (user_id, profession_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql)) {
    echo "Таблица ratings успешно создана или обновлена!\n";
} else {
    echo "Ошибка при создании таблицы: " . $conn->error . "\n";
}

// Проверяем наличие колонки comment
$check_column = $conn->query("SHOW COLUMNS FROM ratings LIKE 'comment'");
if ($check_column->num_rows == 0) {
    $alter_sql = "ALTER TABLE ratings ADD COLUMN comment TEXT AFTER rating";
    if ($conn->query($alter_sql)) {
        echo "Колонка comment успешно добавлена!\n";
    } else {
        echo "Ошибка при добавлении колонки comment: " . $conn->error . "\n";
    }
}

$conn->close();
?> 
 
 
 
 
require_once 'db-connect.php';

// Создаем таблицу ratings
$sql = "CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    profession_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (profession_id) REFERENCES professions(id),
    UNIQUE KEY unique_rating (user_id, profession_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql)) {
    echo "Таблица ratings успешно создана или обновлена!\n";
} else {
    echo "Ошибка при создании таблицы: " . $conn->error . "\n";
}

// Проверяем наличие колонки comment
$check_column = $conn->query("SHOW COLUMNS FROM ratings LIKE 'comment'");
if ($check_column->num_rows == 0) {
    $alter_sql = "ALTER TABLE ratings ADD COLUMN comment TEXT AFTER rating";
    if ($conn->query($alter_sql)) {
        echo "Колонка comment успешно добавлена!\n";
    } else {
        echo "Ошибка при добавлении колонки comment: " . $conn->error . "\n";
    }
}

$conn->close();
?> 
require_once 'db-connect.php';

// Создаем таблицу ratings
$sql = "CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    profession_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (profession_id) REFERENCES professions(id),
    UNIQUE KEY unique_rating (user_id, profession_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql)) {
    echo "Таблица ratings успешно создана или обновлена!\n";
} else {
    echo "Ошибка при создании таблицы: " . $conn->error . "\n";
}

// Проверяем наличие колонки comment
$check_column = $conn->query("SHOW COLUMNS FROM ratings LIKE 'comment'");
if ($check_column->num_rows == 0) {
    $alter_sql = "ALTER TABLE ratings ADD COLUMN comment TEXT AFTER rating";
    if ($conn->query($alter_sql)) {
        echo "Колонка comment успешно добавлена!\n";
    } else {
        echo "Ошибка при добавлении колонки comment: " . $conn->error . "\n";
    }
}

$conn->close();
?> 
 
 
 
 
 
 
 