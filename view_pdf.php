<?php
// Check if the 'file' parameter is set in the URL
if (isset($_GET['file'])) {
    // Sanitize the input to prevent directory traversal attacks
    $file = basename($_GET['file']);
    
    // Define the directory where your PDF files are stored
    $directory = 'Accountability files/'; // Make sure this directory exists and contains your PDF files

    // Create the full path to the PDF file
    $filePath = $directory . $file;

    // Check if the file exists
    if (file_exists($filePath)) {
        // Set the content type to application/pdf
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $file . '"');
        readfile($filePath);
        exit;
    } else {
        echo "File not found.";
    }
} else {
    echo "No file specified.";
}
?>