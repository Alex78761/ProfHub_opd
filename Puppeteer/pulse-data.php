<?php
session_start();
require_once '../db-connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if ($user_id !== null && isset($data['pulse'])) {
        // Сохраняем только "сырое" значение пульса (например, для live-отображения)
        $pulse = intval($data['pulse']);
        $stmt = $mysqli->prepare('INSERT INTO pulse_data (user_id, max_pulse, min_pulse, avg_pulse, time_recorded) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('iiidi', $user_id, $pulse, $pulse, $pulse, $zero = 0);
        $stmt->execute();
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Pulse data not found or user not logged in"]);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    if ($user_id !== null) {
        $sql = "SELECT * FROM pulse_data WHERE user_id = $user_id ORDER BY recorded_at DESC LIMIT 1";
        $result = $mysqli->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode([
                "pulse" => $row['avg_pulse'],
                "max_pulse" => $row['max_pulse'],
                "min_pulse" => $row['min_pulse'],
                "time_recorded" => $row['time_recorded'],
                "recorded_at" => $row['recorded_at']
            ]);
        } else {
            echo json_encode(["pulse" => null]);
        }
    } else {
        echo json_encode(["pulse" => null, "error" => "User not logged in"]);
    }
    exit();
}
?>
