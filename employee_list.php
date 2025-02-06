<?php
include 'config.php'; // Include your database configuration file
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Initialize variables
$employees = [];
$employee_id = "";
$employee_name = "";
$position = "";
$employee_err = "";
$search = "";

// Fetch employees from the database with search functionality
$sql = "SELECT id, employee_id, employee_name, position, department FROM employee_list";
if (isset($_POST['search'])) {
    $search = trim($_POST['search']);
    $sql .= " WHERE employee_id LIKE '%$search%' OR employee_name LIKE '%$search%' OR position LIKE '%$search%' OR department LIKE '%$search%'";
}
if ($result = $mysqli->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
    $result->free();
}

// Handle deletion of an employee
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete_sql = "DELETE FROM employee_list WHERE id = ?";
    
    if ($stmt = $mysqli->prepare($delete_sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo '<script>alert("Employee deleted successfully."); window.location.href="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '";</script>';
        } else {
            echo '<script>alert("Something went wrong. Please try again."); window.location.href="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '";</script>';
        }
        $stmt->close();
    }
}

// Handle adding or updating employees
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['search'])) {
    // Initialize variables and error tracking
    $employee_err = '';
    $employee_id = $employee_name = $position = $department = '';
    
    // Validate employee ID
    if (empty(trim($_POST["employee_id"]))) {
        $employee_err = "Please enter an employee ID.";
    } else {
        $employee_id = trim($_POST["employee_id"]);
    }

    // Validate employee name
    if (empty(trim($_POST["employee_name"]))) {
        $employee_err .= " Please enter an employee name.";
    } else {
        $employee_name = trim($_POST["employee_name"]);
    }

    // Validate position
    if (empty(trim($_POST["position"]))) {
        $employee_err .= " Please enter a position.";
    } else {
        $position = trim($_POST["position"]);
    }

    // Validate department
    if (empty(trim($_POST["department"]))) {
        $employee_err .= " Please select a department.";
    } else {
        $department = trim($_POST["department"]);
    }

    // Determine if adding or updating
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update existing employee
        $id = $_POST['id'];
        if (empty($employee_err)) {
            $update_sql = "UPDATE employee_list SET employee_id = ?, employee_name = ?, position = ?, department = ? WHERE id = ?";
            if ($stmt = $mysqli->prepare($update_sql)) {
                $stmt->bind_param("ssssi", $employee_id, $employee_name, $position, $department, $id);
                if ($stmt->execute()) {
                    echo '<script>alert("Employee updated successfully."); window.location.href="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '";</script>';
                } else {
                    echo '<script>alert("Something went wrong while updating. Please try again.");</script>';
                }
                $stmt->close();
            } else {
                echo '<script>alert("Error preparing update statement.");</script>';
            }
        } else {
            echo '<script>alert("' . $employee_err . '");</script>';
        }
    } else {
        // Add new employee
        if (empty($employee_err)) {
            $insert_sql = "INSERT INTO employee_list (employee_id, employee_name, position, department) VALUES (?, ?, ?, ?)";
            if ($stmt = $mysqli->prepare($insert_sql)) {
                $stmt->bind_param("ssss", $employee_id, $employee_name, $position, $department);
                if ($stmt->execute()) {
                    echo '<script>alert("Employee added successfully."); window.location.href="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '";</script>';
                } else {
                    echo '<script>alert("Something went wrong while adding. Please try again.");</script>';
                }
                $stmt->close();
            } else {
                echo '<script>alert("Error preparing insert statement.");</script>';
            }
        } else {
            echo '<script>alert("' . $employee_err . '");</script>';
        }
    }
}


