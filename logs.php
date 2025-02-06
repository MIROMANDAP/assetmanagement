<?php
// Include the database connection file
require_once('config.php');

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Check if the user has permission to view the logs
if ($_SESSION['account_type'] !== "admin" && $_SESSION['account_type'] !== "superadmin") {
    header('Location: 404.php');
    exit;
}
// Function to backup logs
function backupLogs($mysqli) {
    $backupQuery = "SELECT * FROM logs";
    $result = $mysqli->query($backupQuery);
    
    if ($result->num_rows > 0) {
        $filename = "logs_backup_" . date("Y-m-d_H-i-s") . ".csv";
        $fp = fopen($filename, "w");
        
        $header = array("Log Date", "Log Time", "Action", "Asset Tag", "Firstname", "Username", "Changes");
        fputcsv($fp, $header);
        
        while ($row = $result->fetch_assoc()) {
            fputcsv($fp, $row);
        }
        
        fclose($fp);
        
        return $filename; // Return the filename of the backup file
    } else {
        return false; // No logs to backup
    }
}

// Function to clear logs
function clearLogs($mysqli) {
    $deleteQuery = "DELETE FROM logs";
    if ($mysqli->query($deleteQuery) === TRUE) {
        return true; // Success
    } else {
        return false; // Failure
    }
}

// Handle backup and clear logs request
if (isset($_POST['backup_and_clear_logs'])) {
    $backupFilename = backupLogs($mysqli);
    if ($backupFilename) {
        echo "<script>alert('Logs backed up to $backupFilename and cleared successfully!');</script>";
    } else {
        echo "<script>alert('Failed to backup logs.');</script>";
    }
    
    if (clearLogs($mysqli)) {
        echo "<script>alert('Logs cleared successfully!');</script>";
    } else {
        echo "<script>alert('Failed to clear logs.');</script>";
    }
}

// Set the default date range to the current month
$startDate = date('Y-m-01');
$endDate = date('Y-m-t');

// Check if the date range has been set
if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
}

// Set the default action filter to ALL
$actionFilter = 'ALL';

// Check if the action filter has been set
if (isset($_POST['action_filter'])) {
    $actionFilter = $_POST['action_filter'];
}

// Query to get the recent logs
$recentLogsQuery = "SELECT * FROM logs ORDER BY log_date DESC, log_time DESC LIMIT 10";

// Query to get the logs based on the date range and action filter
$logsQuery = "SELECT * FROM logs WHERE log_date BETWEEN '$startDate' AND '$endDate'";

// Apply the action filter to the query
if ($actionFilter !== 'ALL') {
    if ($actionFilter === 'Insert Asset') {
        $logsQuery .= " AND (action = 'Insert Asset' OR action = 'Bulk Insert Asset')";
    } else {
        $logsQuery .= " AND action = '$actionFilter'";
    }
}

// Order the logs by date and time
$logsQuery .= " ORDER BY log_date DESC, log_time DESC";

// Calculate the number of results per page
$resultsPerPage = 10;

// Calculate the current page number
if (isset($_GET['page'])) {
    $currentPage = $_GET['page'];
} else {
    $currentPage = 1;
}

// Calculate the offset for the current page
$offset = ($currentPage - 1) * $resultsPerPage;

// Modify the query to limit the results to the current page
$logsQuery .= " LIMIT $offset, $resultsPerPage";

// Prepare the queries
$recentLogsStmt = $mysqli->prepare($recentLogsQuery);
$logsStmt = $mysqli->prepare($logsQuery);

// Execute the queries
$recentLogsStmt->execute();
$recentLogsResult = $recentLogsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Close the statement
$recentLogsStmt->close();

// Execute the second query
$logsStmt->execute();
$logsResult = $logsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Close the statement
$logsStmt->close();

// Calculate the total number of results
$totalResults = $mysqli->query("SELECT COUNT(*) FROM logs WHERE log_date BETWEEN '$startDate' AND '$endDate'")->fetch_assoc()['COUNT(*)'];

