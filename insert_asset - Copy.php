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

if ($_SESSION['account_type'] !== "admin" && $_SESSION['account_type'] !== "superadmin") {
    header('Location: 404.php');
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

date_default_timezone_set('Asia/Shanghai');

// Asset input variables
$iboss_tag = $brand = $model = $serial_number = $status = $equipment_name = $location_asset = $price_value = $issued_to = $date_acquired = $remarks = $asset_type = "";
$filename_array = array();
$iboss_tag_err = $brand_err = $model_err = $serial_number_err = $status_err = $equipment_name_err = $location_err = $price_value_err = $issued_to_err = $date_acquired_err = $assettype_err = $remarks_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validating and sanitizing inputs
    if (empty(trim($_POST["iboss_tag"]))) {
        $iboss_tag_err = "Please enter IBOSS Tag.";
    } else {
        $iboss_tag = test_input($_POST["iboss_tag"]);

        // Check if IBOSS Tag already exists, but disregard if it is "N/A"
        if ($iboss_tag !== "N/A") {
            $sql = "SELECT COUNT(*) FROM assets WHERE iboss_tag = ?";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("s", $iboss_tag);
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                $stmt->close();

                if ($count > 0) {
                    $iboss_tag_err = "This IBOSS Tag already exists in the database.";
                }
            }
        }
    }

    if (empty(trim($_POST["asset_type"]))) {
        $assettype_err = "Please enter asset type.";
    } else {
        $asset_type = test_input($_POST["asset_type"]);
    }

    if (empty(trim($_POST["brand"]))) {
        $brand_err = "Please enter brand.";
    } else {
        $brand = test_input($_POST["brand"]);
    }

    if (empty(trim($_POST["status"]))) {
        $status_err = "Please select a status.";
    } else {
        $status = test_input($_POST["status"]);
    }

    if (empty(trim($_POST["model"]))) {
        $model_err = "Please enter model.";
    } else {
        $model = test_input($_POST["model"]);
    }

    if (empty(trim($_POST["serial_number"]))) {
        $serial_number_err = "Please enter serial number.";
    } else {
        $serial_number = test_input($_POST["serial_number"]);
    }

    if (empty(trim($_POST["equipment_name"]))) {
        $equipment_name_err = "Please enter equipment name.";
    } else {
        $equipment_name = test_input($_POST["equipment_name"]);
    }

    if (empty(trim($_POST["location_asset"]))) {
        $location_err = "Please enter location.";
    } else {
        $location_asset = test_input($_POST["location_asset"]);
    }

    if (empty(trim($_POST["price_value"]))) {
        $price_value_err = "Please enter price value.";
    } else {
        $price_value = test_input($_POST["price_value"]);
    }

    if (empty(trim($_POST["issued_to"]))) {
        $issued_to_err = "Please enter the name of the person the asset is issued to.";
    } else {
        $issued_to = test_input($_POST["issued_to"]);
    }

    if (empty(trim($_POST["date_acquired"]))) {
        $date_acquired_err = "Please enter date acquired.";
    } else {
        $date_acquired = test_input($_POST["date_acquired"]);
    }

    $remarks = test_input($_POST["remarks"]);

    // Generate a random asset tag
    function generateRandomLetter($length = 1) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

   $asset_number = "APAC-" . substr($date_acquired, 0, 4) . "-" . substr($asset_type, 0, 3) . substr($iboss_tag, -6) . generateRandomLetter(1);

    // File upload logic
    $countfiles = count($_FILES['documents']['name']);
    $totalFileUploaded = 0;
    for ($i = 0; $i < $countfiles; $i++) {
        $filename = time() . "_" . $_FILES['documents']['name'][$i];
        $location = "uploads/" . $filename;
        $extension = strtolower(pathinfo($location, PATHINFO_EXTENSION));

        $valid_extensions = array("jpg", "jpeg", "png", "pdf", "docx");

        if (in_array($extension, $valid_extensions)) {
            if (move_uploaded_file($_FILES['documents']['tmp_name'][$i], $location)) {
                array_push($filename_array, $filename);
                $totalFileUploaded++;
            }
        }
    }

    // Submit everything to the database
    if (empty($iboss_tag_err) && empty($brand_err) && empty($model_err) && empty($serial_number_err) && empty($status_err) && empty($equipment_name_err) && empty($location_err) && empty($price_value_err) && empty($issued_to_err) && empty($date_acquired_err) && empty($assettype_err)) {
        // Prepare the insert statement
$sql = "INSERT INTO assets (
    brand, model, serial_number, status, equipment_name, location_asset, 
    price_value, issued_to, date_acquired, remarks, asset_tag, asset_type, 
    iboss_tag, documents, user_id, updated_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $mysqli->prepare($sql)) {
            // Bind variables to the prepared statement
$stmt->bind_param("ssssssisssssssss", 
    $param_brand, $param_model, $param_serial_number, $param_status, 
    $param_equipment_name, $param_location, $param_price_value, 
    $param_issued_to, $param_date_acquired, $param_remarks, 
    $param_asset_tag, $param_asset_type, $param_iboss_tag, 
    $param_documents, $param_user_id, $param_updated_at
);

            // Set parameters
            $param_documents = implode(",", $filename_array);
			$param_iboss_tag = $iboss_tag;
            $param_asset_type = $asset_type;
            $param_brand = $brand;
            $param_model = $model;
            $param_serial_number = $serial_number;
            $param_status = $status;
            $param_equipment_name = $equipment_name;
            $param_location = $location_asset;
            $param_price_value = $price_value;
            $param_issued_to = $issued_to;
            $param_date_acquired = $date_acquired;
            $param_remarks = $remarks;
            $param_asset_tag = $asset_number;
            $param_user_id = $_SESSION['id'];

            // Concatenate first name and last name for the `updated_at` column
            $param_updated_at = date("Y-m-d h:i:s A");

            // Execute the prepared statement
            // Execute the prepared statement
if ($stmt->execute()) {
    // Prepare to log the insertion
$changes = "Inserted asset: ";
$changes .= "IBOSS Tag: " . htmlspecialchars($param_iboss_tag) . ", ";
$changes .= "Asset Tag: " . htmlspecialchars($param_asset_tag) . ", ";
$changes .= "Asset Type: " . htmlspecialchars($param_asset_type) . ", ";
$changes .= "Brand: " . htmlspecialchars($param_brand) . ", ";
$changes .= "Model: " . htmlspecialchars($param_model) . ", ";
$changes .= "Serial Number: " . htmlspecialchars($param_serial_number) . ", ";
$changes .= "Status: " . htmlspecialchars($param_status) . ", ";
$changes .= "Equipment Name: " . htmlspecialchars($param_equipment_name) . ", ";
$changes .= "Location: " . htmlspecialchars($param_location) . ", ";
$changes .= "Price Value: " . htmlspecialchars($param_price_value) . ", ";
$changes .= "Issued To: " . htmlspecialchars($param_issued_to) . ", ";
$changes .= "Date Acquired: " . htmlspecialchars($param_date_acquired) . ", ";
$changes .= "Remarks: " . htmlspecialchars($param_remarks);

// Insert log entry
$log_date = date("Y-m-d");
$log_time = date("H:i:s");
$log_sql = "INSERT INTO logs (action, asset_tag, firstname, username, changes, log_date, log_time) VALUES (?, ?, ?, ?, ?, ?, ?)";
if ($log_stmt = $mysqli->prepare($log_sql)) {
    $action = 'Insert Asset';
    $log_stmt->bind_param("sssssss", $action, $param_asset_tag, $_SESSION['firstname'], $_SESSION['username'], $changes, $log_date, $log_time);
    $log_stmt->execute();
    $log_stmt->close();
}
    echo '<script>alert("Asset added successfully. Total files uploaded: ' . $totalFileUploaded . '");</script>';
} else {
    echo '<script>alert("Something went wrong.");</script>';
}

           
        }
    }


}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>APAC-Asset Management System</title>
    <link rel="icon" type="image/x-icon" href="logo small.png">
    <link rel="stylesheet" href="style.css?v=1.1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <link rel="icon" type="image/x-icon" href="white.png">
