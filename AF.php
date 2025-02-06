
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
<meta charset="UTF-8">

<head>
    
    <title>Asset Management System</title>
    <link rel="icon" type="image/x-icon" href="logo small.png">
    <link rel="stylesheet" href="style.css">
	
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

<body>
<!-- Spinner Modal -->
<div class="modal fade" id="spinnerModal" tabindex="-1" aria-labelledby="spinnerModalLabel" aria-hidden="true" style="z-index: 2000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" > <!-- Set a lower z-index -->
            <div class="modal-body text-center">
                <img src="spinner.gif" alt="Loading..." style="width: 100px; height: auto;"/>
                <p>Sending email, please wait...</p>
            </div>
        </div>
    </div>
</div>
<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true" style="z-index: 1999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0"> <!-- Card-like appearance -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="confirmationModalLabel"style="color: black;">Confirm Action</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card p-3"> <!-- Card container -->
                    <h6 class="text-center">Do you want to proceed with sending the email?</h6>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmSendEmail">Proceed</button>
            </div>
        </div>
    </div>
</div>
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
                <h1>Print Acknowledgement Receipt</h1>
				<div class="action-button-container">
    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle action-button" type="button" id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-file-alt"></i> <!-- Replace with your desired Font Awesome icon -->
        </button>
        <ul class="dropdown-menu" aria-labelledby="actionDropdown">
          
			
<li><a class="dropdown-item" href="#" onclick="showPreview()">Send And Generate Acknowledgement Receipt</a></li>
<li><a class="dropdown-item" href="file_browser.php">View Printed Acknowledgement Receipt</a></li>
			
			
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
    <label for="emailInput" class="form-label">Recipient Email</label>
    <input type="email" class="form-control" id="emailInput" placeholder="Enter recipient email">
    <div id="emailError" class="text-danger" style="display: none;">Please enter a valid email address.</div>
</div>
                <div class="mb-3">
    <label for="jobTitleInput" class="form-label">Job Title</label>
    <input type="text" class="form-control" id="jobTitleInput" placeholder="Enter Job Title" readonly>
    <div id="jobTitleError" class="text-danger" style="display: none;">You cannot send an email. The user doesn't have a Job Title.</div> <!-- Error message -->
</div>
                <div class="mb-3">
    <label for="supervisorInput" class="form-label">Supervisor/Manager</label>
    <select class="form-control" id="supervisorInput">
        <option value="" disabled selected>Select a Supervisor/Manager</option>
        <option value="Gerardo Batul Jr.">Gerardo Batul Jr.</option>
        <option value="Will Scola">Will Scola</option>
       
   
    </select>
    <div id="supervisorError" class="text-danger" style="display: none;">Please select a supervisor/manager name.</div> <!-- Error message -->
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
                            <th>Remarks</th>
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
				<button type="button" class="btn btn-primary" onclick="sendAcknowledgementEmail()">Send And Generate Acknowledgement Receipt</button>
				<button type="button" class="btn btn-primary" onclick="printAcknowledgementReceipt()">Generate Acknowledgement Receipt</button>
                
            </div>
        </div>
    </div>
</div>

<script>
var username = "<?php echo $username; ?>"; // Embed PHP variable into JavaScript

