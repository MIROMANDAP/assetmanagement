
<?php
include 'config.php';
header('Content-Type: text/html; charset=utf-8');
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
	
}
include 'nav.php';
$username = htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']);
// Fetch asset types from the database
$asset_types = [];
$sql = "SELECT type_name FROM asset_types";
if ($result = $mysqli->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $asset_types[] = $row['type_name'];
    }
    $result->free();
}


?>




<!DOCTYPE html>
<html lang="en">

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
   
    <link href="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.css" rel="stylesheet">
    <script src="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.js"></script>
    <script src="https://unpkg.com/bootstrap-table@1.22.1/dist/extensions/print/bootstrap-table-print.min.js"></script>
	

    <script>
        function confirmAction() {
            return confirm("Are you sure?");
        }
    </script>
</head>

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
			<!-- Add this to your dropdown menu -->
			
<li><a class="dropdown-item" href="AF.php">Print Acknowledgement Receipt</a></li>
			
            <li><a class="dropdown-item" href="#" onclick="toggleEmailForm(); return false;">Send Email</a></li>
			
        </ul>
    </div>
</div>

<!-- Preview Modal Structure -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Preview Acknowledgement Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="jobTitleInput" class="form-label">Job Title</label>
                    <input type="text" class="form-control" id="jobTitleInput" placeholder="Enter Job Title">
                    <div id="jobTitleError" class="text-danger" style="display: none;">Please enter a job title.</div> <!-- Error message -->
                </div>
                <div class="mb-3">
                    <label for="supervisorInput" class="form-label">Supervisor/Manager</label>
                    <input type="text" class="form-control" id="supervisorInput" placeholder="Enter Supervisor/Manager Name">
                    <div id="supervisorError" class="text-danger" style="display: none;">Please enter a supervisor/manager name.</div> <!-- Error message -->
                </div>
                <!-- Display the username -->
                <div class="mb-3">
                    <strong>Issued By:</strong> <?php echo $username; ?>
                </div>
                <table class="table table-striped" id="previewTable">
                    <thead>
                        <tr>
                            <th>APAC Tag</th>
                            <th>IBOSS Tag</th>
                            <th>Asset Type</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Equipment Name</th>
                            <th>Serial Number</th>
                            <th>Date Acquired</th>
                            <th>Price Value</th>
                            <th>Issued To</th>
                            <th>Location</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rows will be added dynamically -->
                    </tbody>
                </table>
                <!-- Large Image Display -->
                <div id="largeImageContainer" style="display: none; text-align: center;">
                    <img id="largeImage" src="" alt="Large Image" style="max-width: 100%; height: auto;" onclick="closeLargeImage()">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printAcknowledgementReceipt()">Print Acknowledgement Receipt</button>
            </div>
        </div>
    </div>
</div>

<script>
var username = "<?php echo $username; ?>"; // Embed PHP variable into JavaScript

