<?php
include 'config.php';

session_start();
if ($_SESSION['account_type'] !== "superadmin") {
    header('Location: 404.php');
    exit();
}

// Function to test and sanitize input
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$user_id = $firstname = $lastname = $username = $account_type = "";
$firstname_err = $lastname_err = $username_err = $account_type_err = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_GET['id'])) {
        $user_id = test_input($_GET['id']);
    } else {
        echo "User ID not set.";
        exit();
    }

    // Validate input fields
    if (empty(trim($_POST["firstname"]))) {
        $firstname_err = "Please enter your first name.";
    } else {
        $firstname = test_input($_POST["firstname"]);
    }

    if (empty(trim($_POST["lastname"]))) {
        $lastname_err = "Please enter your last name.";
    } else {
        $lastname = test_input($_POST["lastname"]);
    }

    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        $username = test_input($_POST["username"]);
    }

    if (empty(trim($_POST["account_type"]))) {
        $account_type_err = "Please select an account type.";
    } else {
        $account_type = test_input($_POST["account_type"]);
    }

    // Update user if no errors
    if (empty($firstname_err) && empty($lastname_err) && empty($username_err) && empty($account_type_err)) {
        $sql = "UPDATE users SET firstname=?, lastname=?, username=?, account_type=? WHERE user_id=?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("ssssi", $firstname, $lastname, $username, $account_type, $user_id);
            if ($stmt->execute()) {
                echo "<script>alert('User updated successfully.'); window.location.href='list_of_user.php';</script>";
            } else {
                echo "<script>alert('Something went wrong. Please try again later.');</script>";
                error_log($stmt->error);  // Log error for debugging
            }
        }
        $stmt->close();
    }
}

// Fetch user details if the page is loaded via GET
if (isset($_GET['id'])) {
    $user_id = test_input($_GET['id']);

    // Fetch user details
    $sql = "SELECT firstname, lastname, username, account_type FROM users WHERE user_id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($firstname, $lastname, $username, $account_type);
            $stmt->fetch();
        } else {
            echo "No user found.";
            exit();
        }
    }
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User - APAC-Asset Management System</title>
    <link rel="icon" type="image/x-icon" href="logo small.png">
    <link rel="stylesheet" href="style.css?v=1.1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>



<body>
    <div class="container mt-5">
        <h1 class="text-center">Edit User</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $user_id); ?>" method="post" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">First Name</label>
                <input type="text" name="firstname" class="form-control" value="<?php echo htmlspecialchars($firstname); ?>">
                <span class="text-danger"><?php echo $firstname_err; ?></span>
            </div>
            <div class="mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" name="lastname" class="form-control" value="<?php echo htmlspecialchars($lastname); ?>">
                <span class="text-danger"><?php echo $lastname_err; ?></span>
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>">
                <span class="text-danger"><?php echo $username_err; ?></span>
            </div>
            <div class="mb-3">
                <label class="form-label">Account Type</label>
                <select name="account_type" class="form-select">
                    <option value="">---Select---</option>
					<option value="superadmin" <?php echo ($account_type == "superadmin") ? 'selected' : ''; ?>>Super Admin</option>
                    <option value="admin" <?php echo ($account_type == "admin") ? 'selected' : ''; ?>>Admin</option>
                    <option value="user" <?php echo ($account_type == "user") ? 'selected' : ''; ?>>User</option>
                </select>
                <span class="text-danger"><?php echo $account_type_err; ?></span>
            </div>
            <input type="submit" class="btn btn-primary mb-3" value="Update">
            <a href="list_of_user.php" class="btn btn-secondary mb-3">Cancel</a>
        </form>
    </div>
</body>

</html>

<?php
// Close the database connection
$mysqli->close();
?>
