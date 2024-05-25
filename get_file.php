<?php
session_start();
include_once 'db_connect.php';
include_once 'header.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Define folder paths based on user roles
$folderPaths = [
    'Level-1' => 'RAW',
    'Data-Entry' => 'Level1',
    'QA' => 'Data-Entry'
];

// Retrieve the user's role from the session
$userRole = $_SESSION['role'];

// Check if the user role is valid and get the corresponding folder path
if (!isset($folderPaths[$userRole])) {
    echo "Invalid user role.";
    exit();
}

$folderPath = $folderPaths[$userRole];
$loggedInUserId = $_SESSION['user_id'];

try {
    // Check if the user has already downloaded a file and not uploaded it yet
    $checkSql = "";
    switch ($userRole) {
        case 'Level-1':
            $checkSql = "SELECT * FROM work_file WHERE level1_assigned_to = :user_id AND L1_status IS NULL ORDER BY id ASC LIMIT 1";
            break;
        case 'Data-Entry':
            $checkSql = "SELECT * FROM work_file WHERE data_entry_assigned_to = :user_id AND DA_status IS NULL ORDER BY id ASC LIMIT 1";
            break;
        case 'QA':
            $checkSql = "SELECT * FROM work_file WHERE quality_assigned_to = :user_id AND QA_status IS NULL ORDER BY id ASC LIMIT 1";
            break;
        default:
            throw new Exception("Invalid user role.");
    }

    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->bindParam(':user_id', $loggedInUserId);
    $checkStmt->execute();
    $existingFile = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingFile) {
        // If a file is already assigned to the user, download it again
        $filePath = __DIR__ . DIRECTORY_SEPARATOR . $folderPath . DIRECTORY_SEPARATOR . $existingFile['work_file_name'];

        if (!file_exists($filePath)) {
            echo '<script>alert("Error: File not found at path ' . addslashes($filePath) . '");</script>';
            exit();
        }

        // Set a session variable to indicate a successful download
        $_SESSION['download_message'] = "File " . $existingFile['work_file_name'] . " has been downloaded.";
      
        // Set headers for file download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        readfile($filePath);
        exit();
    }

    // Start a transaction
    $pdo->beginTransaction();

    // Determine the column to check based on the user role
    $assignmentColumn = '';
    $datetimeColumn = '';
    if ($userRole == 'Level-1') {
        $assignmentColumn = 'level1_assigned_to';
        $datetimeColumn = 'L1_created_on';
    } elseif ($userRole == 'Data-Entry') {
        $assignmentColumn = 'data_entry_assigned_to';
        $datetimeColumn = 'DA_created_on';
    } elseif ($userRole == 'QA') {
        $assignmentColumn = 'quality_assigned_to';
        $datetimeColumn = 'QA_created_on';
    } else {
        echo "Invalid user role.";
        exit();
    }

    // Find the first unassigned record in the work_file table for the specific role
    $sql = "SELECT * FROM work_file WHERE $assignmentColumn IS NULL ORDER BY id ASC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        // Check if the file exists before updating the database
        $filePath = __DIR__ . DIRECTORY_SEPARATOR . $folderPath . DIRECTORY_SEPARATOR . $file['work_file_name'];

        if (!file_exists($filePath)) {
            echo '<script>alert("No files available are available for this folder");</script>';
            $pdo->rollBack();
            exit();
        }

        // Assign the file to the logged-in user
        $updateSql = "UPDATE work_file SET $assignmentColumn = :user_id, $datetimeColumn = NOW() WHERE id = :file_id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->bindParam(':user_id', $loggedInUserId);
        $updateStmt->bindParam(':file_id', $file['id']);
        $updateStmt->execute();

        // Commit the transaction
        $pdo->commit();

        // Set a session variable to indicate a successful download
        $_SESSION['download_message'] = "File " . $file['work_file_name'] . " has been downloaded.";

        // Set headers for file download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        readfile($filePath);
        exit();
    } else {
        // Display message for no files available for download using JavaScript alert
        echo '<script>alert("No files available for download.");</script>';
    }
} catch (Exception $e) {
    // Roll back the transaction if something failed
    $pdo->rollBack();
    echo "Failed to get file: " . $e->getMessage();
}
?>
