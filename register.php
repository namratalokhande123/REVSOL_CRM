<?php
session_start();
include_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['user_mobile']; // Get mobile number
    $password = $_POST['user_pass']; // Set password from user input
    $role = $_POST['role']; // Get selected role

    // Validate the phone number (must be 10 digits and not start with 0)
    if (!preg_match('/^(?:\+91|0)?[6789]\d{9}$/', $phone)) {
        echo "<script>alert('Invalid phone number. Please enter a valid number');</script>";
    } else {
        // Check if the mobile number already exists in the database
        $sqlCheck = "SELECT COUNT(*) AS count FROM user WHERE user_mobile = ?";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([$phone]);
        $rowCountCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC)['count'];

        if ($rowCountCheck > 0) {
            echo "<script>alert('Mobile number already registered!');</script>";
        } else {
            // Generate user_role_type_id based on the selected role
            $roleAbbreviation = "";
            switch ($role) {
                case "Level-1":
                    $roleAbbreviation = "L1";
                    break;
                case "QA":
                    $roleAbbreviation = "QA";
                    break;
                case "Data-Entry":
                    $roleAbbreviation = "DA";
                    break;
                case "Upload To Raw":
                    $roleAbbreviation = "UR";
                    break;
                case "Upload To Client":
                    $roleAbbreviation = "UC";
                    break;
                default:
                    echo "<script>alert('Invalid role selected!');</script>";
                    exit();
            }

            // Fetch the current count of users for the selected role
            $sqlCount = "SELECT COUNT(*) AS count FROM user WHERE user_role_type = ?";
            $stmtCount = $pdo->prepare($sqlCount);
            $stmtCount->execute([$role]);
            $rowCount = $stmtCount->fetch(PDO::FETCH_ASSOC)['count'];

            // Increment the count and format it
            $roleCount = str_pad($rowCount + 1, 2, '0', STR_PAD_LEFT);
            $user_role_type_id = $roleAbbreviation . "-" . $roleCount;

            // Get the current date and time
            $created_on = date('Y-m-d H:i:s');

            // Set is_active to false (0) for new user registrations
            $is_active = 0;

            // SQL to insert data into the database
            $sql = "INSERT INTO user (user_mobile, user_pass, user_role_type, user_role_type_id, created_on, is_active) VALUES (?, ?, ?, ?, ?, ?)";
            
            // Prepare and execute the SQL statement
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$phone, $password, $role, $user_role_type_id, $created_on, $is_active])) {
                echo "<script>alert('Registration successful! Your user_role_id is: " . htmlspecialchars($user_role_type_id) . "'); window.location.href = 'login.php';</script>";
            } else {
                echo "Error: " . $stmt->errorInfo()[2];
            }
        }
    }
}

// Function to handle user logout and set is_active to 'no'
function logoutUser($userId, $pdo) {
    $sql = "UPDATE user SET is_active = 0 WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    session_destroy();
    echo "<script>alert('Logout successful!'); window.location.href = 'login.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
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
            width: 150px;
            border-radius: 5px;
        }
        .form-group input[type="submit"]:hover {
            background-color: #4cae4c;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Register</h2>
    <form id="registerForm" method="POST" action="">
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" id="user_mobile" name="user_mobile" placeholder="Enter your phone number" required>
            <span class="error" id="phoneError"></span>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="text" id="user_pass" name="user_pass" placeholder="Enter your password" required>
            <span class="error" id="passError"></span>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="" disabled selected>Select an option</option>
                <option value="Level-1">Level 1</option>
                <option value="QA">QA</option>
                <option value="Data-Entry">Data Entry</option>
                <option value="Upload To Raw">Upload To Raw</option>
                <option value="Upload To Client">Upload To Client</option>
            </select>
        </div>
        <div class="form-group" style="text-align: center;">
            <input type="submit" value="Register">
        </div>
    </form>
</div>

<script>
    document.getElementById('registerForm').addEventListener('submit', function(event) {
        let valid = true;

        // Password validation: exactly the phone number followed by '@123'
        const password = document.getElementById('user_pass').value;
        const phone = document.getElementById('user_mobile').value;

        // Check if the password is exactly the phone number followed by '@123'
        const expectedPassword = phone + '@123';

        if (password !== expectedPassword) {
            document.getElementById('passError').textContent = 'Password format should be your phone number followed by @123';
            valid = false;
        } else {
            document.getElementById('passError').textContent = '';
        }

        // Phone number validation: only numbers allowed and must be 10 digits
        const phonePattern = /^[0-9]{10}$/;
        if (!phonePattern.test(phone)) {
            document.getElementById('phoneError').textContent = 'Invalid phone number. Please enter a 10-digit number.';
            valid = false;
        } else {
            document.getElementById('phoneError').textContent = '';
        }

        if (!valid) {
            event.preventDefault();
        }
    });
</script>

</body>
</html>
