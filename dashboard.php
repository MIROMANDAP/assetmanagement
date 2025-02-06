<?php
include 'config.php';

session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}
date_default_timezone_set('Asia/Shanghai');

// Fetch unique asset types
$sql = "SELECT DISTINCT asset_type FROM assets";
$result = mysqli_query($mysqli, $sql);
$asset_types = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Initialize an array to hold data for each asset type
$asset_data = [];

foreach ($asset_types as $type) {
    $type_name = $type['asset_type'];
    $asset_data[$type_name] = [];

    // Count total assets for this type
    $sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = '$type_name'";
    $asset_data[$type_name]['total'] = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

    // Count deployed assets
    $sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = '$type_name' AND status = 'Deployed'";
    $asset_data[$type_name]['in_use'] = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

    // Count Available assets
    $sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = '$type_name' AND status = 'Available'";
    $asset_data[$type_name]['in_storage'] = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

    // Count defective assets
    $sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = '$type_name' AND status = 'Defective'";
    $asset_data[$type_name]['for_repair'] = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

    // Count Decommission assets
    $sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = '$type_name' AND status = 'Decommission'";
    $asset_data[$type_name]['for_disposal'] = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

    // Sum price values
    $sql = "SELECT SUM(price_value) AS total_value FROM assets WHERE asset_type = '$type_name' AND price_value IS NOT NULL";
    $asset_data[$type_name]['total_value'] = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total_value'];
}



// Initialize totals

$total_value = 0;
$total_deployed = 0;
$total_deployed_value = 0; // Total value for deployed assets
$total_Available = 0;
$total_Available_value = 0; // Total value for Available assets
$total_defective = 0;
$total_defective_value = 0; // Total value for defective assets
$total_Decommission = 0;
$total_Decommission_value = 0; // Total value for Decommission assets

// Fetch unique asset types
$sql = "SELECT DISTINCT asset_type FROM assets";
$result = mysqli_query($mysqli, $sql);
$asset_types = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Initialize an array to hold data for each asset type
$asset_data = [];

foreach ($asset_types as $type) {
    $type_name = $type['asset_type'];
    $asset_data[$type_name] = [];

    // Count total assets for this type
    $sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = '$type_name'";
    $asset_data[$type_name]['total'] = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total'];

    // Count deployed assets and their total value
    $sql = "SELECT COUNT(*) AS total, SUM(price_value) AS total_value FROM assets WHERE asset_type = '$type_name' AND status = 'Deployed'";
    $deployed_data = mysqli_fetch_assoc(mysqli_query($mysqli, $sql));
    $asset_data[$type_name]['in_use'] = $deployed_data['total'];
    $asset_data[$type_name]['deployed_value'] = $deployed_data['total_value'] ?? 0; // Handle null values

    // Count Available assets and their total value
    $sql = "SELECT COUNT(*) AS total, SUM(price_value) AS total_value FROM assets WHERE asset_type = '$type_name' AND status = 'Available'";
    $Available_data = mysqli_fetch_assoc(mysqli_query($mysqli, $sql));
    $asset_data[$type_name]['in_storage'] = $Available_data['total'];
    $asset_data[$type_name]['Available_value'] = $Available_data['total_value'] ?? 0;

    // Count defective assets and their total value
    $sql = "SELECT COUNT(*) AS total, SUM(price_value) AS total_value FROM assets WHERE asset_type = '$type_name' AND status = 'Defective'";
    $defective_data = mysqli_fetch_assoc(mysqli_query($mysqli, $sql));
    $asset_data[$type_name]['for_repair'] = $defective_data['total'];
    $asset_data[$type_name]['defective_value'] = $defective_data['total_value'] ?? 0;

    // Count Decommission assets and their total value
    $sql = "SELECT COUNT(*) AS total, SUM(price_value) AS total_value FROM assets WHERE asset_type = '$type_name' AND status = 'Decommission'";
    $Decommission_data = mysqli_fetch_assoc(mysqli_query($mysqli, $sql));
    $asset_data[$type_name]['for_disposal'] = $Decommission_data['total'];
    $asset_data[$type_name]['Decommission_value'] = $Decommission_data['total_value'] ?? 0;

    // Sum price values
    $sql = "SELECT SUM(price_value) AS total_value FROM assets WHERE asset_type = '$type_name' AND price_value IS NOT NULL";
    $asset_data[$type_name]['total_value'] = mysqli_fetch_assoc(mysqli_query($mysqli, $sql))['total_value'] ?? 0;

    // Update total calculations
    $total_value += $asset_data[$type_name]['total_value'];
    $total_deployed += $asset_data[$type_name]['in_use'];
    $total_deployed_value += $asset_data[$type_name]['deployed_value'];
    $total_Available += $asset_data[$type_name]['in_storage'];
    $total_Available_value += $asset_data[$type_name]['Available_value'];
    $total_defective += $asset_data[$type_name]['for_repair'];
    $total_defective_value += $asset_data[$type_name ]['defective_value'];
    $total_Decommission += $asset_data[$type_name]['for_disposal'];
    $total_Decommission_value += $asset_data[$type_name]['Decommission_value'];
}

