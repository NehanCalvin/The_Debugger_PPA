<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ppa_order";

$conn = new mysqli($servername, $username, $password, $dbname, 3307);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$employee_id = isset($data['employee_id']) ? intval($data['employee_id']) : 0;
$num_items = isset($data['num_items']) ? intval($data['num_items']) : 0;
$price_per_item = isset($data['price_per_item']) ? floatval($data['price_per_item']) : 0;
$total_payment = isset($data['total_payment']) ? floatval($data['total_payment']) : 0;

if ($employee_id <= 0 || $num_items <= 0 || $price_per_item <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO employee_payments (employee_id, num_items, price_per_item, total_payment) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("iidd", $employee_id, $num_items, $price_per_item, $total_payment);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Payment saved successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error saving payment: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>