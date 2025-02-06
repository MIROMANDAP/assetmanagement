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
    $new_issued_to = $_POST['new_issued_to'];

    // Fetch the current issued_to of the asset
    $sql_current_issued_to = "SELECT issued_to FROM assets WHERE asset_tag = ?";
    if ($stmt = $mysqli->prepare($sql_current_issued_to)) {
        $stmt->bind_param("s", $asset_tag);
        $stmt->execute();
        $stmt->bind_result($current_issued_to);
        $stmt->fetch();
        $stmt->close();
    } else {
        // Handle SQL error
        echo "Error fetching current issued_to: " . $mysqli->error;
        exit();
    }

    // Update the asset's issued_to
    $sql_update_issued_to = "UPDATE assets SET issued_to = ? WHERE asset_tag = ?";
    if ($stmt = $mysqli->prepare($sql_update_issued_to)) {
        $stmt->bind_param("ss", $new_issued_to, $asset_tag);
        if (!$stmt->execute()) {
            // Handle error
            echo "Error updating asset issued_to: " . $stmt->error;
            exit();
        }
        $stmt->close();
    } else {
        // Handle prepare error
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        exit();
    }

    // Insert into issued_to_history
    $sql_history = "INSERT INTO issued_to_history (asset_tag, old_issued_to, new_issued_to, user_id, username, firstname) VALUES (?, ?, ?, ?, ?, ?)";
    if ($stmt = $mysqli->prepare($sql_history)) {
        $stmt->bind_param("ssssss", $asset_tag, $current_issued_to, $new_issued_to, $user_id, $username, $firstname);
        if (!$stmt->execute()) {
            // Handle error
            echo "Error inserting into issued_to history: " . $stmt->error;
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
        $action = 'Update Issued To';
        $changes_str = "Issued to changed from " . htmlspecialchars($current_issued_to) . " to " . htmlspecialchars($new_issued_to) . ".";
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

    // After processing the update successfully
    header("Location: edit_asset.php?success=1");
    exit();
}
?>