<?php
include 'config.php'; // Make sure this is the correct path to your config file
header('Content-Type: application/json');

$response = [];
try {
    $sql_users = "SELECT employee_name FROM employee_list ORDER BY employee_name ASC";
    $result_users = $mysqli->query($sql_users);

    if ($result_users === false) {
        throw new Exception("Database query failed: " . $mysqli->error);
    }

    $users = [];
    if ($result_users->num_rows > 0) {
        while ($user_row = $result_users->fetch_assoc()) {
            $users[] = htmlspecialchars($user_row['employee_name']);
        }
    }
    $response['users'] = $users; // Return users in a structured way
} catch (Exception $e) {
    error_log($e->getMessage()); // Log the error for debugging
    $response['error'] = 'Unable to fetch users.';
}

echo json_encode($response);
?>