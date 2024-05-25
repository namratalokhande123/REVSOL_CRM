<?php
session_start();
include_once 'db_connect.php';
include_once 'header.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$loggedInUserId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

try {
    // Determine the SQL query based on user role
    $sql = "";
    switch ($userRole) {
        case 'Level-1':
            $sql = "SELECT * FROM work_file WHERE level1_assigned_to = :user_id AND L1_status IS NULL ORDER BY id ASC LIMIT 1";
            break;
        case 'Data-Entry':
            $sql = "SELECT * FROM work_file WHERE data_entry_assigned_to = :user_id AND DA_status IS NULL ORDER BY id ASC LIMIT 1";
            break;
        case 'QA':
            $sql = "SELECT * FROM work_file WHERE quality_assigned_to = :user_id AND QA_status IS NULL ORDER BY id ASC LIMIT 1";
            break;
        default:
            throw new Exception("Invalid user role.");
    }

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $loggedInUserId);
    $stmt->execute();
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        // Read the uploaded file name
        $uploadedFileName = isset($_FILES['uploaded_file']['name']) ? $_FILES['uploaded_file']['name'] : '';
        // Read page count and comments
        $pageCount = isset($_POST['page_count']) ? $_POST['page_count'] : '';
        $comments = isset($_POST['comments']) ? $_POST['comments'] : '';

        if ($uploadedFileName === $file['work_file_name']) {
            // Determine the upload path and status value based on user role
            $uploadDirectory = '';
            $statusColumn = '';
            $statusValue = '';

            switch ($userRole) {
                case 'Level-1':
                    $uploadDirectory = 'Level1';
                    $statusColumn = 'L1_status';
                    $statusValue = 'Level1 done';
                    break;
                case 'Data-Entry':
                    $uploadDirectory = 'Data-Entry';
                    $statusColumn = 'DA_status';
                    $statusValue = 'Data Entry done';
                    break;
                case 'QA':
                    $uploadDirectory = 'QA';
                    $statusColumn = 'QA_status';
                    $statusValue = 'QA done';
                    break;
                default:
                    throw new Exception("Invalid user role.");
            }

            $uploadedFilePath = __DIR__ . DIRECTORY_SEPARATOR . $uploadDirectory . DIRECTORY_SEPARATOR . $uploadedFileName;

            // Move the uploaded file to the determined path
            if (move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $uploadedFilePath)) {
                // Update the status and timestamp in the database
                $updateStatusSql = "UPDATE work_file SET $statusColumn = :status, updated_on = NOW(), page_count = :page_count, comments = :comments WHERE id = :file_id";
                $updateStatusStmt = $pdo->prepare($updateStatusSql);
                $updateStatusStmt->bindParam(':status', $statusValue);
                $updateStatusStmt->bindParam(':file_id', $file['id']);
                $updateStatusStmt->bindParam(':page_count', $pageCount);
                $updateStatusStmt->bindParam(':comments', $comments);
                $updateStatusStmt->execute();

                // Display success message with the uploaded file name
                echo '<script>
                    alert("File ' . $uploadedFileName . ' has been uploaded successfully. You can get the next file to work on.");
                    window.location.href = "dashboard.php?page=level1_work";
                  </script>';
            } else {
                throw new Exception("Failed to move uploaded file.");
            }
        } else {
            echo '<script>
                alert("Please upload the correct file with the same name that you have downloaded.");
                window.location.href = "dashboard.php?page=level1_work";
              </script>';
        }
    } else {
        echo '<script>
            alert("No files available for upload.");
            window.location.href = "dashboard.php?page=level1_work";
          </script>';
    }
} catch (Exception $e) {
    echo "Failed to upload file: " . $e->getMessage();
}
?>
