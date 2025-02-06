<?php
session_start();
include 'config.php';

$user_id = $_SESSION['id']; // Assuming user ID is stored in session
$sql = "SELECT * FROM issued_to_requests WHERE status = 'pending' AND requested_by = ? ORDER BY created_at DESC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

echo json_encode($notifications);
?>