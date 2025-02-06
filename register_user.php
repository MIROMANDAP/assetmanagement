<?php
include 'config.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');

if (!isset($_SESSION['errors'])) {
    $_SESSION['errors'] = [];
}

if ($_SESSION['account_type'] !== "superadmin") {
    header('Location: 404.php');
    exit;
}

function test_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$errors = [];
$fields = [
    'firstname' => '',
    'lastname' => '',
    'username' => '',
    'password' => '',
    'confirm_password' => '',
    'account_type' => ''
];
 if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($fields as $field => $value) {
        if (isset($_POST[$field])) {
            $fields[$field] = test_input($_POST[$field]);
            if (empty($fields[$field])) {
                $errors[$field . '_err'] = "Please enter your " . str_replace('_', ' ', $field) . ".";
            }
        }
    }

    // Generate username based on first name and last name
    if (!empty($fields['firstname']) && !empty($fields['lastname'])) {
        $fields['username'] = strtolower($fields['firstname'] . '.' . $fields['lastname'] . '@ibossasia.com');
    }

    // Validate username uniqueness
    if (empty($errors['username_err'])) {
        $sql = "SELECT user_id FROM users WHERE username = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $fields['username']);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows == 1) {
                $errors['username_err'] = "This username is already taken.";
            }
            $stmt->close();
        }
    }

    // Validate password
    if (strlen($fields['password']) < 6) {
        $errors['password_err'] = "Password must have at least 6 characters.";
    } elseif ($fields['password'] !== $fields['confirm_password']) {
        $errors['confirm_password_err'] = "Passwords did not match.";
    }

    // Insert into database if no errors
if (empty($errors)) {
    $hashed_password = password_hash($fields['password'], PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (firstname, lastname, username, pass_word, account_type) VALUES (?, ?, ?, ?, ?)";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("sssss", $fields['firstname'], $fields['lastname'], $fields['username'], $hashed_password, $fields['account_type']);
        if ($stmt->execute()) {
            // Insert into password_reset table
            $sql2 = "INSERT INTO password_reset (password_reset_code, user_id) VALUES (?, ?)";
            if ($stmt2 = $mysqli->prepare($sql2)) {
                // Generate a unique password reset code
                $password_reset_code = substr(md5($fields['username']), 0, 13);
                $param_user_id = $mysqli->insert_id; // Get the last inserted user ID

                // Bind parameters
                $stmt2->bind_param("si", $password_reset_code, $param_user_id);
                
                // Execute the statement
                if (!$stmt2->execute()) {
                    $_SESSION['errors'][] = "Failed to create password reset entry: " . $stmt2->error;
                }
                $stmt2->close();
            } else {
                $_SESSION['errors'][] = "Prepare failed for password reset: " . $mysqli->error;
            }

            $_SESSION['success'] = "User  {$fields['firstname']} {$fields['lastname']} has been registered.";
        } else {
            $_SESSION['errors'][] = "User  registration failed: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['errors'][] = "Prepare failed: " . $mysqli->error;
    }
}
}
// Display any errors or success messages
if (!empty($_SESSION['errors'])) {
    foreach ($_SESSION['errors'] as $error) {
        echo "<div class='alert alert-danger'>$error</div>";
    }
    // Clear errors after displaying them
    $_SESSION['errors'] = [];
}

if (isset($_SESSION['success'])) {
    echo "<div class='alert alert-success'>{$_SESSION['success']}</div>";
    unset($_SESSION['success']);
}
?>
<!DOCTYPE html>
<?php include 'nav.php'; ?>
<html lang="en">
<script>
    function updateUsername() {
        const firstname = document.querySelector('input[name="firstname"]').value;
        const lastname = document.querySelector('input[name="lastname"]').value;
        const usernameField = document.querySelector('input[name="username"]');

        // Generate username
        if (firstname && lastname) {
            const username = firstname.toLowerCase() + '.' + lastname.toLowerCase() + '@ibossasia.com';
            usernameField.value = username;
        } else {
            usernameField.value = ''; // Clear the username if either field is empty
        }
    }

    // Attach event listeners to input fields
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('input[name="firstname"]').addEventListener('input', updateUsername);
        document.querySelector('input[name="lastname"]').addEventListener('input', updateUsername);
    });
</script>
<head>
    <meta charset="UTF-8">
    <title>APAC-Asset Management System</title>
    <link rel="icon" type="image/x-icon" href="logo small.png">
    <link rel="stylesheet" href="style.css?v=1.1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <link rel="icon" type="image/x-icon" href="white.png">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col">
                <h1>Register to the system</h1>
                <p>Please fill information.</p>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" autocomplete="off">
    <label class="form-label">Username</label>
    <input type="text" name="username" class="form-control" value="<?php echo isset($fields['username']) ? htmlspecialchars($fields['username']) : ''; ?>" readonly>
    <span class="invalid-feedback"><?php echo $errors['username_err'] ?? ''; ?></span>
    
    <label class="form-label">First Name</label>
    <input type="text" name="firstname" class="form-control <?php echo isset($errors['firstname_err']) ? 'is-invalid' : ''; ?>">
    <span class="invalid-feedback"><?php echo $errors['firstname_err'] ?? ''; ?></span>
    
    <label class="form-label">Last Name</label>
    <input type="text" name="lastname" class="form-control <?php echo isset($errors['lastname_err']) ? 'is-invalid' : ''; ?>">
    <span class="invalid-feedback"><?php echo $errors['lastname_err'] ?? ''; ?></span>
    
    <label class="form-label">Password</label>
    <input type="password" name="password" class="form-control <?php echo isset($errors['password_err']) ? 'is-invalid' : ''; ?>" data-toggle="tooltip" data-placement="right" title="Password must have 6 Characters">
    <span class="invalid-feedback"><?php echo $errors['password_err'] ?? ''; ?></span><br>
    
    <label class="form-label">Confirm Password</label>
    <input type="password" name="confirm_password" class="form-control <?php echo isset($errors['confirm_password_err']) ? 'is-invalid' : ''; ?>">
    <span class="invalid-feedback"><?php echo $errors['confirm_password_err'] ?? ''; ?></span><br>
    
    <label class="form-label">User  Type</label>
    <select name="account_type" class="form-select <?php echo isset($errors['account_type_err']) ? 'is-invalid' : ''; ?>">
        <option value="">---Select---</option>
        <option value="superadmin">SuperAdmin</option>
        <option value="admin">Admin</option>
        <option value="user">User  </option>
    </select><br>
    
    <input type="submit" class="btn btn-primary mb-3" value="Register">
    <input type="reset" class="btn btn-secondary mb-3" value="Reset">
    <a href="dashboard.php" class="btn btn-secondary mb-3">Go to Dashboard</a>
</form>
            </div>
        </div>
    </div>
</body>
<?php include 'footer.php'; ?>
</html>