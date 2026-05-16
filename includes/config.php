<?php
// Database configuration
$host = 'localhost';
$dbname = 'earnings_db';
$username = 'root';
$password = '';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch(Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>