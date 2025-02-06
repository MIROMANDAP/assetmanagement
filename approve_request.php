<?php
include 'config.php';
session_start();
date_default_timezone_set('Asia/Shanghai');

// Check if the user is logged in and is a superadmin
if (!isset($_SESSION['loggedin']) || $_SESSION['account_type'] !== "superadmin") {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];

    // Fetch the request details, including requested_by and reason
    $sql_request = "SELECT asset_tag, old_issued_to, new_issued_to, requested_by, reason FROM issued_to_requests WHERE request_id = ?";
    if ($stmt = $mysqli->prepare($sql_request)) {
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->bind_result($asset_tag, $old_issued_to, $new_issued_to, $requested_by, $reason);
        $stmt->fetch();
        $stmt->close();
    } else {
        echo "Error fetching request details: " . $mysqli->error;
        exit();
    }

    // Update the request status to approved
    $stmt = $mysqli->prepare("UPDATE issued_to_requests SET status = 'approved' WHERE request_id = ?");
    $stmt->bind_param("i", $request_id);

    if ($stmt->execute()) {
       // Update the asset's issued_to field and set status to "Deployed"
$stmt = $mysqli->prepare("UPDATE assets SET issued_to = ?, status = 'Deployed' WHERE asset_tag = ?");
$stmt->bind_param("ss", $new_issued_to, $asset_tag);

        if ($stmt->execute()) {
            // Insert into issued_to_history
            $user_id = $_SESSION['id']; // Assuming user_id is stored in session
            $username = $_SESSION['username'];
            $firstname = $_SESSION['firstname'];

            $sql_history = "INSERT INTO issued_to_history (asset_tag, old_issued_to, new_issued_to, user_id, username, firstname) VALUES (?, ?, ?, ?, ?, ?)";
            if ($history_stmt = $mysqli->prepare($sql_history)) {
                $history_stmt->bind_param("ssssss", $asset_tag, $old_issued_to, $new_issued_to, $user_id, $username, $firstname);
                if (!$history_stmt->execute()) {
                    echo "Error inserting into issued_to_history: " . $history_stmt->error;
                }
                $history_stmt->close();
            }

            // Insert log entry
            $log_date = date("Y-m-d");
            $log_time = date("H:i:s");
            $log_sql = "INSERT INTO logs (action, asset_tag, firstname, username, changes, log_date, log_time) VALUES (?, ?, ?, ?, ?, ?, ?)";
            if ($log_stmt = $mysqli->prepare($log_sql)) {
                $action = 'Update Issued To';
                // Create changes string with requested_by info
                $requested_by_name = ''; // Fetch the name of the requester using the requested_by ID
                $sql_user = "SELECT firstname, lastname FROM users WHERE user_id = ?";
                if ($user_stmt = $mysqli->prepare($sql_user)) {
                    $user_stmt->bind_param("i", $requested_by);
                    $user_stmt->execute();
                    $user_stmt->bind_result($req_firstname, $req_lastname);
                    $user_stmt->fetch();
                    $requested_by_name = htmlspecialchars($req_firstname . ' ' . $req_lastname);
                    $user_stmt->close();
                }

                // Include reason in the changes string
                $changes_str = "Requested by: " . $requested_by_name . ". Issued to changed from " . htmlspecialchars($old_issued_to) . " to " . htmlspecialchars($new_issued_to) . ". Reason: " . htmlspecialchars($reason) . ".";
                $log_stmt->bind_param("sssssss", $action, $asset_tag, $firstname, $username, $changes_str, $log_date, $log_time);
                if (!$log_stmt->execute()) {
                    echo "Error inserting log entry: " . $log_stmt->error;
                    exit();
                }
                $log_stmt->close();
            } else {
                echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli-> error;
                exit();
            }

            // Redirect back to the requests page
            header('Location: view_requests.php?success=1');
            exit(); 
        } else {
            echo "Error updating asset issued_to: " . $stmt->error;
            exit();
        }
    } else {
        echo "Error updating request status: " . $stmt->error;
        exit();
    }

    $stmt->close();
}
?>