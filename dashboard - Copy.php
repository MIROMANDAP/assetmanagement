<?php
include 'config.php';

session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}
date_default_timezone_set('Asia/Shanghai');

// Count all asset types
$sql = "SELECT COUNT(*) AS total FROM assets";
$total_assets = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT SUM(price_value) AS total_value FROM assets WHERE price_value IS NOT NULL";
$total_asset_value = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total_value'];

// Check if total_asset_value is null and set it to 0 if true
$total_asset_value = $total_asset_value !== null ? $total_asset_value : 0;

// Function to get total asset value by status
function getTotalAssetValueByStatus($mysqli, $status) {
    $sql = "SELECT SUM(price_value) AS total_value FROM assets WHERE status = ? AND price_value IS NOT NULL";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_value = mysqli_fetch_assoc($result)['total_value'];
    return $total_value !== null ? $total_value : 0; // Return 0 if null
}

$total_in_use_value = getTotalAssetValueByStatus($mysqli, 'Deployed');
$total_in_storage_value = getTotalAssetValueByStatus($mysqli, 'Spare');
$total_for_repair_value = getTotalAssetValueByStatus($mysqli, 'Defective');
$total_for_disposal_value = getTotalAssetValueByStatus($mysqli, 'Faulty');

$sql = "SELECT COUNT(*) AS total FROM assets WHERE status = 'Deployed'";
$total_in_use = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE status = 'Spare'";
$total_in_storage = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE status = 'Defective'";
$total_for_repair = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE status = 'Faulty'";
$total_disposed = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

// Monitor-related queries
$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Monitor'";
$total_monitors = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Monitor' AND status = 'Deployed'";
$monitors_in_use = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Monitor' AND status = 'Spare'";
$monitors_in_storage = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Monitor' AND status = 'Defective'";
$monitors_for_repair = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Monitor' AND status = 'Faulty'";
$monitors_for_disposal = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT SUM(price_value) AS total_value FROM assets WHERE asset_type = 'Monitor' AND price_value IS NOT NULL";
$total_monitor_value = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total_value'];

// System Unit-related queries
$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'System Unit'";
$total_system_units = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'System Unit' AND status = 'Deployed'";
$system_units_in_use = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'System Unit' AND status = 'Spare'";
$system_units_in_storage = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'System Unit' AND status = 'Defective'";
$system_units_for_repair = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'System Unit' AND status = 'Faulty'";
$system_units_for_disposal = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT SUM(price_value) AS total_value FROM assets WHERE asset_type = 'System Unit' AND price_value IS NOT NULL";
$total_system_unit_value = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total_value'];

// Laptop-related queries
$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Laptop'";
$total_laptops = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Laptop' AND status = 'Deployed'";
$laptops_in_use = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Laptop' AND status = 'Spare'";
$laptops_in_storage = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Laptop' AND status = 'Defective'";
$laptops_for_repair = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Laptop' AND status = 'Faulty'";
$laptops_for_disposal = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT SUM(price_value) AS total_value FROM assets WHERE asset_type = 'Laptop' AND price_value IS NOT NULL";
$total_laptop_value = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total_value'];

// Headset-related queries
$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Headset'";
$total_headsets = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Headset' AND status = 'Deployed'";
$headsets_in_use = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Headset' AND status = 'Spare'";
$headsets_in_storage = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Headset' AND status = 'Defective'";
$headsets_for_repair = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Headset' AND status = 'Faulty'";
$headsets_for_disposal = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT SUM(price_value) AS total_value FROM assets WHERE asset_type = 'Headset' AND price_value IS NOT NULL";
$total_headset_value = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total_value'];

// Webcam-related queries
$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Webcam'";
$total_webcams = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Webcam' AND status = 'Deployed'";
$webcams_in_use = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Webcam' AND status = 'Spare'";
$webcams_in_storage = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Webcam' AND status = 'Defective'";
$webcams_for_repair = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Webcam' AND status = 'Faulty'";
$webcams_for_disposal = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT SUM(price_value) AS total_value FROM assets WHERE asset_type = 'Webcam' AND price_value IS NOT NULL";
$total_webcam_value = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total_value'];

