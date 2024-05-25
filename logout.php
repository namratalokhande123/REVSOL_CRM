<?php
include_once 'db_connect.php';
session_start();

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // Get the user ID from the session
    $user_id = $_SESSION['user_id'];

    try {
        // Update the user's status in the database
        $sql = "UPDATE user SET is_active = 0 WHERE user_role_type_id  = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        
        // Destroy the session
        session_unset();
        session_destroy();

        // Send response indicating success
       //echo "success";
    } catch (PDOException $e) {
        // Handle database errors
        echo "Error updating user status: " . $e->getMessage();
    }
} else {
    // Send response indicating failure
  // echo "failure";
}
?>

