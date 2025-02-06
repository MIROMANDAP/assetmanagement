<?php
include 'config.php';
header('Content-Type: text/html; charset=utf-8');
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

?>
<script>
window.onload = function() {
    // Call searchAssets with no parameters to compute totals on load
    searchAssets(); // This will compute totals based on all visible rows
};
function loadImage() {
    var img = document.createElement('img');
    img.src = 'http://localhost/dashboard/PRINT.png';
    img.style.display = 'none'; // Hide the image
    document.body.appendChild(img);

}

// Call the function when the page loads
window.onload = loadImage;
</script>

<!DOCTYPE html>
<html lang="en">
<meta charset="UTF-8">

<head>
    
    <title>Asset Management System</title>
    <link rel="icon" type="image/x-icon" href="logo small.png">
    <link rel="stylesheet" href="style.css?v=1.1">
	
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.css" rel="stylesheet">
    <script src="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.js"></script>
    <script src="https://unpkg.com/bootstrap-table@1.22.1/dist/extensions/print/bootstrap-table-print.min.js"></script>
    <script src="https://unpkg.com/xlsx@0.16.9/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html-to-xlsx@1.0.0/dist/html-to-xlsx.min.js"></script>
	
	
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script>
        function confirmAction() {
            return confirm("Are you sure?");
        }
    </script>
</head>
<style>
/* Position Fixed Styles for Scroll Buttons */
.position-fixed {
    position: fixed;
    bottom: 20px; /* Position from the bottom */
    right: 30px;  /* Position from the right */
    z-index: 99;  /* Ensure it is above other elements */
    display: flex; /* Use flexbox for vertical alignment */
    flex-direction: column; /* Stack buttons vertically */
    gap: 10px; /* Space between buttons */
}

/* Scroll Button Styles */
.scroll-btn {
    display: flex; /* Ensure the button itself is a flex container */
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    border: none; /* No border */
    outline: none; /* No outline */
    background-color: #2cab62; /* Button background color */
    color: white; /* Button text color */
    cursor: pointer; /* Pointer cursor on hover */
    width: 50px; /* Width of the button */
    height: 50px; /* Height of the button */
    border-radius: 50%; /* Circular button */
    box-shadow: 0 2px 5px rgba(0,0,0,0.3); /* Shadow effect */
    transition: background-color 0.3s ease; /* Smooth transition for background color */
}

/* Arrow Styles */
.arrow-up, .arrow-down {
    width: 0;
    height: 0;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    margin: 0; /* Ensure no margin */
}

/* Center the arrows */
.arrow-up {
    border-bottom: 12px solid white; /* Arrow color */
    margin-top: 2px; /* Adjust to center vertically */
}

.arrow-down {
    border-top: 12px solid white; /* Arrow color */
    margin-bottom: 2px; /* Adjust to center vertically */
}

/* Hover Effect */
.scroll-btn:hover {
    background-color: #0056b3; /* Change background on hover */
}


.action-button-container {
    position: fixed; /* Fixed position to stay in view */
    right: 20px; /* Distance from the left edge of the screen */
    top: 1%; /* Adjust this value to position it higher on the screen */
    z-index: 1000; /* Ensure it appears above other elements */
	
}

.action-button {
    border-radius: 50%; /* Make the button circular */
    width: 60px; /* Set width */
    height: 60px; /* Set height */
    display: flex; /* Center text inside */
    align-items: center; /* Vertically center text */
    justify-content: center; /* Horizontally center text */
    font-size: 1.2em; /* Increase font size */
    padding: 0; /* Remove default padding */
    background-color: rgba(0, 123, 255, 0.6); /* Semi-transparent blue background (60% opacity) */
    color: white; /* Text color */
    border: none; /* Remove default button border */
    cursor: pointer; /* Change cursor to pointer on hover */
}

.dropdown-menu {
    border-radius: 8px; /* Rounded corners for dropdown */
}

.dropdown-item {
    transition: background-color 0.3s; /* Smooth transition for hover */
}

.dropdown-item:hover {
    background-color: #54B4D3; /* Change background on hover */
    color: white; /* Change text color on hover */
}

@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

