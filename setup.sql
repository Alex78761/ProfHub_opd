CREATE TABLE IF NOT EXISTS experts (
  id int NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  sgroup varchar(255) NOT NULL,
  code int NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS professions (
  id int NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  description text NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS pvk (
  id int NOT NULL AUTO_INCREMENT,
  category varchar(255) NOT NULL,
  name varchar(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS users (
  id int NOT NULL AUTO_INCREMENT,
  username varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  role enum('admin','expert','respondent','user') NOT NULL DEFAULT 'user',
  respondent_id int DEFAULT NULL,
  expert_id int DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS respondents (
  id int NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  gender enum('Male','Female') NOT NULL,
  age int NOT NULL,
  user_id int NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY name (name),
  KEY user_id (user_id),
  CONSTRAINT respondents_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS tests (
  id int NOT NULL AUTO_INCREMENT,
  test_type varchar(255) NOT NULL,
  test_name varchar(255) NOT NULL,
  file_path varchar(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY test_name (test_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS ratings (
  id int NOT NULL AUTO_INCREMENT,
  profession_id int DEFAULT NULL,
  pvk_id int DEFAULT NULL,
  user_id int DEFAULT NULL,
  rating int DEFAULT NULL,
  PRIMARY KEY (id),
  KEY profession_id (profession_id),
  KEY pvk_id (pvk_id),
  KEY user_id (user_id),
  CONSTRAINT ratings_ibfk_1 FOREIGN KEY (profession_id) REFERENCES professions (id),
  CONSTRAINT ratings_ibfk_2 FOREIGN KEY (pvk_id) REFERENCES pvk (id),
  CONSTRAINT ratings_ibfk_3 FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS respondent_tests (
  id int NOT NULL AUTO_INCREMENT,
  respondent_id int NOT NULL,
  test_id int NOT NULL,
  test_order int NOT NULL,
  PRIMARY KEY (id),
  KEY respondent_id (respondent_id),
  KEY test_id_fk (test_id),
  CONSTRAINT fk_respondent_id FOREIGN KEY (respondent_id) REFERENCES respondents (id),
  CONSTRAINT fk_test_id_fk FOREIGN KEY (test_id) REFERENCES tests (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS test_results (
  id int NOT NULL AUTO_INCREMENT,
  user_id int DEFAULT NULL,
  test_id int NOT NULL,
  result decimal(10,2) NOT NULL,
  test_date timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY user_id (user_id),
  KEY test_id (test_id),
  CONSTRAINT fk_test_id FOREIGN KEY (test_id) REFERENCES tests (id),
  CONSTRAINT fk_user_id FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci; 