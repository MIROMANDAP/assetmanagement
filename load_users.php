<?php
include 'config.php';

// Prepare a single query to fetch all necessary data
$sql = "
    SELECT employee_name AS users
    FROM employee_list
    ORDER BY employee_name ASC
";

// Execute the query
$result = mysqli_query($conn, $sql);

// Fetch the result
$data = array();
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row['users'];
}

// Output the data as JSON
echo json_encode($data);
?>