$total_assets = $total_deployed + $total_Available + $total_defective + $total_Decommission;
?>

<script>
     document.addEventListener('DOMContentLoaded', function() {
        // Handle defective alert
        const defectiveAlert = document.getElementById('defectiveAlert');
        if (defectiveAlert) {
            console.log('Defective alert found. Hiding after 5 seconds.');
            setTimeout(() => {
                defectiveAlert.style.display = 'none';
                console.log('Defective alert hidden.');
            }, 5000);
        } else {
            console.log('No defective alert found.');
        }

        // Handle Decommission alert
        const DecommissionAlert = document.getElementById('DecommissionAlert');
        if (DecommissionAlert) {
            console.log('Decommission alert found. Hiding after 5 seconds.');
            setTimeout(() => {
                DecommissionAlert.style.display = 'none';
                console.log('Decommission alert hidden.');
            }, 5000);
        } else {
            console.log('No Decommission alert found.');
        }
    });
	
	$(document).ready(function() {
    $('#assetReportModal').on('show.bs.modal', function (event) {
        // Load the content into the modal
        let modal = $(this);
        modal.find('#modalContent').load('report_assets.php'); // Load your report page
    });

    $('#generatePdfBtn').on('click', function() {
        // Trigger the PDF generation (you can adjust this function as needed)
        $.post('report_assets.php', { generate_pdf: true }, function(data) {
            // Here you can handle the response from the server if needed
            window.location.href = 'asset_report.pdf'; // Redirect to the PDF
            $('#assetReportModal').modal('hide'); // Close the modal
        });
    });
});
</script>

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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha384-DyZk0q8H9Zq9jA1H0rZQd6X6Hc2Z5hM1f7x8q8F7xQk8m8H9z5Z7D6G5S6G5S6G" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha384-KyZXEAg3QhqLMpG8r+Knujsl5/6P1jF5a3m1dFf5c5a5x5i5j5H5F5F5u5F5u5F5" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-8X5gC5v5A5g5G5g5G5g5G5g5G5g5G5g5G5g5G5g5G5g5G5g5G5g5G5g5G5g5G5" crossorigin="anonymous"></script>
</head>

<body>


    <?php include 'nav.php'; ?>
    <div class="container-fluid" style="background-color: #f2f2f2; padding: 20px; border: 1px solid #ddd; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
    <?php if ($total_defective > 0): ?>
        <div id="defectiveAlert" class="alert alert-warning" role="alert">
            Attention: There are <?php echo $total_defective; ?> defective assets that require your attention.
        </div>
    <?php endif; ?>
	
<?php if ($total_Decommission > 0): ?>
        <div id="DecommissionAlert" class="alert alert-danger" role="alert">
            Attention: There are <?php echo $total_Decommission; ?> Decommission assets that require your attention.
        </div>
    <?php endif; ?>
		<h1 class="text-dark text-center">Device Monitoring Dashboard</h1>
		<!-- Button to Generate Report -->
    <div class="text-center mb-4">
    <button type="button" class="btn btn-primary" onclick="window.location.href='report_assets.php';">
    <i class="fas fa-file-alt"></i> View Asset Report
</button>
</div>
		<div class="row row-cols-1 row-cols-md-3 row-cols-lg-6 g-4">
      <!-- Card for Total Assets -->
    <div class="col-md-4 col-lg-2">
    <div class="card text-black mb-3 text-center border border-dark rounded" style="background: #e9ecef; transition: transform 0.3s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
        <div class="card-header text-white bg-primary font-weight-bold">Total Assets</div>
        <div class="card-body">
            <h3 class="card-title font-weight-bold"><?php echo $total_assets; ?></h3>
            <p>Total Asset Value:<br> ₱<?php echo number_format($total_value, 2); ?></p>
        </div>
    </div>
