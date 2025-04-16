-- Таблицы без внешних ключей
CREATE TABLE `experts` (
 `id` int NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 `sgroup` varchar(255) NOT NULL,
 `code` int NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `professions` (
 `id` int NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 `description` text NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `pvk` (
 `id` int NOT NULL AUTO_INCREMENT,
 `category` varchar(255) NOT NULL,
 `name` varchar(255) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=171 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `tests` (
 `id` int NOT NULL AUTO_INCREMENT,
 `test_type` varchar(255) NOT NULL,
 `test_name` varchar(255) NOT NULL,
 `file_path` varchar(255) NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `test_name` (`test_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Таблицы с внешними ключами
CREATE TABLE `users` (
 `id` int NOT NULL AUTO_INCREMENT,
 `username` varchar(255) NOT NULL,
 `password` varchar(255) NOT NULL,
 `role` enum('admin','expert','respondent','user') NOT NULL DEFAULT 'user',
 `respondent_id` int DEFAULT NULL,
 `expert_id` int DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `users_respondent_id_fk` (`respondent_id`),
 KEY `fk_expert_id` (`expert_id`),
 CONSTRAINT `fk_expert_id` FOREIGN KEY (`expert_id`) REFERENCES `experts` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `respondents` (
 `id` int NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 `gender` enum('Male','Female') NOT NULL,
 `age` int NOT NULL,
 `user_id` int NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `name` (`name`),
 KEY `user_id` (`user_id`),
 CONSTRAINT `respondents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `users` ADD CONSTRAINT `users_respondent_id_fk` FOREIGN KEY (`respondent_id`) REFERENCES `respondents` (`id`);

CREATE TABLE `ratings` (
 `id` int NOT NULL AUTO_INCREMENT,
 `profession_id` int DEFAULT NULL,
 `pvk_id` int DEFAULT NULL,
 `user_id` int DEFAULT NULL,
 `rating` int DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `profession_id` (`profession_id`),
 KEY `pvk_id` (`pvk_id`),
 KEY `user_id` (`user_id`),
 CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`profession_id`) REFERENCES `professions` (`id`),
 CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`pvk_id`) REFERENCES `pvk` (`id`),
 CONSTRAINT `ratings_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=493 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `respondent_tests` (
 `id` int NOT NULL AUTO_INCREMENT,
 `respondent_id` int NOT NULL,
 `test_id` int NOT NULL,
 `test_order` int NOT NULL,
 PRIMARY KEY (`id`),
 KEY `respondent_id` (`respondent_id`),
 KEY `test_id_fk` (`test_id`),
 CONSTRAINT `fk_respondent_id` FOREIGN KEY (`respondent_id`) REFERENCES `respondents` (`id`),
 CONSTRAINT `fk_test_id_fk` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `test_results` (
 `id` int NOT NULL AUTO_INCREMENT,
 `user_id` int DEFAULT NULL,
 `test_id` int NOT NULL,
 `result` decimal(10,2) NOT NULL,
 `test_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`),
 KEY `user_id` (`user_id`),
 KEY `test_id` (`test_id`),
 CONSTRAINT `fk_test_id` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`),
 CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `chat_messages` (
    `id` int NOT NULL AUTO_INCREMENT,
    `sender_id` int NOT NULL,
    `receiver_id` int NOT NULL,
    `message` text NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `is_read` boolean DEFAULT FALSE,
    PRIMARY KEY (`id`),
    KEY `sender_id` (`sender_id`),
    KEY `receiver_id` (`receiver_id`),
    CONSTRAINT `chat_messages_sender_fk` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
    CONSTRAINT `chat_messages_receiver_fk` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Создание таблицы пользователей
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'expert', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Создание таблицы профессий
CREATE TABLE IF NOT EXISTS professions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Создание таблицы критериев оценки
CREATE TABLE IF NOT EXISTS evaluation_criteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profession_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    weight FLOAT DEFAULT 1.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profession_id) REFERENCES professions(id) ON DELETE CASCADE
);

-- Создание таблицы тестов
CREATE TABLE IF NOT EXISTS tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Создание таблицы результатов тестов
CREATE TABLE IF NOT EXISTS test_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    test_id INT,
    score FLOAT,
    data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE
);

-- Создание таблицы связи тестов и критериев оценки
CREATE TABLE IF NOT EXISTS test_criteria (
    test_id INT,
    criteria_id INT,
    weight FLOAT DEFAULT 1.0,
    PRIMARY KEY (test_id, criteria_id),
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE,
    FOREIGN KEY (criteria_id) REFERENCES evaluation_criteria(id) ON DELETE CASCADE
);

-- Создание таблицы сообщений чата
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT,
    receiver_id INT,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Вставка тестовых данных для профессий
INSERT INTO professions (name, description) VALUES
('Программист', 'Специалист по разработке программного обеспечения'),
('Дизайнер', 'Специалист по созданию визуального контента'),
('Аналитик', 'Специалист по анализу данных и бизнес-процессов');

-- Вставка тестовых данных для критериев оценки
INSERT INTO evaluation_criteria (profession_id, name, description, weight) VALUES
(1, 'Логическое мышление', 'Способность решать сложные логические задачи', 1.5),
(1, 'Внимание к деталям', 'Способность замечать и учитывать мелкие детали', 1.2),
(1, 'Математические способности', 'Умение работать с числами и формулами', 1.3),
(2, 'Креативность', 'Способность создавать оригинальные решения', 1.5),
(2, 'Чувство стиля', 'Понимание эстетики и композиции', 1.4),
(2, 'Внимание к трендам', 'Знание современных тенденций в дизайне', 1.2),
(3, 'Аналитическое мышление', 'Способность анализировать большие объемы данных', 1.5),
(3, 'Системное мышление', 'Умение видеть взаимосвязи и закономерности', 1.4),
(3, 'Внимание к деталям', 'Способность замечать важные детали в данных', 1.3);

-- Вставка тестовых данных для тестов
INSERT INTO tests (title, description, type) VALUES
('Тест на логику', 'Проверка логического мышления', 'logic'),
('Тест на внимательность', 'Проверка внимания к деталям', 'attention'),
('Математический тест', 'Проверка математических способностей', 'math'),
('Тест на креативность', 'Проверка творческого мышления', 'creativity');

-- Связываем тесты с критериями оценки
INSERT INTO test_criteria (test_id, criteria_id, weight) VALUES
(1, 1, 1.5), -- Логический тест -> Логическое мышление
(1, 2, 1.2), -- Логический тест -> Внимание к деталям
(2, 2, 1.5), -- Тест на внимательность -> Внимание к деталям
(3, 3, 1.5), -- Математический тест -> Математические способности
(4, 4, 1.5); -- Тест на креативность -> Креативность

-- Создание консультанта
INSERT INTO users (username, password, role) VALUES
('consultant', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'expert');