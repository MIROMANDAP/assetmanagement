<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ensure this path is correct
require 'credential.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $employeeName = filter_var($_POST['employeeName'], FILTER_SANITIZE_EMAIL);
    $jobTitle = filter_var($_POST['jobTitle'], FILTER_SANITIZE_STRING);
    $supervisor = filter_var($_POST['supervisor'], FILTER_SANITIZE_STRING);
    
    // Get asset details
    $assetDetails = json_decode($_POST['assetDetails'], true); // Decode JSON string

    // Initialize PHPMailer
    $mail = new PHPMailer(true); // Enable exceptions
    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = EMAIL; // Your email
        $mail->Password = PASS; // Your email password or app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS
        $mail->Port = 587; // TCP port to connect to

        // Sender
        $mail->setFrom('itnoc.support@ibossasia.com', 'APAC Asset System');

        // Recipient
        $mail->addAddress($email); // Add a recipient

        // Subject
        $mail->Subject = "Acknowledgement Receipt";

        // Email body with logo and styling
        $mail->isHTML(true); // Set email format to HTML
        $mail->Body = '
            <div style="font-family: Arial, sans-serif; width: 100%; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); background-color: #fff;">
                <div style="background-color:#191a40; padding: 20px; text-align: center;">
                    <img src="cid:logo" alt="APAC Logo" style="width:100px; height:auto;"/>
                    <h2 style="color: white;">APAC Asset System</h2>
                </div>
                <div style="padding: 20px;">
                    <h3>Acknowledgement Receipt</h3>
                    <p>Name: <strong>' . htmlspecialchars($employeeName) . '</strong></p>
                    <p>Job Title: <strong>' . htmlspecialchars($jobTitle) . '</strong></p>
                    <p>Supervisor: <strong>' . htmlspecialchars($supervisor) . '</strong></p>
                    <h3>Asset Details</h3>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                        <thead>
                            <tr style="background-color: #f2f2f2;">
                                <th style="border: 1px solid #ddd; padding: 8px;">IBOSS Tag</th>
                                <th style="border: 1px solid #ddd; padding: 8px;">Asset Type</th>
                                <th style="border: 1px solid #ddd; padding: 8px;">Brand</th>
                                <th style="border: 1px solid #ddd; padding: 8px;">Model</th>
                                <th style="border: 1px solid #ddd; padding: 8px;">Equipment Description</th>
                                <th style="border: 1px solid #ddd; padding: 8px;">Serial Number</th>
                                <th style="border: 1px solid #ddd; padding: 8px;">Price Value</th>
                                <th style="border: 1px solid #ddd; padding: 8px;">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>';

        // Skip the first asset entry
        $firstAsset = true; // Flag to skip the first asset
        foreach ($assetDetails as $asset) {
            if ($firstAsset) {
                $firstAsset = false; // Skip the first asset
                continue; // Skip adding the first asset row
            }
            $mail->Body .= '
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($asset['ibossTag']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($asset['assetType']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($asset['brand']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($asset['model']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($asset['equipmentName']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($asset['serialNumber']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($asset['priceValue']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($asset['remarks']) . '</td>
                </tr>';
        }

        $mail->Body .= '
                        </tbody>
                    </table>
                    <p>Thank you for your acknowledgment.</p>
                    <p style="font-size: 14px; color: #555;">This is an auto-generated email. Replies will not be monitored.</p>
                </div>
            </div>
        ';

        // Attach logo image
        $mail->addEmbeddedImage($_SERVER['DOCUMENT_ROOT'].'/dashboard/logo.png', 'logo');

        // Send the email
        if ($mail->send()) {
            echo json_encode(['success' => true, 'message' => 'Email sent successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send email.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Mailer Error: ' . $mail->ErrorInfo]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}