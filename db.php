<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$db   = "event_db";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed. Please check db.php settings.");
}
?>