// IT Peripherals-related queries
$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'IT Pheriperals'";
$total_peripherals = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'IT Pheriperals' AND status = 'Deployed'";
$peripherals_in_use = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'IT Pheriperals' AND status = 'Spare'";
$peripherals_in_storage = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'IT Pheriperals' AND status = 'Defective'";
$peripherals_for_repair = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'IT Pheriperals' AND status = 'Faulty'";
$peripherals_for_disposal = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT SUM(price_value) AS total_value FROM assets WHERE asset_type = 'IT Peripherals' AND price_value IS NOT NULL";
$total_it_peripherals_value = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total_value'];

// Switches-related queries
$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Switches'";
$total_switches = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Switches' AND status = 'Deployed'";
$switches_in_use = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Switches' AND status = 'Spare'";
$switches_in_storage = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Switches' AND status = 'Defective'";
$switches_for_repair = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Switches' AND status = 'Faulty'";
$switches_for_disposal = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT SUM(price_value) AS total_value FROM assets WHERE asset_type = 'Switches' AND price_value IS NOT NULL";
$total_switches_value = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total_value'];

// Printers-related queries
$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Printers'";
$total_printers = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Printers' AND status = 'Deployed'";
$printers_in_use = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Printers' AND status = 'Spare'";
$printers_in_storage = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Printers' AND status = 'Defective'";
$printers_for_repair = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Printers' AND status = 'Faulty'";
$printers_for_disposal = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT SUM(price_value) AS total_value FROM assets WHERE asset_type = 'Printers' AND price_value IS NOT NULL";
$total_printer_value = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total_value'];


// Routers-related queries
$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Routers'";
$total_routers = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Routers' AND status = 'Deployed'";
$routers_in_use = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Routers' AND status = 'Spare'";
$routers_in_storage = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Routers' AND status = 'Defective'";
$routers_for_repair = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Routers' AND status = 'Faulty'";
$routers_for_disposal = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT SUM(price_value) AS total_value FROM assets WHERE asset_type = 'Routers' AND price_value IS NOT NULL";
$total_router_value = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total_value'];


// Servers-related queries
$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Servers'";
$total_servers = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Servers' AND status = 'Deployed'";
$servers_in_use = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Servers' AND status = 'Spare'";
$servers_in_storage = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Servers' AND status = 'Defective'";
$servers_for_repair = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Servers' AND status = 'Faulty'";
$servers_for_disposal = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT SUM(price_value) AS total_value FROM assets WHERE asset_type = 'Servers' AND price_value IS NOT NULL";
$total_server_value = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total_value'];


// Software-related queries
$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Software'";
$total_software = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Software' AND status = 'Deployed'";
$software_in_use = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Software' AND status = 'Spare'";
$software_in_storage = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Software' AND status = 'Defective'";
$software_for_repair = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Software' AND status = 'Faulty'";
$software_for_disposal = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT SUM(price_value) AS total_value FROM assets WHERE asset_type = 'Software' AND price_value IS NOT NULL";
$total_software_value = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total_value'];

// Access Cards-related queries
$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Access Cards'";
$total_access_cards = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Access Cards' AND status = 'Deployed'";
$access_cards_in_use = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Access Cards' AND status = 'Spare'";
$access_cards_in_storage = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Access Cards' AND status = 'Defective'";
$access_cards_for_repair = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = 'Access Cards' AND status = 'Faulty'";
$access_cards_for_disposal = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

$sql = "SELECT SUM(price_value) AS total_value FROM assets WHERE asset_type = 'Access Card' AND price_value IS NOT NULL";
$total_access_card_value = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total_value'];




// Initialize an array to hold data for each status
$data = [];

// Define the statuses
$statuses = ['Deployed', 'Spare', 'Defective', 'Faulty'];

// Initialize the $data array for each status
foreach ($statuses as $status) {
    $data[$status] = []; // Ensure each status has an array
}

foreach ($statuses as $status) {
    // Prepare the SQL query
    $sql = "SELECT YEAR(created_at) AS year, COUNT(*) AS total 
            FROM assets 
            WHERE status = ? 
            GROUP BY YEAR(created_at)";
    
    // Prepare and execute the statement
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $status);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $year = $row['year'];
        $total = $row['total'];
        $data[$status][$year] = $total; // Store counts by year for each status
    }
}

