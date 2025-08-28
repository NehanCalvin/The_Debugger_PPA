<?php
header('Content-Type: application/json');

// Database connection details
$host = 'localhost';
$db = 'ppa_order';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Get raw JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing employee ID']);
    exit;
}

$id = intval($data['id']);

// Delete the employee
$sql = "DELETE FROM employee WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Deletion failed']);
}

$stmt->close();
$conn->close();
