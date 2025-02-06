<?php
include 'config.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_tag = $_POST['asset_tag'];
    $new_issued_to = $_POST['new_issued_to'];
    $reason = $_POST['reason'];
    $requested_by = $_SESSION['id']; // Assuming user_id is stored in session

    // Prepare and execute the update statement
    $stmt = $mysqli->prepare("UPDATE assets SET issued_to = ? WHERE asset_tag = ?");
$stmt->bind_param("ss", $new_issued_to, $asset_tag);

    if ($stmt->execute()) {
        // Redirect back to the assets page with success message
        header('Location: edit_asset.php?success=1');
    } else {
        // Handle error
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch the asset details for the form
$asset_tag = $_GET['asset_tag'];
$stmt = $mysqli->prepare("SELECT * FROM assets WHERE asset_tag = ?");
$stmt->bind_param("s", $asset_tag);
$stmt->execute();
$result = $stmt->get_result();
$asset = $result->fetch_assoc();
$stmt->close();

if (!$asset) {
    echo "Asset not found.";
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
    <title>Edit Issued To</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>Edit Issued To for Asset: <?php echo htmlspecialchars($asset['asset_tag']); ?></h1>
    <form action="issued_test1.php" method="POST">
        <input type="hidden" name="asset_tag" value="<?php echo htmlspecialchars($asset['asset_tag']); ?>">
        
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

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Submit Request</button>
        </div>
    </form>
</div>
</body>
</html>