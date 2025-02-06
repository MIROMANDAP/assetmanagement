<?php
include 'config.php'; // Include your database configuration file
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Initialize variables
$asset_types = [];
$asset_type = "";
$asset_type_err = "";

// Fetch asset types from the database
$sql = "SELECT id, type_name FROM asset_types";
if ($result = $mysqli->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $asset_types[] = $row;
    }
    $result->free();
}

// Handle deletion of an asset type
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete_sql = "DELETE FROM asset_types WHERE id = ?";
    
    if ($stmt = $mysqli->prepare($delete_sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo '<script>alert("Asset type deleted successfully."); window.location.href="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '";</script>';
        } else {
            echo '<script>alert("Something went wrong. Please try again."); window.location.href="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '";</script>';
        }
        $stmt->close();
    }
}

// Handle adding or updating asset types
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate asset type
    if (empty(trim($_POST["asset_type"]))) {
        $asset_type_err = "Please enter an asset type.";
    } else {
        $asset_type = trim($_POST["asset_type"]);
    }

    // Determine if adding or updating
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update existing asset type
        $id = $_POST['id'];
        if (empty($asset_type_err)) {
            $update_sql = "UPDATE asset_types SET type_name = ? WHERE id = ?";
            if ($stmt = $mysqli->prepare($update_sql)) {
                $stmt->bind_param("si", $asset_type, $id);
                if ($stmt->execute()) {
                    echo '<script>alert("Asset type updated successfully."); window.location.href="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '";</script>';
                } else {
                    echo '<script>alert("Something went wrong. Please try again."); window.location.href="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '";</script>';
                }
                $stmt->close();
            }
        }
    } else {
        // Add new asset type
        if (empty($asset_type_err)) {
            $insert_sql = "INSERT INTO asset_types (type_name) VALUES (?)";
            if ($stmt = $mysqli->prepare($insert_sql)) {
                $stmt->bind_param("s", $asset_type);
                if ($stmt->execute()) {
                    echo '<script>alert("Asset type added successfully."); window.location.href="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '";</script>';
                } else {
                    echo '<script>alert("Something went wrong. Please try again."); window.location.href="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '";</script>';
                }
                $stmt->close();
            }
        }
    }
}

?>
<?php include 'nav.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Asset Types</title>
    <link rel="stylesheet" href="style.css?v=1.1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 20px;
        }
        .modal-header {
            background-color: #007bff;
            color: white;
        }
        .modal-footer {
            border-top: none;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-action {
            margin-right: 5px;
        }
        .table th {
            text-align: center;
        }
        .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4 text-center">Manage Asset Types</h1>
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#addModal">Add New Asset Type</button>
        </div>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th scope="col" class="text-center">ID</th>
                    <th scope="col" class="text-center">Asset Type</th>
                    <th scope="col" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($asset_types)): ?>
                    <tr>
                        <td colspan="3" class="text-center">No asset types found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($asset_types as $type): ?>
                        <tr>
                            <td class="text-center"><?php echo htmlspecialchars($type['id']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($type['type_name']); ?></td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-action" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $type['id']; ?>" data-name="<?php echo htmlspecialchars($type['type_name']); ?>">Edit</button>
                                <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?delete=<?php echo $type['id']; ?>" class="btn btn-danger btn-action">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Add Modal -->
        <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Add New Asset Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" autocomplete="off">
                            <div class="form-group">
                                <label for="asset_type">Asset Type</label>
                                <input type="text" name="asset_type" id="asset_type" class="form-control <?php echo (!empty($asset_type_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($asset_type); ?>">
                                <span class="invalid-feedback"><?php echo $asset_type_err; ?></span>
                            </div>
                            <br>
                            <input type="submit" class="btn btn-primary" value="Add Asset Type">
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Asset Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" autocomplete="off">
                            <input type="hidden" name="id" id="edit_id">
                            <div class="form-group">
                                <label for="asset_type">Asset Type</label>
                                <input type="text" name="asset_type" id="edit_asset_type" class="form-control <?php echo (!empty($asset_type_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($asset_type); ?>">
                                <span class="invalid-feedback"><?php echo $asset_type_err; ?></span>
                            </div>
                            <br>
                            <input type="submit" class="btn btn-primary" value="Update Asset Type">
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            const editModal = document.getElementById('editModal');
            editModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_asset_type').value = name;
            });
        </script>
    </div>
</body>
<?php include 'footer.php'; ?>
</html>