.futuristic-modal {
    background: linear-gradient(135deg, #f0f0f0, #d9d9d9);
    color: #333;
    border-radius: 15px;
    box-shadow: 0 8px 40px rgba(0, 0, 0, 0.3); /* Deeper shadow */
    transition: transform 0.3s ease, box-shadow 0.3s ease; /* Transition for shadow */
    animation: fadeIn 0.5s ease; /* Animation for modal appearance */
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.futuristic-modal:hover {
    transform: translateY(-5px); /* Slight lift on hover */
    box-shadow: 0 12px 60px rgba(0, 0, 0, 0.5); /* Enhanced shadow on hover */
}

.modal-header {
    border-bottom: 1px solid #ccc;
    background: rgba(255, 255, 255, 0.8); /* Semi-transparent background */
}

.modal-title {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    color: #007bff;
    text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.7); /* Light text shadow */
}

.modal-body p {
    margin: 0.5em 0;
    font-family: 'Poppins', sans-serif;
}

.modal-body h6 {
    color: #007bff;
    margin-top: 1em;
    text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.7);
}

.modal-footer {
    border-top: 1px solid #ccc;
}

.btn-secondary {
    background: linear-gradient(90deg, #007bff, #00bfff); /* Gradient button */
    border: none;
    color: #fff;
    transition: background 0.3s ease; /* Transition for button */
}

.btn-secondary:hover {
    background: linear-gradient(90deg, #0056b3, #00aaff); /* Darker gradient on hover */
}

    </style>
<body>
<script>
function scrollToTop(button) {
    button.classList.add('clicked'); // Add clicked class for animation
    window.scrollTo({
        top: 0,
        behavior: 'smooth' // Smooth scroll
    });
    setTimeout(() => button.classList.remove('clicked'), 200); // Remove class after animation
}

function scrollToBottom(button) {
    button.classList.add('clicked'); // Add clicked class for animation
    window.scrollTo({
        top: document.body.scrollHeight,
        behavior: 'smooth' // Smooth scroll
    });
    setTimeout(() => button.classList.remove('clicked'), 200); // Remove class after animation
}

// Show the buttons when scrolling
window.onscroll = function() {
    const topButton = document.getElementById("scrollToTopBtn");
    const bottomButton = document.getElementById("scrollToBottomBtn");

    // Show or hide the scroll to top button
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        topButton.style.display = "flex"; // Show button
    } else {
        topButton.style.display = "none"; // Hide button
    }

    // Show or hide the scroll to bottom button
    if (document.body.scrollHeight - window.innerHeight - document.body.scrollTop > 20) {
        bottomButton.style.display = "flex"; // Show button
    } else {
        bottomButton.style.display = "none"; // Hide button
    }
};
</script>


<div class="position-fixed">
    <button id="scrollToTopBtn" class="scroll-btn" onclick="scrollToTop(this)">
        <div class="arrow-up"></div>
    </button>
    <button id="scrollToBottomBtn" class="scroll-btn" onclick="scrollToBottom(this)">
        <div class="arrow-down"></div>
    </button>
</div>
    <?php include 'nav.php'; ?>
   <div class="container-fluid" style="background-color: #f2f2f2; padding: 20px; border: 1px solid #ddd; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
  
        <div class="row">
            <div class="col">
			<div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Assets</h1>
				<div class="action-button-container">
    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle action-button" type="button" id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-file-alt"></i> <!-- Replace with your desired Font Awesome icon -->
        </button>
        <ul class="dropdown-menu" aria-labelledby="actionDropdown">
            <li><a class="dropdown-item" href="#" onclick="printTable()">Print Everything</a></li>
            <li><a class="dropdown-item" href="insert_asset.php">Add Asset</a></li>
            <li><a class="dropdown-item" href="edit_asset.php">Edit Asset</a></li>
            <li><a class="dropdown-item" href="#" onclick="saveAsExcel()">Save as Excel</a></li>
			
            <li><a class="dropdown-item" href="#" onclick="toggleEmailForm(); return false;">Send Email</a></li>
			
        </ul>
    </div>
</div>
<!-- Modal Structure -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content futuristic-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Asset Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>APAC Tag:</strong> <span id="modalAPACTag"></span></p>
                <p><strong>IBOSS Tag:</strong> <span id="modalIBOSSTag"></span></p>
                <p><strong>Asset Type:</strong> <span id="modalAssetType"></span></p>
                <p><strong>Brand:</strong> <span id="modalBrand"></span></p>
                <p><strong>Model:</strong> <span id="modalModel"></span></p>
                <p><strong>Equipment Name:</strong> <span id="modalEquipmentName"></span></p>
                <p><strong>Serial Number:</strong> <span id="modalSerialNumber"></span></p>
                <p><strong>Date Acquired:</strong> <span id="modalDateAcquired"></span></p>
                <p><strong>Price Value:</strong> <span id="modalPriceValue"></span></p>
                <p><strong>Issued To:</strong> <span id="modalIssuedTo"></span></p>
                <p><strong>Location:</strong> <span id="modalLocation"></span></p>
                <p><strong>Status:</strong> <span id="modalStatus"></span></p>
                <p><strong>Remarks:</strong> <span id="modalRemarks"></span></p>
                <p><strong>Last Updated:</strong> <span id="modalLastUpdated"></span></p>
                
                <hr>
                <h6>Documents:</h6>
                <div id="modalDocumentPreviews"></div> <!-- Section for document previews -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
                <script>
				function toggleEmailForm() {
    const emailFormContainer = document.getElementById('emailFormContainer');
    if (emailFormContainer) {
        if (emailFormContainer.style.display === 'none') {
            // Show the form
            emailFormContainer.style.display = 'block';
            
            // Scroll to the form
            setTimeout(() => {
                emailFormContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        } else {
            // Hide the form
            emailFormContainer.style.display = 'none';
        }
    } else {
        console.error("Email form container not found");
    }
}
</script>
            </div>
                <input type="text" id="searchInput" class="form-control form-control-lg d-print-none" placeholder="Search for an asset..."><br>
                <select id="assetType" class="form-select form-select d-print-none">
                    <option value="">All</option>
                    <option value="System Unit">System Unit</option>
                    <option value="Monitor">Monitor</option>
                    <option value="Headset">Headset</option>
                    <option value="Laptop">Laptop</option>
                    <option value="Webcam">Webcam</option>
                    <option value="IT Peripherals">IT Peripherals</option>
                    <option value="Switches">Switches</option>
                    <option value="Printers">Printers</option>
                    <option value="Routers">Routers</option>
                    <option value="Servers">Servers</option>
                    <option value="Software">Software</option>
                    <option value="Access Card">Access Card</option>
                </select>
                <script>
				
                    var searchInput = document.getElementById('searchInput');
                    var assetType = document.getElementById('assetType');
					var totalValue = 0; // Initialize total value for filtered results

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
					
					window.onload = function() {
    searchAssets(); // Call without parameters to count all assets
};
function searchAssets(query = '', selectedAssetType = '') {
    var table = document.getElementById('assetsTable');
    var rows = table.getElementsByTagName('tr');
    var totalValue = 0; // Initialize total value for filtered results
    var assetCounts = {}; // Object to hold counts of asset types
    var visibleCount = 0; // Initialize count of visible assets

    // Initialize status counts
    var statusCounts = {
        Deployed: 0,
        Spare: 0,
        Faulty: 0,
        Defective: 0
    };

    // Check if a search query is provided
    var isSearchEmpty = query.trim() === '';

    for (var i = 1; i < rows.length; i++) {
        var found = false;
        var cells = rows[i].getElementsByTagName('td');

        // Check if the row has the expected number of cells
        if (cells.length < 12) {
            continue; // Skip rows that don't have enough cells
        }

        // If no search is performed, count all rows
        if (isSearchEmpty) {
            found = true; // Mark as found to count all assets
        } else {
            for (var j = 0; j < cells.length; j++) {
                if ([0, 1, 2, 3, 4, 5, 6, 9, 10, 11].includes(j)) {
                    var name = cells[j].textContent || cells[j].innerText;

                    // Check if the query is in the current cell and if the selected asset type matches
                    if ((name.toLowerCase().indexOf(query.toLowerCase()) > -1) &&
                        (selectedAssetType === '' || selectedAssetType === cells[2].textContent)) {
                        found = true;
                        break;
                    }
                }
            }
        }

        // Show or hide the row based on the search result
        rows[i].style.display = found ? '' : 'none';

        // If the row is visible, add its price_value to the total
        if (found) {
            totalValue += parseFloat(cells[8].textContent.replace(/,/g, '')); // Assuming Price Value is in the 9th column (index 8)
            var assetType = cells[2].textContent; // Get the asset type from the third column

            // Count the asset type
            assetCounts[assetType] = (assetCounts[assetType] || 0) + 1;
            visibleCount++; // Increment the count of visible assets

            // Count the status
            var status = cells[11].textContent; // Assuming Status is in the 12th column (index 11)
            if (statusCounts.hasOwnProperty(status)) {
                statusCounts[status]++;
            }
        }
    }

    // Update the displayed total value and asset type counts, including status counts
    updateTotalValue(totalValue, assetCounts, visibleCount, statusCounts);
}

function updateTotalValue(totalValue, assetCounts, visibleCount, statusCounts) {
    // Clear existing summary rows
    const tbody = document.querySelector('#assetsTable tbody');
    
    // Remove all existing summary rows
    const existingSummaryRows = tbody.querySelectorAll('.summary-row');
    existingSummaryRows.forEach(row => row.remove());

    // Add a blank row for spacing above the summary
    const blankRowAbove = document.createElement('tr');
    blankRowAbove.classList.add('summary-row'); // Add a class to identify summary rows
    blankRowAbove.innerHTML = `<td colspan='12'>&nbsp;</td>`; // Use &nbsp; for a blank row
    tbody.appendChild(blankRowAbove);

    // Create a new row for the summary report
    const summaryRow = document.createElement('tr');
    summaryRow.classList.add('summary-row'); // Add a class to identify summary rows
    summaryRow.innerHTML = `<td colspan='12' class='summary-header'>Summary Report of Asset:</td>`;
    tbody.appendChild(summaryRow);

    // Create a row for asset type counts and status counts
    const countsRow = document.createElement('tr');
    countsRow.classList.add('summary-row'); // Add a class to identify summary rows
    let assetCountsHTML = "Asset Type Counts:";
    for (let type in assetCounts) {
        assetCountsHTML += ` ${type}: ${assetCounts[type]} `;
    }

    let statusCountsHTML = "Status Counts:";
    for (let status in statusCounts) {
        statusCountsHTML += ` ${status}: ${statusCounts[status]} `;
    }

    countsRow.innerHTML = `<td colspan='8'>${assetCountsHTML}</td>
                           <td colspan='4'>${statusCountsHTML}</td>`;
    tbody.appendChild(countsRow);

    // Create a row for total asset value
    const totalValueRow = document.createElement('tr');
    totalValueRow.classList.add('summary-row'); // Add a class to identify summary rows
    const formattedTotalValue = totalValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    totalValueRow.innerHTML = `<td colspan='8'>Total Asset Value:</td>
                               <td colspan='4'>₱${formattedTotalValue}</td>`;
    tbody.appendChild(totalValueRow);

    // Create a row for total assets
    const totalAssetsRow = document.createElement('tr');
    totalAssetsRow.classList.add('summary-row'); // Add a class to identify summary rows
    totalAssetsRow.innerHTML = `<td colspan='8'>Total Assets:</td>
                                <td colspan='4'>${visibleCount}</td>`;
    tbody.appendChild(totalAssetsRow);

    // Add a blank row for better visibility
    const blankRow = document.createElement('tr');
    blankRow.classList.add('summary-row'); // Add a class to identify summary rows
    blankRow.innerHTML = `<td colspan='12'>&nbsp;</td>`; // Use &nbsp; for a blank row
   
}</script>

<style>
.summary-header {
    text-align: center; /* Center the text */
    background-color: #54B4D3 !important; /* Set background color to #54B4D3 */
    font-weight: bold; /* Make the text bold */
    padding: 10px; /* Add some padding for better appearance */
    font-size: 1.2em; /* Increase font size for emphasis */
	    color: white; /* Change text color to white for better contrast */
}
/* General Table Styles */
#assetsTable {
    border-collapse: collapse;
    width: 100%;
}

/* Header Styles */
#assetsTable th {
    background-color: #14A44D; /* Updated header background color */
    color: white; /* White text for better visibility */
    padding: 10px; /* Padding for better spacing */
    text-align: left; /* Left align text */
}

/* Table Cell Styles */
#assetsTable th, #assetsTable td {
    border: 1px solid #ddd;
    padding: 8px;
    transition: transform 0.2s ease; /* Smooth transition for scaling */
}

