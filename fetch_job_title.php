<?php
include 'config.php'; // Include your database configuration file
header('Content-Type: application/json'); // Set the content type to JSON

$response = ['position' => '']; // Initialize the response array

if (isset($_GET['name'])) {
    // Get the name from the query string and escape it to prevent SQL injection
    $name = $mysqli->real_escape_string($_GET['name']);
    
    // SQL query to fetch the position based on the employee name
    $sql = "SELECT position FROM employee_list WHERE employee_name LIKE '%$name%'";
    $result = $mysqli->query($sql); // Execute the query

    // Check if the query was successful and if any rows were returned
    if ($result && $row = $result->fetch_assoc()) {
        $response['position'] = $row['position']; // Set the position in the response
    }
}

// Echo the response as JSON
echo json_encode($response);
?>