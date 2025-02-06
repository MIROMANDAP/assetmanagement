<?php
include 'config.php';
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['asset_tag']) && isset($_GET['document'])) {
    $asset_tag = $_GET['asset_tag'];
    $document = $_GET['document'];

    // Fetch the existing documents
    $query = "SELECT documents FROM assets WHERE asset_tag = ?";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("s", $asset_tag);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $documents = explode(',', $row['documents']);
            // Remove the document from the array
            if (($key = array_search($document, $documents)) !== false) {
                unset($documents[$key]);
            }
            // Update the database
            $new_documents = implode(',', $documents);
            $update_query = "UPDATE assets SET documents = ? WHERE asset_tag = ?";
            if ($update_stmt = $mysqli->prepare($update_query)) {
                $update_stmt->bind_param("ss", $new_documents, $asset_tag);
                $update_stmt->execute();
                $update_stmt->close();
            }
            // Delete the actual file
            if (file_exists("uploads/" . $document)) {
                unlink("uploads/" . $document);
            }
        }
        $stmt->close();
    }
    // Redirect back to the update asset page
    header("Location: update_asset.php?asset_tag=" . urlencode($asset_tag));
    exit;
}
?>