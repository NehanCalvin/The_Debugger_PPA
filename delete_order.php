<?php
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$order_id = $data['id'] ?? null;

if (!$order_id) {
    echo json_encode(['error' => 'Missing order_id']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
$stmt->bind_param("i", $order_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => $stmt->error]);
}
?>