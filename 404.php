<?php
include 'config.php';
date_default_timezone_set('Asia/Shanghai');
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Asset Management System</title>
    <link rel="stylesheet" href="style.css?v=1.1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <link rel="icon" type="image/x-icon" href="white.png">
    <link href="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.css" rel="stylesheet">
    <script src="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.js"></script>
    <script src="https://unpkg.com/bootstrap-table@1.22.1/dist/extensions/print/bootstrap-table-print.min.js"></script>
    <style>
        #loadingIndicator {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none; /* Initially hidden */
            text-align: center;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 0.3rem;
            color: #007bff; /* Change this to your desired color */
        }

        /* Optional: Style for the loading text */
        #loadingIndicator p {
            font-size: 1.2rem;
            color: #333; /* Change this to your desired text color */
        }
    </style>
</head>
<body style="background-color: white !important;">

<!-- Loading Spinner -->
<div id="loadingIndicator">
    <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p>Loading data, please wait...</p>
</div>

<div class="d-flex align-items-center justify-content-center vh-100">
    <div class="text-center">
        <h1 class="display-1 fw-bold">404</h1>
        <p class="fs-3"> <span class="text-danger">Oops!</span> Page not found.</p>
        <p class="lead">
            The page you’re looking for doesn’t exist.
        </p>
        <a href="index.php" class="btn btn-primary">Go Home</a>
    </div>
</div>

<script>
    // Show the loading spinner
    document.getElementById('loadingIndicator').style.display = 'block';

    // Simulate data loading (replace this with your actual data loading logic)
    setTimeout(function() {
        // Hide the loading spinner after data is loaded
        document.getElementById('loadingIndicator').style.display = 'none';
    }, 3000); // Simulate a 3-second loading time
</script>

</body>
</html><?php
// Initialize variables
$searchName = "";
$employeeResults = [];
$assetResults = [];

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $searchName = $_POST['employee_name'];

    // Prepare and bind for employee search
    $stmt = $mysqli->prepare("SELECT * FROM employee_list WHERE employee_name LIKE ?");
    $searchTerm = "%" . $searchName . "%";
    $stmt->bind_param("s", $searchTerm);

    // Execute the statement
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch employee results
    while ($row = $result->fetch_assoc()) {
        $employeeResults[] = $row;
    }

    // Close statement
    $stmt->close();

    // If employees are found, fetch their assets
    if (!empty($employeeResults)) {
        // Assuming employee_id is the primary key in employee_list
        $employeeIds = array_column($employeeResults, 'employee_id');
        $ids = implode(',', $employeeIds);

        // Prepare and bind for asset search
        $stmt = $mysqli->prepare("SELECT * FROM assets WHERE issued_to IN ($ids)");
        $stmt->execute();
        $assetResult = $stmt->get_result();

        // Fetch asset results
        while ($row = $assetResult->fetch_assoc()) {
            $assetResults[] = $row;
        }

        // Close statement
        $stmt->close();
    }
}

// Close connection
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Search</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Search Employee</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="employee_name">Employee Name:</label>
                <input type="text" class="form-control" id="employee_name" name="employee_name" value="<?php echo htmlspecialchars($searchName); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <?php if (!empty($employeeResults)): ?>
            <h3 class="mt-4">Employee Details:</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employeeResults as $employee): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($employee['employee_id']); ?></td>
                            <td><?php echo htmlspecialchars($employee['employee_name']); ?></td>
                            <td><?php echo htmlspecialchars($employee['email']); ?></td>
                            <td><?php echo htmlspecialchars($employee['phone_number']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (!empty($assetResults)): ?>
                <h3 class="mt-4">Assets Issued:</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Asset ID</th>
                            <th>Asset Name</th>
                            <th>Issued To</th>
                            <th>Date Issued</th>
                        </tr>
                    </thead>
                <tbody>
                    <?php foreach ($assetResults as $asset): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($asset['asset_id']); ?></td>
                            <td><?php echo htmlspecialchars($asset['asset_name']); ?></td>
                            <td><?php echo htmlspecialchars($asset['issued_to']); ?></td>
                            <td><?php echo htmlspecialchars($asset['date_issued']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="alert alert-warning">No assets found for the selected employee.</div>
            <?php endif; ?>
        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <div class="alert alert-warning">No results found for "<?php echo htmlspecialchars($searchName); ?>".</div>
        <?php endif; ?>
    </div>
</body>
</html>