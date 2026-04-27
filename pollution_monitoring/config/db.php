<?php
// config/db.php

// Use environment variables for Vercel, fallback to localhost for XAMPP
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$dbname = getenv('DB_NAME') ?: 'pollution_db';
$port = getenv('DB_PORT') ?: 3306;

// Create connection
$conn = new mysqli($host, $user, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Set charset
$conn->set_charset("utf8mb4");
?>
