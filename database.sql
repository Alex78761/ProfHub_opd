-- Создание базы данных
CREATE DATABASE IF NOT EXISTS mukhinnnik;
USE mukhinnnik;

-- Создание таблицы пользователей
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(32) NOT NULL,
    role ENUM('admin', 'expert', 'respondent', 'user') NOT NULL DEFAULT 'user'
);

-- Создание таблицы тестов
CREATE TABLE IF NOT EXISTS tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_type VARCHAR(100) NOT NULL,
    test_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL
);

-- Создание таблицы результатов тестов
CREATE TABLE IF NOT EXISTS test_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_id INT NOT NULL,
    user_id INT NOT NULL,
    result FLOAT NOT NULL,
    date_taken TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (test_id) REFERENCES tests(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Добавление тестового администратора
INSERT INTO users (username, password, role) VALUES 
('admin', MD5('admin123'), 'admin');

-- Добавление тестового эксперта
INSERT INTO users (username, password, role) VALUES 
('expert', MD5('expert123'), 'expert');

-- Добавление тестового пользователя
INSERT INTO users (username, password, role) VALUES 
('user', MD5('user123'), 'user'); 