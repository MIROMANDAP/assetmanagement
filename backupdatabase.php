<?php
include 'config.php';

session_start();
if ($_SESSION['account_type'] !== "superadmin" && $_SESSION['account_type'] !== "admin") {
    header('Location: 404.php');
    exit();
}

// Initialize message variable
$message = "";

// Check if the backup button was clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['backup'])) {
    // Backup file name
    $backupFile = 'Database_bak/asset-inv-' . date('Y-m-d-H-i-s') . '.sql';

    // Create uploads directory if it doesn't exist
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // Create a new MySQLi connection
    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);

    // Check connection
    if ($mysqli->connect_error) {
        die("ERROR: Could not connect. " . $mysqli->connect_error);
    }

    // Get all the tables in the database
    $tables = [];
    $result = $mysqli->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }

    // Start the backup process
    $backupContent = "-- Database Backup\n";
    $backupContent .= "-- Backup Date: " . date('Y-m-d H:i:s') . "\n\n";

    foreach ($tables as $table) {
        // Get the CREATE TABLE statement
        $createTableResult = $mysqli->query("SHOW CREATE TABLE `$table`");
        $createTableRow = $createTableResult->fetch_array();
        $backupContent .= $createTableRow[1] . ";\n\n";

        // Get the data from the table
        $dataResult = $mysqli->query("SELECT * FROM `$table`");
        while ($dataRow = $dataResult->fetch_assoc()) {
            $backupContent .= "INSERT INTO `$table` (" . implode(", ", array_keys($dataRow)) . ") VALUES ('" . implode("', '", array_map([$mysqli, 'real_escape_string'], array_values($dataRow))) . "');\n";
        }
        $backupContent .= "\n\n";
    }

    // Save the backup to a file
    if (file_put_contents($backupFile, $backupContent)) {
		//<a href='$backupFile'>Download Backup</a>
        $message = "Database backup successfully created";
    } else {
        $message = "Error creating backup file.";
    }


}
?>
<?php include 'nav.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup</title>
    <link rel="icon" type="image/x-icon" href="logo small.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 50px;
            max-width: 600px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        h1 {
            color: #343a40;
            margin-bottom: 30px;
            text-align: center;
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            text-align: center;
        }
        button {
            width: 100%;
            margin-top: 20px;
        }
    </style>
</head>
<body>

 <div class="container">
    <h1>Database Backup</h1>
    <p class="text-center">Create a backup of your database with a single click. This will help you restore your data in case of any issues.</p>
    <form method="post">
        <button type="submit" name="backup" class="btn btn-success">Create Backup</button>
    </form>

    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>