<script>
    document.addEventListener("DOMContentLoaded", function() {
        function handleAssetTypeChange() {
            const assetType = document.getElementById("asset_type").value;

            // Get references to the input fields and their labels
            const modelField = document.getElementById("model");
            const modelLabel = document.querySelector("label[for='model']");
            const serialNumberField = document.getElementById("serial_number");
            const serialNumberLabel = document.querySelector("label[for='serial_number']");
            const ibossTagField = document.getElementById("iboss_tag");
            const ibossTagLabel = document.querySelector("label[for='iboss_tag']");
            const equipmentNameField = document.getElementById("equipment_name");
            const equipmentNameLabel = document.querySelector("label[for='equipment_name']");
            const issuedToField = document.getElementById("issued_to");
            const issuedToLabel = document.querySelector("label[for='issued_to']");

            // Get references to the <br> elements
            const brModel = document.getElementById("br_model");
            const brSerialNumber = document.getElementById("br_serial_number");
            const brIbossTag = document.getElementById("br_iboss_tag");
            const brEquipmentName = document.getElementById("br_equipment_name");
            const brIssuedTo = document.getElementById("br_issued_to");

            // Handle Asset Type visibility
            if (assetType === "IT Peripherals") {
                // Set values to "N/A" for specific fields
                modelField.value = "N/A";
                serialNumberField.value = "N/A";
                ibossTagField.value = "N/A";
                equipmentNameField.value = ""; // Keep equipment name empty

                // Hide the fields and their labels
                modelField.style.display = 'none';
                modelLabel.style.display = 'none';  
                serialNumberField.style.display = 'none';
                serialNumberLabel.style.display = 'none';  
                ibossTagField.style.display = 'none';
                ibossTagLabel.style.display = 'none';  
                brModel.style.display = 'none';
                brSerialNumber.style.display = 'none';
                brIbossTag.style.display = 'none';
            } else {
                // Clear values if not "IT Peripherals"
                modelField.value = "";
                serialNumberField.value = "";
                ibossTagField.value = "";
                equipmentNameField.value = ""; // Clear equipment name as well

                // Show the fields and their labels
                modelField.style.display = 'block';
                modelLabel.style.display = 'block';  
                serialNumberField.style.display = 'block';
                serialNumberLabel.style.display = 'block';  
                ibossTagField.style.display = 'block';
                ibossTagLabel.style.display = 'block';  
                equipmentNameField.style.display = 'block';
                equipmentNameLabel.style.display = 'block';  
                brModel.style.display = 'inline';
                brSerialNumber.style.display = 'inline';
                brIbossTag.style.display = 'inline';
            }

            // Call the function to handle status visibility
            handleStatusChange();
        }

        function handleStatusChange() {
            const statusField = document.getElementById("status");
            const selectedStatus = statusField.value;
            const issuedToField = document.getElementById("issued_to");
            const issuedToLabel = document.querySelector("label[for='issued_to']");
            const brIssuedTo = document.getElementById("br_issued_to");

            // Hide Issued To field if the status is one of the specified values
            if (selectedStatus === "Available" || selectedStatus === "Defective" || selectedStatus === "Decommission") {
                issuedToField.style.display = 'none';
                issuedToLabel.style.display = 'none';  
                brIssuedTo.style.display = 'none'; 
				issuedToField.value = "N/A"; // Set value to "N/A"
            } else {
                issuedToField.style.display = 'block';
                issuedToLabel.style.display = 'block';  
                brIssuedTo.style.display = 'inline'; 
				issuedToField.value = "";
            }
        }

        // Add event listeners to both asset type and status fields to handle changes
        document.getElementById("asset_type").addEventListener("change", handleAssetTypeChange);
        document.getElementById("status").addEventListener("change", handleStatusChange);

        // Initial call to set the correct visibility on page load
        handleAssetTypeChange();
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const issuedToInput = document.getElementById("issued_to");
        const suggestionsContainer = document.getElementById("suggestions");

        issuedToInput.addEventListener("input", function() {
            const query = this.value;

            if (query.length > 0) {
                // Make AJAX request to fetch suggestions
                fetch(`search_employee.php?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        // Clear previous suggestions
                        suggestionsContainer.innerHTML = "";
                        suggestionsContainer.style.display = "none";

                        if (data.length > 0) {
                            // Populate suggestions
                            data.forEach(name => {
                                const suggestionItem = document.createElement("a");
                                suggestionItem.classList.add("list-group-item", "list-group-item-action");
                                suggestionItem.textContent = name;

                                // Add click event to populate input field
                                suggestionItem.addEventListener("click", function() {
                                    issuedToInput.value = name;
                                    suggestionsContainer.innerHTML = "";
                                    suggestionsContainer.style.display = "none";
                                });

                                suggestionsContainer.appendChild(suggestionItem);
                            });
                            suggestionsContainer.style.display = "block"; // Show suggestions
                        } else {
                            suggestionsContainer.style.display = "none"; // Hide if no suggestions
                        }
                    });
            } else {
                // Hide suggestions if input is empty
                suggestionsContainer.innerHTML = "";
                suggestionsContainer.style.display = "none";
            }
        });

        // Hide suggestions when clicking outside
        document.addEventListener("click", function(event) {
            if (!issuedToInput.contains(event.target) && !suggestionsContainer.contains(event.target)) {
                suggestionsContainer.innerHTML = "";
                suggestionsContainer.style.display = "none"; // Hide suggestions
            }
        });
    });
</script>

</head>
<style>
#suggestions {
    position: absolute; /* Ensure it is positioned relative to the nearest positioned ancestor */
    max-height: 200px; /* Set a maximum height */
    overflow-y: auto; /* Enable vertical scrolling */
    border: 1px solid #ccc; /* Add a border */
    background-color: white; /* Background color */
    width: 100%; /* Full width */
    z-index: 1000; /* Ensure it appears above other content */
    display: none; /* Initially hidden */
}
</style>
<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <div class="row">
            <div class="col">
                <h1>Add Asset</h1>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" autocomplete="off" enctype='multipart/form-data'>
                    <label for="asset_type">Asset Type</label>
                    <select name="asset_type" id="asset_type" class="form-control <?php echo (!empty($assettype_err)) ? 'is-invalid' : ''; ?>" onchange="toggleFields()">
                        <option value="">---Select Asset Type---</option>
                        <?php foreach ($asset_types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="invalid-feedback"><?php echo $assettype_err; ?></span>
                    <span id="br_asset_type"><br></span>

                    <label for="iboss_tag">IBOSS Tag</label>
                    <input type="text" name="iboss_tag" id="iboss_tag" class="form-control <?php echo (!empty($iboss_tag_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $iboss_tag; ?>">
                    <span class="invalid-feedback"><?php echo $iboss_tag_err; ?></span>
                    <span id="br_iboss_tag"><br></span>

                    <label for="brand">Brand</label>
                    <input type="text" name="brand" id="brand" class="form-control <?php echo (!empty($brand_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $brand; ?>">
                    <span class="invalid-feedback"><?php echo $brand_err; ?></span>
                    <span id="br_brand"><br></span>

                    <label for="model">Model</label>
                    <input type="text" name="model" id="model" class="form-control <?php echo (!empty($model_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $model; ?>">
                    <span class="invalid-feedback"><?php echo $model_err; ?></span>
                    <span id="br_model"><br></span>

                    <label for="serial_number">Serial Number</label>
                    <input type="text" name="serial_number" id="serial_number" class="form-control <?php echo (!empty($serial_number_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $serial_number; ?>">
                    <span class="invalid-feedback"><?php echo $serial_number_err; ?></span>
                    <span id="br_serial_number"><br></span>

                    <label for="equipment_name">Equipment Name</label>
                    <input type="text" name="equipment_name" id="equipment_name" class="form-control <?php echo (!empty($equipment_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $equipment_name; ?>">
                    <span class="invalid-feedback"><?php echo $equipment_name_err; ?></span>
                    <span id="br_equipment_name"><br></span>

                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control <?php echo (!empty($status_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $status; ?>">
                        <option value="">---Select Status---</option>
                        <option value="Deployed">Deployed</option>
                        <option value="Available">Available</option>
                        <option value="Defective">Defective</option>
                        <option value="Decommission">Decommission</option>
                    </select>
                    <span class="invalid-feedback"><?php echo $status_err; ?></span>
                    <span id="br_status"><br></span>

                    <label for="location_asset">Location</label>
                    <select name="location_asset" id="location_asset" class="form-control <?php echo (!empty($location_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $location_asset; ?>">
                        <option value="">---Select Location ---</option>
                        <?php foreach ($locations as $location): ?>
                            <option value="<?php echo htmlspecialchars($location); ?>"><?php echo htmlspecialchars($location); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="invalid-feedback"><?php echo $location_err; ?></span>
                    <span id="br_location_asset"><br></span>

                    <label for="price_value">Price Value</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">₱</span>
                        </div>
                        <input type="number" name="price_value" id="price_value" class="form-control <?php echo (!empty($price_value_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $price_value; ?>">
                        <span class="invalid-feedback"><?php echo $price_value_err; ?></span>
                    </div>
                    <span id="br_price_value"><br></span>

                    <label for="issued_to">Issued to</label>
<div style="position: relative;">
    <input type="text" name="issued_to" id="issued_to" class="form-control <?php echo (!empty($issued_to_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $issued_to; ?>" autocomplete="off">
    <span class="invalid-feedback"><?php echo $issued_to_err; ?></span>
    <span id="br_issued_to"><br></span>
    <div id="suggestions" class="list-group" style="display: none;"></div> <!-- Suggestions list -->
</div>

                    <label for="date_acquired">Date Acquired</label>
                    <input type="date" name="date_acquired" id="date_acquired" class="form-control <?php echo (!empty($date_acquired_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $date_acquired; ?>">
                    <span class="invalid-feedback"><?php echo $date_acquired_err; ?></span>
                    <span id="br_date_acquired"><br></span>

                    <label for="documents">Documents</label>
                    <input type="file" name="documents[]" id="documents" class="form-control" multiple>
                    <span id="br_documents"><br></span>

                    <label for="remarks">Remarks</label>
                    <input type="text" name="remarks" id="remarks" class="form-control <?php echo (!empty($remarks_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $remarks; ?>">
                    <span class="invalid-feedback"><?php echo $remarks_err; ?></span>
                    <span id="br_remarks"><br></span>

                    <input type="submit" class="btn btn-primary" value="Submit" onClick="return confirm('Confirm to Register this Asset?')">
                    <a href="dashboard.php" class="btn btn-secondary ml-2" onClick="return confirm('Do you want to go back? All inserted data here before submitting will be gone!')">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</body>
<?php include 'footer.php'; ?>
</html>