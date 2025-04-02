-- Создание базы данных
CREATE DATABASE IF NOT EXISTS it_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE it_portal;

-- Таблица пользователей
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin', 'expert', 'consultant') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Таблица профессий
CREATE TABLE professions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT,
    salary_range VARCHAR(100),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Таблица оценок профессий
CREATE TABLE profession_ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    profession_id INT NOT NULL,
    expert_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profession_id) REFERENCES professions(id),
    FOREIGN KEY (expert_id) REFERENCES users(id)
);

-- Таблица консультаций
CREATE TABLE consultations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    consultant_id INT NOT NULL,
    status ENUM('pending', 'scheduled', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    topic VARCHAR(255) NOT NULL,
    message TEXT,
    scheduled_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (consultant_id) REFERENCES users(id)
);

-- Создание администратора по умолчанию
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Добавление нескольких примеров профессий
INSERT INTO professions (title, description, requirements, salary_range, created_by) VALUES
('Frontend-разработчик', 'Специалист, который создает пользовательский интерфейс веб-приложений', 'HTML, CSS, JavaScript, React/Vue/Angular', '60,000 - 150,000 руб.', 1),
('Backend-разработчик', 'Специалист, работающий с серверной частью приложений', 'PHP/Python/Java, SQL, REST API', '80,000 - 180,000 руб.', 1),
('DevOps-инженер', 'Специалист по автоматизации и управлению инфраструктурой', 'Linux, Docker, CI/CD, Cloud platforms', '100,000 - 200,000 руб.', 1); 