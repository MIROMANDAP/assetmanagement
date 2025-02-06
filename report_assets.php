<?php
include 'config.php';

session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

date_default_timezone_set('Asia/Shanghai');

// Fetch unique asset types
function fetchAssetTypes($mysqli) {
    $sql = "SELECT DISTINCT asset_type FROM assets";
    $result = mysqli_query($mysqli, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Fetch asset data for each type
function fetchAssetData($mysqli, $type_name) {
    $data = [];
    $sql = "SELECT COUNT(*) AS total FROM assets WHERE asset_type = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $type_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $data['total'] = $result->fetch_assoc()['total'];

    // Change 'Spare' to 'Available' and 'Faulty' to 'Decommission'
    $statuses = ['Deployed', 'Available', 'Defective', 'Decommission'];
    foreach ($statuses as $status) {
        $sql = "SELECT COUNT(*) AS total, SUM(price_value) AS value FROM assets WHERE asset_type = ? AND status = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ss", $type_name, $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $data[strtolower($status)] = $row['total'];
        $data[strtolower($status) . '_value'] = $row['value'] ?? 0;
    }

    $sql = "SELECT SUM(price_value) AS total_value FROM assets WHERE asset_type = ? AND price_value IS NOT NULL";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $type_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $data['total_value'] = $result->fetch_assoc()['total_value'] ?? 0;

    return $data;
}

// Calculate totals
function calculateTotals($asset_data) {
    $totals = [
        'total_assets' => 0,
        'total_value' => 0,
        'total_deployed' => 0,
        'total_available' => 0, // Changed from total_spare to total_available
        'total_defective' => 0,
        'total_decommission' => 0, // Changed from total_decommissioning to total_decommission
        'total_deployed_value' => 0,
        'total_available_value' => 0, // Changed from total_spare_value to total_available_value
        'total_defective_value' => 0,
        'total_decommission_value' => 0, // Changed from total_decommissioning_value to total_decommission_value
    ];

    foreach ($asset_data as $data) {
        $totals['total_assets'] += $data['total'];
        $totals['total_value'] += $data['total_value'];
        $totals['total_deployed'] += $data['deployed'];
        $totals['total_available'] += $data['available']; // Changed from total_spare
        $totals['total_defective'] += $data['defective'];
        $totals['total_decommission'] += $data['decommission']; // Changed from total_decommissioning
        $totals['total_deployed_value'] += $data['deployed_value'];
        $totals['total_available_value'] += $data['available_value']; // Changed from total_spare_value
        $totals['total_defective_value'] += $data['defective_value'];
        $totals['total_decommission_value'] += $data['decommission_value']; // Changed from total_decommissioning_value
    }

    return $totals;
}

// Fetch asset types and data
$asset_types = fetchAssetTypes($mysqli);
$asset_data = [];
foreach ($asset_types as $type) {
    $type_name = $type['asset_type'];
    $asset_data[$type_name] = fetchAssetData($mysqli, $type_name);
}

// Calculate totals
$totals = calculateTotals($asset_data);

// Generate PDF report
if (isset($_POST['generate_pdf'])) {
    require('fpdf/fpdf.php');

    class PDF extends FPDF
    {
        function Header()
        {
            // Logo $this->Image('Print.png', 10, 6, 30); // Adjust the path and dimensions as needed
            // Arial bold 15
            $this->SetFont('Arial', 'B', 15);
            // Title
            $this->Cell(0, 10, 'Asset Report', 0, 0, 'C');
            // Line break
            $this->Ln(10);
            
            // Date - Set to a smaller font size
            $this->SetFont('Arial', 'I', 10); // Changed from 12 to 10
            $this->Cell(0, 10, 'Date: ' . date("F d, Y H:i:s"), 0, 0, 'C');
            // Line break
            $this->Ln(20);
        }

        function Footer()
        {
            // Position at 1.5 cm from bottom
            $this->SetY(-15);
            // Arial italic 8
            $this->SetFont('Arial', 'I', 8);
            // Page number
            $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        }

        function ChapterTitle($label)
        {
            // Arial 12
            $this->SetFont('Arial', 'B', 12);
            // Background color
            $this->SetFillColor(200, 220, 255);
            // Title
            $this->Cell(0, 6, $label, 0, 1, 'C', true);
            // Line break
            $this->Ln(4);
        }

        function TableHeader($widths)
        {
            $this->SetFont('Arial', 'B', 9);
            $this->SetFillColor(220, 220, 220);
            $headers = array('Asset Type', 'Total', 'Deployed', 'Available', 'Defective', 'Decommission', 'Total Value'); // Changed here
            foreach ($headers as $i => $header) {
                $this->Cell($widths[$i], 7, $header, 1, 0, 'C', true);
            }
            $this->Ln();
        }

        function BarChart($data, $x, $y, $w, $h, $colors, $title) {
            $this->SetXY($x, $y);
            $this->SetFont('Arial', 'B', 12);
            $this->Cell($w, 10, $title, 0, 1, 'C');
            $this->SetFont('Arial', '', 8);

            $maxValue = max($data);
            $barWidth = $w / (count($data) * 2 + 1);
            $barHeight = ($h - 40) / $maxValue;

            $startX = $x + $barWidth;
            $startY = $y + $h - 30;

            // Draw bars
            foreach ($data as $key => $value) {
                $this->SetFillColor($colors[$key][0], $colors[$key][1], $colors[$key][2]);
                $this->Rect($startX, $startY - $value * $barHeight, $barWidth, $value * $barHeight, 'F');
                $this->SetXY($startX, $startY + 5);
                $this->Cell($barWidth, 5, $key, 0, 0, 'C');
                $startX += $barWidth * 2;
            }

            // Draw Y-axis
            $this->SetDrawColor(0, 0, 0);
            $this->Line($x, $y + 10, $x, $startY);
            for ($i = 0; $i <= $maxValue; $i += max(1, floor($maxValue / 5))) {
                $this->SetXY($x - 15, $startY - $i * $barHeight);
                $this->Cell(10, 5, $i, 0, 0, 'R');
                $this->Line($x - 2, $startY - $i * $barHeight, $x, $startY - $i * $barHeight);
            }

            // Legend
            $this->SetXY($x, $y + $h + 5);
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(0, 10, 'Legend', 0, 1, 'L');
 $this->SetFont('Arial', '', 8);
            foreach ($data as $key => $value) {
                $this->SetFillColor($colors[$key][0], $colors[$key][1], $colors[$key][2]);
                $this->Cell(5, 5, '', 1, 0, '', true);
                $this->Cell(20, 5, $key . ': ' . $value, 0, 1);
            }

            // Draw a border around the legend
            $this->SetDrawColor(0, 0, 0);
            $this->Rect($x, $y + $h + 5, 50, count($data) * 5 + 10); // Adjust dimensions as needed
        }
    }

    // Generate PDF report
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();

    $pdf->ChapterTitle('Asset Summary Report');
    $pdf->Ln(5);

    // Adjust column widths
    $col_widths = array(30, 20, 20, 20, 20, 20, 30);

    // Calculate total width and left margin for centering
    $total_width = array_sum($col_widths);
    $left_margin = ($pdf->GetPageWidth() - $total_width) / 2;

    $pdf->SetLeftMargin($left_margin);

    $pdf->TableHeader($col_widths);

    $pdf->SetFont('Arial', '', 8);
    foreach ($asset_data as $type_name => $data) {
        $pdf->Cell($col_widths[0], 6, $type_name, 1);
        $pdf->Cell($col_widths[1], 6, $data['total'], 1, 0, 'R');
        $pdf->Cell($col_widths[2], 6, $data['deployed'], 1, 0, 'R');
        $pdf->Cell($col_widths[3], 6, $data['available'], 1, 0, 'R');
        $pdf->Cell($col_widths[4], 6, $data['defective'], 1, 0, 'R');
        $pdf->Cell($col_widths[5], 6, $data['decommission'], 1, 0, 'R'); // Changed here
        $pdf->Cell($col_widths[6], 6, 'PHP ' . number_format($data['total_value'], 2), 1, 1, 'R');
    }

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell($col_widths[0], 6, 'Totals', 1, 0, 'L', true);
    $pdf->Cell($col_widths[1], 6, $totals['total_assets'], 1, 0, 'R', true);
    $pdf->Cell($col_widths[2], 6, $totals['total_deployed'], 1, 0, 'R', true);
    $pdf->Cell($col_widths[3], 6, $totals['total_available'], 1, 0, 'R', true);
    $pdf->Cell($col_widths[4], 6, $totals['total_defective'], 1, 0, 'R', true);
    $pdf->Cell($col_widths[5], 6, $totals['total_decommission'], 1, 0, 'R', true); // Changed here
    $pdf->Cell($col_widths[6], 6, 'PHP ' . number_format($totals['total_value'], 2), 1, 1, 'R', true);

    $pdf->Ln(10);

    // Add value breakdown
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 6, 'Value Breakdown by Status', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(40, 6, 'Deployed: PHP ' . number_format($totals['total_deployed_value'], 2), 0, 1);
    $pdf->Cell(40, 6, 'Available: PHP ' . number_format($totals['total_available_value'], 2), 0, 1);
    $pdf->Cell(40, 6, 'Defective: PHP ' . number_format($totals['total_defective_value'], 2), 0, 1);
    $pdf->Cell(40, 6, 'Decommission: PHP ' . number_format($totals['total_decommission_value'], 2), 0, 1); // Changed here

    $pdf->Ln(10);

    // Add pie chart
    $chart_data = [
        'Deployed' => $totals['total_deployed'],
        'Available' => $totals['total_available'],
        'Defective' => $totals['total_defective'],
        'Decommission' => $totals['total_decommission'] // Changed here
    ];
    $colors = [
        'Deployed' => [75, 192, 192],
        'Available' => [255, 99, 132],
        'Defective' => [255, 205, 86],
        'Decommission' => [54, 162, 235] // Changed here
    ];
    $pdf->BarChart($chart_data, 10, $pdf->GetY(), 90, 50, $colors, 'Asset Status Distribution');

    $pdf->Output('D', 'asset_report.pdf');
    exit;

}
?>
   
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Asset Reporting</title>
    <link rel="stylesheet" href="style.css?v=1.1">
    <link rel="icon" type="image/x-icon" href="logo small.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .table th, .table td {
            text-align: center;
        }
        .report-container {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .chart-container {
            margin-top: 30px;
        }
    </style>
</head>

<body>

    <div class="container-fluid" style="padding: 20px;">
        <div class="report-container">
            <div class="report-header">
                <div class="text-end">
                    <button type="button" class="btn btn-danger" onclick="window.location.href='dashboard.php';">
                        <i class="fas fa-file-alt"></i> Close
                    </button>
                </div>
                <h1 class="text-dark">Asset Report</h1>
                <p>Date: <?php echo date("F d, Y H:i:s"); ?></p>
                <hr>
            </div>

            <table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>Asset Type</th>
            <th>Total Assets</th>
            <th>Deployed</th>
            <th>Available</th>
            <th>Defective</th>
            <th>Decommission</th> <!-- Changed here -->
            <th>Total Value</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($asset_data as $type_name => $data): ?>
            <tr>
                <td><?php echo $type_name; ?></td>
                <td><?php echo $data['total']; ?></td>
                <td><?php echo $data['deployed']; ?> (₱<?php echo number_format($data['deployed_value'], 2); ?>)</td>
                <td><?php echo $data['available']; ?> (₱<?php echo number_format($data['available_value'], 2); ?>)</td>
                <td><?php echo $data['defective']; ?> (₱<?php echo number_format($data['defective_value'], 2); ?>)</td>
                <td><?php echo $data['decommission']; ?> (₱<?php echo number_format($data['decommission_value'], 2); ?>)</td>
             <td>₱<?php echo number_format($data['total_value'], 2); ?></td>
            </tr>
        <?php endforeach; ?> <!-- Make sure to close the foreach loop -->
    </tbody>
</table>

            <div class="chart-container">
                <canvas id="assetChart"></canvas>
            </div>

            <div class="mt-4">
                <form method="post">
                    <button type="submit" name="generate_pdf" class="btn btn-primary">Generate PDF Report</button>
                </form>
            </div>

            <div class="footer mt-4">
                <p class="text-center text-muted">Generated by Asset Management System</p>
            </div>
        </div>
    </div>

    <script>
        var ctx = document.getElementById('assetChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($asset_data)); ?>,
                datasets: [{
                    label: 'Deployed',
                    data: <?php echo json_encode(array_column($asset_data, 'deployed')); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgb(75, 192, 192)',
                    borderWidth: 1
                }, {
                    label: 'Available',
                    data: <?php echo json_encode(array_column($asset_data, 'available')); ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgb(255, 99, 132)',
                    borderWidth: 1
                }, {
                    label: 'Defective',
                    data: <?php echo json_encode(array_column($asset_data, 'defective')); ?>,
                    backgroundColor: 'rgba(255, 205, 86, 0.6)',
                    borderColor: 'rgb(255, 205, 86)',
                    borderWidth: 1
                }, {
                    label: 'Decommission',
                    data: <?php echo json_encode(array_column($asset_data, 'decommission')); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Asset Status Distribution by Type'
                    },
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Assets'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Asset Type'
                        }
                    }
                }
            }
        });
    </script>
</body>
<?php include 'footer.php'; ?>
</html>