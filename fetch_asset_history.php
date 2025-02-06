<?php
include 'config.php';
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['asset_tag'])) {
    $asset_tag = $mysqli->real_escape_string($_GET['asset_tag']);
    
    // Fetch asset history from the database
    $sql = "SELECT action, asset_tag, firstname, username, changes, log_date, log_time 
            FROM logs 
            WHERE asset_tag = '$asset_tag' 
            ORDER BY log_date DESC, log_time DESC";
    
    $result = $mysqli->query($sql);
    
    if ($result->num_rows > 0) {
        echo "<table class='table table-striped'>";
        echo "<thead><tr><th>Date</th><th>Time</th><th>Assettag</th><th>Action</th><th>Changes</th><th>User</th></tr></thead><tbody>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['log_date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['log_time']) . "</td>";
			echo "<td>" . htmlspecialchars($row['asset_tag']) . "</td>";
            echo "<td>" . htmlspecialchars($row['action']) . "</td>";
            echo "<td>" . htmlspecialchars($row['changes']) . "</td>";
            echo "<td>" . htmlspecialchars($row['firstname'] . ' (' . $row['username'] . ')') . "</td>";
            echo "</tr>";
        }
        
        echo "</tbody></table>";
    } else {
        echo "<p>No history available for this asset .</p>";
    }
} else {
    echo "<p>Invalid asset tag.</p>";
}
?>