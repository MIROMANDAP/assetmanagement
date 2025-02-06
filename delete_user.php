<?php
include 'config.php';

session_start();
if ($_SESSION['account_type'] !== "superadmin") {
    header('Location: 404.php');
    exit();
}

// Function to test and sanitize input
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if (isset($_GET['id'])) {
    $user_id = test_input($_GET['id']);
    
    // Prepare a delete statement for related records first
    $sql_delete_reset = "DELETE FROM password_reset WHERE user_id = ?";
    
    if ($stmt = $mysqli->prepare($sql_delete_reset)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }

    // Now, prepare a delete statement for the user
    $sql = "DELETE FROM users WHERE user_id = ?";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            echo "<script>alert('User deleted successfully.'); window.location.href='list_of_user.php';</script>";
        } else {
            echo "<script>alert('Error deleting user. Please try again later.'); window.location.href='list_of_user.php';</script>";
        }
    }
    $stmt->close();
} else {
    echo "<script>alert('Invalid request.'); window.location.href='list_of_user.php';</script>";
}

// Close the database connection
$mysqli->close();
?>
