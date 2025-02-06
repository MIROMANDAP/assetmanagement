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


// Set the default date to the current month
$currentMonth = date('Y-m');
$startDate = date('Y-m-01', strtotime($currentMonth));
$endDate = date('Y-m-t', strtotime($currentMonth));

// Check if the month has been set
if (isset($_POST['month'])) {
    $currentMonth = $_POST['month'];
    $startDate = date('Y-m-01', strtotime($currentMonth));
    $endDate = date('Y-m-t', strtotime($currentMonth));
}

// Query to get logs for the month
$logsQuery = "SELECT * FROM logs WHERE log_date BETWEEN '$startDate' AND '$endDate' ORDER BY log_date, log_time";
$logsResult = $mysqli->query($logsQuery)->fetch_all(MYSQLI_ASSOC);

// Group logs by date
$logsByDate = [];
foreach ($logsResult as $log) {
    $logsByDate[$log['log_date']][] = $log;
}

// Create a calendar layout
$firstDayOfMonth = new DateTime($startDate);
$lastDayOfMonth = new DateTime($endDate);
$daysInMonth = $lastDayOfMonth->format('t');

$firstDayOfWeek = (int)$firstDayOfMonth->format('N'); // 1 (for Monday) through 7 (for Sunday)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Asset Management System - Logs</title>
    <link rel="icon" type="image/x-icon" href="logo small.png">
    <link rel="stylesheet" href="style.css?v=1.1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 20px;
            padding: 20px;
 background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }
        .day {
            border: 1px solid #dee2e6;
            padding: 5px;
            min-height: 80px;
            position: relative;
        }
        .day-header {
            font-weight: bold;
            text-align: center;
            background-color: #2F4F4F;
            color: white;
            padding: 5px 0;
        }
        .log-summary {
            background-color: #e9ecef;
            margin: 2px 0;
            padding: 3px;
            border-radius: 5px;
            font-size: 0.9em;
        }
        .btn-view {
            margin-top: 5px;
            font-size: 0.8em;
        }
		.btn-sm {
    padding: 0.25rem 0.5rem; /* Adjust padding for smaller size */
    font-size: 0.8rem; /* Smaller font size */
}
.container-fluid2 {
    background-color: #ffffff; /* White background for the container */
    padding: 20px; /* Padding around the container */
    border-radius: 10px; /* Rounded corners */
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
}

h2 {
    font-size: 2em; /* Larger font size for the title */
    color: #333; /* Darker color for better readability */
}

.form-label {
    font-weight: bold; /* Bold label for better visibility */
}

.btn-primary {
    background-color: #007bff; /* Bootstrap primary color */
    border: none; /* Remove border */
    border-radius: 5px; /* Rounded button corners */
}

.btn-primary:hover {
    background-color: #0056b3; /* Darker shade on hover */
}

.calendar {
    margin-top: 20px; /* Space between the form and calendar */
    /* Additional styles for calendar can go here */
}
    </style>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <?php include 'nav.php'; ?>
  <div class="container-fluid2 d-flex flex-column align-items-center">
    <h2 class="text-center mb-4">Logs Calendar</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="mb-4 w-75"> <!-- Set width to 75% -->
        <div class="row align-items-end">
            <div class="col-md-8">
                <label for="month" class="form-label">Select Month:</label>
                <input type="month" name="month" id="month" class="form-control" value="<?php echo $currentMonth; ?>">
            </div>
            <div class="col-md-4">
             <button type="submit" class="btn btn-primary btn-lg">
    <i class="fas fa-search"></i>
