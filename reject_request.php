<?php
include 'config.php';
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['account_type'] !== "superadmin") {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];

    // Update the request status to rejected
    $stmt = $mysqli->prepare("UPDATE issued_to_requests SET status = 'rejected' WHERE request_id = ?");
    $stmt->bind_param("i", $request_id);

    if ($stmt->execute()) {
        // Notify the admin (you can implement a notification system here)
        // Redirect back to the requests page
        header('Location: view_requests.php?success=1');
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>