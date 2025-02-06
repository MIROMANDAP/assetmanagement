<?php
include 'config.php';
header('Content-Type: text/html; charset=utf-8');
session_start();

// Redirect if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Fetch asset types, locations, and users in a single query
$asset_types = $locations = $users = [];

// Prepare a single query to fetch all necessary data
$sql = "
    SELECT (SELECT GROUP_CONCAT(type_name) FROM asset_types) AS asset_types,
           (SELECT GROUP_CONCAT(location_name) FROM locations) AS locations,
           (SELECT GROUP_CONCAT(employee_name) FROM employee_list ORDER BY employee_name ASC) AS users
";

if ($result = $mysqli->query($sql)) {
    $data = $result->fetch_assoc();
    
    // Explode the concatenated strings into arrays
    $asset_types = explode(',', $data['asset_types']);
    $locations = explode(',', $data['locations']);
    $users = array_map('htmlspecialchars', explode(',', $data['users']));

    $result->free();
}

// Now $asset_types, $locations, and $users are ready for use
?>
<?php if (isset($_GET['success'])): ?>
    <script>
        alert("Successfully updated!");
    </script>
<?php endif; ?>
<!DOCTYPE html>
<html lang="en">
<!-- Loading Screen 
<div id="loadingOverlay">
    <div id="loadingIndicator">
        <img src="logo.png" alt="Logo" id="logo" /><br><br>
        <div class="spinner">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
        <p>Loading Apac Asset, please wait...</p>
    </div>
</div>

<script>
    // Show the loading spinner
    document.getElementById('loadingOverlay').style.display = 'flex';

    // Simulate data loading (replace this with your actual data loading logic)
    setTimeout(function() {
        // Hide the loading spinner after data is loaded
        document.getElementById('loadingOverlay').style.display = 'none';
    }, 2000); // Simulate a 3-second loading time
</script>-->

<style>
    /* Full-screen overlay for the loading spinner */
    #loadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
        display: none; /* Initially hidden */
        align-items: center;
        justify-content: center;
        z-index: 9999; /* Ensure it appears above other content */
        backdrop-filter: blur(10px); /* Apply blur effect */
    }

    #loadingIndicator {
        text-align: center;
        color: #fff; /* White text for contrast */
        font-family: 'Arial', sans-serif; /* Clean font */
        padding: 2rem; /* Padding around the loading indicator */
        border-radius: 10px; /* Rounded corners for the loading box */
        background-color: rgba(255, 255, 255, 0.1); /* Slightly transparent background for the loading box */
    }

    /* Logo styling */
    #logo {
        width: 100px; /* Adjust the logo size as needed */
        margin-bottom: 1rem; /* Space between logo and spinner */
    }

    /* Custom spinner animation */
    .spinner {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 1rem; /* Space between spinner and text */
    }

    .dot {
        width: 1rem;
        height: 1rem;
        margin: 0 0.3rem;
        border-radius: 50%;
        background-color: #fff; /* White dots */
        animation: bounce 0.6s infinite alternate; /* Bouncing animation */
    }

    .dot:nth-child(1) {
        animation-delay: 0s;
    }
    .dot:nth-child(2) {
        animation-delay: 0.2s;
    }
    .dot:nth-child(3) {
        animation-delay: 0.4s;
    }

    /* Keyframes for bouncing animation */
    @keyframes bounce {
        from {
            transform: translateY(0);
        }
        to {
            transform: translateY(-20px);
        }
    }

    /* Style for the loading text */
    #loadingIndicator p {
        font-size: 1.5rem; /* Larger font size for better readability */
        color: #fff; /* White text for contrast */
        margin-top: 1rem; /* Space between spinner and text */
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5); /* Subtle text shadow for depth */
    }
</style>
<head>
    <meta charset="UTF-8">
    <title>Asset Management System</title>
	<link rel="icon" type="image/x-icon" href="logo small.png">
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="white.png">
    <link href="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.css" rel="stylesheet">
    <script src="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.js"></script>
    <script src="https://unpkg.com/bootstrap-table@1.22.1/dist/extensions/print/bootstrap-table-print.min.js"></script>
	
    <script>
        function confirmAction() {
            return confirm("Are you sure you want to delete this asset?");
        }
    </script>
</head>

