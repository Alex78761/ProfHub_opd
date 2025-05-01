<?php
session_start();

// Подключение к базе данных
require_once "../db-connect.php";

// Проверяем, была ли отправлена форма для сохранения результатов теста
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['avgReactionTime'])) {
    // Получаем среднее время реакции из POST-данных
    $avgReactionTime = $_POST['avgReactionTime'];

    // Проверяем, авторизован ли пользователь
    if (isset($_SESSION['user_id'])) {
        // Получаем user_id из сессии
        $user_id = $_SESSION['user_id'];

        // Устанавливаем test_type и test_name
        $test_type = "Реакция";
        $test_name = "Тест на скорость сложения в уме";

        // Получаем test_id по test_type и test_name из таблицы tests
        $stmt_test_id = $conn->prepare("SELECT id FROM tests WHERE test_type = ? AND test_name = ?");
        $stmt_test_id->bind_param("ss", $test_type, $test_name);
        $stmt_test_id->execute();
        $result_test_id = $stmt_test_id->get_result();

        if ($result_test_id->num_rows == 1) {
            $row_test_id = $result_test_id->fetch_assoc();
            $test_id = $row_test_id['id'];

            // Подготовка и выполнение запроса на вставку результатов теста в базу данных
            $stmt = $conn->prepare("INSERT INTO test_results (user_id, test_id, test_name, result, score) VALUES (?, ?, ?, ?, ?)");
            $result_value = floatval($avgReactionTime); // Преобразуем в число
            $stmt->bind_param("iisdd", $user_id, $test_id, $test_name, $result_value, $result_value);
            
            if ($stmt->execute()) {
                echo "Результаты успешно сохранены";
            } else {
                echo "Ошибка при сохранении результатов: " . $conn->error;
            }
            $stmt->close();
        } else {
            echo "Ошибка при получении идентификатора теста";
        }
        $stmt_test_id->close();
    } else {
        // Пользователь не авторизован, сохраняем данные в сессию
        $_SESSION['guest_results']['Тест на скорость сложения в уме'] = $avgReactionTime . ' сек.';
        echo "Результаты сохранены для гостя";
    }
} else {
    echo "Нет данных о времени реакции";
}
?>
