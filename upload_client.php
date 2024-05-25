

<?php
session_start();
include_once 'db_connect.php';





// Ensure the user_id is set and is an integer
if (!isset($_SESSION['user_id'])) {
    exit("Invalid user session.");
}
// Define the source and destination paths using DIR constant
$source_folder = __DIR__ . '/QA/';
$destination_folder = __DIR__ . '/client-upload/';

// Ensure the destination folder exists
if (!is_dir($destination_folder)) {
    mkdir($destination_folder, 0777, true);
}

// Initialize a flag to check if any file has been moved
$file_moved = false;

// Open the source directory
if ($handle = opendir($source_folder)) {
    // Loop through the files in the source directory
    while (false !== ($file = readdir($handle))) {
        // Skip the current and parent directory entries
        if ($file != "." && $file != "..") {
            // Construct the full file paths
            $source_path = $source_folder . $file;
            $destination_path = $destination_folder . $file;

            // Copy the file (not move)
            if (copy($source_path, $destination_path)) {
                // Set file moved flag to true
                $file_moved = true;
            } else {
                $escaped_file = htmlspecialchars($file, ENT_QUOTES, 'UTF-8');
                echo "<script>alert('An error occurred while copying the file $escaped_file.');</script>";
            }
        }
    }
    // Close the directory handle
    closedir($handle);

    // If at least one file was moved, show success message
    if ($file_moved) {
        echo "File's uploaded successfully to Client upload folder.";
    } else {
        echo "<script>alert('No files were found in the QA folder or an error occurred.');</script>";
    }
} else {
    echo "<script>alert('Could not open the source directory.');</script>";
}
?>
