<?php
require_once 'db-connect.php';

// Добавляем колонки avg_salary и prospects, если они не существуют
$sql = "ALTER TABLE professions 
        ADD COLUMN IF NOT EXISTS avg_salary DECIMAL(10,2) DEFAULT 0.00,
        ADD COLUMN IF NOT EXISTS prospects TEXT;";

if ($conn->query($sql)) {
    echo "Таблица professions успешно обновлена!";
} else {
    echo "Ошибка при обновлении таблицы: " . $conn->error;
}

// Обновляем существующие записи с примерными данными
$update_sql = "UPDATE professions 
               SET avg_salary = 80000.00,
                   prospects = 'Перспективы развития будут добавлены позже.'
               WHERE avg_salary IS NULL OR avg_salary = 0";

if ($conn->query($update_sql)) {
    echo "\nДанные успешно обновлены!";
} else {
    echo "\nОшибка при обновлении данных: " . $conn->error;
}

$conn->close();
?> 
require_once 'db-connect.php';

// Добавляем колонки avg_salary и prospects, если они не существуют
$sql = "ALTER TABLE professions 
        ADD COLUMN IF NOT EXISTS avg_salary DECIMAL(10,2) DEFAULT 0.00,
        ADD COLUMN IF NOT EXISTS prospects TEXT;";

if ($conn->query($sql)) {
    echo "Таблица professions успешно обновлена!";
} else {
    echo "Ошибка при обновлении таблицы: " . $conn->error;
}

// Обновляем существующие записи с примерными данными
$update_sql = "UPDATE professions 
               SET avg_salary = 80000.00,
                   prospects = 'Перспективы развития будут добавлены позже.'
               WHERE avg_salary IS NULL OR avg_salary = 0";

if ($conn->query($update_sql)) {
    echo "\nДанные успешно обновлены!";
} else {
    echo "\nОшибка при обновлении данных: " . $conn->error;
}

$conn->close();
?> 
 
 
 
 
require_once 'db-connect.php';

// Добавляем колонки avg_salary и prospects, если они не существуют
$sql = "ALTER TABLE professions 
        ADD COLUMN IF NOT EXISTS avg_salary DECIMAL(10,2) DEFAULT 0.00,
        ADD COLUMN IF NOT EXISTS prospects TEXT;";

if ($conn->query($sql)) {
    echo "Таблица professions успешно обновлена!";
} else {
    echo "Ошибка при обновлении таблицы: " . $conn->error;
}

// Обновляем существующие записи с примерными данными
$update_sql = "UPDATE professions 
               SET avg_salary = 80000.00,
                   prospects = 'Перспективы развития будут добавлены позже.'
               WHERE avg_salary IS NULL OR avg_salary = 0";

if ($conn->query($update_sql)) {
    echo "\nДанные успешно обновлены!";
} else {
    echo "\nОшибка при обновлении данных: " . $conn->error;
}

$conn->close();
?> 
require_once 'db-connect.php';

// Добавляем колонки avg_salary и prospects, если они не существуют
$sql = "ALTER TABLE professions 
        ADD COLUMN IF NOT EXISTS avg_salary DECIMAL(10,2) DEFAULT 0.00,
        ADD COLUMN IF NOT EXISTS prospects TEXT;";

if ($conn->query($sql)) {
    echo "Таблица professions успешно обновлена!";
} else {
    echo "Ошибка при обновлении таблицы: " . $conn->error;
}

// Обновляем существующие записи с примерными данными
$update_sql = "UPDATE professions 
               SET avg_salary = 80000.00,
                   prospects = 'Перспективы развития будут добавлены позже.'
               WHERE avg_salary IS NULL OR avg_salary = 0";

if ($conn->query($update_sql)) {
    echo "\nДанные успешно обновлены!";
} else {
    echo "\nОшибка при обновлении данных: " . $conn->error;
}

$conn->close();
?> 
 
 
 
 
 
 
 