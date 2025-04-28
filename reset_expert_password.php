<?php
require_once "db-connect.php";

$new_password = 'expert123';
$hash = password_hash($new_password, PASSWORD_BCRYPT);

$stmt = $conn->prepare("UPDATE users SET password=? WHERE username='expert'");
$stmt->bind_param("s", $hash);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Пароль успешно сброшен!";
} else {
    echo "Ошибка при сбросе пароля или пользователь не найден.";
}
$stmt->close();
$conn->close(); 