<body>
<div id="successMessage" style="display:none; color: green;">Successfully updated!</div>
    <?php include 'nav.php'; ?>
  <div class="container-fluid" style="background-color: #f2f2f2; padding: 20px; border: 1px solid #ddd; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
        <div class="row">
            <div class="col">
                <h1>Assets</h1>
                <input type="text" id="searchInput" class="form-control form-control-lg d-print-none"
                    placeholder="Search for an asset..."><br>
                <select id="assetType" class="form-select form-select d-print-none">
                    <option value="">All</option>
                    <?php foreach ($asset_types as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                    <?php endforeach; ?>
                </select>
				

                <script>
                var searchInput = document.getElementById('searchInput');
                var assetType = document.getElementById('assetType');

                searchInput.addEventListener('input', function() {
                    var searchQuery = searchInput.value;
                    var selectedAssetType = assetType.value;
                    searchAssets(searchQuery, selectedAssetType);
                });

                assetType.addEventListener('change', function() {
                    var searchQuery = searchInput.value;
                    var selectedAssetType = assetType.value;
                    searchAssets(searchQuery, selectedAssetType);
                });

                function searchAssets(query, assetType) {
    var table = document.getElementById('assetsTable');
    var rows = table.getElementsByTagName('tr');

    for (var i = 1; i < rows.length; i++) {
        var found = false;
        var cells = rows[i].getElementsByTagName('td');

        for (var j = 0; j < cells.length; j++) {
            if ([0, 1, 2, 3, 4, 5, 6, 9, 10, 11].includes(j)) {
                var name = cells[j].textContent || cells[j].innerText;
                if ((name.toLowerCase().indexOf(query.toLowerCase()) > -1) &&
                    (assetType === '' || assetType === cells[2].textContent)) {
                    found = true;
                    break;
                }
            }
        }

        rows[i].style.display = found ? '' : 'none';
    }
}
                </script>
                <br>
<div class="table-responsive">
    <table class="table table-striped table-bordered border-start w-100" id="assetsTable" data-show-print="true">
        <thead>
    <tr style="text-align: center; background-color: #4CAF50; color: white;">
        
        <th style="text-align: center; vertical-align: middle; padding-bottom: 15px;">APAC Tag</th>
		<th style="text-align: center; vertical-align: middle; padding-bottom: 15px;">IBOSS Tag</th>
        <th style="text-align: center; vertical-align: middle; padding-bottom: 15px;">Asset Type</th>
        <th style="text-align: center; vertical-align: middle; padding-bottom: 15px;">Brand</th>
        <th style="text-align: center; vertical-align: middle; padding-bottom: 15px;">Model</th>
        <th style="text-align: center; vertical-align: middle; padding-bottom: 15px;">Equipment Name</th>
        <th style="text-align: center; vertical-align: middle; padding-bottom: 15px;">Serial Number</th>
        
        <th style="text-align: center; vertical-align: middle; padding-bottom: 15px;">Date Acquired</th>
        <th style="text-align: center; vertical-align: middle; padding-bottom: 15px;">Price Value</th>
        <th style="text-align: center; vertical-align: middle; padding-bottom: 15px;">Issued to</th>
        <th style="text-align: center; vertical-align: middle; padding-bottom: 15px;">Location</th>
		<th style="text-align: center; vertical-align: middle; padding-bottom: 15px;">Status</th>
        <th style="text-align: center; vertical-align: middle; padding-bottom: 15px;">Remarks</th>
     <!--    <th style="text-align: center; vertical-align: middle; padding-bottom: 15px;">Date Updated and User</th>-->
        <th style="text-align: center; vertical-align: middle; padding-bottom: 15px;">Documents</th>
        <?php if ($_SESSION['account_type'] === "admin" || $_SESSION['account_type'] === "superadmin") {
            echo "<th class='d-print-none actions text-center'>Actions</th>";
        } ?>
    </tr>
</thead>
        <tbody>
<?php

// Fetch locations from the database
$locations = [];
$sql_locations = "SELECT location_name FROM locations"; // Adjust the query as necessary
$result_locations = $mysqli->query($sql_locations);
if ($result_locations->num_rows > 0) {
    while ($location_row = $result_locations->fetch_assoc()) {
        $locations[] = $location_row['location_name']; // Adjust the key based on your database structure
    }
}

$sql = "SELECT asset_tag, iboss_tag, asset_type, brand, model, equipment_name, serial_number, date_acquired, price_value, issued_to, location_asset, status, remarks, updated_at, user_id, documents FROM assets";
$result = $mysqli->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Fetch the user details
        $sql2 = "SELECT * FROM users WHERE user_id = '" . $row["user_id"] . "'";
        $result2 = $mysqli->query($sql2);
        $row2 = $result2->fetch_assoc();

        // Extracting the location
        $location = isset($row["location_asset"]) ? $row["location_asset"] : 'Unknown Location';

        echo "<tr style='text-align: center;'>";
        echo "<td style='font-family: consolas;'>" . htmlspecialchars($row["asset_tag"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["iboss_tag"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["asset_type"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["brand"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["model"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["equipment_name"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["serial_number"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["date_acquired"]) . "</td>";
        echo "<td>₱" . number_format(intval($row["price_value"]), 2) . "</td>";
        echo "<td>" . htmlspecialchars($row["issued_to"]) . "</td>";
        echo "<td>" . htmlspecialchars($location) . "</td>"; // Use the location variable
        echo "<td>" . htmlspecialchars($row["status"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["remarks"]) . "</td>";
     //   echo "<td>" . htmlspecialchars($row["updated_at"]) . " by " . htmlspecialchars($row2["firstname"]) . " " . htmlspecialchars($row2["lastname"]) . "</td>";

        // Documents Section
        echo "<td>";
        if (!empty($row["documents"])) {
            $documents = explode(",", $row["documents"]);
            foreach ($documents as $document) {
                echo "<a href='uploads/$document' class='btn btn-sm btn-outline-secondary' target='_blank'>" . htmlspecialchars($document) . "</a><br>";
            }
        } else {
            echo "No Documents";
        }
        echo "</td>";

// Actions
echo "<td class='d-print-none actions text-center'>"; // Center align the buttons

// Check if the user is superadmin
if ($_SESSION['account_type'] === "superadmin") {
    echo "<div class='d-flex flex-column align-items-center'>"; // Flexbox for vertical alignment
    echo "<div class='d-flex justify-content-center mb-2'>"; // First row
 echo "<a href='update_asset.php?asset_tag=" . htmlspecialchars($row["asset_tag"]) . "' class='btn btn-sm btn-primary me-2' title='Edit'><i class='fas fa-edit'></i></a>";
    echo "<a href='delete_asset.php?asset_tag=" . htmlspecialchars($row["asset_tag"]) . "' class='btn btn-sm btn-danger' onclick='return confirmAction();' title='Delete'><i class='fas fa-trash'></i></a>";
    echo "</div>"; // Close first row

    echo "<div class='d-flex justify-content-center'>"; // Second row
    echo "<button class='btn btn-sm btn-warning me-2' data-bs-toggle='modal' data-bs-target='#transferModal" . htmlspecialchars($row["asset_tag"]) . "' title='Transfer Location'><i class='fas fa-exchange-alt'></i></button>";
	 echo "<button class='btn btn-sm btn-info me-2' data-bs-toggle='modal' data-bs-target='#editIssuedToModal" . htmlspecialchars($row["asset_tag"]) . "' title='Edit Issued To'><i class='fas fa-user-edit'></i></button>"; // New button for editing Issued To
    echo "<button class='btn btn-sm btn-info' title='View Asset History' onclick='loadAssetHistory(\"" . htmlspecialchars($row["asset_tag"]) . "\")' data-bs-toggle='modal' data-bs-target='#historyModal'><i class='fas fa-history'></i></button>";
    echo "</div>"; // Close second row
    echo "</div>"; // Close flex container
}

// Check if the user is admin
if ($_SESSION['account_type'] === "admin") {
    echo "<div class='d-flex flex-column align-items-center'>"; // Flexbox for vertical alignment
    echo "<div class='d-flex justify-content-center mb-2'>"; // First row
    echo "<a href='update_asset.php?asset_tag=" . htmlspecialchars($row["asset_tag"]) . "' class='btn btn-sm btn-primary me-2' title='Edit'><i class='fas fa-edit'></i></a>";
    echo "<button class='btn btn-sm btn-warning' data-bs-toggle='modal' data-bs-target='#transferModal" . htmlspecialchars($row["asset_tag"]) . "' title='Transfer Location'><i class='fas fa-exchange-alt'></i></button>";
    echo "</div>"; // Close first row
	
    echo "<div class='d-flex justify-content-center'>"; // Second row
	 echo "<button class='btn btn-sm btn-info me-2' data-bs-toggle='modal' data-bs-target='#editIssuedToModaladmin" . htmlspecialchars($row["asset_tag"]) . "' title='Edit Issued To'><i class='fas fa-user-edit'></i></button>"; // New button for editing Issued To
    echo "<button class='btn btn-sm btn-info' title='View Asset History' onclick='loadAssetHistory(\"" . htmlspecialchars($row["asset_tag"]) . "\")' data-bs-toggle='modal' data-bs-target='#historyModal'><i class='fas fa-history'></i></button>";
    echo "</div>"; // Close second row
    echo "</div>"; // Close flex container
}

echo "</td>";
echo "</tr>";

       
        ?>
		
	


<!-- Modal for Admin Request -->
<div class="modal fade" id="editIssuedToModaladmin<?php echo htmlspecialchars($row['asset_tag']); ?>" tabindex="-1" aria-labelledby="editIssuedToModaladminLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editIssuedToModaladminLabel">Request Change for Asset: <?php echo htmlspecialchars($row["asset_tag"]); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Asset Details:</strong></p>
                <ul>
                    <li><strong>Asset Tag:</strong> <?php echo htmlspecialchars($row["asset_tag"]); ?></li>
                    <li><strong>Brand:</strong> <?php echo htmlspecialchars($row["brand"]); ?></li>
                    <li><strong>Model:</strong> <?php echo htmlspecialchars($row["model"]); ?></li>
                    <li><strong>Current Issued To:</strong> <?php echo htmlspecialchars($row["issued_to"]); ?></li>
                </ul>
                <form action="submit_request.php" method="POST">
                    <input type="hidden" name="asset_tag" value="<?php echo htmlspecialchars($row['asset_tag']); ?>">
                    <input type="hidden" name="old_issued_to" value="<?php echo htmlspecialchars($row['issued_to']); ?>">
                    
                    <div class="mb-3">
                        <label for="new_issued_to_admin" class="form-label">New Issued To</label>
                        <select class="form-select" name="new_issued_to" required>
                           <option value="">Select User</option>
<?php
sort($users);
foreach ($users as $fullName) {
    // Set the selected option if it matches the current issued to
    echo "<option value='" . $fullName . "' " . ($row['issued_to'] === $fullName ? 'selected' : '') . ">$fullName</option>";
}
?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Request</label>
                        <textarea class="form-control" name="reason" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for editing Issued To -->
<div class="modal fade" id="editIssuedToModal<?php echo htmlspecialchars($row['asset_tag']); ?>" tabindex="-1" aria-labelledby="editIssuedToModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editIssuedToModalLabel">Edit Issued To for Asset: <?php echo htmlspecialchars($row["asset_tag"]); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
			    <p><strong>Asset Details:</strong></p>
                <ul>
                    <li><strong>Asset Tag:</strong> <?php echo htmlspecialchars($row["asset_tag"]); ?></li>
                    <li><strong>Brand:</strong> <?php echo htmlspecialchars($row["brand"]); ?></li>
                    <li><strong>Model:</strong> <?php echo htmlspecialchars($row["model"]); ?></li>
                    <li><strong>Current Issued To:</strong> <?php echo htmlspecialchars($row["issued_to"]); ?></li>
                </ul>
                <form id="editIssuedToForm<?php echo htmlspecialchars($row['asset_tag']); ?>" action="update_issued_to.php" method="POST">
                    <input type="hidden" name="asset_tag" value="<?php echo htmlspecialchars($row['asset_tag']); ?>">
                    
                    <div class="mb-3">
                        <label for="new_issued_to<?php echo htmlspecialchars($row['asset_tag']); ?>" class="form-label">Select New Issued To</label>
                        <select class="form-select" id="new_issued_to<?php echo htmlspecialchars($row['asset_tag']); ?>" name="new_issued_to" required>
                            <option value="">Select User</option>
<?php
sort($users);
foreach ($users as $fullName) {
    // Set the selected option if it matches the current issued to
    echo "<option value='" . $fullName . "' " . ($row['issued_to'] === $fullName ? 'selected' : '') . ">$fullName</option>";
}
?>
                        </select>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="confirmEditIssuedTo('<?php echo htmlspecialchars($row['asset_tag']); ?>')">Update Issued To</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
		
		<!-- Asset History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">Asset History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="assetHistoryContent">
                    <!-- Asset history will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

  <!--Modal for transferring location -->
        <div class="modal fade" id="transferModal<?php echo htmlspecialchars($row['asset_tag']); ?>" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="transferModalLabel">Transfer Location for Asset: <?php echo htmlspecialchars($row["asset_tag"]); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
 <div class="modal-body">
                        <p><strong>Current Asset Details:</strong></p>
                        <ul>
                            <li><strong>Asset Tag:</strong> <?php echo htmlspecialchars($row["asset_tag"]); ?></li>
							<li><strong>IBOSS Tag:</strong> <?php echo htmlspecialchars($row["iboss_tag"]); ?></li>
                            <li><strong>Equipment Name:</strong> <?php echo htmlspecialchars($row["equipment_name"]); ?></li>
                            <li><strong>Current Location:</strong> <?php echo htmlspecialchars($location); ?></li>
                        </ul>
  <form id="transferForm<?php echo htmlspecialchars($row['asset_tag']); ?>" action="transfer_location.php" method="POST">
    <input type="hidden" name="asset_tag" value="<?php echo htmlspecialchars($row['asset_tag']); ?>">
    <input type="hidden" id="current_location<?php echo htmlspecialchars($row['asset_tag']); ?>" value="<?php echo htmlspecialchars($location); ?>">
    
    <div class="mb-3">
        <label for="new_location<?php echo htmlspecialchars($row['asset_tag']); ?>" class="form-label">Select New Location</label>
        <select class="form-select" id="new_location<?php echo htmlspecialchars($row['asset_tag']); ?>" name="new_location" required>
            <?php foreach ($locations as $loc) : ?>
                <option value="<?php echo htmlspecialchars($loc); ?>" 
                    <?php echo htmlspecialchars($loc) === htmlspecialchars($location) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($loc); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="mb-3">
        <label for="transfer_reason<?php echo htmlspecialchars($row['asset_tag']); ?>" class="form-label">Reason for Transfer</label>
        <textarea class="form-control" id="transfer_reason<?php echo htmlspecialchars($row['asset_tag']); ?>" name="transfer_reason" rows="3" required></textarea>
    </div>
    
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="confirmTransfer('<?php echo htmlspecialchars($row['asset_tag']); ?>')">Transfer Location</button>
    </div>
</form>

                </div>
            </div>
        </div>
        <?php
    }
}
?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
	<script>
function confirmEditIssuedTo(assetTag) {
    var newIssuedTo = document.getElementById('new_issued_to' + assetTag).value;

    if (newIssuedTo === "") {
        alert("Please select a user to issue the asset to.");
        return; // Stop the function execution
    }

    if (confirm("Are you sure you want to update the 'Issued To' field?")) {
        document.getElementById('editIssuedToForm' + assetTag).submit();
    }
}
</script>
	<script>
	function loadAssetHistory(assetTag) {
    // Clear previous history content
    document.getElementById('assetHistoryContent').innerHTML = 'Loading...';

    // Make an AJAX request to fetch asset history
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'fetch_asset_history.php?asset_tag=' + encodeURIComponent(assetTag), true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            // Update the modal content with the response
            document.getElementById('assetHistoryContent').innerHTML = xhr.responseText;
        } else {
            document.getElementById('assetHistoryContent').innerHTML = 'Error loading history.';
        }
    };
    xhr.send();
}
</script>
<script>
function confirmTransfer(assetTag) {
    // Get the current location, selected new location, and transfer reason
    var currentLocation = document.getElementById('current_location' + assetTag).value;
    var newLocation = document.getElementById('new_location' + assetTag).value; // Updated ID
    var transferReason = document.getElementById('transfer_reason' + assetTag).value.trim(); // Updated ID

    console.log("Current Location:", currentLocation);
    console.log("New Location:", newLocation);
    console.log("Transfer Reason:", transferReason);

    // Check if the transfer reason is empty
    if (transferReason === "") {
        alert("Please provide a reason for the transfer.");
        return; // Stop the function execution
    }

    // Check if the new location is the same as the current location
    if (newLocation === currentLocation) {
        alert("The new location is the same as the current location. Please select a different location.");
        return; // Stop the function execution
    }

    // If the locations are different, show confirmation
    if (confirm("Are you sure you want to transfer the location from " + currentLocation + " to " + newLocation + "?")) {
        // If the user confirms, submit the form
        document.getElementById('transferForm' + assetTag).submit();
        
        // Show success message (this will only run if the form is submitted successfully)
        document.getElementById('successMessage').style.display = 'block';
    }
}
</script>
<?php include 'footer.php'; ?>

</body>

</html>