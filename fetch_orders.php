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

// Handle optional search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$orders = [];

if (!empty($search)) {
    $search = "%$search%";
    $orders_sql = "SELECT * FROM orders WHERE invoice LIKE ? OR company LIKE ? ORDER BY created_at DESC";
    $stmt_orders = $conn->prepare($orders_sql);
    $stmt_orders->bind_param("ss", $search, $search);
    $stmt_orders->execute();
    $orders_result = $stmt_orders->get_result();
} else {
    $orders_sql = "SELECT * FROM orders ORDER BY created_at DESC";
    $orders_result = $conn->query($orders_sql);
}

while ($order = $orders_result->fetch_assoc()) {
    $order_id = $order['id'];

    // Fetch fabric items
    $fabrics_sql = "SELECT * FROM fabrics WHERE order_id = ?";
    $stmt = $conn->prepare($fabrics_sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $fabrics = [];
    while ($fabric = $result->fetch_assoc()) {
        $fabrics[] = $fabric;
    }

    $order['fabrics'] = $fabrics;
    $orders[] = $order;

    $stmt->close();
}

// Close DB
$conn->close();

// Return as JSON
echo json_encode($orders);
?>
