<?php
include 'config.php';
session_start();

if (isset($_SESSION['loggedin'])) {
    header("location: dashboard.php");
    exit;
}

// processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // check if username is empty
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    // check if new password is empty
    if (empty(trim($_POST["new_password"]))) {
        $password_err = "Please enter your new password.";
    } elseif (strlen(trim($_POST["new_password"])) < 6) {
        $password_err = "Password must have atleast 6 characters.";
    } else {
        $password = trim($_POST["new_password"]);
    }
    
    // reset password with code
    if (empty(trim($_POST["password_reset"]))) {
        $password_reset_err = "Please enter your password reset code.";
    } else {
        $password_reset = trim($_POST["password_reset"]);
    }

    // validate username first then password reset code, if match then update password
    if (empty($username_err) && empty($password_reset_err)) {
        // prepare a select statement
        $sql = "SELECT user_id, username, firstname, lastname, pass_word FROM users WHERE username = ?";

        if ($stmt = $mysqli->prepare($sql)) {
            // bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_username);

            // set parameters
            $param_username = $username;

            // attempt to execute the prepared statement
            if ($stmt->execute()) {
                // store result
                $stmt->store_result();

                // check if username exists, if yes then verify password reset code
                if ($stmt->num_rows == 1) {
                    // bind result variables
                    $stmt->bind_result($id, $username, $firstname, $lastname, $hashed_password);
                    if ($stmt->fetch()) {
                        // check if password reset code is correct
                        $sql2 = "SELECT password_reset_code FROM password_reset WHERE user_id = '$id'";
                        $result = $mysqli->query($sql2);
                        $row = $result->fetch_assoc();
                        if ($password_reset == $row['password_reset_code']) {
                            // password reset code is correct, so update password
                            $sql3 = "UPDATE users SET pass_word = ? WHERE user_id = ?";

                            if ($stmt3 = $mysqli->prepare($sql3)) {
                                // bind variables to the prepared statement as parameters
                                $stmt3->bind_param("si", $param_password, $param_id);

                                // set parameters
                                $param_password = password_hash($password, PASSWORD_DEFAULT); // creates a password hash
                                $param_id = $id;

                                // attempt to execute the prepared statement
                                if ($stmt3->execute()) {
                                    echo "<script>alert('Your password has been recovered. Please login')</script>";
                                    header("login.php");
                                }
                            }
                        } else {
                            // display an error message if password reset code is not valid
                            $password_reset_err = "The password reset code you entered was not valid.";
                        }
                    }
                } else {
                    // display an error message if username doesn't exist
                    $password_reset_err = "No account found with that username.";
                }
            } else {
                echo "<script>alert('Something went wrong.')</script>";
            }
        // close statement
        $stmt->close();
    } else {
        echo "<script>alert('Something went wrong.')</script>";
    }
}
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Asset Management System</title>
    <link rel="stylesheet" href="style.css?v=1.1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <link rel="icon" type="image/x-icon" href="white.png">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col">
                <h1>Reset Password</h1>
                <p>Please try to remember your password first, then proceed here if you forgot totally.</p>

                <?php
                if (isset($password_recovered)) {
                    echo '<div class="alert alert-success">' . $password_recovered . '</div>';
                }

                if (isset($password_reset_err)) {
                    echo '<div class="alert alert-danger">' . $password_reset_err . '</div>';
                }
                ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" autocomplete="off">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control">
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    <label class="form-label">Password Reset Code</label>
                    <input type="password" name="password_reset" class="form-control">
                    <br>
                    <input type="submit" class="btn btn-primary mb-3" value="Reset Password">
                    <button type="button" class="btn btn-link mb-3" onclick="window.location.href='login.php';">Remember
                        Password?</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>