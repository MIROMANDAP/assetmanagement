<?php
include 'config.php';
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Function to create new acknowledgment
function createAcknowledgment($mysqli, $employee_name, $items) {
    $mysqli->begin_transaction();
    try {
        // Insert main acknowledgment record
        $stmt = $mysqli->prepare("INSERT INTO asset_acknowledgments (employee_name, issued_date) VALUES (?, CURRENT_DATE)");
        $stmt->bind_param("s", $employee_name);
        $stmt->execute();
        $acknowledgment_id = $mysqli->insert_id;

        // Insert items
        $stmt = $mysqli->prepare("INSERT INTO acknowledgment_items (acknowledgment_id, apac_tag, iboss_tag, asset_type, brand, model, equipment_name, serial_number, date_acquired, price_value, location, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($items as $item) {
            $stmt->bind_param("issssssssdss",
                $acknowledgment_id,
                $item['apac_tag'],
                $item['iboss_tag'],
                $item['asset_type'],
                $item['brand'],
                $item['model'],
                $item['equipment_name'],
                $item['serial_number'],
                $item['date_acquired'],
                $item['price_value'],
                $item['location'],
                $item['status']
            );
            $stmt->execute();
        }

        $mysqli->commit();
        return true;
    } catch (Exception $e) {
        $mysqli->rollback();
        return false;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_name = $_POST['employee_name'];
    $items = json_decode($_POST['items'], true);

    if (empty($employee_name) || empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Employee name or items are missing']);
        exit;
    }

    if (createAcknowledgment($mysqli, $employee_name, $items)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create acknowledgment']);
    }
    exit;
}

// Fetch assets for a specific employee
if (isset($_GET['employee_name'])) {
    $employee_name = $_GET['employee_name'];
    $stmt = $mysqli->prepare("SELECT * FROM assets WHERE issued_to = ?");
    $stmt->bind_param("s", $employee_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $assets = [];
    while ($row = $result->fetch_assoc()) {
        $assets[] = $row;
    }
    echo json_encode($assets);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Asset Acknowledgment</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Create Asset Acknowledgment</h2>
        <form id="acknowledgmentForm">
            <div class="form-group">
    <label>Employee Name:</label>
    <input type="text" class="form-control" id="employee_name" name="employee_name" required>
</div>

            <button type="button" class="btn btn-primary" id="loadAssets">Load Assets</button>

            <div id="assetsContainer" class="mt-4">
                <h4>Assets Issued to Employee</h4>
                <table class="table table-bordered" id="assetsTable">
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
                            <th>Location</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Assets will be dynamically loaded here -->
                    </tbody>
                </table>
            </div>

            <div id=" itemsContainer">
                <!-- Items will be added here dynamically -->
            </div>

            <button type="button" class="btn btn-secondary" onclick="addItem()">Add Item</button>
            <button type="submit" class="btn btn-primary">Create Acknowledgment</button>
        </form>
    </div>

    <script>
        let itemCount = 0;

        function addItem() {
            const container = document.getElementById('itemsContainer');
            const itemHtml = `
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Item ${++itemCount}</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>APAC Tag:</label>
                                    <input type="text" class="form-control" name="items[${itemCount}][apac_tag]" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>IBOSS Tag:</label>
                                    <input type="text" class="form-control" name="items[${itemCount}][iboss_tag]" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Asset Type:</label>
                                    <input type="text" class="form-control" name="items[${itemCount}][asset_type]" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Brand:</label>
                                    <input type="text" class="form-control" name="items[${itemCount}][brand]" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Model:</label>
                                    <input type="text" class="form-control" name="items[${itemCount}][model]" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Equipment Name:</label>
                                    <input type="text" class="form-control" name="items[${itemCount}][equipment_name]" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Serial Number:</label>
                                    <input type="text" class="form-control" name="items[${itemCount}][serial_number]" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Date Acquired:</label>
                                    <input type="date" class="form-control" name="items[${itemCount}][date_acquired]" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Price Value:</label>
                                    <input type="number" class="form-control" name="items[${itemCount}][price_value]" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Location:</label>
                                    <input type="text" class="form-control" name="items[${itemCount}][location]" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status:</label>
                                    <input type="text" class="form-control" name="items[${itemCount}][status]" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', itemHtml);
        }

  document.getElementById('loadAssets').addEventListener('click', async () => {
    const employeeName = document.getElementById('employee_name').value;
    const response = await fetch(`create_acknowledgment.php?employee_name=${employeeName}`);
    const assets = await response.json();

    console.log(assets); // Log the assets to see their structure

    const tableBody = document.getElementById('assetsTable').getElementsByTagName('tbody')[0];
    tableBody.innerHTML = '';

    assets.forEach((asset) => {
        const row = `
            <tr>
                <td>${asset.asset_tag || 'N/A'}</td>
                <td>${asset.iboss_tag || 'N/A'}</td>
                <td>${asset.asset_type || 'N/A'}</td>
                <td>${asset.brand || 'N/A'}</td>
                <td>${asset.model || 'N/A'}</td>
                <td>${asset.equipment_name || 'N/A'}</td>
                <td>${asset.serial_number || 'N/A'}</td>
                <td>${asset.date_acquired || 'N/A'}</td>
                <td>${asset.price_value || 'N/A'}</td>
                <td>${asset.location_asset || 'N/A'}</td> <!-- Updated to location_asset -->
                <td>${asset.status || 'N/A'}</td>
            </tr>
        `;
        tableBody.insertAdjacentHTML('beforeend', row);
    });
});
       document.getElementById('acknowledgmentForm').onsubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);

    // Log the form data for debugging
    for (const [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }

    const items = [];

    for (let i = 1; i <= itemCount; i++) {
        const item = {
            apac_tag: formData.get(`items[${i}][apac_tag]`),
            iboss_tag: formData.get(`items[${i}][iboss_tag]`),
            asset_type: formData.get(`items[${i}][asset_type]`),
            brand: formData.get(`items[${i}][brand]`),
            model: formData.get(`items[${i}][model]`),
            equipment_name: formData.get(`items[${i}][equipment_name]`),
            serial_number: formData.get(`items[${i}][serial_number]`),
            date_acquired: formData.get(`items[${i}][date_acquired]`),
            price_value: formData.get(`items[${i}][price_value]`),
            location: formData.get(`items[${i}][location]`),
            status: formData.get(`items[${i}][status]`)
        };
        items.push(item);
    }

    // Check if items are empty
    if (items.length === 0) {
        alert('No items have been added.');
        return;
    }

    formData.set('items', JSON.stringify(items));

    try {
        const response = await fetch('create_acknowledgment.php', {
            method: 'POST',
            body: formData
        });
        const text = await response.text(); // Get response as text
        console.log(text); // Log the raw response for debugging

        const result = JSON.parse(text); // Try parsing as JSON

        if (result.success) {
            alert('Acknowledgment created successfully!');
            window.location.reload();
        } else {
            alert('Failed to create acknowledgment: ' + result.message);
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
};

    </script>
</body>
</html>