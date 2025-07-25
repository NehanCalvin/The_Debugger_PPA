<?php
$host = 'localhost';        // Your DB host
$user = 'root';             // Your MySQL username
$password = '';             // Your MySQL password
$database = 'PPA_Order';    // Your actual database name

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>