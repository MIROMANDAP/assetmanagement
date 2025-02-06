<?php
include 'config.php';
session_start();

// Check if the user is logged in and has the correct account type
if (!isset($_SESSION['loggedin']) || $_SESSION['account_type'] !== "superadmin") {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Prepare the SQL query to fetch pending requests
$sql = "SELECT r.request_id, r.asset_tag, r.old_issued_to, r.new_issued_to, r.reason, r.status, u.firstname, u.lastname 
        FROM issued_to_requests r 
        JOIN users u ON r.requested_by = u.user_id 
        WHERE r.status = 'pending'";

$result = $mysqli->query($sql);

// Check for query execution errors
if (!$result) {
    // Log the error for debugging (consider using a logging library)
    error_log("Database query failed: " . $mysqli->error);
    echo json_encode(['error' => 'Database query failed']);
    exit;
}

// Fetch the results into an array
$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

// Prepare the response
$response = [
    'pending_count' => count($requests),
    'requests' => $requests,
];

// Return the JSON response
echo json_encode($response);
?>