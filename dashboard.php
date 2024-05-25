<?php
session_start();
include_once 'db_connect.php';
include 'header.php';

// Check if a page parameter is set, and sanitize it
$page = isset($_GET['page']) ? basename($_GET['page']) : 'default';

?>




<?php include 'footer.php'; ?>
