USE mukhinnnik;

-- Добавление тестов для разных типов сенсорных реакций
INSERT INTO tests (test_type, test_name, file_path) VALUES 
('Сенсорные реакции', 'Тест на скорость реакции', 'tests/reaction_speed.php'),
('Сенсорные реакции', 'Тест на слуховое восприятие', 'tests/audio_perception.php'),
('Сенсорные реакции', 'Тест на зрительное восприятие', 'tests/visual_perception.php'),
('Сенсорные реакции', 'Тест на тактильную чувствительность', 'tests/tactile_sensitivity.php'),
('Сенсорные реакции', 'Комплексный тест на реакцию', 'tests/complex_reaction.php');

-- Добавление тестов для профессиональных навыков
INSERT INTO tests (test_type, test_name, file_path) VALUES 
('Профессиональные навыки', 'Оценка внимательности', 'tests/attention.php'),
('Профессиональные навыки', 'Тест на координацию', 'tests/coordination.php'),
('Профессиональные навыки', 'Тест на память', 'tests/memory.php'),
('Профессиональные навыки', 'Логическое мышление', 'tests/logic.php'),
('Профессиональные навыки', 'Пространственное мышление', 'tests/spatial.php'); 