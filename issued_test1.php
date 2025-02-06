<?php
include 'config.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Fetch the asset details for the form
$asset_tag = $_GET['asset_tag'] ?? '';
$stmt = $mysqli->prepare("SELECT * FROM assets WHERE asset_tag = ?");
$stmt->bind_param("s", $asset_tag);
$stmt->execute();
$result = $stmt->get_result();
$asset = $result->fetch_assoc();
$stmt->close();

if (!$asset) {
    header('Location: error.php?message=Asset not found.');
    exit;
}

// Fetch users for the dropdown
$users = [];
$sql_users = "SELECT employee_name FROM employee_list ORDER BY employee_name ASC";
$result_users = $mysqli->query($sql_users);
while ($row = $result_users->fetch_assoc()) {
    $users[] = $row['employee_name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Change for Issued To</title>
    <link rel="icon" type="image/x-icon" href="logo small.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            margin-top: 50px;
            max-width: 800px;
            background: linear-gradient(to bottom, #f8f9fa, #e9ecef 70%); /* Fading two-tone background */
            border-radius: 8px; /* Rounded corners */
            border-radius: 8px; /* Rounded corners */
            padding: 20px; /* Padding inside the container */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
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
        <h1>Request Change for Issued To</h1>
        <form action="submit_request.php" method="POST">
            <input type="hidden" name="asset_tag" value="<?php echo htmlspecialchars($asset['asset_tag']); ?>">
            <input type="hidden" name="old_issued_to" value="<?php echo htmlspecialchars($asset['issued_to']); ?>">

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="iboss_tag" class="form-label">IBOSS Tag</label>
                    <input type="text" name="iboss_tag" id="iboss_tag" class="form-control" value="<?php echo htmlspecialchars($asset['iboss_tag']); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="brand" class="form-label">Brand</label>
                    <input type="text" name="brand" id="brand" class="form-control" value="<?php echo htmlspecialchars($asset['brand']); ?>" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="model" class="form-label">Model</label>
                    <input type="text" name="model" id="model" class="form-control" value="<?php echo htmlspecialchars($asset['model']); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="serial_number" class="form-label">Serial Number</label>
                    <input type="text" name="serial_number" id="serial_number" class="form-control" value="<?php echo htmlspecialchars($asset['serial_number']); ?>" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <input type="text" name="status" id="status" class="form-control" value="<?php echo htmlspecialchars($asset['status']); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="equipment_name" class="form-label">Equipment Name</label>
                    <input type="text" name="equipment_name" id="equipment_name" class="form-control" value="<?php echo htmlspecialchars($asset['equipment_name']); ?>" readonly>
                </div>
            </div>

            <div class="mb-3">
                <label for="new_issued_to" class="form-label">New Issued To</label>
                <select class="form-select" name="new_issued_to" required>
                    <option value="">Select User</option>
                    <?php
                    sort($users);
                    foreach ($users as $fullName) {
                        echo "<option value='" . htmlspecialchars($fullName) . "' " . ($asset['issued_to'] === $fullName ? 'selected' : '') . ">$fullName</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="reason" class="form-label">Reason for Request</label>
                <textarea class="form-control" name="reason" rows="3" required></textarea>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary">Submit Request</button>
                <button type="button" class="btn btn-secondary" onclick="confirmClose()">Close</button>
            </div>
        </form>
    </div>

    <script>
        function confirmClose() {
            if (confirm("Are you sure you want to exit? Any unsaved changes will be lost.")) {
                window.location.href = 'edit_asset.php'; // Redirect to the desired page
            }
        }
    </script>
</body>
</html>