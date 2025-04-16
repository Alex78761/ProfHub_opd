-- Удаляем старых пользователей
DELETE FROM users WHERE username IN ('admin', 'consultant');

-- Добавляем администратора с новым паролем
INSERT INTO users (username, password, role) 
VALUES ('admin', '$2y$12$OF4UVk34VjMHSFsJWdb42u8KfPcnh8MacGTu2h6mMGENXGL3TiZY2', 'admin');

-- Добавляем консультанта с новым паролем
INSERT INTO users (username, password, role) 
VALUES ('consultant', '$2y$12$WoI.GnhGXYx55wUo5oz4Cuf9YQ20OyLdg6VdFmCrg/MoOinMul1ja', 'expert'); 