// Prepare data for the chart
$years = []; // Array to hold all unique years
$chartData = []; // Array to hold the total counts for each status

// Populate the chart data structure
foreach ($statuses as $status) {
    $chartData[$status] = []; // Initialize the array for this status
    foreach ($data[$status] as $year => $total) {
        $chartData[$status][$year] = $total; // Set total for that year
        if (!in_array($year, $years)) {
            $years[] = $year; // Add year to the years array if not already present
        }
    }
}

// Remove duplicates from years and re-index the array
$years = array_unique($years);
$years = array_values($years); // Re-index array

// Sort years to maintain order
sort($years);
?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Asset Management System</title>
    <link rel="stylesheet" href="style.css?v=1.1">
    <link rel="icon" type="image/x-icon" href="logo small.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script type="text/javascript">
        var currenttime = '<?php print date("F d, Y H:i:s", time())?>'; //PHP method of getting server date
        var montharray = new Array("January", "February", "March", "April", "May", "June", "July", "August", "September",
            "October", "November", "December");
        var serverdate = new Date(currenttime);

        function padlength(what) {
            return (what.toString().length == 1) ? "0" + what : what;
        }

        function displaytime() {
            serverdate.setSeconds(serverdate.getSeconds() + 1);
            var datestring = montharray[serverdate.getMonth()] + " " + padlength(serverdate.getDate()) + ", " + serverdate.getFullYear();
            var timestring = padlength(serverdate.getHours()) + ":" + padlength(serverdate.getMinutes()) + ":" + padlength(serverdate.getSeconds());
            document.getElementById("servertime").innerHTML = datestring + " " + timestring;
        }

        window.onload = function () {
            setInterval("displaytime()", 1000);
        }
    </script>
	 <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php include 'nav.php'; ?>
 <div class="container-fluid" style="background-color: #f2f2f2; padding: 20px; border: 1px solid #ddd; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
    <h1 class="text-dark text-center">Device Monitoring Dashboard</h1>
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-6 g-4">
            <div class="col-">
                <div class="card text-white bg-primary mb-3 text-center">
                    <div class="card-header">All Assets</div>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo $total_assets; ?></h3>
                        <h5 class="card-text">Total Of Overall Assets </h5>
						<h5 class="card-text"><?php echo "Total Asset Value: ₱" . number_format($total_asset_value, 2);?></h5>
                    </div>
                </div>
            </div>
            <div class="col-">
                <div class="card text-white bg-success mb-3 text-center">
                    <div class="card-header">Deployed</div>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo $total_in_use; ?></h3>
                        <h5 class="card-text">Assets Currently Deployed</h5>
						<h5 class="card-text"><?php echo "Total Asset Deployed: ₱" . number_format($total_in_use_value, 2);?></h5>
                    </div>
                </div>
            </div>
            <div class="col-">
                <div class="card text-white bg-info mb-3 text-center">
                    <div class="card-header">Spare</div>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo $total_in_storage; ?></h3>
                        <h5 class="card-text">Assets Currently Spare</h5>
						<h5 class="card-text"><?php echo "Total Asset Spare: ₱" . number_format($total_in_storage_value, 2); ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-">
                <div class="card text-white bg-warning mb-3 text-center">
                    <div class="card-header">Defective</div>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo $total_for_repair; ?></h3>
                        <h5 class="card-text">Assets Defective</h5>
						<h5 class="card-text"><?php echo "Total Asset Defective: ₱" . number_format($total_for_repair_value, 2); ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-">
                <div class="card text-white bg-danger mb-3 text-center">
                    <div class="card-header">Faulty</div>
                    <div class="card-body">
                        <h3 class="card-title"><?php echo $total_disposed; ?></h3>
                        <h5 class="card-text">Assets Faulty</h5>
						<h5 class="card-text"><?php echo "Total Asset Faulty: ₱" . number_format($total_for_disposal_value, 2); ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card text-white bg-dark mb-3 text-center">
                    <div class="card-header">Date and Time</div>
                    <div class="card-body">
                        <h3 class="card-title">
						
                            <div id="servertime"></div>
                        </h3>
                        <h3 class="card-text">Asia/Pacific</h3>
                    </div>
                </div>
            </div>
        <!-- Monitor asset card with breakdown -->
		
    <div class="col-md-6 col-lg-2">
    <div class="card text-black mb-3 text-center border border-dark rounded" style="background: #e9ecef; transition: transform 0.3s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
        <div class="card-header text-white bg-primary font-weight-bold">Monitors</div>
        <div class="card-body">
            <h3 class="card-title font-weight-bold"><?php echo $total_monitors; ?></h3>
            <canvas id="monitorChart"></canvas>
            <div id="monitorChartLegend"></div> <!-- Legend container -->

            <!-- Styled List for Statuses -->
            <ul class="list-unstyled text-center" style="font-size: 0.85rem;"> <!-- Smaller font size -->
                <li class="text-success font-weight-bold">Deployed: <?php echo $monitors_in_use; ?></li>
                <li class="text-info font-weight-bold">Spare: <?php echo $monitors_in_storage; ?></li>
                <li class="text-warning font-weight-bold">Defective: <?php echo $monitors_for_repair; ?></li>
                <li class="text-danger font-weight-bold" >Faulty: <?php echo $monitors_for_disposal; ?></li>
				<li class="text-dark font-weight-bold">Total Monitor &#8369;<?php echo number_format($total_monitor_value, 2); ?></li>
            </ul>
        </div>
    </div>
