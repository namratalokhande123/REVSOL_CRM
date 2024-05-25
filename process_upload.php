<?php
session_start();
include_once 'db_connect.php';





// Ensure the user_id is set and is an integer
if (!isset($_SESSION['user_id'])) {
    exit("Invalid user session.");
}

// Function to create a unique batch ID in the format YYYYMMDD_HHMMSS
// Function to create a batch ID in the format YYYYMMDD_NN (where NN is auto-incremented)
function createBatchID($pdo) {
    $currentDate = date("Ymd");
    $sql_get_max_suffix = "SELECT MAX(CAST(SUBSTRING(batch_id, 10) AS UNSIGNED)) AS max_suffix FROM batch_master WHERE batch_id LIKE :currentDate";
    $stmt_get_max_suffix = $pdo->prepare($sql_get_max_suffix);
    $likePattern = $currentDate . "_%";
    $stmt_get_max_suffix->bindParam(':currentDate', $likePattern);
    $stmt_get_max_suffix->execute();
    $result = $stmt_get_max_suffix->fetch(PDO::FETCH_ASSOC);

    $maxSuffix = $result['max_suffix'];
    $nextSuffix = $maxSuffix ? $maxSuffix + 1 : 1; // Increment suffix or start with 1
    $batch_id = $currentDate . "_" . str_pad($nextSuffix, 2, '0', STR_PAD_LEFT); // Pad suffix to ensure it's 2 digits

    return $batch_id;
}

// Function to insert a new batch in the batch_master table and return the batch ID
function createNewBatch($pdo) {
    $batchNo = createBatchID($pdo);
    $status = "Active";
    $createdOn = date("Y-m-d H:i:s");

    $sql_insert_batch = "INSERT INTO batch_master (batch_id, status, created_on) VALUES (:batch_id, :status, :created_on)";
    $stmt_insert_batch = $pdo->prepare($sql_insert_batch);
    $stmt_insert_batch->bindParam(':batch_id', $batchNo);
    $stmt_insert_batch->bindParam(':status', $status);
    $stmt_insert_batch->bindParam(':created_on', $createdOn);
    if ($stmt_insert_batch->execute()) {
        logActivity($pdo, "Created new batch with batch_id: $batchNo", "Success", $_SESSION['user_id']);
        return $batchNo; // Return the generated batch_id
    } else {
        $errorInfo = $stmt_insert_batch->errorInfo();
        logActivity($pdo, "Error inserting batch: " . $errorInfo[2], "Error", $_SESSION['user_id']);
        exit("Error inserting batch: " . $errorInfo[2]);
    }
}

// Function to log activity
function logActivity($pdo, $activity, $status, $userId) {
    $createdOn = date("Y-m-d H:i:s");
    $sql_insert_log = "INSERT INTO activity_log (activity, status, user_id, created_on) VALUES (:activity, :status, :user_id, :created_on)";
    $stmt_insert_log = $pdo->prepare($sql_insert_log);
    $stmt_insert_log->bindParam(':activity', $activity);
    $stmt_insert_log->bindParam(':status', $status);
    $stmt_insert_log->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt_insert_log->bindParam(':created_on', $createdOn);
    if (!$stmt_insert_log->execute()) {
        $errorInfo = $stmt_insert_log->errorInfo();
        echo "Error logging activity: " . $errorInfo[2] . "<br>";
    }
}

// Define client download and raw folders
$clientDownloadFolder = __DIR__ . '/CLIENT-DOWNLOAD/';
$rawFolder = __DIR__ . '/RAW/';

// Initialize counters
$totalFilesRead = 0;
$totalFilesUploaded = 0;
$totalRecordsInserted = 0;
$totalDuplicatesSkipped = 0;

// Check if client download folder exists
if (!is_dir($clientDownloadFolder)) {
    logActivity($pdo, "Client download folder does not exist or is not accessible.", "Error", $_SESSION['user_id']);
    exit("Error: Client download folder does not exist or is not accessible.<br>");
}

// Check if raw folder exists, create if not
if (!is_dir($rawFolder)) {
    if (!mkdir($rawFolder, 0777, true)) {
        logActivity($pdo, "Failed to create raw folder.", "Error", $_SESSION['user_id']);
        exit("Error: Failed to create raw folder.<br>");
    }
}

// Scan client download folder for files
$files = scandir($clientDownloadFolder);

// Create a new batch and get the batch ID
$batchID = createNewBatch($pdo);
logActivity($pdo, "Batch created with ID: $batchID", "Success", $_SESSION['user_id']);

// Process each file
foreach ($files as $file) {
    // Ignore current and parent directory entries
    if ($file != "." && $file != "..") {
        $source = $clientDownloadFolder . $file;
        $destination = $rawFolder . $file;

        // Check if the file already exists in the database
        $sql_check = "SELECT COUNT(*) FROM work_file WHERE work_file_name = :work_file_name";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->bindParam(':work_file_name', $file);
        $stmt_check->execute();
        $count = $stmt_check->fetchColumn();

        if ($count > 0) { // File is a duplicate
            logActivity($pdo, "Duplicate file found in the database: $file. Skipping upload and database insertion.", "Duplicate", $_SESSION['user_id']);
            $totalDuplicatesSkipped++;
            continue; // Skip to the next file
        }

        // Copy file from client download folder to raw folder
        if (copy($source, $destination)) {
            logActivity($pdo, "File copied: $file", "Success", $_SESSION['user_id']);
            $totalFilesUploaded++;

            // Insert record into work_file table
            $workfileName = $file;
            $status = "Uploaded to Raw";
            $createdOn = date("Y-m-d H:i:s");

            $sql_insert = "INSERT INTO work_file (batch_id, work_file_name, status, created_on) VALUES (:batch_id, :work_file_name, :status, :created_on)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->bindParam(':batch_id', $batchID);
            $stmt_insert->bindParam(':work_file_name', $workfileName);
            $stmt_insert->bindParam(':status', $status);
            $stmt_insert->bindParam(':created_on', $createdOn);

            if ($stmt_insert->execute()) {
                logActivity($pdo, "Record inserted for file: $file", "Success", $_SESSION['user_id']);
                $totalRecordsInserted++;
            } else {
                // Handle database insertion error
                $errorInfo = $stmt_insert->errorInfo();
                logActivity($pdo, "Error inserting record for file: $file - " . $errorInfo[2], "Error", $_SESSION['user_id']);
            }
        } else {
            // Handle file copying error
            logActivity($pdo, "Error copying file: $file", "Error", $_SESSION['user_id']);
        }
        // Increment total files read
        $totalFilesRead++;
    }
}

// Log summary activity
$loggedInUserId = $_SESSION['user_id'];
logActivity($pdo, "Batch Processing Completed - Total Files Read: $totalFilesRead, Total Files Uploaded: $totalFilesUploaded, Total Records Inserted: $totalRecordsInserted, Total Duplicate Files Skipped: $totalDuplicatesSkipped", "Completed", $loggedInUserId);

// Print summary
echo "Total Files Read: $totalFilesRead<br>";
echo "Total Files Uploaded: $totalFilesUploaded<br>";
echo "Total Records Inserted: $totalRecordsInserted<br>";
echo "Total Duplicate Files Skipped: $totalDuplicatesSkipped<br>";
?>