/* Total Row Styles */
.total-row {
    background-color: #54B4D3; /* Background color for total row */
    color: white; /* Text color for total row */
    font-weight: bold;
    text-align: center;
    border-top: 2px solid #000;
    padding: 10px;
}

.total-row:hover {
    background-color: #66C7F5; /* Lighter blue on hover */
}

/* Counts Row Styles */
.counts-row {
    background-color: #14A44D; /* Background color for counts row */
    color: white; /* Text color for counts row */
    font-weight: bold;
    text-align: center;
}

.counts-row:hover {
    background-color: #2E865F; /* Darker green on hover */
}

/* Hover Effect for Table Rows */
#assetsTable tr {
	 cursor: pointer; /* Change cursor to hand on hover */
    transition: transform 0.2s ease; /* Smooth transition for scaling */
}

#assetsTable tr:hover {
    background-color: #54B4D3; /* Green background on hover */
    color: white; /* White text color on hover */
    transform: scale(1.00); /* Slightly enlarge the row */
}
</style>
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
        <th style="text-align: center; vertical-align: middle; padding-bottom: 15px;">Date Updated and User</th>	
        <th style="text-align: center; vertical-align: middle; padding-bottom: 15px;">Documents</th>
        
    </tr>
                        </thead>
                        <tbody>
    <?php
    $sql = "SELECT * FROM assets";
    $result = $mysqli->query($sql);
	$totalValue = 0; // Initialize total value
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $sql2 = "SELECT * FROM users WHERE user_id = '" . $row["user_id"] . "'";
            $result2 = $mysqli->query($sql2);
            $row2 = $result2->fetch_assoc();
			$fullName = $row2['firstname'] . " " . $row2['lastname'];
            $param = $row["documents"];
            $array = explode(",", $param);
			$totalValue += intval($row["price_value"]); // Sum up price_value

            echo "<tr style='text-align: center;'>";
            echo "<tr style='text-align: center;' onclick='showDetails(" . json_encode(array_merge($row, [
        'firstname' => $row2['firstname'],
        'lastname' => $row2['lastname']
    ])) . ")'>";
            echo "<td style='font-family: consolas;'>" . $row["asset_tag"] . "</td>";
			echo "<td>" . $row["iboss_tag"] . "</td>";
            echo "<td>" . $row["asset_type"] . "</td>";
            echo "<td>" . $row["brand"] . "</td>";
            echo "<td>" . $row["model"] . "</td>";
            echo "<td>" . $row["equipment_name"] . "</td>";
            echo "<td>" . $row["serial_number"] . "</td>";
            
            echo "<td>" . $row["date_acquired"] . "</td>";
            echo "<td>" . number_format(intval($row["price_value"]), 2) . "</td>";
            echo "<td>" . $row["issued_to"] . "</td>";
            echo "<td>" . $row["location_asset"] . "</td>";
			echo "<td>" . $row["status"] . "</td>";
            echo "<td>" . $row["remarks"] . "</td>";
            echo "<td>" . $row["updated_at"] . " by " . $row2["firstname"] . " " . $row2["lastname"] . "</td>";

                                    // Documents Section
                                    echo "<td>";
                                    if (!empty($param)) {
                                        foreach ($array as $document) {
                                            echo "<a href='uploads/$document' class='btn btn-sm btn-outline-secondary' target='_blank'>$document</a><br>";
                                        }
                                    } else {
                                        echo "No Documents";
                                    }
                                    echo "</td>";

                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='100%' style='text-align: center;'>No Data Available</td></tr>";
                            }
							
							// Display total asset value
