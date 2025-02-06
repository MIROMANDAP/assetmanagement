<?php
// pre-config commands
$cmd = "mkdir uploads && chmod 777 uploads";
system($cmd);

$msg = "You do not have permission to access this page.";
date_default_timezone_set('Asia/Shanghai');
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
define('DB_SERVER', "localhost");
define('DB_USERNAME', "root");
define('DB_PASSWORD', "");
define('DB_NAME', "asset-inv");
define('DB_PORT', 3306);
 
/* Attempt to connect to MySQL database */
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);

// Check connection
if($mysqli === false){
    die("ERROR: Could not connect. " . $mysqli->connect_error);
} else {
    // create table if not exists
    $sql = "CREATE TABLE IF NOT EXISTS `users` (
        `user_id` int(13) NOT NULL AUTO_INCREMENT,
        `username` varchar(255) NOT NULL,
        `firstname` varchar(255) NOT NULL,
        `lastname` varchar(255) NOT NULL,
        `pass_word` varchar(255) NOT NULL,
        `account_type` varchar(255) NOT NULL DEFAULT 'user',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`user_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    // create table for assets
    $sql2 = "CREATE TABLE IF NOT EXISTS `assets` (
        `asset_id` int(255) NOT NULL AUTO_INCREMENT,
        `brand` varchar(255) NOT NULL,
        `model` varchar(255) NOT NULL,
        `serial_number` varchar(255) NOT NULL,
        `asset_tag` varchar(255) NOT NULL,
        `asset_type` varchar(255) NOT NULL,
        `status` varchar(255) NOT NULL,
        `equipment_name` varchar(255) NOT NULL,
        `location_asset` varchar(255) NOT NULL,
        `price_value` int(255) NOT NULL,
        `date_acquired` varchar(255) NOT NULL,
        `remarks` text NOT NULL,
        `documents` text NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` text NOT NULL,
        `user_id` int(255) NOT NULL,
        PRIMARY KEY (`asset_id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    // create table for password reset code
    $sql3 = "CREATE TABLE IF NOT EXISTS `password_reset` (
        `password_reset_id` int(255) NOT NULL AUTO_INCREMENT,
        `password_reset_code` varchar(255) NOT NULL,
        `user_id` int(255) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`password_reset_id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    // execute query
    if ($mysqli->query($sql) === TRUE) {
        // echo "Table created successfully";
        if ($mysqli->query($sql2) === TRUE) {
            // echo "Table created successfully";
            if ($mysqli->query($sql3) === TRUE) {
                // echo "Table created successfully";
            } else {
                echo "Error creating table: " . $mysqli->error;
            }
        } else {
            echo "Error creating table: " . $mysqli->error;
        }
    } else {
        echo "Error creating table: " . $mysqli->error;
    }

   /* // insert superadmin user
    $superadmin_hash = password_hash("superadmin", PASSWORD_DEFAULT);
    $superadmin_reset_code = substr(md5("superadmin"), 0, 13);
    $sql4 = "SELECT * FROM users WHERE username = 'superadmin'";
    if ($result = $mysqli->query($sql4)) {
        if ($result->num_rows > 0) {
            // do nothing
        } else {
            $sql3 = "INSERT INTO users (`user_id`, `username`, `firstname`, `lastname`, `pass_word`, `created_at`, `account_type`) VALUES
            (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, 'superadmin');";
            if ($stmt = $mysqli->prepare($sql3)) {
                $stmt->bind_param("sssss", $param_id, $param_username, $param_firstname, $param_lastname, $param_pass_word);
                
                // set parameters
                $param_id = 1;
                $param_username = "superadmin";
                $param_firstname = "superadmin";
                $param_lastname = "superadmin";
                $param_pass_word = $superadmin_hash;
                
                    // attempt to execute the prepared statement
                    if ($stmt->execute()) {
                        // insert password reset code into password_reset table
                        $sql2 = "INSERT INTO password_reset (password_reset_code, user_id) VALUES (?, ?)";
                        if ($stmt2 = $mysqli->prepare($sql2)) {
                            // bind variables to the prepared statement as parameters
                            $stmt2->bind_param("si", $param_password_reset_code, $param_user_id);
                            
                            // set parameters
                            $param_password_reset_code = $superadmin_reset_code;
                            // set param user_id from username
                            $sql3 = "SELECT user_id FROM users WHERE username = 'superadmin'";
                            $result = $mysqli->query($sql3);
                            $row = $result->fetch_assoc();
                            $param_user_id = $row['user_id'];
        
                            
                            // attempt to execute the prepared statement
                            if ($stmt2->execute()) {
                                // redirect to login page
                                // echo "<script>alert('User $firstname $lastname has been registered.')</script>";
                                // header("register_user.php");
                            } else {
                                echo "Something went wrong. Please try again later.";
                            }
                        }
                    } else {
                        echo "Something went wrong. Please try again later.";
                    }
                }
            $stmt->close();
        }
    } */
}
?>