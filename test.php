<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Image Example</title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            #printArea {
                display: block;
            }
        }
        #printArea {
            display: none; /* Hide by default */
        }
    </style>
</head>
<body>
    <h1>Image Print Example</h1>
    <button onclick="printImage()">Print Image</button>

    <div id="printArea">
        <img src="http://localhost:8080/dashboard/PRINT.png" alt="Test Image" style="width: 100%; height: auto;">
    </div>

    <script>
        function printImage() {
            // Show the print area
            document.getElementById('printArea').style.display = 'block';
            window.print();
            // Hide the print area again after printing
            document.getElementById('printArea').style.display = 'none';
        }
    </script>
</body>
</html>