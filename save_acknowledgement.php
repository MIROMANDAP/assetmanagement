<?php
include 'config.php';
session_start();
require_once('tcpdf/tcpdf.php'); // Adjust the path as necessary

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the data from the POST request
    $jobTitle = htmlspecialchars($_POST['jobTitle']);
    $supervisor = htmlspecialchars($_POST['supervisor']);
    $username = htmlspecialchars($_POST['username']);
    $currentDate = date('Y-m-d H:i:s');

    // Define the path to save the file
    $folderPath = 'FORM/'; // Change this to your desired folder path
    $fileName = 'acknowledgement_' . time() . '.pdf'; // Example file name
    $filePath = $folderPath . $fileName;

    // Create a new PDF document
    $pdf = new TCPDF();

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your Name');
    $pdf->SetTitle('Acknowledgement Receipt');
    $pdf->SetSubject('Acknowledgement Receipt');
    $pdf->SetKeywords('TCPDF, PDF, acknowledgement, receipt');

    // Set default header data
    $pdf->SetHeaderData('', 0, 'Acknowledgement Receipt', 'Issued By: ' . $username);

    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Add a page
    $pdf->AddPage();

    // Create the content to save
    $content = "<h1>Acknowledgement Receipt</h1>";
    $content .= "<p><strong>Job Title:</strong> $jobTitle</p>";
    $content .= "<p><strong>Supervisor:</strong> $supervisor</p>";
    $content .= "<p><strong>Issued By:</strong> $username</p>";
    $content .= "<p><strong>Date:</strong> $currentDate</p>";

    // Output the HTML content
    $pdf->writeHTML($content, true, false, true, false, '');

    // Close and output PDF document
    $pdf->Output($filePath, 'F'); // Save the PDF to the specified path

    // Check if the file was created successfully
    if (file_exists($filePath)) {
        echo "Form saved successfully as PDF.";
    } else {
        echo "Error saving the form.";
    }
} else {
    echo "Invalid request.";
}
?>