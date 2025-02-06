<?php
include 'config.php';
session_start();

$asset_types = [];
$sql = "SELECT type_name FROM asset_types";
if ($result = $mysqli->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $asset_types[] = $row['type_name'];
    }
    $result->free();
}

// Fetch locations from the database
$locations = [];
$sql = "SELECT location_name FROM locations";
if ($result = $mysqli->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $locations[] = $row['location_name'];
    }
    $result->free();
}

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

date_default_timezone_set('Asia/Shanghai');
if ($_SESSION['account_type'] !== "admin" && $_SESSION['account_type'] !== "superadmin") {
    header('Location: 404.php');
}

// Show results from database using the URL parameter
$query = "SELECT * FROM assets WHERE asset_tag = ?";
if ($stmt = $mysqli->prepare($query)) {
    $stmt->bind_param("s", $_GET['asset_tag']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        $row = null;
    }
    $stmt->close();
}

// Fetch the data from the database before updating it
$query = "SELECT * FROM assets WHERE asset_tag = ?";
if ($stmt = $mysqli->prepare($query)) {
    $stmt->bind_param("s", $_POST['asset_tag']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $previous_row = $result->fetch_assoc();
    } else {
        $previous_row = null;
    }
    $stmt->close();
}

// Fetch user's first name and username from session
$firstname = $_SESSION['firstname'];
$username = $_SESSION['username'];

// Initialize error messages
$filename_array = array();
$document_err = "";

// Parse update data to MySQL
if (isset($_POST['submit'])) {
    // Initialize the filename array
    $filename_array = array();
    $totalFileUploaded = 0;

    // File upload logic
    $countfiles = count($_FILES['documents']['name']);
    for ($i = 0; $i < $countfiles; $i++) {
        $filename = time() . "_" . $_FILES['documents']['name'][$i];
        $location = "uploads/" . $filename;
        $extension = strtolower(pathinfo($location, PATHINFO_EXTENSION));

        $valid_extensions = array("jpg", "jpeg", "png", "pdf", "docx");

        if (in_array($extension, $valid_extensions)) {
            if (move_uploaded_file($_FILES['documents']['tmp_name'][$i], $location)) {
                array_push($filename_array, $filename);
                $totalFileUploaded++;
            } else {
                $document_err = "Error uploading file: " . $_FILES['documents']['name'][$i];
            }
        } else {
            $document_err = "Invalid file type: " . $_FILES['documents']['name'][$i];
        }
    }

    // Prepare update SQL
    $sql = "UPDATE assets SET iboss_tag=?, brand=?, model=?, serial_number=?, asset_type=?, status=?, equipment_name=?, location_asset=?, price_value=?, issued_to=?, date_acquired=?, remarks=?, user_id=?, updated_at=? WHERE asset_tag = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        // Prepare variables for binding
        $iboss_tag = $_POST['iboss_tag'];
        $brand = $_POST['brand'];
        $model = $_POST['model'];
        $serial_number = $_POST['serial_number'];
        $asset_tag = $_POST['asset_tag'];
        $asset_type = $_POST['asset_type'];
        $status = $_POST['status'];
        $equipment_name = $_POST['equipment_name'];
        $location_asset = $_POST['location_asset'];
        $price_value = $_POST['price_value'];
        $issued_to = $_POST['issued_to'];
        $date_acquired = $_POST['date_acquired'];
        $remarks = $_POST['remarks'];
        $user_id = $_SESSION['id'];
        $updated_at = date("Y-m-d H:i:s");

        // Check if new documents were uploaded
        if (!empty($filename_array)) {
            // Prepare the documents string
            $existing_documents = explode(',', $previous_row['documents']);
            $documents = array_merge($existing_documents, $filename_array); // Merge existing and new documents
            $documents_string = implode(",", $documents);
        } else {
            // If no new documents were uploaded, keep the existing documents
            $documents_string = $previous_row['documents'];
        }

        // Bind parameters
        $stmt->bind_param("sssssssssssssss", $iboss_tag, $brand, $model, $serial_number, $asset_type, $status, $equipment_name, $location_asset, $price_value, $issued_to, $date_acquired, $remarks, $user_id, $updated_at, $asset_tag);

        if ($stmt->execute()) {
            // Update the documents only if new documents were uploaded
            if (!empty($filename_array)) {
                $update_doc_sql = "UPDATE assets SET documents = ? WHERE asset_tag = ?";
                if ($update_doc_stmt = $mysqli->prepare($update_doc_sql)) {
                    $update_doc_stmt->bind_param("ss", $documents_string, $asset_tag);
                    $update_doc_stmt->execute();
                    $update_doc_stmt->close();
                }
            }

            // Log changes
            $changes = [];
            if ($iboss_tag !== $previous_row['iboss_tag']) {
                $changes[] = "IBOSS Tag: " . htmlspecialchars($previous_row['iboss_tag']) . " to " . htmlspecialchars($iboss_tag);
            }
            if ($brand !== $previous_row['brand']) {
                $changes[] = "Brand: " . htmlspecialchars($previous_row['brand']) . " to " . htmlspecialchars($brand);
            }
            if ($model !== $previous_row['model']) {
                $changes[] = "Model: " . htmlspecialchars($previous_row['model']) . " to " . htmlspecialchars($model);
            }
            if ($serial_number !== $previous_row['serial_number']) {
                $changes[] = "Serial Number: " . htmlspecialchars($previous_row['serial_number']) . " to " . htmlspecialchars($serial_number);
            }
            if ($asset_type !== $previous_row['asset_type']) {
                $changes[] = "Asset Type: " . htmlspecialchars($previous_row['asset_type']) . " to " . htmlspecialchars($asset_type);
            }
            if ($status !== $previous_row['status']) {
                $changes[] = "Status: " . htmlspecialchars($previous_row['status']) . " to " . htmlspecialchars($status);
            }
            if ($equipment_name !== $previous_row['equipment_name']) {
                $changes[] = "Equipment Name: " . htmlspecialchars($previous_row['equipment_name']) . " to " . htmlspecialchars($equipment_name);
            }
            if ($location_asset !== $previous_row['location_asset']) {
                $changes[] = "Location: " . htmlspecialchars($previous_row['location_asset']) . " to " . htmlspecialchars($location_asset);
            }
            if ($price_value !== $previous_row['price_value']) {
                $changes[] = "Price Value: " . htmlspecialchars($previous_row['price_value']) . " to " . htmlspecialchars($price_value);
            }
            if ($issued_to !== $previous_row['issued_to']) {
                $changes[] = "Issued To: " . htmlspecialchars($previous_row['issued_to']) . " to " . htmlspecialchars($issued_to);
            }
            if ($date_acquired !== $previous_row['date_acquired']) {
                $changes[] = "Date Acquired: " . htmlspecialchars($previous_row['date_acquired']) . " to " . htmlspecialchars($date_acquired);
            }
            if ($remarks !== $previous_row['remarks']) {
                $changes[] = "Remarks: " . htmlspecialchars($previous_row['remarks']) . " to " . htmlspecialchars($remarks);
            }

            // Prepare the changes string
            $changes_string = implode(", ", $changes);

            // Insert log entry
            $log_date = date("Y-m-d");
            $log_time = date("H:i:s");
            $log_sql = "INSERT INTO logs (action, asset_tag, firstname, username, changes, log_date, log_time) VALUES (?, ?, ?, ?, ?, ?, ?)";
            if ($log_stmt = $mysqli->prepare($log_sql)) {
                $action = 'Update Asset';
                $log_stmt->bind_param("sssssss", $action, $asset_tag, $firstname, $username, $changes_string, $log_date, $log_time);
                $log_stmt->execute();
                $log_stmt->close();
            }

            echo "<script>alert('The Asset has been successfully updated.'); window.location.href='edit_asset.php';</script>";
        } else {
            echo "<script>alert('Something went wrong. Please try again!')</script>";
        }
        $stmt->close();
    }
}

