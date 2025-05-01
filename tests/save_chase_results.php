<?php
session_start();

// Подключение к базе данных
require_once "../db-connect.php";

// Проверяем, была ли отправлена форма для сохранения результатов теста
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['res'])) {
    // Получаем среднее расстояние из POST-данных
    $avgDistance = $_POST['res'];

    // Проверяем, авторизован ли пользователь
    if (isset($_SESSION['user_id'])) {
        // Получаем user_id из сессии
        $user_id = $_SESSION['user_id'];

        // Получаем test_id и test_name по file_path из таблицы tests
        $file_path = 'chaseTest.php';
        $stmt_test_id = $conn->prepare("SELECT id, test_name FROM tests WHERE file_path = ? ORDER BY id ASC LIMIT 1");
        $stmt_test_id->bind_param("s", $file_path);
        $stmt_test_id->execute();
        $result_test_id = $stmt_test_id->get_result();

        if ($result_test_id->num_rows == 1) {
            $row_test_id = $result_test_id->fetch_assoc();
            $test_id = $row_test_id['id'];
            $test_name = $row_test_id['test_name'];

            // Подготовка и выполнение запроса на вставку результатов теста в базу данных
            $stmt = $conn->prepare("INSERT INTO test_results (user_id, test_id, test_name, result) VALUES (?, ?, ?, ?)");
            $result_value = floatval($avgDistance); // Преобразуем в число
            $stmt->bind_param("iiss", $user_id, $test_id, $test_name, $result_value);
            
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
        $_SESSION['guest_results']['chase_test'] = $avgDistance . ' см';
        echo "Результаты сохранены для гостя";
    }
} else {
    echo "Нет данных о расстоянии";
}
?>
