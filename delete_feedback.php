<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "PPA_feedback";

// Connect to DB
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  http_response_code(500);
  echo "Database connection failed.";
  exit;
}

// Accept method override
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'DELETE') {
  $id = intval($_POST['id']);

  $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
  $stmt->bind_param("i", $id);

  if ($stmt->execute()) {
    echo "Feedback deleted successfully.";
  } else {
    http_response_code(500);
    echo "Failed to delete feedback.";
  }

  $stmt->close();
  $conn->close();
} else {
  http_response_code(405);
  echo "Method not allowed.";
}
?>