<?php
session_start();
include_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_mobile = $_POST['user_mobile'];
    $user_pass = $_POST['user_pass'];

    // Prepare the SQL statement using PDO
    $sql = "SELECT * FROM user WHERE user_mobile = :user_mobile AND user_pass = :user_pass";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_mobile' => $user_mobile, 'user_pass' => $user_pass]);

    // Check if the user exists
    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if the user is already active
        if ($user['is_active'] == 1) {
            echo '<script>alert("User already logged in elsewhere. Please logout from other session first.");</script>';
        } else {
            // Update is_active to true
            $sqlUpdate = "UPDATE user SET is_active = 1 WHERE user_mobile = :user_mobile";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute(['user_mobile' => $user_mobile]);

            // Authentication successful, set session variables
            $_SESSION['user_id'] = $user['user_role_type_id'];
            $_SESSION['user_mobile'] = $user['user_mobile'];
            $_SESSION['role'] = $user['user_role_type']; // Assuming the role is stored in the database

            // Redirect to dashboard
            echo '<script>
                    alert("Login successfully...!");
                    window.location.href = "dashboard.php";
                  </script>';
            exit();
        }
    } else {
        // Authentication failed, display error message
        echo '<script>alert("Invalid mobile number or password. Please try again.");</script>';
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 85vh;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 350px;
        }
        .form-container h2 {
            margin-bottom: 20px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .form-group input[type="submit"] {
            background-color: #5cb85c;
            color: #fff;
            border: none;
            cursor: pointer;
            width: 150px; /* Adjust the width of the button here */
            border-radius: 5px;
            margin-top: 12px;
        }
        .form-group input[type="submit"]:hover {
            background-color: #4cae4c;
        }
        .error {
            color: red;
        }
        .p {
            margin-top: 13px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Login</h2>
    <form id="loginForm" method="POST" action="">
        <div class="form-group">
            <label for="user_mobile">Mobile Number</label>
            <input type="text" id="user_mobile" name="user_mobile" placeholder="Mobile Number" required>
            <span class="error" id="phoneError"></span>
        </div>
        <div class="form-group">
            <label for="user_pass">Password</label>
            <input type="password" id="user_pass" name="user_pass" placeholder="Password" required>
            <span class="error" id="passwordError"></span>
        </div>
        <div class="form-group">
            <a href="forgetpassword.php">forget password</a>
        </div>
        <div class="form-group" style="text-align: center;">
            <input type="submit" value="Log In">
        </div>
        <div class="form-group" style="text-align: center;">
            <p>Don't have an account? <a href="register.php">SignUp</a></p>
        </div>
    </form>
     
</div>

</body>
</html>
