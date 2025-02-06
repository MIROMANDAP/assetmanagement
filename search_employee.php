<?php
include 'config.php'; // Include your database configuration file

// Check if the request is an AJAX request
if (isset($_GET['query'])) {
    $search_query = trim($_GET['query']);
    $employees = [];

    // Prepare the SQL statement
    $sql = "SELECT employee_name FROM employee_list WHERE employee_name LIKE ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $param_query = "%" . $search_query . "%";
        $stmt->bind_param("s", $param_query);
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch results
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row['employee_name'];
        }
        $stmt->close();
    }

    // Return the results as a JSON array
    echo json_encode($employees);
}
$mysqli->close();
?>