function printAcknowledgementReceipt() {
    var jobTitleInput = document.getElementById("jobTitleInput").value.trim(); // Get the job title input
    var supervisorInput = document.getElementById("supervisorInput").value.trim(); // Get the supervisor input
    var jobTitleError = document.getElementById("jobTitleError"); // Get the job title error message element
    var supervisorError = document.getElementById("supervisorError"); // Get the supervisor error message element

    // Check if the job title input is empty
    if (jobTitleInput === "") {
        jobTitleError.style.display = "block"; // Show the error message
        return; // Exit the function
    } else {
        jobTitleError.style.display = "none"; // Hide the error message if input is valid
    }

    // Check if the supervisor input is empty
    if (supervisorInput === "") {
        supervisorError.style.display = "block"; // Show the error message
        return; // Exit the function
    } else {
        supervisorError.style.display = "none"; // Hide the error message if input is valid
    }
  var currentDate = new Date();
        var options = { year: 'numeric', month: 'long', day: 'numeric' };
        var formattedDate = currentDate.toLocaleDateString('en-US', options);
    var printImageSrc = "PRINT.png";
    var img = new Image();
    img.src = printImageSrc;

    img.onload = function() {
        var previewTable = document.getElementById("previewTable");
        var rows = previewTable.getElementsByTagName("tr");
        var employeeName = '';

        // Assuming the first row of the table contains the relevant data
        if (rows.length > 1) { // Check if there are any rows
            var lastRowCells = rows[rows.length - 1].cells; // Get the last data row
            employeeName = lastRowCells[9].textContent; // Assuming employee name is in the 10th column
        } // Assuming employee name is in the 10th column
        var jobTitle = document.getElementById("jobTitleInput").value;
        var supervisorName = document.getElementById("supervisorInput").value; // Get the supervisor name
        var printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
            <head>
                <title>Acknowledgement Receipt</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; padding-bottom: 80px; }
                    .header { text-align: center; margin-bottom: 20px; }
                    .header img { padding-top: 0px; padding-bottom: 0px; max-width: 100%; }
                    table { width: 100%; border-collapse: collapse; table-layout: auto; margin-bottom: 10px; margin-top: 0; }
                    th, td { border: 1px solid black; padding: 4px; text-align: left; overflow-wrap: break-word; font-size: 10px; }
                    th { background-color: #f2f2f2; font-size: 10px; }
                    tbody tr { page-break-inside: avoid; }
                    .page-break { page-break-before: always; }
                    .signature-section { margin-top: 100px; }
                    .signature-line { border-top: 1px solid black; width: 200px; margin-top: 10px; }
                    .certification { margin-top: 10px; text-align: center; }
                    .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 9px; padding: 5px 0; border-top: 1px solid black; }
                    .address { margin-top: 5px; text-align: center; }
                    .address-line { border-top: 1px solid black; width: 100%; margin-top: 1px; display: inline-block; }
                    .footer-space { height: 60px; }
                    .underline { text-decoration: underline; font-weight: bold; }
                </style>
            </head>
            <body>
                <div class="header">
                    <img src="${printImageSrc}" alt="Header Image">
                    <h2>ACKNOWLEDGEMENT RECEIPT OF IBOSS ASIA'S IT ASSET</h2>
                </div>
                <div class="certification">
                    <p>
                        Employee Name: <span class="underline">${employeeName}</span> &nbsp; 
                        Job Title: <span class="underline">${jobTitleInput}</span> &nbsp; 
                         Date: <span class="underline">${formattedDate}</span>
                    </p>
                </div>
                <p>I hereby acknowledge receipt and assignment of the following company IT Tools. I agree to keep the equipment in good condition and to report any loss or damage immediately. I further agree to use them for work-related purposes only.</p>
                
                <table>
                    <thead>
                        <tr>
                            <th>APAC Tag</th>
                            <th>IBOSS Tag</th>
                            <th>Asset Type</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Equipment Name</th>
                            <th>Serial Number</th>
                            <th>Date Acquired</th>
                            <th>Price Value</th>
                            <th>Issued to</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
        `);

        for (var i = 1; i < rows.length; i++) {
            var row = rows[i];
            var cells = row.cells;

            printWindow.document.write(`
                <tr>
                    <td>${cells[0].textContent}</td>
                    <td>${cells[1].textContent}</td>
                    <td>${cells[2].textContent}</td>
                    <td>${cells[3].textContent}</td>
                    <td>${cells[4].textContent}</td>
                    <td>${cells[5].textContent}</td>
                    <td>${cells[6].textContent}</td>
                    <td>${cells[7].textContent}</td>
                    <td>${cells[ 8].textContent}</td>
                    <td>${cells[9].textContent}</td>
                    <td>${cells[10].textContent}</td>
                </tr>
            `);
        }

      

        printWindow.document.write(`
                    </tbody>
                </table>
                <p style="margin-top: 0;">In the event of resignation/termination of employment, I will return all company properties specified above or on the attached sheet upon my last day of work or as specified by my supervisor. If any property is not returned, I authorize a reasonable value for such equipment to be deducted from my final paycheck.</p>
                <div class="signature-section">
                    <div style="display: flex; justify-content: space-between;">
                        <div style="position: relative; text-align: center;">
                            <div class="signature-line"></div>
                            <p style="position: absolute; top: -10px; left: 0; right: 0; margin: 0;">${employeeName}</p>
                            <p>Employee Signature</p>
                            <p>Date: ${formattedDate}</p>
                        </div>
                        <div style="position: relative; text-align: center;">
                            <p style="position: absolute; top: -10px; left: 0; right: 0; margin: 0;">${username}</p>
                            <div class="signature-line"></div>
                            <p>Issued by</p>
                            <p>Date: ${formattedDate}</p>
                        </div>
                        <div style="position: relative; text-align: center;">
                            <p style="position: absolute; top: -10px; left: 0; right: 0; margin: 0;">${supervisorName}</p>
                            <div class="signature-line"></div>
                            <p>Supervisor/Manager</p>
                            <p>Date:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                        </div>
                    </div>
                </div>
                <div class="footer page-break">
                    <div class="address-line"></div>
                    <div class="address">Unit 1F Business Center 9, PhilExcel Business Park, Clark Freeport Zone, 2023 | 0917 652 6327 / 0998 594 4722</div>
                </div>
            </body>
            </html>
        `);

        printWindow.document.close();
        printWindow.print();
    };

    img.onerror = function() {
        console.error("Failed to load image: " + printImageSrc);
    };
}
</script>


 <script>
 function showPreview() {
    var table = document.getElementById("assetsTable");
    var selectedEquipment = [];

    for (var i = 1; i < table.rows.length; i++) {
        if (table.rows[i].style.display !== 'none') {
            var cells = table.rows[i].cells;

            if (cells.length >= 11) {
                var equipmentData = {
                    apacTag: cells[0].textContent.trim(),
                    ibossTag: cells[1].textContent.trim(),
                    assetType: cells[2].textContent.trim(),
                    brand: cells[3].textContent.trim(),
                    model: cells[4].textContent.trim(),
                    equipmentName: cells[5].textContent.trim(),
                    serialNumber: cells[6].textContent.trim(),
                    dateAcquired: cells[7].textContent.trim(),
                    priceValue: cells[8].textContent.trim(),
                    issuedTo: cells[9].textContent.trim(),
                    location: cells[10].textContent.trim()
                };
                selectedEquipment.push(equipmentData);
            }
        }
    }

    // Populate the preview table
    var previewTableBody = document.getElementById("previewTable").getElementsByTagName("tbody")[0];
    previewTableBody.innerHTML = ""; // Clear previous content

    selectedEquipment.forEach(function(item) {
        var row = previewTableBody.insertRow();
        for (var key in item) {
            var cell = row.insertCell();
            cell.textContent = item[key];
        }
        var actionCell = row.insertCell();
        var removeButton = document.createElement("button");
        removeButton.textContent = "Remove";
        removeButton.className = "btn btn-danger btn-sm";
        removeButton.onclick = function() {
            row.remove();
        };
        actionCell.appendChild(removeButton);
    });

    // Show the modal
    var myModal = new bootstrap.Modal(document.getElementById('previewModal'), {
        keyboard: false
    });
    myModal.show();
}
</script>
<style>
.modal-body {
    max-height: 70vh; /* Set a maximum height for the modal body */
    overflow-y: auto; /* Enable scrolling if content exceeds max height */
}
/* Set a specific width for the modal */
.modal-dialog {
    max-width: 1200px; /* Set your desired width here */
    width: 100%; /* Ensure it takes the full width up to max-width */
}
    /* Other styles */
    .address { 
        font-size: 12px; 
        margin-top: 20px; 
        text-align: center; /* Center the address */
		
    }
	
	.custom-modal {
    max-width: 400px; /* Adjust the width as needed */
}
</style>
<!-- Modal Structure -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered modal-sm custom-modal"> <!-- Use modal-dialog-centered for vertical centering -->
        <div class="modal-content" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);">
<div class="modal-header" style="background-color: #007bff; color: white; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h5 class="modal-title" id="detailsModalLabel" style="margin: 0; text-align: center; color: white;">Asset Details</h5> <!-- Set title color to white -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 20px; text-align: left;"> <!-- Center the text here -->
                <div style="margin-bottom: 15px;">
                    <strong>APAC Tag:</strong> <span id="modalAPACTag"></span>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>IBOSS Tag:</strong> <span id="modalIBOSSTag"></span>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Asset Type:</strong> <span id="modalAssetType"></span>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Brand:</strong> <span id="modalBrand"></span>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Model:</strong> <span id="modalModel"></span>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Equipment Name:</strong> <span id="modalEquipmentName"></span>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Serial Number:</strong> <span id="modalSerialNumber"></span>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Date Acquired:</strong> <span id="modalDateAcquired"></span>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Price Value:</strong> <span id="modalPriceValue"></span>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Issued To:</strong> <span id="modalIssuedTo"></span>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Location:</strong> <span id="modalLocation"></span>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Status:</strong> <span id="modalStatus"></span>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Remarks:</strong> <span id="modalRemarks"></span>
                </div>
                <!--<div style="margin-bottom: 15px;">
                    <strong>Last Updated:</strong> <span id="modalLastUpdated"></span>
                </div>-->
                
                <hr style="margin: 20px 0;">
                <h6 style="margin-bottom: 10px;">Documents:</h6>
                <div id="modalDocumentPreviews" style="text-align: center;"></div> <!-- Center text in document previews -->
            </div> 
            <div class="modal-footer" style="border-top: none; justify-content: center;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 800px; width: 100%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Preview Acknowledgement Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
			
                <div class="mb-3">
                    <label for="assetTagInput" class="form-label">Search Asset Tag</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="assetTagInput" placeholder="Enter Asset Tag">
                        <button class="btn btn-primary" id="searchAssetButton" onclick="searchAsset()">Add</button>
                    </div>
                </div>
                <table class="table table-striped" id="previewTable">
                    <thead>
                        <tr>
                            <th>APAC Tag</th>
                            <th>IBOSS Tag</th>
                            <th>Asset Type</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Equipment Name</th>
                            <th>Serial Number</th>
                            <th>Date Acquired</th>
                            <th>Price Value</th>
                            <th>Issued To</th>
                            <th>Location</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rows will be added dynamically based on search results -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printAcknowledgementReceipt()">Print Acknowledgement Receipt</button>
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
        Available: 0,
        Faulty: 0,
        Decommission: 0
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

        // If an asset type is selected, filter based on that type
        var assetType = cells[2].textContent; // Assuming Asset Type is in the 3rd column
        var assetTypeMatches = (selectedAssetType === '' || selectedAssetType === assetType);

        // If no search query is provided, just check the asset type
        if (isSearchEmpty) {
            found = assetTypeMatches; // Show rows that match the asset type
        } else {
            // If a search query is provided, check both the query and asset type
            for (var j = 0; j < cells.length; j++) {
                if ([0, 1, 2, 3, 4, 5, 6, 9, 10, 11].includes(j)) {
                    var name = cells[j].textContent || cells[j].innerText;
                    if (name.toLowerCase().indexOf(query.toLowerCase()) > -1 && assetTypeMatches) {
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
                               <td colspan='4'>PHP ${formattedTotalValue}</td>`;
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
     <!--    <th style="text-align: center; vertical-align: middle; padding-bottom: 15px;">Date Updated and User</th>	-->
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
           // echo "<td>" . $row["updated_at"] . " by " . $row2["firstname"] . " " . $row2["lastname"] . "</td>";

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
   // document.getElementById("modalLastUpdated").textContent = 
   //     asset.updated_at + " by " + asset.firstname + " " + asset.lastname; // Now this will work

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
    newDoc.write('.actions, th:nth-child(14), td:nth-child(14) { display: none; }'); // Hide the 'Actions' column
    newDoc.write('.actions, th:nth-child(15), td:nth-child(15) { display: none; }'); // Hide the 'Actions' column
    newDoc.write('table { width: auto; max-width: 100%; margin: 0 auto; border-collapse: collapse; overflow-x: auto; }'); // Allow horizontal scrolling
    newDoc.write('table, th, td { border: 1px solid black; }'); // Add borders for table
    newDoc.write('th, td { padding: 5px; text-align: left; font-size: 10px; }'); // Reduced padding and font size

    // Set fixed widths for specific columns (adjust as necessary)
    newDoc.write('th:nth-child(1), td:nth-child(1) { width: 80px; }'); // APAC Tag
    newDoc.write('th:nth-child(2), td:nth-child(2) { width: 80px; }'); // IBOSS Tag
    newDoc.write('th:nth-child(3), td:nth-child(3) { width: 100px; }'); // Asset Type
    newDoc.write('th:nth-child(4), td:nth-child(4) { width: 80px; }'); // Brand
    newDoc.write('th:nth-child(5), td:nth-child(5) { width: 100px; }'); // Model
    newDoc.write('th:nth-child(6), td:nth-child(6) { width: 120px; }'); // Equipment Name
    newDoc.write('th:nth-child(7), td:nth-child(7) { width: 100px; }'); // Serial Number
    newDoc.write('th:nth-child(8), td:nth-child(8) { width: 80px; }'); // Date Acquired
    newDoc.write('th:nth-child(9), td:nth-child(9) { width: 80px; }'); // Price Value
    newDoc.write('th:nth-child(10), td:nth-child(10) { width: 80px; }'); // Issued To
    newDoc.write('th:nth-child(11), td:nth-child(11) { width: 80px; }'); // Location
    newDoc.write('th:nth-child(12), td:nth-child(12) { width: 80px; }'); // Status
    newDoc.write('th:nth-child(13), td:nth-child(13) { width: 100px; }'); // Remarks

    newDoc.write('h1 { font-size: 24px; margin: 0; }'); // Remove margin from the title
    newDoc.write('img { display: block; margin: 0 auto; max-width: 100%; height: auto; }'); // Center the image
    newDoc.write('thead { background-color: #f2f2f2; }'); // Light background for the header
    newDoc.write('tr:nth-child(even) { background-color: #f9f9f9; }'); // Alternate row colors
    newDoc.write('@page { margin: 10mm; }'); // Page margins for printing
    newDoc.write('}</style>');
    newDoc.write('</head><body>');

    // Adding the title and the table to the new window
    var img = new Image();
    img.src = 'PRINT.png';
    img.onload = function() {
        newDoc.write('<div>' + img.outerHTML + '</div>'); // Center the image
         newDoc.write('<h1>APAC Asset List - ' + new Date().toLocaleDateString() + '</h1>'); // Add date next to the title
     // Clone the original table to modify the status colors
        var clonedTable = table.cloneNode(true);
        var statusCells = clonedTable.querySelectorAll("td:nth-child(12)"); // Assuming Status is the 12th column

        statusCells.forEach(cell => {
            switch (cell.textContent.trim()) {
                case "Deployed":
                    cell.style.backgroundColor = "#14A44D"; // Green
                    cell.style.color = "white"; // White text
                    break;
                case "Available":
                    cell.style.backgroundColor = "#54B4D3"; // Blue
                    cell.style.color = "white"; // White text
                    break;
                case "Decommission":
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
    };

    // If the image fails to load, you can handle it here
    img.onerror = function() {
        newDoc.write('<h1>APAC Asset List</h1>');
        newDoc.write(clonedTable.outerHTML); // Print the modified table without the image
        newDoc.write('</body></html>');
        newDoc.close();
        newWin.print();
    };
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
                if (row.style.display !== 'none') { // Check if the row is visible
                    var cells = row.getElementsByTagName('td');
                    var rowArray = [];
                    var isEmptyRow = true; // Flag to check if the row is empty

                    for (var j = 0; j < cells.length; j++) {
                        var cellText = cells[j].textContent.trim(); // Trim whitespace
                        if (cellText) {
                            isEmptyRow = false; // Found non-empty cell
                        }
                        // Check if the cell text contains a comma or double quote
                        if (cellText.indexOf(',') !== -1 || cellText.indexOf('"') !== -1) {
                            // If it does, wrap the text in double quotes and escape any existing double quotes
                            rowArray.push('"' + cellText.replace(/"/g, '""') + '"');
                        } else {
                            rowArray.push(cellText);
                        }
                    }

                    // Only add non-empty rows
                    if (!isEmptyRow) {
                        csv.push(rowArray.join(','));
                    }
                }
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
 
 
 <?php include 'footer.php'; ?>
<div id="loadingSpinner" style="display:none;">
    <img src="spinner.gif" alt="Loading..." />
</div>
</body>
</html>
			