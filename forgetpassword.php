<?php
include_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Start the session
    session_start();

    // Retrieve and sanitize form inputs
    $user_id = $_POST['user_id']; // Assuming user_id is provided in the form
    $user_pass = $_POST['user_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    // Validate passwords
    $passwordPattern = "/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";

    if (preg_match($passwordPattern, $user_pass) && $user_pass === $confirm_pass) {
        // Hash the password before storing it in the database for security
        $hashed_password = password_hash($user_pass, PASSWORD_BCRYPT);

        // Update password in the database
        // Assuming you have a way to identify the user (e.g., user_id from session or other means)
        // $user_id = $_SESSION['user_id']; // Uncomment if you are getting user_id from session
        try {
            $stmt = $pdo->prepare("UPDATE user SET user_pass = :user_pass WHERE user_role_type_id = :user_id");
            $stmt->bindParam(':user_pass', $user_pass);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            echo "<script>alert('Password updated successfully.'); window.location.href = 'login.php';</script>";
        } catch (PDOException $e) {
            echo "<script>alert('Error updating password: " . $e->getMessage() . "')</script>";
        }
    } else {
        echo "<script>alert('Password validation failed.')</script>";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forget Password Form</title>
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
    <h2>Forget Password</h2>
    <form id="forgetPasswordForm" method="POST" action="">
        <div class="form-group">
             <label for="user_pass">User id</label>
            <input type="password" id="user_id" name="user_id" placeholder="User_id" required>
            <label for="user_pass">Password</label>
            <input type="password" id="user_pass" name="user_pass" placeholder="Password" required>
            <span class="error" id="passError"></span>
        </div>
        <div class="form-group">
            <label for="confirm_pass">Confirm Password</label>
            <input type="password" id="confirm_pass" name="confirm_pass" placeholder="Confirm Password" required>
            <span class="error" id="confirmPassError"></span>
        </div>
        <div class="form-group" style="text-align: center;">
            <input type="submit" value="Submit">
        </div>
    </form>
</div>

<script>
    document.getElementById('forgetPasswordForm').addEventListener('submit', function(event) {
        const password = document.getElementById('user_pass').value;
        const confirmPassword = document.getElementById('confirm_pass').value;

        const passErrorElement = document.getElementById('passError');
        const confirmPassErrorElement = document.getElementById('confirmPassError');

        // Clear previous error messages
        passErrorElement.textContent = '';
        confirmPassErrorElement.textContent = '';

        // Password validation pattern: at least 8 characters, one uppercase, one special character, and one number
        const passwordPattern = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

        let valid = true;

        if (!passwordPattern.test(password)) {
            passErrorElement.textContent = 'Password must be at least 8 characters long and contain at least one uppercase letter, at least one digit, and one special character.';
            valid = false;
        }

        if (password !== confirmPassword) {
            confirmPassErrorElement.textContent = 'Passwords do not match.';
            valid = false;
        }

        if (!valid) {
            event.preventDefault();
        }
    });
</script>

</body>
</html>