// Pass the total value to JavaScript
echo "<script>updateTotalValue($totalValue);</script>";
                            ?>
							
                        </tbody>
						
                    </table>
					
                </div>
               
            </div>
        </div>
    </div>
	<br><br>
	<div id="emailFormContainer" style="display: none;">
<div class="container-fluid" style="background-color: #f2f2f2; padding: 20px; border: 1px solid #ddd; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
    <h3>Send Search Results via Email</h3>
    <form id="emailForm" action="send_email.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="recipients">Recipient Emails:</label>
            <div id="recipients-container">
                <div class="input-group">
                    <input type="email" class="form-control" id="recipient-1" name="recipients[]" placeholder="e.g. recipient1@example.com" required>
                   
                </div>
            </div>
            <button type="button" class="btn btn-primary mt-3" onclick="addRecipient()">+</button>
        </div>
        <div class="form-group mt-3">
            <label for="customMessage">Custom Message (optional):</label>
            <textarea class=" form-control" id="customMessage" name="customMessage" rows="4" placeholder="Add your custom message here..."></textarea>
        </div>
        <div class="form-group mt-3">
            <label for="attachment">Attach a File (optional):</label>
            <input type="file" class="form-control" id="attachment" name="attachment">
        </div>
        <button type="button" class="btn btn-primary mt-3" onclick="sendEmail()">Send Email</button>
    </form>