</div>

    <!-- Card for Total Deployed Assets -->
    <div class="col-md-4 col-lg-2">
        <div class="card text-black mb-3 text-center border border-dark rounded" style="background: #e9ecef; transition: transform 0.3s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
            <div class="card-header text-white bg-success font-weight-bold">Total Deployed Assets</div>
            <div class="card-body">
                <h3 class="card-title font-weight-bold"><?php echo $total_deployed; ?></h3>
                <p>Total Deployed Value:<br> ₱<?php echo number_format($total_deployed_value, 2); ?></p>
            </div>
        </div>
    </div>

    <!-- Card for Total Available Assets -->
    <div class="col-md-4 col-lg-2">
        <div class="card text-black mb-3 text-center border border-dark rounded" style="background: #e9ecef; transition: transform 0.3s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
            <div class="card-header text-white bg-info font-weight-bold">Total Available Assets</div>
            <div class="card-body">
                <h3 class="card-title font-weight-bold"><?php echo $total_Available; ?></h3>
                <p>Total Available Value:<br> ₱<?php echo number_format($total_Available_value, 2); ?></p>
            </div>
        </div>
    </div>

    <!-- Card for Total Defective Assets -->
    <div class="col-md-4 col-lg-2">
        <div class="card text-black mb-3 text-center border border-dark rounded" style="background: #e9ecef; transition: transform 0.3s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
            <div class="card-header text-white bg-warning font-weight-bold">Total Defective Assets</div>
            <div class="card-body">
                <h3 class="card-title font-weight-bold"><?php echo $total_defective; ?></h3>
                <p>Total Defective Value:<br> ₱<?php echo number_format($total_defective_value, 2); ?></p>
            </div>
        </div>
    </div>

    <!-- Card for Total Decommission Assets -->
    <div class="col-md-4 col-lg-2">
        <div class="card text-black mb-3 text-center border border-dark rounded" style="background: #e9ecef; transition: transform 0.3s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
            <div class="card-header text-white bg-danger font-weight-bold">Total Decommission Assets</div>
            <div class="card-body">
                <h3 class="card-title font-weight-bold"><?php echo $total_Decommission; ?></h3>
                <p>Total Decommission Value:<br> ₱<?php echo number_format($total_Decommission_value, 2); ?></p>
            </div>
        </div>
    </div>
	<!-- Card for Total Decommission Assets -->
    <div class="col-md-4 col-lg-2">
        <div class="card text-black mb-3 text-center border border-dark rounded" style="background: #e9ecef; transition: transform 0.3s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
            <div class="card-header text-black bg-white font-weight-bold">Time & Date</div>
            <div class="card-body">
                <h3 class="card-title font-weight-bold">Asia & Pacific</h3>
                <p><br>   <?php echo date("F d, Y H:i:s"); // Display current date and time ?></p>
            </div>
        </div>
    </div>

  

    <!-- Cards for each asset type -->
    <?php foreach ($asset_data as $type_name => $data): ?>
        <!-- Your existing code for generating cards for each asset type -->
    <?php endforeach; ?>
</div>
       <div class="row row-cols-1 row-cols-md-3 row-cols-lg-6 g-4">
    <?php foreach ($asset_data as $type_name => $data): ?>
        <div class="col-md-4 col-lg-2">
            <div class="card text-black mb-3 text-center border border-dark rounded" style="background: #e9ecef; transition: transform 0.3s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                <div class="card-header text-white bg-primary font-weight-bold"><?php echo $type_name; ?></div>
                <div class="card-body">
                    <h3 class="card-title font-weight-bold"><?php echo $data['total']; ?></h3>
                    <ul class="list-unstyled text-center" style="font-size: 0.85rem;">
                        <li class="text-success font-weight-bold">Deployed: <?php echo $data['in_use']; ?></li>
                        <li class="text-info font-weight-bold">Available: <?php echo $data['in_storage']; ?></li>
                        <li class="text-warning font-weight-bold">Defective: <?php echo $data['for_repair']; ?></li>
                        <li class="text-danger font-weight-bold">Decommission: <?php echo $data['for_disposal']; ?></li>
                        <li class="text-dark font-weight-bold">Total Value: ₱<?php echo number_format($data['total_value'], 2); ?></li>
                    </ul>
                   <canvas id="<?php echo $type_name; ?>" width="50" height="50"></canvas>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('<?php echo $type_name; ?>').getContext('2d');
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['Deployed', 'Available', 'Defective', 'Decommission'],
                        datasets: [{
                            label: '<?php echo $type_name; ?>',
                            data: [<?php echo $data['in_use']; ?>, <?php echo $data['in_storage']; ?>, <?php echo $data['for_repair']; ?>, <?php echo $data['for_disposal']; ?>],
                            backgroundColor: [
                                'rgba(0, 128, 0, 0.2)',
                                'rgba(0, 0, 255, 0.2)',
                                'rgba(255, 165, 0, 0.2)',
                                'rgba(255, 0, 0, 0.2)'
                            ],
                            borderColor: [
                                'rgba(0, 128, 0, 1)',
                                'rgba(0, 0, 255, 1)',
                                'rgba(255, 165, 0, 1)',
                                'rgba(255, 0, 0, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            });
        </script>
    <?php endforeach; ?>

       
    </div>

<?php include 'footer.php'; ?>

</html>