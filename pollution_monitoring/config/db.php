<?php
// config/db.php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'pollution_db';

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Set charset
$conn->set_charset("utf8mb4");
?>
