<?php
require_once 'db-connect.php';

$sql = "SELECT tr.*, t.test_type, t.test_name 
        FROM test_results tr 
        JOIN tests t ON tr.test_id = t.id 
        ORDER BY tr.created_at DESC 
        LIMIT 5";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<pre>";
    while($row = $result->fetch_assoc()) {
        print_r($row);
    }
    echo "</pre>";
} else {
    echo "Нет результатов";
}

$conn->close();
?> 