</div>

        <!-- System Unit asset card with breakdown -->
<div class="col-md-4 col-lg-2">
    <div class="card text-black mb-3 text-center border border-dark rounded" style="background: #f0f0f0; transition: transform 0.3s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
        <div class="card-header text-white bg-primary font-weight-bold">System Units</div>
        <div class="card-body">
            <h3 class="card-title font-weight-bold"><?php echo $total_system_units; ?></h3>
            <canvas id="systemUnitChart"></canvas>
            <ul class="list-unstyled text-center" style="font-size: 0.85rem;">
                <li class="text-success font-weight-bold">Deployed: <?php echo $system_units_in_use; ?></li>
                <li class="text-info font-weight-bold">Spare: <?php echo $system_units_in_storage; ?></li>
                <li class="text-warning font-weight-bold">Defective: <?php echo $system_units_for_repair; ?></li>
                <li class="text-danger font-weight-bold">Faulty: <?php echo $system_units_for_disposal; ?></li>
				 <li class="text-dark font-weight-bold">Total System Units: ₱<?php echo number_format($total_system_unit_value, 2); ?></li>
            </ul>
        </div>
    </div>
</div>

        <!-- Laptop asset card with breakdown -->
       <div class="col-md-4 col-lg-2">
    <div class="card text-black mb-3 text-center border border-dark rounded" style="background: #e9ecef; transition: transform 0.3s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
        <div class="card-header text-white bg-primary font-weight-bold">Laptops</div>
        <div class="card-body">
            <h3 class="card-title font-weight-bold"><?php echo $total_laptops; ?></h3>
            <canvas id="laptopChart"></canvas>
            <ul class="list-unstyled text-center" style="font-size: 0.85rem;">
                <li class="text-success font-weight-bold">Deployed: <?php echo $laptops_in_use; ?></li>
                <li class="text-info font-weight-bold">Spare: <?php echo $laptops_in_storage; ?></li>
                <li class="text-warning font-weight-bold">Defective: <?php echo $laptops_for_repair; ?></li>
                <li class="text-danger font-weight-bold">Faulty: <?php echo $laptops_for_disposal; ?></li>
				  <li class="text-dark font-weight-bold">Total Laptops: ₱<?php echo number_format($total_laptop_value, 2); ?></li>
            </ul>
        </div>
    </div>
