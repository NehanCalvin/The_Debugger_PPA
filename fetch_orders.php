<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Fetch single order by ID if provided
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Changed WHERE id to WHERE order_id
    $order_sql = "SELECT * FROM orders WHERE order_id = ?";
    $stmt_order = $conn->prepare($order_sql);
    if ($stmt_order === false) {
        http_response_code(500);
        echo json_encode(["error" => "Prepare failed: " . $conn->error]);
        exit();
    }
    $stmt_order->bind_param("i", $id);
    $stmt_order->execute();
    $order_result = $stmt_order->get_result();

    if ($order = $order_result->fetch_assoc()) {
        // Fetch fabric items for this order
        $fabrics_sql = "SELECT itemName, shopName, billId, qty, amt, total FROM fabrics WHERE order_id = ?";
        $stmt_fab = $conn->prepare($fabrics_sql);
        if ($stmt_fab === false) {
            http_response_code(500);
            echo json_encode(["error" => "Prepare failed: " . $conn->error]);
            exit();
        }
        $stmt_fab->bind_param("i", $id);
        $stmt_fab->execute();
        $fab_result = $stmt_fab->get_result();

        $fabrics = [];
        while ($fabric = $fab_result->fetch_assoc()) {
            $fabrics[] = $fabric;
        }
        $order['fabrics'] = $fabrics;

        $stmt_fab->close();
        $stmt_order->close();
        $conn->close();

        echo json_encode($order);
        exit();
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Order not found"]);
        $stmt_order->close();
        $conn->close();
        exit();
    }
}

// Handle optional search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$orders = [];

if (!empty($search)) {
    $search_param = "%$search%";
    $orders_sql = "SELECT * FROM orders WHERE invoice LIKE ? OR company LIKE ? ORDER BY created_at DESC";
    $stmt_orders = $conn->prepare($orders_sql);
    if ($stmt_orders === false) {
        http_response_code(500);
        echo json_encode(["error" => "Prepare failed: " . $conn->error]);
        exit();
    }
    $stmt_orders->bind_param("ss", $search_param, $search_param);
    $stmt_orders->execute();
    $orders_result = $stmt_orders->get_result();
} else {
    $orders_sql = "SELECT * FROM orders ORDER BY created_at DESC";
    $orders_result = $conn->query($orders_sql);
}

// For each order, fetch associated fabrics
while ($order = $orders_result->fetch_assoc()) {
    // Changed $order['id'] to $order['order_id']
    $order_id = $order['order_id'];

    $fabrics_sql = "SELECT itemName, shopName, billId, qty, amt, total FROM fabrics WHERE order_id = ?";
    $stmt = $conn->prepare($fabrics_sql);
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(["error" => "Prepare failed: " . $conn->error]);
        exit();
    }
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

$conn->close();

echo json_encode($orders);
?>