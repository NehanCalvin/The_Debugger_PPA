<?php
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "PPA_feedback";

// Connect
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get POST data
$id = $_POST['id'];
$name = $_POST['name'];
$product = $_POST['product'];
$rating = $_POST['rating'];
$comment = $_POST['comment'];

// Update query
$sql = "UPDATE feedback SET 
  customer_name = ?, 
  product_name = ?, 
  rating = ?, 
  comments = ?
WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssisi", $name, $product, $rating, $comment, $id);

if ($stmt->execute()) {
  echo "Success";
} else {
  echo "Error updating record: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