</div>

        <!-- Headset asset card with breakdown -->
    <div class="col-md-4 col-lg-2">
    <div class="card text-black mb-3 text-center border border-dark rounded" style="background: #f0f0f0; transition: transform 0.3s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
        <div class="card-header text-white bg-primary font-weight-bold">Headsets</div>
        <div class="card-body">
            <h3 class="card-title font-weight-bold"><?php echo $total_headsets; ?></h3>
            <canvas id="headsetChart"></canvas>
            <ul class="list-unstyled text-center" style="font-size: 0.85rem;">
                <li class="text-success font-weight-bold">Deployed: <?php echo $headsets_in_use; ?></li>
                <li class="text-info font-weight-bold">Spare: <?php echo $headsets_in_storage; ?></li>
                <li class="text-warning font-weight-bold">Defective: <?php echo $headsets_for_repair; ?></li>
                <li class="text-danger font-weight-bold">Faulty: <?php echo $headsets_for_disposal; ?></li>
				  <li class="text-dark font-weight-bold">Total Headsets: ₱<?php echo number_format($total_headset_value, 2); ?></li>
            </ul>
        </div>
    </div>
</div>
        <!-- Webcam asset card with breakdown -->
       <div class="col-md-4 col-lg-2">
    <div class="card text-black mb-3 text-center border border-dark rounded" style="background: #e9ecef; transition: transform 0.3s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
        <div class="card-header text-white bg-primary font-weight-bold">Webcams</div>
        <div class="card-body">
            <h3 class="card-title font-weight-bold"><?php echo $total_webcams; ?></h3>
            <canvas id="webcamChart"></canvas>
            <ul class="list-unstyled text-center" style="font-size: 0.85rem;">
                <li class="text-success font-weight-bold">Deployed: <?php echo $webcams_in_use; ?></li>
                <li class="text-info font-weight-bold">Spare: <?php echo $webcams_in_storage; ?></li>
                <li class="text-warning font-weight-bold">Defective: <?php echo $webcams_for_repair; ?></li>
                <li class="text-danger font-weight-bold">Faulty: <?php echo $webcams_for_disposal; ?></li>
				<li class="text-dark font-weight-bold">Total Webcams: ₱<?php echo number_format($total_webcam_value, 2); ?></li>
            </ul>
        </div>
    </div>
</div>


        <!-- IT Peripherals asset card with breakdown -->
     <div class="col-md-4 col-lg-2">
    <div class="card text-black mb-3 text-center border border-dark rounded" style="background: #f0f0f0; transition: transform 0.3s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
        <div class="card-header text-white bg-primary font-weight-bold">IT Peripherals</div>
        <div class="card-body">
            <h3 class="card-title font-weight-bold"><?php echo $total_peripherals; ?></h3>
            <canvas id="peripheralsChart"></canvas>
            <ul class="list-unstyled text-center" style="font-size: 0.85rem;">
                <li class="text-success font-weight-bold">Deployed: <?php echo $peripherals_in_use; ?></li>
                <li class="text-info font-weight-bold">Spare: <?php echo $peripherals_in_storage; ?></li>
                <li class="text-warning font-weight-bold">Defective: <?php echo $peripherals_for_repair; ?></li>
                <li class="text-danger font-weight-bold">Faulty: <?php echo $peripherals_for_disposal; ?></li>
				<li class="text-dark font-weight-bold">Total IT Peripherals: ₱<?php echo number_format($total_it_peripherals_value, 2); ?></li>
            </ul>
        </div>
    </div>
</div>

        <!-- Switches asset card with breakdown -->
       <div class="col-md-4 col-lg-2">
    <div class="card text-black mb-3 text-center border border-dark rounded" style="background: #e9ecef; transition: transform 0.3s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
        <div class="card-header text-white bg-primary font-weight-bold">Switches</div>
        <div class="card-body">
            <h3 class="card-title font-weight-bold"><?php echo $total_switches; ?></h3>
            <canvas id="switchesChart"></canvas>
            <ul class="list-unstyled text-center" style="font-size: 0.85rem;">
                <li class="text-success font-weight-bold">Deployed:  <?php echo $switches_in_use; ?></li>
                <li class="text-info font-weight-bold">Spare: <?php echo $switches_in_storage; ?></li>
                <li class="text-warning font-weight-bold">Defective: <?php echo $switches_for_repair; ?></li>
                <li class="text-danger font-weight-bold">Faulty: <?php echo $switches_for_disposal; ?></li>
				<li class="text-dark font-weight-bold">Total Switches: ₱<?php echo number_format($total_switches_value, 2); ?></li>
            </ul>
        </div>
    </div>
