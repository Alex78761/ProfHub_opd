<?php
// Параметры подключения к базе данных
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'opdlab';

// Создаем подключение к базе данных
$mysqli = new mysqli($host, $username, $password, $database);

// Проверяем подключение
if ($mysqli->connect_error) {
    die("Ошибка подключения к базе данных: " . $mysqli->connect_error);
}

// Устанавливаем кодировку
$mysqli->set_charset("utf8");
?> 