function printAcknowledgementReceipt() {
	alert("Please save the generated form as PDF at this location: \\\\cl-ws049\\Accountability files"); // Show alert message
    var jobTitleInput = document.getElementById("jobTitleInput").value.trim(); // Get the job title input
    var supervisorInput = document.getElementById("supervisorInput").value.trim(); // Get the supervisor input
    var jobTitleError = document.getElementById("jobTitleError"); // Get the job title error message element
    var supervisorError = document.getElementById("supervisorError"); // Get the supervisor error message element

    // Check if the job title input is empty
    if (jobTitleInput === "") {
        jobTitleError.style.display = "block"; // Show the error message
		 alert("Cannot proceed because the user does not have a valid Job title."); // Show alert message
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
                <title>${employeeName} Acknowledgement Receipt ${formattedDate}</title>
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
                    <h2>IBOSS ASIA'S IT ASSET ACCOUNTABILITY FORM</h2>
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
                            
                            <th>IBOSS Tag</th>
                            <th>Asset Type</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Equipment Description</th>
                            <th>Serial Number</th>
                            
                            <th>Price Value</th>
                           
							<th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
        `);

        for (var i = 1; i < rows.length; i++) {
            var row = rows[i];
            var cells = row.cells;

            printWindow.document.write(`
                <tr>
                    
                    <td>${cells[1].textContent}</td>
                    <td>${cells[2].textContent}</td>
                    <td>${cells[3].textContent}</td>
                    <td>${cells[4].textContent}</td>
                    <td>${cells[5].textContent}</td>
                    <td>${cells[6].textContent}</td>
                    
                    <td>${cells[ 8].textContent}</td>
                   
                    <td>${cells[11].textContent}</td>
					
					
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
                            <p>Date:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                        </div>
                        <div style="position: relative; text-align: center;">
                            <p style="position: absolute; top: -10px; left: 0; right: 0; margin: 0;">${username}</p>
                            <div class="signature-line"></div>
                            <p>Issued by</p>
                           <p>Date:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
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

function sendAcknowledgementEmail() {
      var emailInput = document.getElementById("emailInput").value.trim();
    var emailError = document.getElementById("emailError");
    var jobTitleInput = document.getElementById("jobTitleInput").value.trim();
    var jobTitleError = document.getElementById("jobTitleError");
    var supervisorInput = document.getElementById("supervisorInput").value.trim();
    var supervisorError = document.getElementById("supervisorError");

    // Validate email
    if (!validateEmail(emailInput)) {
        emailError.style.display = "block"; // Show error message
        return; // Exit the function
    } else {
        emailError.style.display = "none"; // Hide error message if input is valid
    }

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

    // Show confirmation modal
    $('#confirmationModal').modal('show');

    // Set up the event listener for the confirmation button
    document.getElementById("confirmSendEmail").onclick = function() {
        // Close the confirmation modal
        $('#confirmationModal').modal('hide');

        // Close the print acknowledgment modal if it's open
        $('#previewModal').modal('hide');

        // Show spinner modal
        $('#spinnerModal').modal('show');

        // Prepare data to send
        var jobTitle = document.getElementById("jobTitleInput").value;
        var supervisorName = document.getElementById("supervisorInput").value;

        // Gather asset details from the preview table
        var previewTable = document.getElementById("previewTable");
        var assetDetails = [];

        for (var i = 0; i < previewTable.rows.length; i++) {
            var cells = previewTable.rows[i].cells;
            if (cells.length > 0) {
                var assetDetail = {
                    ibossTag: cells[1].textContent,
                    assetType: cells[2].textContent,
                    brand: cells[3].textContent,
                    model: cells[4].textContent,
                    equipmentName: cells[5].textContent,
                    serialNumber: cells[6].textContent,
                    priceValue: cells[8].textContent,
                    remarks: cells[11].textContent
                };
                assetDetails.push(assetDetail);
            }
        }
		  // Fetch employee name from the last row of the preview table
        var employeeName = '';
        if (previewTable.rows.length > 1) { // Ensure there are rows
            var lastRowCells = previewTable.rows[previewTable.rows.length - 1].cells; // Get the last row
            employeeName = lastRowCells[9].textContent; // Assuming employee name is in the 10th column
        }

        // Convert asset details to JSON string for sending
        var assetDetailsJson = JSON.stringify(assetDetails);

        // Send email via AJAX
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "send_emailv2.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                // Hide spinner modal
                $('#spinnerModal').modal('hide');

                if (xhr.status === 200) {
              alert("Email sent successfully! Please save the generated form as PDF at this location: \\\\cl-ws049\\Accountability files");
                    printAcknowledgementReceipt(); // Call the print function after successful email
                } else {
                    alert("Error sending email.");
                }
            }
        };
         xhr.send("email=" + encodeURIComponent(emailInput) + 
                 "&employeeName=" + encodeURIComponent(employeeName) + // Send employee name
                 "&jobTitle=" + encodeURIComponent(jobTitle) + 
                 "&supervisor=" + encodeURIComponent(supervisorName) + 
                 "&assetDetails=" + encodeURIComponent(assetDetailsJson));
    };
}
function validateEmail(email) {
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
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
                    location: cells[10].textContent.trim(),
					remarks: cells[12].textContent.trim()
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
.suggestions {
    border: 1px solid #ddd; /* Softer border color */
    background: #ffffff; /* White background */
    border-radius: 8px; /* Rounded corners */
    max-height: 200px; /* Maximum height */
    overflow-y: auto; /* Enable vertical scrolling */
    z-index: 1000; /* Ensure it appears above other elements */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    margin-top: 5px; /* Space between input and suggestions */
    transition: all 0.3s ease; /* Smooth transition for hover effects */
}

.suggestion-item {
    padding: 10px 15px; /* Increased padding for better touch targets */
    cursor: pointer; /* Pointer cursor on hover */
    font-size: 14px; /* Slightly larger font size */
    color: #333; /* Darker text color for better readability */
    transition: background-color 0.3s ease; /* Smooth background color transition */
}

.suggestion-item:hover {
    background-color: #e9ecef; /* Light gray background on hover */
    color: #007bff; /* Change text color on hover for emphasis */
    border-left: 4px solid #007bff; /* Add a left border for visual feedback */
}

.suggestion-item:active {
    background-color: #d6d8db; /* Darker gray when item is clicked */
    color: #0056b3; /* Darker blue on active */
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
                            
                            <th>IBOSS Tag</th>
                            <th>Asset Type</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Equipment Name</th>
                            <th>Serial Number</th>
                           
                            <th>Price Value</th>
                           
							<th>Remarks</th>
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
            </div>
               <input type="text" id="searchInput" class="form-control form-control-lg d-print-none" placeholder="Search for employee..." autocomplete="off">
<div id="suggestions" class="suggestions" style="display: none; position: absolute; z-index: 1000; background: white; border: 1px solid #ccc; max-height: 200px; overflow-y: auto;"></div>
              
				
                <script>
	var searchInput = document.getElementById('searchInput');
var jobTitleInput = document.getElementById('jobTitleInput');
var suggestionsBox = document.getElementById('suggestions');

searchInput.addEventListener('input', function() {
    var searchQuery = searchInput.value.toLowerCase();
    // Reset the job title input every time the search input changes
    jobTitleInput.value = ''; // Clear job title input
    if (searchQuery.length > 0) {
        fetchSuggestions(searchQuery);
    } else {
        suggestionsBox.style.display = 'none'; // Hide suggestions if input is empty
    }
});

function fetchJobTitle(name) {
    fetch(`fetch_job_title.php?name=${encodeURIComponent(name)}`)
        .then(response => response.json())
        .then(data => {
            if (data.position) {
                jobTitleInput.value = data.position; // Update job title input
            } else {
                jobTitleInput.value = ''; // Clear if no position found
            }
        })
        .catch(error => console.error('Error fetching job title:', error));
}

function fetchSuggestions(query) {
    var table = document.getElementById('assetsTable');
    var rows = table.getElementsByTagName('tr');
    var suggestions = [];

    for (var i = 1; i < rows.length; i++) {
        var cells = rows[i].getElementsByTagName('td');
        if (cells.length > 0) {
            var issuedTo = cells[9].textContent.toLowerCase(); // Assuming 'issued_to' is in the 10th column
            if (issuedTo.includes(query) && !suggestions.includes(issuedTo)) {
                suggestions.push(issuedTo);
            }
        }
    }

    displaySuggestions(suggestions);
}

function displaySuggestions(suggestions) {
    suggestionsBox.innerHTML = ''; // Clear previous suggestions
    if (suggestions.length > 0) {
        suggestions.forEach(function(suggestion) {
            var suggestionItem = document.createElement('div');
            suggestionItem.textContent = suggestion;
            suggestionItem.classList.add('suggestion-item');
            suggestionItem.onclick = function() {
                searchInput.value = suggestion; // Set input value to the selected suggestion
                suggestionsBox.style.display = 'none'; // Hide suggestions
                fetchJobTitle(suggestion); // Fetch job title based on the selected suggestion
                searchAssets(suggestion); // Call search function with the selected suggestion
            };
            suggestionsBox.appendChild(suggestionItem);
        });
        suggestionsBox.style.display = 'block'; // Show suggestions
    } else {
        suggestionsBox.style.display = 'none'; // Hide if no suggestions
    }
}

// Update the searchAssets function to accept a query
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
            var issuedTo = cells[9].textContent.toLowerCase(); // Assuming 'issued_to' is in the 10th column
            if (issuedTo.includes(query) && assetTypeMatches) {
                found = true;
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
                            ?>
							
                        </tbody>
						
                    </table>
					
                </div>
               
            </div>
        </div>
    </div>
	<br><br>



    

 
 
 <?php include 'footer.php'; ?>
</body>
</html>
			