<?php
header('Content-Type: application/json');

// DB config
$host = "localhost";
$user = "root";
$pass = "";
$db = "PPA_Order";

// Connect
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid order ID"]);
    exit();
}

// Fetch order
$order_sql = "SELECT * FROM orders WHERE id = ?";
$stmt_order = $conn->prepare($order_sql);
$stmt_order->bind_param("i", $id);
$stmt_order->execute();
$order_result = $stmt_order->get_result();

if ($order_result->num_rows !== 1) {
    echo json_encode(["error" => "Order not found"]);
    exit();
}

$order = $order_result->fetch_assoc();

// Fetch fabric items
$fabrics_sql = "SELECT * FROM fabrics WHERE order_id = ?";
$stmt_fabrics = $conn->prepare($fabrics_sql);
$stmt_fabrics->bind_param("i", $id);
$stmt_fabrics->execute();
$fabrics_result = $stmt_fabrics->get_result();

$fabrics = [];
while ($fabric = $fabrics_result->fetch_assoc()) {
    $fabrics[] = $fabric;
}
$order['fabrics'] = $fabrics;

// Close DB
$conn->close();

echo json_encode($order);
?>