<?php
date_default_timezone_set('Asia/Shanghai');

// Assuming you have a way to get the user's account type
$account_type = $_SESSION['account_type']; // e.g., 'superadmin', 'admin', 'user'
$username = $_SESSION['firstname'] . ' ' . $_SESSION['lastname']; // Assuming you store the user's name in the session

// Fetch pending requests count for superadmin
$pending_count = 0;
$notifications = []; // Initialize notifications array
if ($account_type === 'superadmin') {
    $sql_pending = "SELECT * FROM issued_to_requests WHERE status = 'pending'";
    if ($result = $mysqli->query($sql_pending)) {
        $pending_count = $result->num_rows; // Count the number of pending requests
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row; // Store each notification
        }
        $result->close();
    } else {
        echo "Error fetching pending requests: " . $mysqli->error;
    }
}

$register = '<a class="dropdown-item" href="register_user.php">Register User</a>';
$insert_asset = '<a class="dropdown-item" href="insert_asset.php">Insert Asset</a>';
$asset = '<a class="dropdown-item" href="assets.php">List Asset</a>';
$bulk_insert = '<a class="dropdown-item" href="upload_csv.php">Bulk Insert (TEST)</a>';
$list_of_user = '<a class="dropdown-item" href="list_of_user.php">List of Users</a>';
$update_asset = '<a class="dropdown-item" href="edit_asset.php">Update Asset</a>';
$logs = '<a class="dropdown-item" href="logs.php">Activity Logs</a>';
$backup = '<a class="dropdown-item" href="backupdatabase.php">Database Backup</a>'; // Backup link
?>

<style>
  .navbar {
    background-color: #343a40; /* Dark background */
  }
  .navbar-nav .nav-link {
    color: #ffffff; /* White text */
    transition: color 0.3s ease;
  }
  .navbar-nav .nav-link:hover {
    color: #ffd700; /* Gold color on hover */
  }
  .dropdown-menu {
    background-color: #495057; /* Darker dropdown background */
  }
  .dropdown-item {
    color: #ffffff; /* White dropdown text */
    transition: background-color 0.3s ease;
  }
  .dropdown-item:hover {
    background-color: #6c757d; /* Light grey on hover */
  }
  .navbar-brand img {
    margin-right: 10px; /* Space between logo and text */
  }
  .badge {
    background-color: #dc3545; /* Bootstrap danger color for the badge */
    color: white;
    border-radius: 10px;
    padding: 0.2em 0.5em;
    font-size: 0.8em;
  }
  .notification-icon {
    position: relative;
    margin-right: 15px; /* Space between icon and welcome message */
  }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="logo small.png" alt="logo.png" width="30" height="30" class="d-inline-block align-text-top">
      APAC-Asset Inventory System
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
      <div class="navbar-nav">
        <a class="nav-item nav-link" href="/">Home</a>
        
        <!-- Dropdown for Assets -->
        <div class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="assetsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Assets
          </a>
          <ul class="dropdown-menu" aria-labelledby="assetsDropdown">
            <?php 
              // Display options based on account type
              if ($account_type === 'superadmin' || $account_type === 'admin') {
                echo $asset; 
                echo $insert_asset; 
                echo $update_asset; 
				echo '<a class="dropdown-item" href="AF.php">Print Accountability Form</a>'; 
              } elseif ($account_type === 'user') {
                echo $asset; 
              }
            ?>
          </ul>
        </div>

        <!-- Dropdown for Users -->
        <?php if ($account_type === 'superadmin'): ?>
        <div class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="usersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Users
          </a>
          <ul class="dropdown-menu" aria-labelledby="usersDropdown">
            <?php 
              echo $list_of_user; 
              echo $register; 
            ?>
          </ul>
        </div>
        <?php endif; ?>

        <!-- Settings Section -->
        <?php if ($account_type === 'superadmin' || $account_type === 'admin'): ?>
        <div class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="settingsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Settings
          </a>
          <ul class="dropdown-menu" aria-labelledby="settingsDropdown">
            <?php 
              if ($account_type === 'superadmin') {
                echo $logs; 
                echo '<a class="dropdown-item" href="list_asset_types.php">List Asset Types</a>'; 
                echo '<a class="dropdown-item" href="list_locations.php">List Locations</a>'; 
                echo '<a class="dropdown-item" href="employee_list.php">List Employee</a>'; 
                echo $backup;
                echo $bulk_insert;
              } elseif ($account_type === 'admin') {
                echo $logs;
                echo $backup;
              }
            ?>
          </ul>
        </div>
        <?php endif; ?>
      </div>

      <!-- Notification Icon -->
   <?php if ($account_type === 'superadmin'): ?>
    <div class="notification-icon" id="notification-icon">
        <a href="view_requests.php" class="nav-link" title="Pending Requests">
            <i class="fas fa-bell"></i>
            <span class="badge" id="pending-count"><?php echo $pending_count > 0 ? $pending_count : '0'; ?></span>
        </a>
        <ul class="dropdown-menu" aria-labelledby="notificationDropdown" id="notification-list">
            <?php if ($pending_count > 0): ?>
                <?php foreach ($notifications as $notification): ?>
                    <li>
                        <a class="dropdown-item" href="view_request.php?id=<?php echo $notification['request_id']; ?>">
            <?php echo htmlspecialchars($notification['reason'] ?? ''); ?> (Asset Tag: <?php echo htmlspecialchars($notification['asset_tag'] ?? ''); ?>)
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li><span class="dropdown-item">No new notifications</span></li>
            <?php endif; ?>
        </ul>
    </div>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Set a JavaScript variable for the user's account type
    var userRole = '<?php echo $account_type; ?>'; // This will be 'superadmin', 'admin', or 'user'

    function fetchNotifications() {
        // Check if the user is a superadmin
        if (userRole !== 'superadmin') {
            
            return; // Exit the function if the user is not a superadmin
        }

        $.ajax({
            url: 'fetch_requests.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.error) {
                    console.error(data.error);
                    return;
                }
                // Update the notification count
                $('#pending-count').text(data.pending_count > 0 ? data.pending_count : '0');
                $('#notification-list').empty();

                if (data.pending_count > 0) {
                    data.requests.forEach(function(request) {
                        $('#notification-list').append(
                            '<li><a class="dropdown-item" href="view_request.php?id=' + request.request_id + '">' +
                            request.reason + ' (Asset Tag: ' + request.asset_tag + ')</a></li>'
                        );
                    });
                } else {
                    $('#notification-list').append('<li><span class="dropdown-item">No new notifications</span></li>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching notifications:', error);
            }
        });
    }

    // Fetch notifications every 5 seconds
    setInterval(fetchNotifications, 5000);
    // Initial fetch
    fetchNotifications();
</script>
      <!-- User Dropdown -->
      <div class="navbar-nav ms-auto dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          Welcome, <?php echo htmlspecialchars($username); ?>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
          <li><a class="dropdown-item" href="logout.php" onClick="return confirm('Do you want to Logout?')">Logout</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>