</div>
</div>

<script>
function showDetails(asset) {
    // Populate modal fields with asset data
    document.getElementById("modalAPACTag").textContent = asset.asset_tag;
    document.getElementById("modalIBOSSTag").textContent = asset.iboss_tag;
    document.getElementById("modalAssetType").textContent = asset.asset_type;
    document.getElementById("modalBrand").textContent = asset.brand;
    document.getElementById("modalModel").textContent = asset.model;
    document.getElementById("modalEquipmentName").textContent = asset.equipment_name;
    document.getElementById("modalSerialNumber").textContent = asset.serial_number;
    document.getElementById("modalDateAcquired").textContent = asset.date_acquired;
    document.getElementById("modalPriceValue").textContent = asset.price_value;
    document.getElementById("modalIssuedTo").textContent = asset.issued_to;
    document.getElementById("modalLocation").textContent = asset.location_asset;
    document.getElementById("modalStatus").textContent = asset.status;
    document.getElementById("modalRemarks").textContent = asset.remarks;
    document.getElementById("modalLastUpdated").textContent = 
        asset.updated_at + " by " + asset.firstname + " " + asset.lastname; // Now this will work

    // Populate document previews
    const documentPreviewsContainer = document.getElementById("modalDocumentPreviews");
    documentPreviewsContainer.innerHTML = ''; // Clear previous content

    // Assuming asset.documents is a comma-separated string of document names
    const documents = asset.documents.split(',').map(doc => doc.trim()).filter(doc => doc); // Split and trim documents
    if (documents.length === 0) {
        // No documents found
        const noDocumentsText = document.createElement('p');
        noDocumentsText.textContent = "No attached Documents";
        noDocumentsText.style.textAlign = 'center'; // Center the text
        noDocumentsText.style.fontStyle = 'italic'; // Optional styling
        documentPreviewsContainer.appendChild(noDocumentsText);
    } else {
        // Create document previews
        documents.forEach(function(documentLink) {
            // Create a link element
            const linkElement = document.createElement('a');
            linkElement.href = 'uploads/' + documentLink; // Link to the document
            linkElement.target = '_blank'; // Open in a new tab

            // Create an image element
            const imgElement = document.createElement('img');
            imgElement.src = 'uploads/' + documentLink; // Assuming documents are stored in an 'uploads' directory
            imgElement.alt = documentLink;
            imgElement.style.width = '100px'; // Set a width for the image
            imgElement.style.margin = '10px'; // Add some margin for spacing

            // Append the image to the link
            linkElement.appendChild(imgElement);
            
            // Append the link to the previews container
            documentPreviewsContainer.appendChild(linkElement);
        });
    }

    // Show the modal
    var myModal = new bootstrap.Modal(document.getElementById('detailsModal'), {
        keyboard: false
    });
    myModal.show();
}
    </script>

    <script>
