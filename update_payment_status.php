<?php
// Connect to DB
$conn = new mysqli("localhost", "root", "", "PPA_Order");
if ($conn->connect_error) {
    http_response_code(500);
    die("Database connection failed: " . $conn->connect_error);
}

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['invoice']) || !isset($data['status'])) {
    http_response_code(400);
    echo "Missing invoice or status.";
    exit;
}

$invoice = $data['invoice'];
$status = $data['status'];

// Define payment flags
$advanced_paid = 0;
$full_paid = 0;

if ($status === "advance") {
    $advanced_paid = 1;
} elseif ($status === "full") {
    $full_paid = 1;
}

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("UPDATE orders SET advanced_paid = ?, full_paid = ? WHERE invoice = ?");
$stmt->bind_param("iis", $advanced_paid, $full_paid, $invoice);

if ($stmt->execute()) {
    echo "success";
} else {
    http_response_code(500);
    echo "Database error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>