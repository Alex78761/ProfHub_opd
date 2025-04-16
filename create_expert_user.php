<?php
require_once "db-connect.php";

// Данные для эксперта
$username = "expert";
$password = password_hash("expert123", PASSWORD_DEFAULT);
$role = "expert";

// Проверяем, существует ли уже пользователь с таким именем
$check_query = "SELECT id FROM users WHERE username = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("s", $username);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    // Добавляем эксперта в таблицу users
    $insert_query = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("sss", $username, $password, $role);
    
    if ($insert_stmt->execute()) {
        echo "Эксперт успешно создан!\n";
        echo "Логин: expert\n";
        echo "Пароль: expert123\n";
    } else {
        echo "Ошибка при создании эксперта: " . $conn->error;
    }
} else {
    echo "Пользователь с именем 'expert' уже существует";
}

$conn->close();
?> 
require_once "db-connect.php";

// Данные для эксперта
$username = "expert";
$password = password_hash("expert123", PASSWORD_DEFAULT);
$role = "expert";

// Проверяем, существует ли уже пользователь с таким именем
$check_query = "SELECT id FROM users WHERE username = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("s", $username);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    // Добавляем эксперта в таблицу users
    $insert_query = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("sss", $username, $password, $role);
    
    if ($insert_stmt->execute()) {
        echo "Эксперт успешно создан!\n";
        echo "Логин: expert\n";
        echo "Пароль: expert123\n";
    } else {
        echo "Ошибка при создании эксперта: " . $conn->error;
    }
} else {
    echo "Пользователь с именем 'expert' уже существует";
}

$conn->close();
?> 
 
 
 
 
require_once "db-connect.php";

// Данные для эксперта
$username = "expert";
$password = password_hash("expert123", PASSWORD_DEFAULT);
$role = "expert";

// Проверяем, существует ли уже пользователь с таким именем
$check_query = "SELECT id FROM users WHERE username = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("s", $username);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    // Добавляем эксперта в таблицу users
    $insert_query = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("sss", $username, $password, $role);
    
    if ($insert_stmt->execute()) {
        echo "Эксперт успешно создан!\n";
        echo "Логин: expert\n";
        echo "Пароль: expert123\n";
    } else {
        echo "Ошибка при создании эксперта: " . $conn->error;
    }
} else {
    echo "Пользователь с именем 'expert' уже существует";
}

$conn->close();
?> 
require_once "db-connect.php";

// Данные для эксперта
$username = "expert";
$password = password_hash("expert123", PASSWORD_DEFAULT);
$role = "expert";

// Проверяем, существует ли уже пользователь с таким именем
$check_query = "SELECT id FROM users WHERE username = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("s", $username);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    // Добавляем эксперта в таблицу users
    $insert_query = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("sss", $username, $password, $role);
    
    if ($insert_stmt->execute()) {
        echo "Эксперт успешно создан!\n";
        echo "Логин: expert\n";
        echo "Пароль: expert123\n";
    } else {
        echo "Ошибка при создании эксперта: " . $conn->error;
    }
} else {
    echo "Пользователь с именем 'expert' уже существует";
}

$conn->close();
?> 
 
 
 
 
 
 
 