</div>
<div class="col-md-4 col-lg-2">
    <div class="card text-black mb-3 text-center border border-dark rounded" style="background: #f0f0f0; transition: transform 0.3s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
        <div class="card-header text-white bg-primary font-weight-bold">Printers</div>
        <div class="card-body">
            <h3 class="card-title font-weight-bold"><?php echo $total_printers; ?></h3>
            <canvas id="printerChart"></canvas>
            <ul class="list-unstyled text-center" style="font-size: 0.85rem;">
                <li class="text-success font-weight-bold">Deployed: <?php echo $printers_in_use; ?></li>
                <li class="text-info font-weight-bold">Spare: <?php echo $printers_in_storage; ?></li>
                <li class="text-warning font-weight-bold">Defective: <?php echo $printers_for_repair; ?></li>
                <li class="text-danger font-weight-bold">Faulty: <?php echo $printers_for_disposal; ?></li>
				 <li class="text-dark font-weight-bold">Total Printers: ₱<?php echo number_format($total_printer_value, 2); ?></li>
            </ul>
        </div>
    </div>
</div>

<!-- Routers asset card with breakdown -->
<div class="col-md-4 col-lg-2">
    <div class="card text-black mb-3 text-center border border-dark rounded" style="background: #e9ecef; transition: transform 0.3s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
        <div class="card-header text-white bg-primary font-weight-bold">Routers</div>
        <div class="card-body">
            <h3 class="card-title font-weight-bold"><?php echo $total_routers; ?></h3>
            <canvas id="routerChart"></canvas>
            <ul class="list-unstyled text-center" style="font-size: 0.85rem;">
                <li class="text-success font-weight-bold">Deployed: <?php echo $routers_in_use; ?></li>
                <li class="text-info font-weight-bold">Spare: <?php echo $routers_in_storage; ?></li>
                <li class="text-warning font-weight-bold">Defective: <?php echo $routers_for_repair; ?></li>
                <li class="text-danger font-weight-bold">Faulty: <?php echo $routers_for_disposal; ?></li>
				<li class="text-dark font-weight-bold">Total Routers: ₱<?php echo number_format($total_router_value, 2); ?></li>
            </ul>
        </div>
    </div>
</div>

<!-- Servers asset card with breakdown -->
<div class="col-md-4 col-lg-2">
    <div class="card text-black mb-3 text-center border border-dark rounded" style="background: #f0f0f0; transition: transform 0.3s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
        <div class="card-header text-white bg-primary font-weight-bold">Servers</div>
        <div class="card-body">
            <h3 class="card-title font-weight-bold"><?php echo $total_servers; ?></h3>
            <canvas id="serverChart"></canvas>
            <ul class="list-unstyled text-center" style="font-size: 0.85rem;">
                <li class="text-success font-weight-bold">Deployed: <?php echo $servers_in_use; ?></li>
                <li class="text-info font-weight-bold">Spare: <?php echo $servers_in_storage; ?></li>
                <li class="text-warning font-weight-bold">Defective: <?php echo $servers_for_repair; ?></li>
                <li class="text-danger font-weight-bold">Faulty: <?php echo $servers_for_disposal; ?></li>
				<li class="text-dark font-weight-bold">Total Servers: ₱<?php echo number_format($total_server_value, 2); ?></li>
            </ul>
        </div>
    </div>
</div>

<!-- Software asset card with breakdown -->
<div class="col-md-4 col-lg-2">
    <div class="card text-black mb-3 text-center border border-dark rounded" style="background: #e9ecef; transition: transform 0.3s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
        <div class="card-header text-white bg-primary font-weight-bold">Software</div>
        <div class="card-body">
            <h3 class="card-title font-weight-bold"><?php echo $total_software; ?></h3>
            <canvas id="softwareChart"></canvas>
            <ul class="list-unstyled text-center" style="font-size: 0.85rem;">
                <li class="text-success font-weight-bold">Deployed: <?php echo $software_in_use; ?></li>
                <li class="text-info font-weight-bold">Spare: <?php echo $software_in_storage; ?></li>
                <li class="text-warning font-weight-bold">Defective: <?php echo $software_for_repair; ?></li>
                <li class="text-danger font-weight-bold">Faulty: <?php echo $software_for_disposal; ?></li>
				 <li class="text-dark font-weight-bold">Total Software: ₱<?php echo number_format($total_software_value, 2); ?></li>
            </ul>
        </div>
    </div>