?>
<?php include 'nav.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Employees</title>
    <link rel="stylesheet" href=" style.css?v=1.1">
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
        <h1 class="mb-4 text-center">Manage Employees</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by ID, Name, or Position" value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
        </form>
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#addModal">Add New Employee</button>
        </div>
      <table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th scope="col" class="text-center">ID</th>
            <th scope="col" class="text-center">Employee ID</th>
            <th scope="col" class="text-center">Employee Name</th>
            <th scope="col" class="text-center">Position</th>
            <th scope="col" class="text-center">Department</th>
            <th scope="col" class="text-center">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($employees)): ?>
            <tr>
                <td colspan="6" class="text-center">No employees found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($employees as $employee): ?>
                <tr>
                    <td class="text-center"><?php echo htmlspecialchars($employee['id']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($employee['employee_id']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($employee['employee_name']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($employee['position']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($employee['department']); ?></td>
                    <td class="text-center">
                        <button class="btn btn-warning btn-action" data-bs-toggle="modal" data-bs-target="#editModal" 
                            data-id="<?php echo $employee['id']; ?>" 
                            data-employee-id="<?php echo htmlspecialchars($employee['employee_id']); ?>" 
                            data-name="<?php echo htmlspecialchars($employee['employee_name']); ?>" 
                            data-position="<?php echo htmlspecialchars($employee['position']); ?>" 
                            data-department="<?php echo htmlspecialchars($employee['department']); ?>">
                            Edit
                        </button>
                        <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?delete=<?php echo $employee['id']; ?>" class="btn btn-danger btn-action">Delete</a>
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
                        <h5 class="modal-title" id="addModalLabel">Add New Employee</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" autocomplete="off">
                            <div class="form-group">
                                <label for="employee_id">Employee ID</label>
                                <input type="text" name="employee_id" id="employee_id" class="form-control <?php echo (!empty($employee_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($employee_id); ?>">
                                <span class="invalid-feedback"><?php echo $employee_err; ?></span>
                            </div>
                            <div class="form-group">
                                <label for="employee_name">Employee Name</label>
                                <input type="text" name="employee_name" id="employee_name" class="form-control <?php echo (!empty($employee_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($employee_name); ?>">
                                <span class="invalid-feedback"><?php echo $employee_err; ?></span>
                            </div>
                            <div class="form-group">
                                <label for="position">Position</label>
                                <input type="text" name="position" id="position" class="form-control <?php echo (!empty($employee_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($position); ?>">
                                <span class="invalid-feedback"><?php echo $employee_err; ?></span>
                            </div>
							<div class="form-group">
    <label for="department">Department</label>
    <select name="department" id="department" class="form-control <?php echo (!empty($employee_err)) ? 'is-invalid' : ''; ?>" required>
        <option value="">Select Department</option>
        <option value="Revenue Operations">Revenue Operations</option>
        <option value="Inside Sales">Inside Sales</option>
        <option value="Finance & Accounting">Finance & Accounting</option>
        <option value="People Operations">People Operations</option>
        <option value="Support & Professional Services">Support & Professional Services</option>
        <option value="Cloud Services & Infrastructure">Cloud Services & Infrastructure</option>
        <option value="Marketing">Marketing</option>
        <option value="Sales - EMEIA & APJ">Sales - EMEIA & APJ</option>
        <option value="Legal">Legal</option>
    </select>
    <span class="invalid-feedback"><?php echo $employee_err; ?></span>
</div>
                            <br>
                            <input type="submit" class="btn btn-primary" value="Add Employee">
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
                        <h5 class="modal-title" id="editModalLabel">Edit Employee</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" autocomplete="off">
                            <input type="hidden" name="id" id="edit_id">
                            <div class="form-group">
                                <label for="edit_employee_id">Employee ID</label>
                                <input type="text" name="employee_id" id="edit_employee_id" class="form-control <?php echo (!empty($employee_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($employee_id); ?>">
                                <span class="invalid-feedback"><?php echo $employee_err; ?></span>
                            </div>
                            <div class="form-group">
                                <label for="edit_employee_name">Employee Name</label>
                                <input type="text" name="employee_name" id="edit_employee_name" class="form-control <?php echo (!empty($employee_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($employee_name); ?>">
                                <span class="invalid-feedback"><?php echo $employee_err; ?></span>
                            </div>
                            <div class="form-group">
                                <label for="edit_position">Position</label>
                                <input type="text" name="position" id="edit_position" class="form-control <?php echo (!empty($employee_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($position); ?>">
                                <span class="invalid-feedback"><?php echo $employee_err; ?></span>
                            </div>
			<div class="form-group">
    <label for="edit_department">Department</label>
    <select name="department" id="edit_department" class="form-control <?php echo (!empty($employee_err)) ? 'is-invalid' : ''; ?>" required>
        <option value="Revenue Operations">Revenue Operations</option>
        <option value="Inside Sales">Inside Sales</option>
        <option value="Finance & Accounting">Finance & Accounting</option>
        <option value="People Operations">People Operations</option>
        <option value="Support & Professional Services">Support & Professional Services</option>
        <option value="Cloud Services & Infrastructure">Cloud Services & Infrastructure</option>
        <option value="Marketing">Marketing</option>
        <option value="Sales - EMEIA & APJ">Sales - EMEIA & APJ</option>
        <option value="Legal">Legal</option>
    </select>
    <span class="invalid-feedback"><?php echo $employee_err; ?></span>
</div>

                            <br>
                            <input type="submit" class="btn btn-primary" value="Update Employee">
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
    const employeeId = button.getAttribute('data-employee-id');
    const name = button.getAttribute('data-name');
    const position = button.getAttribute('data-position');
    const department = button.getAttribute('data-department');

    document.getElementById('edit_id').value = id;
    document.getElementById('edit_employee_id').value = employeeId;
    document.getElementById('edit_employee_name').value = name;
    document.getElementById('edit_position').value = position;
    document.getElementById('edit_department').value = department;
});
        </script>
    </div>
</body>
<?php include 'footer.php'; ?>
</html>