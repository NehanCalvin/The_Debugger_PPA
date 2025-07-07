<?php
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'stitch_db'; // Change to your database name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die(json_encode(['error' => 'Database connection failed']));
}

// Fetch employee data
$sql = "SELECT name, position, salary, attendance, bonus FROM employees";
$result = $conn->query($sql);

$employees = [];

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
  }
}

echo json_encode($employees);

$conn->close();
?>
