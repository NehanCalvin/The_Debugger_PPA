<?php
// Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Connect to database
$conn = new mysqli("localhost", "root", "", "PPA_feedback");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Collect data
$name = $_POST['name'] ?? '';
$product = $_POST['product'] ?? '';
$rating = $_POST['rating'] ?? '';
$comments = $_POST['comments'] ?? '';

// Validate required fields
if (empty($name) || empty($product) || empty($rating)) {
    echo json_encode(["success" => false, "message" => "Please fill all required fields."]);
    exit();
}

// Prepare insert
$sql = "INSERT INTO feedback (customer_name, product_name, rating, comments) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
    exit();
}

$stmt->bind_param("ssis", $name, $product, $rating, $comments);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Feedback submitted successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Execute failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
