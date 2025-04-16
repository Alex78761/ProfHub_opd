<?php
session_start();

// Подключение к базе данных
require_once "../db-connect.php";

// Проверяем, была ли отправлена форма для сохранения результатов теста
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['avgReactionTime'])) {
    // Получаем среднее время реакций из POST-данных
    $res = $_POST['avgReactionTime'];

    // Проверяем, авторизован ли пользователь
    if (isset($_SESSION['user_id'])) {
        // Получаем user_id из сессии
        $user_id = $_SESSION['user_id'];

        // Устанавливаем test_type и test_name
        $test_type = "Оценка скорости реакции на цвет";
        $test_name = "реакция на цвет";

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
            $stmt->bind_param("iissd", $user_id, $test_id, $test_name, $res, $res);
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
        $_SESSION['guest_avg_reaction_time'] = $res;
        echo "Результаты успешно сохранены в сессии";
    }
} else {
    echo "Нет данных о реакционном времени";
}
?>