</div>
<div class="col-md-4 col-lg-2">
    <div class="card text-black mb-3 text-center border border-dark rounded" style="background: #f0f0f0; transition: transform 0.3s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
        <div class="card-header text-white bg-primary font-weight-bold">Access Cards</div>
        <div class="card-body">
            <h3 class="card-title font-weight-bold"><?php echo $total_access_cards; ?></h3>
            <canvas id="accessCardChart"></canvas>
            <ul class="list-unstyled text-center" style="font-size: 0.85rem;">
                <li class="text-success font-weight-bold">Deployed: <?php echo $access_cards_in_use; ?></li>
                <li class="text-info font-weight-bold">Spare: <?php echo $access_cards_in_storage; ?></li>
                <li class="text-warning font-weight-bold">Defective: <?php echo $access_cards_for_repair; ?></li>
                <li class="text-danger font-weight-bold">Faulty: <?php echo $access_cards_for_disposal; ?></li>
				 <li class="text-dark font-weight-bold">Total Access Cards: ₱<?php echo number_format($total_access_card_value, 2); ?></li>
            </ul>
        </div>
    </div>
</div>
<div class="col-md-12 col-lg-12">
    <div class="card text-black mb-3 text-center border border-dark rounded" style="background: #e9ecef; transition: transform 0.3s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
        <div class="card-header text-white bg-primary font-weight-bold">Asset Status by Year</div>
        <div class="card-body">
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Status by Year</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<div style="width: 80%; margin: auto;">
    <canvas id="assetStatusChart"></canvas>
</div>
</div>
</div>
</div>
</div>

<script>
// Prepare the data for Chart.js
const years = <?php echo json_encode(array_keys($chartData['Deployed'])); ?>;
const inUseData = <?php echo json_encode(array_values($chartData['Deployed'])); ?>;
const inStorageData = <?php echo json_encode(array_values($chartData['Spare'])); ?>;
const forRepairData = <?php echo json_encode(array_values($chartData['Defective'])); ?>;
const forDisposalData = <?php echo json_encode(array_values($chartData['Faulty'])); ?>;

// Create the chart
const ctx = document.getElementById('assetStatusChart').getContext('2d');
const assetStatusChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: years,
        datasets: [
            {
                label: 'Deployed',
                data: inUseData,
                backgroundColor: '#14A44D',
            },
            {
                label: 'Spare',
                data: inStorageData,
                backgroundColor: '#54B4D3',
            },
            {
                label: 'Defective',
                data: forRepairData,
                backgroundColor: '#E4A11B',
            },
            {
                label: 'Faulty',
                data: forDisposalData,
                backgroundColor: '#DC4C64',
            },
        ],
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
            },
        },
    },
});
</script>


