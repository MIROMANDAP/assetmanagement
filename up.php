<?php
error_reporting(E_ALL & ~E_WARNING);
include 'config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Increase memory limit and execution time
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);

// Generate a random letter
function generateRandomLetter() {
    return chr(random_int(65, 90)); // A-Z
}

// Validate CSV file
function validateCSV($file) {
    $file_type = pathinfo($file['name'], PATHINFO_EXTENSION);
    return $file_type === 'csv' && $file['error'] === 0;
}

// Insert asset into the database
function insertAsset($mysqli, $data, $user_id) {
    if (is_null($user_id)) {
        return false; // or handle the error as needed
    }
    
    $asset_tag = generateAssetTag($data);
    $sql = "INSERT INTO assets (asset_tag, asset_type, iboss_tag, brand, model, equipment_name, serial_number, date_acquired, price_value, issued_to, location_asset, status, remarks, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $mysqli->prepare($sql)) {
        // Prepare the data array with the asset_tag at the beginning
        $dataWithTag = array_merge([$asset_tag], $data, [$user_id]);
        
        // Check the count of the type definition string
        $types = "ssssssssdssssi"; // This should match the number of variables
        if (count($dataWithTag) !== 14) {
            throw new Exception("Mismatch between number of placeholders and bound variables.");
        }

        $stmt->bind_param($types, ...$dataWithTag);
        $stmt->execute();
        $stmt->close();
        return $asset_tag;
    }
    return false;
}

// Generate a unique asset tag
function generateAssetTag($data) {
    global $mysqli;
    $date_acquired = $data[6];
    $asset_type = $data[0];
    $iboss_tag = $data[1];

    do {
        $asset_tag = "APAC-" . substr($date_acquired, 0, 4) . "-" . substr($asset_type, 0, 3) . substr($iboss_tag, -6) . generateRandomLetter();
        $sql = "SELECT * FROM assets WHERE asset_tag = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $asset_tag);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0);
    
    $stmt->close();
    return $asset_tag;
}

// Log the insertion
function logInsertion($mysqli, $log_data) {
    $log_sql = "INSERT INTO logs (action, asset_tag, firstname, username, changes, log_date, log_time) VALUES (?, ?, ?, ?, ?, ?, ?)";
    if ($log_stmt = $mysqli->prepare($log_sql)) {
        foreach ($log_data as $log) {
            $log_stmt->bind_param("sssssss", ...array_values($log));
            $log_stmt->execute();
        }
        $log_stmt->close();
    }
}

// Main upload logic
if (isset($_POST['upload_csv']) && !empty($_FILES['csv_file'])) {
    $csv_file = $_FILES['csv_file'];

    if (validateCSV($csv_file)) {
        $file_tmp = $csv_file['tmp_name'];
        $file = fopen($file_tmp, 'r');
        $log_data = [];
        $user_id = $_SESSION['user_id'];

        while (($row = fgetcsv($file)) !== FALSE) {
            $row = array_filter($row);
            if (count($row) >= 12) {
                $asset_tag = insertAsset($mysqli, $row, $user_id);
                if ($asset_tag) {
                    $log_data[] = [
                        'action' => 'Bulk Insert Asset',
                        'asset_tag' => $asset_tag,
                        'firstname' => $_SESSION['firstname'],
                        'username' => $_SESSION['username'],
                        'changes' => implode(", ", $row),
                        'log_date' => date("Y-m-d"),
                        'log_time' => date("H:i:s")
                    ];
                }
            }
        }
        fclose($file);
        logInsertion($mysqli, $log_data);
        echo '<script>alert("CSV file uploaded successfully!");</script>';
    } else {
        echo '<script>alert("Invalid file type or error uploading file.");</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload CSV File</title>
    <link rel="stylesheet" href="style.css?v=1.1 ">
    <style>
        body { background-color: #f8f9fa; }
        .report-header { text-align: center; margin-bottom: 20px; }
        .table th, .table td { text-align: center; }
        .report-container { background-color: white; border-radius: 10px; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .chart-container { margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <button type="button" class="btn btn-danger" onclick="window.location.href='dashboard.php';">
            <i class="fas fa-file-alt"></i> Close
        </button>
        <h1>Upload CSV File</h1>
        <p>Please ensure that your CSV file is in the following format:</p>
        <p><strong>Note:</strong> Please edit the format of the <em>Date Acquired</em> column to be <strong>YYYY-MM-DD</strong> (e.g., 2024-10-16) and the <em>Price Value</em> column to be <strong>1000.00</strong>.</p>
        <table border="1">
            <tr>
                <th>Asset Type</th>
                <th>IBOSS Tag</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Equipment Name</th>
                <th>Serial Number</th>
                <th>Date Acquired</th>
                <th>Price Value</th>
                <th>Issued To</th>
                <th>Location</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Updated</th>
                <th>Documents</th>
            </tr>
            <tr>
                <td>Monitor</td>
                <td>PT000221890</td>
                <td>ASUS</td>
                <td>VC239</td>
                <td>CL-WS047</td>
                <td>G2LMTJ003865</td>
                <td>2024-10-16</td>
                <td>6300.00</td>
                <td>Admin</td>
                <td>FD</td>
                <td>Deployed</td>
                <td>N/a</td>
                <td>2024-10-14 08:33:39</td>
                <td>No Documents</td>
            </tr>
            <tr>
                <td>Monitor</td>
                <td>PT000221914</td>
                <td>Dell</td>
                <td>U2715HC</td>
                <td>CL-WS047</td>
                <td>CN-OVGTFG-64180-56N-O5HS</td>
                <td>2024-10-16</td>
                <td>7100.00</td>
                <td>Admin</td>
                <td>FD</td>
                <td>Deployed</td>
                <td>N/a</td>
                <td>2024-10-14 08:33:39</td>
                <td>No Documents</td>
            </tr>
        </table>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="csv_file" accept=".csv">
            <button type="submit" name="upload_csv">Upload CSV</button>
        </form>
    </div>
</body>
</html>