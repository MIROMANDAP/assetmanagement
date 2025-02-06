<?php
error_reporting(E_ALL & ~E_WARNING);
// Include the database connection file
include 'config.php';
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}
// At the beginning of your script, after starting the session
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;


try {
    // CSV parsing code here
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}

// Generate a random letter
function generateRandomLetter() {
  $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = $characters[random_int(0, $charactersLength - 1)];
  return $randomString;
}

// Check if the upload button is clicked
if (isset($_POST['upload_csv'])) {
    if (!empty($_FILES) && array_key_exists('csv_file', $_FILES)) {
        $csv_file = $_FILES['csv_file'];
        if ($csv_file['error'] == 0) {
            $file_tmp = $csv_file['tmp_name'];
            $file_name = $csv_file['name'];

            // Check if the file is a CSV file
            if (pathinfo($file_name, PATHINFO_EXTENSION) == 'csv') {
                // Open the CSV file
                $file = fopen($file_tmp, 'r');

                // Initialize an empty array to store the logs
                $log_data = array();

                // Read the CSV file row by row
                while (($row = fgetcsv($file)) !== FALSE) {
                    // Remove any empty values from the row
                    $row = array_filter($row);

                    // Check if the row is not empty and has the expected number of columns
                    if (!empty($row) && count($row) >= 12) { // Ensure at least 12 columns
                        // Extract the data from the row
$asset_type = $row[0];
$iboss_tag = $row[1];
$brand = $row[2];
$model = $row[3];
$equipment_name = $row[4];
$serial_number = $row[5];
$date_acquired = $row[6];
$price_value = $row[7];
$issued_to = $row[8];
$location_asset = $row[9];
$status = $row[10];
$remarks = $row[11];

                        // Generate a randomized asset tag
                        $asset_tag = "APAC-" . substr($date_acquired, 0, 4) . "-" . substr($asset_type, 0, 3) . substr($iboss_tag, -6) . generateRandomLetter();

                        // Check if the asset tag already exists in the database
                        $sql = "SELECT * FROM assets WHERE asset_tag = ?";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param("s", $asset_tag);
                        $stmt->execute();
                        $stmt->store_result();
                        if ($stmt->num_rows > 0) {
                            // If the asset tag already exists, generate a new one
                            $asset_tag = "APAC-" . substr($date_acquired, 0, 4) . "-" . substr(uniqid(), 0, 4) . generateRandomLetter();
                            // Repeat the check until a unique asset tag is generated
                            while ($stmt->num_rows > 0) {
                                $asset_tag = "APAC-" . substr($date_acquired, 0, 4) . "-" . substr(uniqid(), 0, 4) . generateRandomLetter();
                                $stmt = $mysqli->prepare($sql);
                                $stmt->bind_param("s", $asset_tag);
                                $stmt->execute();
                                $stmt->store_result();
                            }
                        }
                        $stmt->close();
$user_id = 1;
                        // Insert the data into the database
                       $sql = "INSERT INTO assets (
    asset_tag, asset_type, iboss_tag, brand, model, equipment_name, 
    serial_number, date_acquired, price_value, issued_to, 
    location_asset, status, remarks, user_id
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("ssssssssdssssi", 
        $asset_tag, $asset_type, $iboss_tag, $brand, $model, $equipment_name, 
        $serial_number, $date_acquired, $price_value, $issued_to, 
        $location_asset, $status, $remarks, $user_id
    );

    $stmt->execute();
    $stmt->close();

                            // Prepare to log the insertion
                            $log_date = date("Y-m-d");
                            $log_time = date("H:i:s");
                            $changes = "Inserted asset: ";
                            $changes .= "IBOSS Tag: " . htmlspecialchars($iboss_tag) . ", ";
                            $changes .= "Asset Tag: " . htmlspecialchars($asset_tag) . ", ";
                            $changes .= "Asset Type: " . htmlspecialchars($asset_type) . ", ";
                            $changes .= "Brand: " . htmlspecialchars($brand) . ", ";
                            $changes .= "Model: " . htmlspecialchars($model) . ", ";
                            $changes .= "Serial Number: " . htmlspecialchars($serial_number) . ", ";
                            $changes .= "Status: " . htmlspecialchars($status) . ", ";
                            $changes .= "Equipment Name: " . htmlspecialchars($equipment_name) . ", ";
                            $changes .= "Location: " . htmlspecialchars($location_asset) . ", ";
                            $changes .= "Price Value: " . htmlspecialchars($price_value) . ", ";
                            $changes .= "Issued To: " . htmlspecialchars($issued_to) . ", ";
                            $changes .= "Date Acquired: " . htmlspecialchars($date_acquired) . ", ";
                            $changes .= "Remarks: " . htmlspecialchars($remarks);

                            // Add the log data to the array
                            $log_data[] = array(
                                'action' => 'Bulk Insert Asset',
                                'asset_tag' => $asset_tag,
                                'firstname' => $_SESSION['firstname'],
                                'username' => $_SESSION['username'],
                                'changes' => $changes,
                                'log_date' => $log_date,
                                'log_time' => $log_time
                            );
                        }
                    }
                }
                fclose($file);

                // Insert the logs into the database
                $log_sql = "INSERT INTO logs (action, asset_tag, firstname, username, changes, log_date, log_time) VALUES (?, ?, ?, ?, ?, ?, ?)";
                if ($log_stmt = $mysqli->prepare($log_sql)) {
                    foreach ($log_data as $log) {
                        $log_stmt->bind_param("sssssss", $log['action'], $log['asset_tag'], $log['firstname'], $log['username'], $log['changes'], $log['log_date'], $log['log_time']);
                        $log_stmt->execute();
                    }
                    $log_stmt->close();
                }

                echo '<script>alert("CSV file uploaded successfully!"); </script>';
            } else {
                echo '<script>alert("Invalid file type. Please upload a CSV file.");</script>';
            }
        } else {
            echo '<script>alert("Error uploading file.");</script>';
        }
    } else {
        echo '<script>alert("Please select a file to upload.");</script>';
    }
} else {
    // Check if a file is selected
    if (isset($_FILES['csv_file'])) {
        $csv_file = $_FILES['csv_file'];
        if ($csv_file['error'] == 0) {
            $file_tmp = $csv_file['tmp_name'];
            $file_name = $csv_file['name'];

            // Check if the file is a CSV file
            if (pathinfo($file_name, PATHINFO_EXTENSION) == 'csv') {
                // Open the CSV file
                $file = fopen($file_tmp, 'r');

                // Read the CSV file row by row
                $rows = array();
                while (($row = fgetcsv($file)) !== FALSE) {
                    // Remove any empty values from the row
                    $row = array_filter($row);
                    $rows[] = $row;
                }
                fclose($file);

                // Display the preview
                if (!empty($rows)) {
                    echo '<h2>Preview:</h2>';
                    echo '<table border="1">';
                    foreach ($rows as $row) {
                        echo '<tr>';
                        foreach ($row as $cell) {
                            echo '<td>' . $cell . '</td>';
                        }
                        echo '</tr>';
                    }
                    echo '</table>';
                    echo '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post" enctype="multipart/form-data">';
                    echo '<input type="hidden" name="csv_file" value="' . $file_name . '">';
                    echo '<button type="submit" name="upload_csv">Upload CSV File</button>';
                    echo '</form>';
                } else {
                    echo '<p>No data found in the CSV file.</p>';
                }
            } else {
                echo '<script>alert("Invalid file type. Please upload a CSV file.");</script>';
            }
        } else {
            echo '<script>alert("Error uploading file.");</script>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload CSV File</title>
    <link rel="stylesheet" href="style.css?v=1.1 ">
</head>
<body>
    <div class="container">
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
            <td>2024-10-14 08:33:39</td> <!-- Example update timestamp -->
            <td>No Documents</td> <!-- Example document status -->
        </tr>
        <tr>
            <td>Monitor</td>
            <td>PT000221914</td>
            <td>Dell</td>
            <td>U2715HC</td>
            <td>CL-WS047</td>
            <td>CN-OVGTFG-64180-56N-O5HS</td>
            <td>2024-10-16</td>
            <td>7100.00</td> <!-- Corrected price format -->
            <td>Admin</td>
            <td>FD</td>
            <td>Deployed</td>
            <td>N/a</td>
            <td>2024-10-14 08:33:39</td> <!-- Example update timestamp -->
            <td>No Documents</td> <!-- Example document status -->
        </tr>
    </table>
       <form method="post" enctype="multipart/form-data">
    <input type="file" name="csv_file" accept=".csv">
    <button type="submit" name="upload_csv">Upload CSV</button>
</form>


    </div>
</body>
</html>