<script>
    // Function to create pie chart
    function createPieChart(ctx, data) {
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Deployed', 'Spare', 'Defective', 'Faulty'],
				
                datasets: [{
                    data: data,
                    backgroundColor: ['#14A44D', '#54B4D3', '#E4A11B', '#DC4C64'],
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'right',

                    }
                }
            }
        });
    }

    // Monitor chart data
    const monitorData = [
        <?php echo $monitors_in_use; ?>,
        <?php echo $monitors_in_storage; ?>,
        <?php echo $monitors_for_repair; ?>,
        <?php echo $monitors_for_disposal; ?>
    ];
    createPieChart(document.getElementById('monitorChart').getContext('2d'), monitorData);

    // System Units chart data
    const systemUnitData = [
        <?php echo $system_units_in_use; ?>,
        <?php echo $system_units_in_storage; ?>,
        <?php echo $system_units_for_repair; ?>,
        <?php echo $system_units_for_disposal; ?>
    ];
    createPieChart(document.getElementById('systemUnitChart').getContext('2d'), systemUnitData);

    // Laptop chart data
    const laptopData = [
        <?php echo $laptops_in_use; ?>,
        <?php echo $laptops_in_storage; ?>,
        <?php echo $laptops_for_repair; ?>,
        <?php echo $laptops_for_disposal; ?>
    ];
    createPieChart(document.getElementById('laptopChart').getContext('2d'), laptopData);

    // Headset chart data
    const headsetData = [
        <?php echo $headsets_in_use; ?>,
        <?php echo $headsets_in_storage; ?>,
        <?php echo $headsets_for_repair; ?>,
        <?php echo $headsets_for_disposal; ?>
    ];
    createPieChart(document.getElementById('headsetChart').getContext('2d'), headsetData);

    // Webcam chart data
    const webcamData = [
        <?php echo $webcams_in_use; ?>,
        <?php echo $webcams_in_storage; ?>,
        <?php echo $webcams_for_repair; ?>,
        <?php echo $webcams_for_disposal; ?>
    ];
    createPieChart(document.getElementById('webcamChart').getContext('2d'), webcamData);

    // IT Peripherals chart data
    const peripheralsData = [
        <?php echo $peripherals_in_use; ?>,
        <?php echo $peripherals_in_storage; ?>,
        <?php echo $peripherals_for_repair; ?>,
        <?php echo $peripherals_for_disposal; ?>
    ];
    createPieChart(document.getElementById('peripheralsChart').getContext('2d'), peripheralsData);

    // Switches chart data
    const switchesData = [
        <?php echo $switches_in_use; ?>,
        <?php echo $switches_in_storage; ?>,
        <?php echo $switches_for_repair; ?>,
        <?php echo $switches_for_disposal; ?>
    ];
    createPieChart(document.getElementById('switchesChart').getContext('2d'), switchesData);
	
	// Printers chart data
const printersData = [
    <?php echo $printers_in_use; ?>,
    <?php echo $printers_in_storage; ?>,
    <?php echo $printers_for_repair; ?>,
    <?php echo $printers_for_disposal; ?>
];
createPieChart(document.getElementById('printerChart').getContext('2d'), printersData);

// Routers chart data
const routersData = [
    <?php echo $routers_in_use; ?>,
    <?php echo $routers_in_storage; ?>,
    <?php echo $routers_for_repair; ?>,
    <?php echo $routers_for_disposal; ?>
];
createPieChart(document.getElementById('routerChart').getContext('2d'), routersData);

// Servers chart data
const serversData = [
    <?php echo $servers_in_use; ?>,
    <?php echo $servers_in_storage; ?>,
    <?php echo $servers_for_repair; ?>,
    <?php echo $servers_for_disposal; ?>
];
createPieChart(document.getElementById('serverChart').getContext('2d'), serversData);

// Software chart data
const softwareData = [
    <?php echo $software_in_use; ?>,
    <?php echo $software_in_storage; ?>,
    <?php echo $software_for_repair; ?>,
    <?php echo $software_for_disposal; ?>
];
createPieChart(document.getElementById('softwareChart').getContext('2d'), softwareData);

// Access Cards chart data
const accessCardData = [
    <?php echo $access_cards_in_use; ?>,
    <?php echo $access_cards_in_storage; ?>,
    <?php echo $access_cards_for_repair; ?>,
    <?php echo $access_cards_for_disposal; ?>
];
createPieChart(document.getElementById('accessCardChart').getContext('2d'), accessCardData);

</script>

<style>

    .card:hover {
        transform: scale(1.05); /* Slightly increase the size on hover */
    }
    canvas {
        max-height: 150px; /* Set a maximum height for the charts */
        margin-bottom: 10px; /* Add some space below the charts */
    }
</style>

<style>
    .card:hover {
        transform: scale(1.05); /* Slightly increase the size on hover */
    }
    canvas {
        max-height: 150px; /* Set a maximum height for the charts */
        margin-bottom: 10px; /* Add some space below the charts */
    }
</style>
<style>
    .card:hover {
        transform: scale(1.05); /* Slightly increase the size on hover */
    }
	
</style>

</body>
</html>
