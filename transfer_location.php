<?php
include 'config.php';
session_start();
date_default_timezone_set('Asia/Shanghai');

// Check if the user is logged in and has the correct account type
if (!isset($_SESSION['loggedin']) || ($_SESSION['account_type'] !== "admin" && $_SESSION['account_type'] !== "superadmin")) {
    header('Location: 404.php');
    exit;
}

// Fetch user details from session
$user_id = $_SESSION['id']; // User ID from session
$username = $_SESSION['username']; // Username from session
$firstname = $_SESSION['firstname']; // First name from session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_tag = $_POST['asset_tag'];
    $new_location = $_POST['new_location'];
    $transfer_reason = $_POST['transfer_reason'];

    // Fetch the current location of the asset
    $sql_current_location = "SELECT location_asset FROM assets WHERE asset_tag = ?";
    if ($stmt = $mysqli->prepare($sql_current_location)) {
        $stmt->bind_param("s", $asset_tag);
        $stmt->execute();
        $stmt->bind_result($current_location);
        $stmt->fetch();
        $stmt->close();
    } else {
        // Handle SQL error
        echo "Error fetching current location: " . $mysqli->error;
        exit();
    }

    // Update the asset's location
    $sql_update_location = "UPDATE assets SET location_asset = ? WHERE asset_tag = ?";
    if ($stmt = $mysqli->prepare($sql_update_location)) {
        $stmt->bind_param("ss", $new_location, $asset_tag);
        if (!$stmt->execute()) {
            // Handle error
            echo "Error updating asset location: " . $stmt->error;
            exit();
        }
        $stmt->close();
    } else {
        // Handle prepare error
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        exit();
    }

    // Insert into location_history
    $sql_history = "INSERT INTO location_history (asset_tag, old_location, new_location, transfer_reason, user_id, username, firstname) VALUES (?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $mysqli->prepare($sql_history)) {
        $stmt->bind_param("sssssss", $asset_tag, $current_location, $new_location, $transfer_reason, $user_id, $username, $firstname);
        if (!$stmt->execute()) {
            // Handle error
            echo "Error inserting into location history: " . $stmt->error;
            exit();
        }
        $stmt->close();
    } else {
        // Handle prepare error
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        exit();
    }

    // Insert log entry
    $log_date = date("Y-m-d");
    $log_time = date("H:i:s");
    $log_sql = "INSERT INTO logs (action, asset_tag, firstname, username, changes, log_date, log_time) VALUES (?, ?, ?, ?, ?, ?, ?)";
    if ($log_stmt = $mysqli->prepare($log_sql)) {
        $action = 'Transfer Location';
        $changes_str = "Location changed from " . htmlspecialchars($current_location) . " to " . htmlspecialchars($new_location) . ". Reason: " . htmlspecialchars($transfer_reason);
        $log_stmt->bind_param("sssssss", $action, $asset_tag, $firstname, $username, $changes_str, $log_date, $log_time);
        if (!$log_stmt->execute()) {
            // Handle error
            echo "Error inserting log entry: " . $log_stmt->error;
            exit();
        }
        $log_stmt->close();
    } else {
        // Handle prepare error
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        exit();
    }

    // After processing the transfer successfully
header("Location: edit_asset.php?success1=1");
exit();
}
?>