?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const issuedToField = document.getElementById("issued_to");
        const originalIssuedToValue = issuedToField.value; // Store the original value

        function handleStatusChange() {
            const statusField = document.getElementById("status");
            const selectedStatus = statusField.value;

            // Logic for Issued To field based on selected status
            if (selectedStatus === "Deployed") {
                issuedToField.readOnly = true; // Make the field read-only
                issuedToField.style.backgroundColor = "#f0f0f0"; // Optional: Change background to indicate read-only
                issuedToField.value = originalIssuedToValue; // Retain the original value
            } else {
                issuedToField.readOnly = true; // Make the field editable
                issuedToField.value = "N/A"; // Set value to "N/A" for other statuses
                issuedToField.style.backgroundColor = ""; // Reset background color
            }
        }

        // Add event listener to the status field to handle changes
        document.getElementById("status").addEventListener("change", handleStatusChange);

        // Initial call to set the correct visibility and state on page load
        handleStatusChange();
    });
</script>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Asset</title>
    <link rel="icon" type="image/x-icon" href="logo small.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

     .container {
            margin-top: 50px;
            max-width: 800px;
            background-color: #ffffff; /* White background for the container */
            border-radius: 8px; /* Rounded corners */
            padding: 20px; /* Padding inside the container */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        }

        h1 {
            text-align: center;
            margin-bottom: 50px;
        }

        .form-control {
            margin-bottom: 15px;
        }

        .btn-primary {
            margin-right: 10px;
        }

        .btn-secondary {
            margin-left: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col">
                <h1>Update Asset</h1>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" autocomplete="off" enctype='multipart/form-data'>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="asset_type">Asset Type</label>
                            <input type="text" name="asset_type" id="asset_type" class="form-control <?php echo (!empty($assettype_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($row['asset_type']); ?>" readonly>
                            <span class="invalid-feedback"><?php echo $assettype_err; ?></span>
                        </div>
                        <div class="col-md-6">
                            <label for="iboss_tag">IBOSS Tag</label>
                            <input type="text" name="iboss_tag" id="iboss_tag" class="form-control" value="<?php echo htmlspecialchars($row['iboss_tag']); ?>" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label for="brand">Brand</label>
                            <input type="text" name="brand" id="brand" class="form-control" value="<?php echo htmlspecialchars($row['brand']); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="model">Model</label>
                            <input type="text" name="model" id="model" class="form-control" value="<?php echo htmlspecialchars($row['model']); ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label for="serial_number">Serial Number</label>
                            <input type="text" name="serial_number" id="serial_number" class="form-control" value="<?php echo htmlspecialchars($row['serial_number']); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control <?php echo (!empty($status_err)) ? 'is-invalid' : ''; ?>">
 <?php if ($row['status'] === 'Available'): ?>
     <option value="Available" <?php if ($row['status'] === 'Available') echo 'selected'; ?>>Available</option>
        <option value="Defective" <?php if ($row['status'] === 'Defective') echo 'selected'; ?>>Defective</option>
        <option value="Decommission" <?php if ($row['status'] === 'Decommission') echo 'selected'; ?>>Decommission</option>
    <?php elseif ($row['status'] === 'Deployed'): ?>
        <option value="Deployed" <?php if ($row['status'] === 'Deployed') echo 'selected'; ?>>Deployed</option>
        <option value="Available" <?php if ($row['status'] === 'Available') echo 'selected'; ?>>Available</option>
        <option value="Defective" <?php if ($row['status'] === 'Defective') echo 'selected'; ?>>Defective</option>
        <option value="Decommission" <?php if ($row['status'] === 'Decommission') echo 'selected'; ?>>Decommission</option>
		<?php elseif ($row['status'] === 'Defective'): ?>
		<option value="Available" <?php if ($row['status'] === 'Available') echo 'selected'; ?>>Available</option>
        <option value="Defective" <?php if ($row['status'] === 'Defective') echo 'selected'; ?>>Defective</option>
        <option value="Decommission" <?php if ($row['status'] === 'Decommission') echo 'selected'; ?>>Decommission</option>
		<?php else :?>
        <option value="Decommission" <?php if ($row['status'] === 'Decommission') echo 'selected'; ?>>Decommission</option>
    <?php endif; ?>
                            </select>
                            <span class="invalid-feedback"><?php echo $status_err; ?></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label for="equipment_name">Equipment Name</label>
                            <input type="text" name="equipment_name" id="equipment_name" class="form-control" value="<?php echo htmlspecialchars($row['equipment_name']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="location_asset">Location</label>
                            <input type="text" name="location_asset" id="location_asset" class="form-control <?php echo (!empty($location_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($row['location_asset']); ?>" readonly>
                            <span class="invalid-feedback"><?php echo $location_err; ?></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label for="price_value">Price Value</label>
                            <input type="text" name="price_value" id="price_value" class="form-control" value="<?php echo htmlspecialchars($row['price_value']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="issued_to">Issued To</label>
                            <input type="text" name="issued_to" id="issued_to" class="form-control" value="<?php echo htmlspecialchars($row['issued_to']); ?>" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label for="date_acquired">Date Acquired</label>
                            <input type="date" name="date_acquired" id="date_acquired" class="form-control" value="<?php echo htmlspecialchars($row['date_acquired']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="documents">Documents</label>
                            <input type="file" name="documents[]" id="documents" class="form-control" multiple>
                            <span class="invalid-feedback"><?php echo $document_err; ?></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label for="existing_documents">Existing Documents:</label>
                            <div id="existing_documents" style="margin-top: 5px;">
                                <?php
                                if (!empty($row['documents'])) {
                                    $documents = explode(',', $row['documents']);
                                    foreach ($documents as $document) {
                                        echo '<div>';
                                        echo '<a href="uploads/' . htmlspecialchars($document) . '" target="_blank">' . htmlspecialchars($document) . '</a>';
                                        echo ' <a href="delete_document.php?asset_tag=' . urlencode($row['asset_tag']) . '&document=' . urlencode($document) . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this document?\')">Delete</a>';
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<p>No documents uploaded.</p>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label for="remarks">Remarks</label>
                            <textarea name="remarks" id="remarks" class="form-control"><?php echo htmlspecialchars($row['remarks']); ?></textarea>
                        </div>
                    </div>

                    <input type="hidden" name="asset_tag" value="<?php echo htmlspecialchars($row['asset_tag']); ?>">
                    <div class="row">
                        <div class="col-12 text-center">
                            <input type="submit" name="submit" value="Update" class="btn btn-primary">
                            <button type="button" class="btn btn-secondary" onclick="confirmClose()">Close</button>
                        </div>
                    </div>
                </form>
                <script>
                    function confirmClose() {
                        if (confirm("Are you sure you want to exit? Any unsaved changes will be lost.")) {
                            window.location.href = 'edit_asset.php'; // Redirect to the desired page
                        }
                    }
                </script>
            </div>
        </div>
    </div>
</body>
</html>