<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "PPA_feedback"; // your database

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM feedback";
$result = $conn->query($sql);

$feedbacks = array();
while ($row = $result->fetch_assoc()) {
  $feedbacks[] = $row;
}

header('Content-Type: application/json');
echo json_encode($feedbacks);

$conn->close();
?>