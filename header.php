<?php

include_once 'db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();

}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="logo.jpg" alt="Company Logo" class="company-logo">
        </div>
        <div class="search-bar">
            <input type="text" placeholder="Search...">
        </div>
        <div class="user-profile">
            <img src="avatar.png" alt="User Avatar" class="user-avatar">
            <span>
                <?php 
                if (isset($_SESSION['user_id'])) {
                    echo htmlspecialchars($_SESSION['user_id'], ENT_QUOTES, 'UTF-8');
                } else {
                    echo "Guest";
                }
                ?>
            </span>
            <form action="logout.php" method="post" class="logout-form">
                <button type="submit" onclick="confirmLogout()">Logout</button>
            </form>
        </div>
    </div>
    <div class="sidebar">
        <ul>
           <li><a href="dashboard.php?page=dashboard">Dashboard</a></li>

            <?php
            if (isset($_SESSION['role'])) {
                $role = $_SESSION['role'];
                if ($role === 'Level-1' || $role === 'Data-Entry' || $role === 'QA') {
                    echo '<li><a href="dashboard.php?page=level1_work"> Work</a></li>';
                } elseif ($role === 'Upload To Client') {
                    echo '<li><a href="dashboard.php?page=upload_to_client">Upload to Client</a></li>';
                } elseif ($role === 'Upload To Raw') {
                    echo '<li><a href="dashboard.php?page=upload_to_raw">Upload to RAW</a></li>';
                } else {
                    echo '<li><a href="dashboard.php?page=dashboard">Dashboard</a></li>';
                }
            } else {
                echo '<li><a href="dashboard.php?page=dashboard">Dashboard</a></li>';
            }
            ?>
               <li><a href="dashboard.php?page=reports">Reports</a></li>
        </ul>
    </div>
    <div class="main-content">
        <?php
        // Check if a page parameter is set, and sanitize it
        $page = isset($_GET['page']) ? basename($_GET['page']) : 'dashboard';

        // Define allowed pages for different roles
        $allowed_pages = [];
        if (isset($_SESSION['role'])) {
            $role = $_SESSION['role'];
            if ($role === 'Level-1' || $role === 'Data-Entry' || $role === 'QA') {
                $allowed_pages = ['level1_work'];
            } elseif ($role === 'Upload To Client') {
                $allowed_pages = ['upload_to_client'];
            } elseif ($role === 'Upload To Raw') {
                $allowed_pages = ['upload_to_raw'];
            }
        }

        // Load the appropriate content based on the page parameter
        if (in_array($page, $allowed_pages) && file_exists("$page.php")) {
            include "$page.php";
        } else {
          
        }
        ?>
    </div>
    <div class="footer">
        &copy; 2024 Company Name. All rights reserved.
    </div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript">
        // Function to confirm logout and handle the action
        function confirmLogout() {
            if (confirm("Are you sure you want to logout?")) {
                // If user confirms, send AJAX request to logout.php
                $.ajax({
                    type: "POST",
                    url: "login.php",
                    success: function(data) {
                        if(data === "success") {
                            alert("Logout successful!");
                            // Redirect to login page
                            window.location.href = "login.php";
                        } else {
                            alert("Logout successful!");
                              window.location.href = "login.php";
                        }
                    },
                    error: function(xhr, status, error) {
                        alert("An error occurred while logging out.");
                    }
                });
            }
        }
    </script>
</body>
</html>