<?php
include 'config.php';

session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// get the firstname of the user and save to variable
$id = $_SESSION['id'];
$result = mysqli_query($mysqli, "SELECT * FROM users WHERE user_id = '$id'");
$row = mysqli_fetch_array($result);
$firstname = $row['firstname'];
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
    <link href="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.css" rel="stylesheet">
    <script src="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.js"></script>
    <script src="https://unpkg.com/bootstrap-table@1.22.1/dist/extensions/print/bootstrap-table-print.min.js"></script>
</head>

<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <div class="row">
            <div class="col">
                <h1>Help Center</h1>
                <br>

                <h3>Login and Forgot Password</h3>
                <ul>
                    <li><a href="help/login.php">Logging in to the system</a></li>
                    <li><a href="help/forgot-password.php">I forgot my password</a>
                    <li><a href="help/change-password.php">I want to change my password</a>
                </ul>
                <hr>
                <h3>Dashboard</h3>
                <ul>
                    <li><a href="help/dashboard.php">An overview of the dashboard</a></li>
                </ul>
                <hr>
                <h3>Assets</h3>
                <ul>
                    <li><a href="help/assets.php">An overview of the assets page</a></li>
                    <li><a href="help/addassets.php">Adding assets into the system</a></li>
                    <li><a href="help/editdeleteassets.php">Editing and Deleting assets in the system</a></li>
                </ul>
                <hr>
                <h3>System Admin Guide</h3>
                <ul>
                    <li><a href="help/register.php">Registering a new user</a></li>
                    <li><a href="help/forgothelp.php">Helping user with forgotten password</a></li>
                </ul>
            </div>
        </div>
    </div>
</body>

</html>