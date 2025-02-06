<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();
if (isset($_SESSION['loggedin'])) {
	header("location: dashboard.php");
	exit;
}

// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) {
	header('Location: login.php');
	exit;
}
?>