function printTable() {
    var table = document.getElementById("assetsTable");
    var newWin = window.open("", "_blank");
    var newDoc = newWin.document;
    newDoc.open();

    // Writing the structure of the new document for printing
    newDoc.write('<html><head><title>Print</title>');
    newDoc.write('<style>');
    newDoc.write('body { font-family: sans-serif; text-align: center; }'); // Center the content
    newDoc.write('@media print {');
    newDoc.write('.actions, th:nth-child(14), td:nth-child(14) { display: none; }');
    newDoc.write('.actions, th:nth-child(15), td:nth-child(15) { display: none; }'); // Hide the 'Actions' column
    newDoc.write('table { width: 100%; border-collapse: collapse; }'); // Make table full width
    newDoc.write('table, th, td { border: 1px solid black; }'); // Add borders for table
    newDoc.write('th, td { padding: 10px; text-align: left; }'); // Padding for readability
    newDoc.write('h1 { font-size: 24px; margin: 0; }'); // Remove margin from the title
    newDoc.write('img { margin: 0; padding: 0; display: block; }'); // Remove margin and padding, set display to block
    newDoc.write('thead { background-color: #f2f2f2; }'); // Light background for the header
    newDoc.write('tr:nth-child(even) { background-color: #f9f9f9; }'); // Alternate row colors
newDoc.write('.actions, td:nth-child(7) { max-width: 130px; overflow: hidden; white-space: normal; word-wrap: break-word; }'); // Serial Number
    newDoc.write('.actions, td:nth-child(8) { max-width: 70px; }'); // Location
newDoc.write('.actions, td:nth-child(9) { max-width: 80px; overflow: hidden; white-space: normal; word-wrap: break-word; }'); // Price
newDoc.write('.actions, td:nth-child(10) { max-width: 80px; overflow: hidden; white-space: normal; word-wrap: break-word; }'); // Issue
newDoc.write('.actions, td:nth-child(11) { max-width: 50px; overflow: hidden;  }');  Location
    newDoc.write('.actions, td:nth-child(12) { max-width: 80px; }'); // Status
    newDoc.write('.actions, td:nth-child(13) { max-width: 100px; overflow: hidden; white-space: normal; word-wrap: break-word; }');// Remarks
    newDoc.write('@page { margin: 10mm; }'); // Page margins for printing
    newDoc.write('}</style>');
    newDoc.write('</head><body>');

    // Adding the title and the table to the new window
    var img = document.createElement('img');
    img.src = 'http://localhost/dashboard/PRINT.png'; // Use an absolute URL for the image
    newDoc.write(img.outerHTML);
    newDoc.write('<h1>APAC Asset List</h1>');

    // Clone the original table to modify the status colors
    var clonedTable = table.cloneNode(true);
    var statusCells = clonedTable.querySelectorAll("td:nth-child(12)"); // Assuming Status is the 12th column

statusCells.forEach(cell => {
        switch (cell.textContent.trim()) {
            case "Deployed":
                cell.style.backgroundColor = "#14A44D"; // Green
                cell.style.color = "white"; // White text
                break;
            case "Spare":
                cell.style.backgroundColor = "#54B4D3"; // Blue
                cell.style.color = "white"; // White text
                break;
            case "Defective":
                cell.style.backgroundColor = "#E4A11B"; // Yellow
                cell.style.color = "white"; // White text
                break;
            case "Faulty":
                cell.style.backgroundColor = "#DC4C64"; // Red
                cell.style.color = "white"; // White text
                break;
            default:
                cell.style.backgroundColor = ""; // Default color
                cell.style.color = ""; // Default text color
        }
    });
    newDoc.write(clonedTable.outerHTML); // Print the modified table
    newDoc.write('</body></html>');
    newDoc.close();

    // Triggering the print and closing the window after printing
    newWin.onafterprint = function() {
        newWin.close();
    };
    newWin.print();
}
    </script>

    <script>
    let recipientCount = 1;

    function addRecipient() {
    recipientCount++;
    const container = document.getElementById('recipients-container');
    const inputGroup = document.createElement('div');
    inputGroup.className = 'input-group';
    const input = document.createElement('input');
    input.type = 'email';
    input.className = 'form-control';
    input.id = `recipient-${recipientCount}`;
    input.name = 'recipients[]';
    input.placeholder = 'e.g. recipient@example.com';
    input.required = true;
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-danger';
    button.onclick = removeRecipient;
    button.innerHTML = '-';
    inputGroup.appendChild(input);
    inputGroup.appendChild(button);
    container.appendChild(inputGroup);
}

  function removeRecipient(event) {
    const button = event.target;
    const inputGroup = button.parentNode;
    inputGroup.remove();
    recipientCount--;
}

    function sendEmail() {
        var table = document.getElementById("assetsTable").outerHTML;
        var searchQuery = document.getElementById('searchInput').value;
        var assetType = document.getElementById('assetType').value;
        var recipients = [];
        var recipientInputs = document.getElementsByName('recipients[]');
        for (var i = 0; i < recipientInputs.length; i++) {
            recipients.push(recipientInputs[i].value);
        }
        var customMessage = document.getElementById('customMessage').value;
        var attachment = document.getElementById('attachment').files[0]; // Get the file from the input field

        // Confirmation dialog
        if (!confirm("Are you sure you want to send this email?")) {
            return; // Exit the function if the user cancels
        }

        // Show loading message
        var loadingMessage = document.createElement("div");
        loadingMessage.id = "loadingMessage";
        loadingMessage.innerHTML = "Sending email... Please wait.";
        loadingMessage.style.position = "fixed";
        loadingMessage.style.top = "50%";
        loadingMessage.style.left = "50%";
        loadingMessage.style.transform = "translate(-50%, -50%)";
        loadingMessage.style.backgroundColor = "#fff";
        loadingMessage.style.border = "1px solid #ddd";
        loadingMessage.style.padding = "20px";
        loadingMessage.style.zIndex = "1000";
        document.body.appendChild(loadingMessage);

        // Create form data
        var formData = new FormData();
        formData.append('tableData', table);
        formData.append('searchQuery', searchQuery);
        formData.append('assetType', assetType);
        formData.append('recipients', recipients.join(','));
        formData.append('customMessage', customMessage);

        // Append the file to the form data if there is a file
        if (attachment) {
            formData.append('attachment', attachment);
        }

        // Use fetch to send form data to PHP
        fetch('send_email.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Remove loading message
            document.body.removeChild(loadingMessage);

            if (data.success) {
                alert('Email sent successfully!');
            } else {
                alert('Failed to send email: ' + data.message);
            }
        })
        .catch(error => {
            // Remove loading message
            document.body.removeChild(loadingMessage);
            alert('Error: ' + error);
        });
    }

    document.getElementById('loadingSpinner').style.display = "block";
