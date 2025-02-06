<?php
date_default_timezone_set('Asia/Shanghai');
include 'config.php';
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_tag = $_POST['asset_tag'];
    $old_issued_to = $_POST['old_issued_to'];
    $new_issued_to = $_POST['new_issued_to'];
    $reason = $_POST['reason'];
    $requested_by = $_SESSION['id']; // Assuming user_id is stored in session

    // Prepare and execute the insert statement
    $stmt = $mysqli->prepare("INSERT INTO issued_to_requests (asset_tag, old_issued_to, new_issued_to, reason, requested_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $asset_tag, $old_issued_to, $new_issued_to, $reason, $requested_by);

    if ($stmt->execute()) {
        // Redirect back to the assets page with success message
        header('Location: edit_asset.php?success=1');
    } else {
        // Handle error
        echo "Error : " . $stmt->error;
    }

    $stmt->close();
}
?>