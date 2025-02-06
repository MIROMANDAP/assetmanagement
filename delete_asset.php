<?php
//include config
require_once('config.php');
session_start();

//check if already logged in
if (!isset($_SESSION['loggedin'])) {
	header('Location: login.php');
	exit;
}

// check if user has permission to view only admin or superadmin can view this page
if ($_SESSION['account_type'] !== "admin" && $_SESSION['account_type'] !== "superadmin") {
    header('Location: 404.php');
    exit;
}

// Fetch the data from the database before deleting it
$query = "SELECT * FROM assets WHERE asset_tag = ?";
if ($stmt = $mysqli->prepare($query)) {
    $stmt->bind_param("s", $_GET['asset_tag']);
    $stmt->execute();
    $result = $stmt->get_result();
    $deleted_row = $result->fetch_assoc();
    $stmt->close();
}

// delete the the asset id from database using the url parameter
if (isset($_GET['asset_tag'])) {
    $id = $_GET['asset_tag'];
    $sql = "DELETE FROM assets WHERE asset_tag = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("s", $id);
        if ($stmt->execute()) {
            // Prepare to log the deletion
            $changes = "Deleted asset: ";
            $changes .= "Asset Tag: " . htmlspecialchars($deleted_row['asset_tag']) . ", ";
            $changes .= "Asset Type: " . htmlspecialchars($deleted_row['asset_type']) . ", ";
            $changes .= "Brand: " . htmlspecialchars($deleted_row['brand']) . ", ";
            $changes .= "Model: " . htmlspecialchars($deleted_row['model']) . ", ";
            $changes .= "Serial Number: " . htmlspecialchars($deleted_row['serial_number']) . ", ";
            $changes .= "Status: " . htmlspecialchars($deleted_row['status']) . ", ";
            $changes .= "Equipment Name: " . htmlspecialchars($deleted_row['equipment_name']) . ", ";
            $changes .= "Location: " . htmlspecialchars($deleted_row['location_asset']) . ", ";
            $changes .= "Price Value: " . htmlspecialchars($deleted_row['price_value']) . ", ";
            $changes .= "Issued To: " . htmlspecialchars($deleted_row['issued_to']) . ", ";
            $changes .= "Date Acquired: " . htmlspecialchars($deleted_row['date_acquired']) . ", ";
            $changes .= "Remarks: " . htmlspecialchars($deleted_row['remarks']);

            // Insert log entry
            $log_date = date("Y-m-d");
            $log_time = date("H:i:s");
            $log_sql = "INSERT INTO logs (action, asset_tag, firstname, username, changes, log_date, log_time) VALUES (?, ?, ?, ?, ?, ?, ?)";
            if ($log_stmt = $mysqli->prepare($log_sql)) {
                $action = 'Delete Asset';
                $log_stmt->bind_param("sssssss", $action, $id, $_SESSION['firstname'], $_SESSION['username'], $changes, $log_date, $log_time);
                $log_stmt->execute();
                $log_stmt->close();
            }

            header('Location: assets.php');
            echo "<script>alert('The Asset has been successfully removed.')</script>";
        } else {
            header('Location: assets.php');
            echo "<script>alert('Something went wrong. Please try again!')</script>";
        }
    }
    $stmt->close();
}
?>