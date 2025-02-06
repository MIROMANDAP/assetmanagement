<?php
// log_activity.php
function log_activity($action, $asset_tag, $user_id, $mysqli) {
    $timestamp = date("Y-m-d H:i:s");
    
    // Get the username of the person who made the change
    $sql_user = "SELECT username FROM users WHERE id = ?";
    if ($stmt_user = $mysqli->prepare($sql_user)) {
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $stmt_user->bind_result($username);
        $stmt_user->fetch();
        $stmt_user->close();
    } else {
        $username = "Unknown";
    }

    // Insert log into the activity_log table
    $sql_log = "INSERT INTO activity_log (user_id, username, action, asset_tag, timestamp) VALUES (?, ?, ?, ?, ?)";
    if ($stmt_log = $mysqli->prepare($sql_log)) {
        $stmt_log->bind_param("issss", $user_id, $username, $action, $asset_tag, $timestamp);
        $stmt_log->execute();
        $stmt_log->close();
    }
}
?>