// Calculate the total number of pages
$totalPages = ceil($totalResults / $resultsPerPage);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Asset Management System - Logs</title>
    <link rel="icon" type="image/x-icon" href="logo small.png">
    <link rel="stylesheet" href="style.css?v=1.1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <link rel="icon" type="image/x-icon" href="white.png">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            padding: 20px;
        }
        h1, h2 {
            color: #343a40;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        table {
            margin-top: 20px;
        }
        th {
            background-color: #2F4F4F; /* Dark green color */
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .pagination {
            justify-content: center;
        }
        .pagination li a {
            color: #007bff;
        }
        .pagination li a:hover {
            color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container     ">
        <div class="row">
            <div class="col">
              
          
<h2>Search Logs</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div class="row">
	  
        <div class="col-md-6">
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo $startDate; ?>">
        </div>
        <div class="col-md-6">
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo $endDate; ?>">
        </div>
    </div>
	
    <div class="row">
        <div class="col-md-12">
            <label for="action_filter">Action Filter:</label>
            <div class="input-group">
                <select name="action_filter" id="action_filter" class="form-control">
                    <option value="ALL" <?php if ($actionFilter === 'ALL') echo 'selected'; ?>>ALL</option>
                    <option value="Insert Asset" <?php if ($actionFilter === 'Insert Asset') echo 'selected'; ?>>Insert Asset (includes Bulk Insert)</option>
                    <option value="Update Asset" <?php if ($actionFilter === 'Update Asset') echo 'selected'; ?>>Update Asset</option>
                    <option value="Delete Asset" <?php if ($actionFilter === 'Delete Asset') echo 'selected'; ?>>Delete Asset</option>
					 <option value="Transfer Location" <?php if ($actionFilter === 'Transfer Location') echo 'selected'; ?>>Transfer Location</option>
                </select>
                <div class="input-group-append">
                    <button type="submit" name="submit" class="btn btn-primary btn-lg">Search</button>
                </div>
            </div>
        </div>
    </div><br>
	<div class="row">
    <div class="col-md-12">
        <?php if ($_SESSION['account_type'] === "superadmin") { ?>
            <a href="calendarlogs.php" class="btn btn-primary btn-lg">View Calendar Logs</a>
			<button type="submit" name="backup_and_clear_logs" class="btn btn-danger btn-lg">Backup and Clear All Logs</button>
        <?php } ?>
		<?php if ($_SESSION['account_type'] === "admin") { ?>
            <a href="calendarlogs.php" class="btn btn-primary btn-lg">View Calendar Logs</a>
        <?php } ?>
    </div>
</div>
			
</form>
				
                <h2>Search Results</h2>
                <table class="table table-striped">
    <thead>
        <tr>
            <th style="background-color: #2F4F4F; color: white;">Log Date</th>
            <th style="background-color: #2F4F4F; color: white;">Log Time</th>
            <th style="background-color: #2F4F4F; color: white;">Action</th>
            <th style="background-color: #2F4F4F; color: white;">Asset Tag</th>
            <th style="background-color: #2F4F4F; color: white;">Firstname</th>
            <th style="background-color: #2F4F4F; color: white;">Username</th>
            <th style="background-color: #2F4F4F; color: white;">Changes</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($logsResult as $row) { ?>
            <tr>
                <td><?php echo $row['log_date']; ?></td>
                <td><?php echo $row['log_time']; ?></td>
                <td><?php echo $row['action']; ?></td>
                <td><?php echo $row['asset_tag']; ?></td>
                <td><?php echo $row['firstname']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['changes']; ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
                <nav aria-label="Page navigation example">
    <ul class="pagination">
        <?php if ($currentPage > 1) { ?>
            <li class="page-item"><a class="page-link" href="?page=<?php echo $currentPage - 1; ?>">Previous</a></li>
        <?php } ?>
        <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
            <li class="page-item <?php if ($i == $currentPage) echo 'active'; ?>"><a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
        <?php } ?>
        <?php if ($currentPage < $totalPages) { ?>
            <li class="page-item"><a class="page-link" href="?page=<?php echo $currentPage + 1; ?>">Next</a></li>
        <?php } ?>
    </ul>
</nav>
            </div>
        </div>
		     <h2>Recent Logs</h2>
                <table class="table table-striped">
    <thead>
        <tr>
            <th style="background-color: #2F4F4F; color: white;">Log Date</th>
            <th style="background-color: #2F4F4F; color: white;">Log Time</th>
            <th style="background-color: #2F4F4F; color: white;">Action</th>
            <th style="background-color: #2F4F4F; color: white;">Asset Tag</th>
            <th style="background-color: #2F4F4F; color: white;">Firstname</th>
            <th style="background-color: #2F4F4F; color: white;">Username</th>
            <th style="background-color: #2F4F4F; color: white;">Changes</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($recentLogsResult as $row) { ?>
            <tr>
                <td><?php echo $row['log_date']; ?></td>
                <td><?php echo $row['log_time']; ?></td>
                <td><?php echo $row['action']; ?></td>
                <td><?php echo $row['asset_tag']; ?></td>
                <td><?php echo $row['firstname']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['changes']; ?></td>
            </tr>
        <?php } ?>
    </tbody>
	
</table>
    </div>
	
</body>
<?php include 'footer.php'; ?>
</html>