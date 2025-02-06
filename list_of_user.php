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

// Fetch all users along with their password reset codes
$sql = "
    SELECT u.user_id, u.firstname, u.lastname, u.username, u.account_type, u.created_at, pr.password_reset_code 
    FROM users u 
    LEFT JOIN password_reset pr ON u.user_id = pr.user_id
";
$result = $mysqli->query($sql);

// Check for query execution errors
if (!$result) {
    echo "Error fetching users: " . $mysqli->error;
    exit();
}

// Prepare arrays to hold users by account type
$users_by_account_type = [];
while ($row = $result->fetch_assoc()) {
    $account_type = test_input($row["account_type"]);
    if (!isset($users_by_account_type[$account_type])) {
        $users_by_account_type[$account_type] = [];
    }
    $users_by_account_type[$account_type][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Users - APAC-Asset Management System</title>
    <link rel="icon" type="image/x-icon" href="logo small.png">
    <link rel="stylesheet" href="style.css?v=1.1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }

        .container {
            margin-top: 50px;
        }

        h1 {
            color: #343a40;
            margin-bottom: 30px;
            text-align: center;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            background-color: #ffffff;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            background-color: #007bff;
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            font-size: 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-body {
            padding: 20px;
        }

        .action-buttons {
            display: flex;
            justify-content: space-between;
        }

        .btn-custom {
            background-color: #28a745;
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .btn-custom:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-2px);
 }

        .btn-warning {
            background-color: #ffc107;
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .btn-warning:hover {
            background-color: #e0a800;
            transform: translateY(-2px);
        }

        .no-users {
            text-align: center;
            font-size: 1.5rem;
            color: #6c757d;
            margin-top: 20px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>

<?php include 'nav.php'; ?>

<body>
    <div class="container">
        <h1 class="text-center">User List</h1>

        <?php foreach ($users_by_account_type as $account_type => $users) { ?>
        <h2 class="text-center"><?= ucfirst($account_type) ?> Users</h2>
        <div class="row">
            <?php foreach ($users as $user) { ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?= test_input($user["firstname"]) . " " . test_input($user["lastname"]) ?></h5>
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="card-body">
                        <p><strong>Username:</strong> <?= test_input($user["username"]) ?></p>
                        <p><strong>Account Type:</strong> <?= test_input($user["account_type"]) ?></p>
                        <p><strong>Created At:</strong> <?= test_input($user["created_at"]) ?></p>
                        <p><strong>Password Reset Code:</strong> <?= isset($user["password_reset_code"]) ? test_input($user["password_reset_code"]) : 'N/A' ?></p>
                    </div>
                    <div class="card-footer">
                        <div class="action-buttons">
                            <a href="edit_user.php?id=<?= test_input($user["user_id"]) ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                            <a href="delete_user.php?id=<?= test_input($user["user_id"]) ?>" class="btn btn-danger btn-sm" onClick="return confirm('Are you sure you want to delete this user?')"><i class="fas fa-trash-alt"></i> Delete</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } ?>

        <?php if (empty($users_by_account_type)) { ?>
        <div class="col-md-12">
            <h5 class="no-users">No users found</h5>
        </div>
        <?php } ?>

        <div class="footer">
            <a href="dashboard.php" class="btn btn-secondary">Go to Dashboard</a>
        </div>
    </div>
</body>
<?php include 'footer.php'; ?>
</html>

<?php
// Close the database connection
$mysqli->close();
?>