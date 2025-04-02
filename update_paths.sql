USE mukhinnnik;

-- Обновление путей для тестов сенсомоторных реакций
UPDATE tests SET file_path = 'tests/simple_color_test.php' WHERE test_name = 'реакция на свет';
UPDATE tests SET file_path = 'tests/sound_reaction_test.php' WHERE test_name = 'реакция на звук';
UPDATE tests SET file_path = 'tests/advanced_color_test.php' WHERE test_name = 'оценка скорости реакции на разные цвета';
UPDATE tests SET file_path = 'tests/audio_count_test.php' WHERE test_name = 'оценка скорости реакции на сложный звуковой сигнал – сложение в уме';
UPDATE tests SET file_path = 'tests/visual_count_test.php' WHERE test_name = 'оценка скорости реакции на сложение в уме (чет/нечет) - визуально';
UPDATE tests SET file_path = 'tests/movement_test.php' WHERE test_name = 'реакция на движение';
UPDATE tests SET file_path = 'tests/advanced_movement_test.php' WHERE test_name = 'реакция на множество движущихся объектов';
UPDATE tests SET file_path = 'tests/analog_test.php' WHERE test_name = 'реакция на изменение направления движения';
UPDATE tests SET file_path = 'tests/chaseTest.php' WHERE test_name = 'слежение за объектом';

-- Обновление путей для тестов профессиональных навыков
UPDATE tests SET file_path = '/test_result.php?id=6' WHERE test_name = 'Оценка внимательности';
UPDATE tests SET file_path = '/test_result.php?id=7' WHERE test_name = 'Тест на координацию';
UPDATE tests SET file_path = '/test_result.php?id=8' WHERE test_name = 'Тест на память';
UPDATE tests SET file_path = '/test_result.php?id=9' WHERE test_name = 'Логическое мышление';
UPDATE tests SET file_path = '/test_result.php?id=10' WHERE test_name = 'Пространственное мышление'; 