</button>
            </div>
        </div>
    </form>
        <div class="calendar">
            <?php
            // Print the day headers
            $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            foreach ($dayNames as $dayName) {
                echo "<div class='day-header'>$dayName</div>";
            }

            // Print empty cells for days before the first day of the month
            for ($i = 1; $i < $firstDayOfWeek; $i++) {
                echo "<div class='day'></div>";
            }

            // Print each day of the month
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $currentDate = $currentMonth . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                echo "<div class='day'>";
                echo "<div class='day-header'>$day</div>";
                if (isset($logsByDate[$currentDate])) {
                    $logsToShow = array_slice($logsByDate[$currentDate], 0, 3); // Limit to 3 logs
                    foreach ($logsToShow as $log) {
                        echo "<div class='log-summary' data-bs-toggle='modal' data-bs-target='#logModal' 
                              data-log-date='{$log['log_date']}' 
                              data-log-time='{$log['log_time']}' 
                              data-action='{$log['action']}' 
                              data-asset-tag='{$log['asset_tag']}' 
                              data-firstname='{$log['firstname']}' 
                              data-username='{$log['username']}' 
                              data-changes='{$log['changes']}' 
                              style='cursor:pointer; padding: 5px; border: 1px solid #ccc; margin-bottom: 3px; font-size: 12px;'>
                              <strong>{$log['log_time']}</strong>: {$log['action']}<br>
                              <span>Asset Tag: {$log['asset_tag']}</span><br>
                              <span>User: {$log['username']}</span>
                              </div>";
                    }
                    // If there are more than 3 logs, show a "View More" button
                    if (count($logsByDate[$currentDate]) > 3) {
    echo "<button class='btn btn-secondary btn-sm' data-bs-toggle='modal' data-bs-target='#logModal' data-log-date='$currentDate' data-log-time='' data-action='View More' data-asset-tag='' data-firstname='' data-username='' data-changes=''>View More</button>";
}
                }
                echo "</div>";
            }
            ?>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="logModal" tabindex="-1" aria-labelledby="logModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id ="logModalLabel">Log Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Date:</strong> <span id="modal-log-date"></span></p>
                    <p><strong>Time:</strong> <span id="modal-log-time"></span></p>
                    <p><strong>Action:</strong> <span id="modal-action"></span></p>
                    <p><strong>Asset Tag:</strong> <span id="modal-asset-tag"></span></p>
                    <p><strong>Firstname:</strong> <span id="modal-firstname"></span></p>
                    <p><strong>Username:</strong> <span id="modal-username"></span></p>
                    <p><strong>Changes:</strong> <span id="modal-changes"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <script>
    // JavaScript to handle modal data population
    const logModal = document.getElementById('logModal');
    logModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget; // Button that triggered the modal
        const logDate = button.getAttribute('data-log-date');
        const logTime = button.getAttribute('data-log-time');
        const action = button.getAttribute('data-action');
        const assetTag = button.getAttribute('data-asset-tag');
        const firstname = button.getAttribute('data-firstname');
        const username = button.getAttribute('data-username');
        const changes = button.getAttribute('data-changes');

        // Reset the modal's content
        logModal.querySelector('.modal-body').innerHTML = `
            <p><strong>Date:</strong> <span id="modal-log-date">${logDate}</span></p>
            <p><strong>Time:</strong> <span id="modal-log-time">${logTime}</span></p>
            <p><strong>Action:</strong> <span id="modal-action">${action}</span></p>
            <p><strong>Asset Tag:</strong> <span id="modal-asset-tag">${assetTag}</span></p>
            <p><strong>Firstname:</strong> <span id="modal-firstname">${firstname}</span></p>
            <p><strong>Username:</strong> <span id="modal-username">${username}</span></p>
            <p><strong>Changes:</strong> <span id="modal-changes">${changes}</span></p>
        `;

        // If "View More" is clicked, fetch all logs for that date
        if (action === 'View More') {
            const allLogs = <?php echo json_encode($logsByDate); ?>;
            const logsForDate = allLogs[logDate] || [];
            let logDetails = '';

            logsForDate.forEach(log => {
                logDetails += `<strong>${log.log_time}</strong>: ${log.action}<br>`;
                logDetails += `Asset Tag: ${log.asset_tag}<br>`;
                logDetails += `:User  ${log.username}<br>`;
                logDetails += `Changes: ${log.changes}<br><br>`;
            });

            // Update the modal's body with the detailed logs
            logModal.querySelector('.modal-body').innerHTML = logDetails;
        }
    });
    </script>
</body>
<?php include 'footer.php'; ?>
</html>