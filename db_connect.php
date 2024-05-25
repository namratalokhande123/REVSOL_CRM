<?php
// db_connect.php

$host = 'localhost'; // Database host
$dbname = 'revsol_crm'; // Database name
$user = 'root'; // Database username
$password = ''; // Database password

// Establish database connection using PDO
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);

// Set PDO error mode to exception
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