</script>
<script>
function saveAsExcel() {
 if (confirm("Are you sure you want to save the table data as a CSV file?")) {
        try {
            var table = document.getElementById("assetsTable");
            var csv = [];

            // Get the table headers
            var headers = table.getElementsByTagName('th');
            var headerRow = [];
            for (var i = 0; i < headers.length; i++) {
                headerRow.push('"' + headers[i].textContent + '"');
            }
            csv.push(headerRow.join(','));

            // Get the table data
            var rows = table.getElementsByTagName('tr');
            for (var i = 1; i < rows.length; i++) {
                var row = rows[i];
                var cells = row.getElementsByTagName('td');
                var rowArray = [];
                for (var j = 0; j < cells.length; j++) {
                    var cellText = cells[j].textContent;
                    // Check if the cell text contains a comma or double quote
                    if (cellText.indexOf(',') !== -1 || cellText.indexOf('"') !== -1) {
                        // If it does, wrap the text in double quotes and escape any existing double quotes
                        rowArray.push('"' + cellText.replace(/"/g, '""') + '"');
                    } else {
                        rowArray.push(cellText);
                    }
                }
                csv.push(rowArray.join(','));
            }

            // Create a blob and save it as a CSV file
            var csvString = csv.join('\n');
            var blob = new Blob([csvString], {
                type: 'text/csv'
            });
            if (window.navigator.msSaveBlob) {
                // For IE and Edge
                window.navigator.msSaveBlob(blob, "assets_table.csv");
            } else {
                // For other browsers
                saveAs(blob, "assets_table.csv");
            }
        } catch (error) {
            console.error("Error saving CSV file:", error);
            alert("An error occurred while saving the file. Check the console for details.");
        }
    }
}
 </script>
 
<div id="loadingSpinner" style="display:none;">
    <img src="spinner.gif" alt="Loading..." />
</div>
</body>
</html>
			