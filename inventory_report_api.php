<?php
// Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "PPA_Order";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection Failed: " . $conn->connect_error]));
}

// Set content type to JSON
header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(["success" => false, "message" => "Invalid JSON input"]);
    exit;
}

$reportType = $input['reportType'] ?? 'activity';
$actionType = $input['actionType'] ?? 'all';
$startDate = $input['startDate'] ?? date('Y-m-d');
$endDate = $input['endDate'] ?? date('Y-m-d');

try {
    $data = [];
    $summary = [];
    
    switch ($reportType) {
        case 'activity':
            $data = generateActivityReport($conn, $actionType, $startDate, $endDate);
            $summary = generateActivitySummary($conn, $actionType, $startDate, $endDate);
            break;
            
        case 'inventory':
            $data = generateInventoryReport($conn, $startDate, $endDate);
            $summary = generateInventorySummary($conn, $startDate, $endDate);
            break;
            
        case 'summary':
            $data = generateSummaryReport($conn, $startDate, $endDate);
            break;
    }
    
    echo json_encode([
        "success" => true,
        "data" => $data,
        "summary" => $summary
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error generating report: " . $e->getMessage()
    ]);
}

$conn->close();

function generateActivityReport($conn, $actionType, $startDate, $endDate) {
    $sql = "SELECT * FROM inventory_log 
            WHERE DATE(action_date) BETWEEN ? AND ?";
    
    $params = [$startDate, $endDate];
    $types = "ss";
    
    if ($actionType !== 'all') {
        $sql .= " AND action = ?";
        $params[] = $actionType;
        $types .= "s";
    }
    
    $sql .= " ORDER BY action_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    $stmt->close();
    return $data;
}

function generateActivitySummary($conn, $actionType, $startDate, $endDate) {
    $summary = [];
    
    // Total actions
    $sql = "SELECT action, COUNT(*) as count 
            FROM inventory_log 
            WHERE DATE(action_date) BETWEEN ? AND ?";
    
    $params = [$startDate, $endDate];
    $types = "ss";
    
    if ($actionType !== 'all') {
        $sql .= " AND action = ?";
        $params[] = $actionType;
        $types .= "s";
    }
    
    $sql .= " GROUP BY action";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $totalActions = 0;
    while ($row = $result->fetch_assoc()) {
        $summary[strtolower($row['action']) . '_count'] = $row['count'];
        $totalActions += $row['count'];
    }
    $summary['total_actions'] = $totalActions;
    
    // Total value affected
    $sql = "SELECT SUM(total_price) as total_value 
            FROM inventory_log 
            WHERE DATE(action_date) BETWEEN ? AND ? AND total_price IS NOT NULL";
    
    $params = [$startDate, $endDate];
    $types = "ss";
    
    if ($actionType !== 'all') {
        $sql .= " AND action = ?";
        $params[] = $actionType;
        $types .= "s";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $summary['total_value_affected'] = $row['total_value'] ?? 0;
    
    $stmt->close();
    return $summary;
}

function generateInventoryReport($conn, $startDate, $endDate) {
    $sql = "SELECT * FROM inventory 
            WHERE DATE(created_date) BETWEEN ? AND ?
            ORDER BY created_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    $stmt->close();
    return $data;
}

function generateInventorySummary($conn, $startDate, $endDate) {
    $summary = [];
    
    // Total items
    $sql = "SELECT COUNT(*) as total_items,
                   SUM(quantity) as total_quantity,
                   SUM(total_price) as total_value,
                   AVG(total_price) as avg_value
            FROM inventory 
            WHERE DATE(created_date) BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $summary['total_items'] = $row['total_items'];
    $summary['total_quantity'] = $row['total_quantity'];
    $summary['total_inventory_value'] = $row['total_value'];
    $summary['average_item_value'] = $row['avg_value'];
    
    // Unique suppliers
    $sql = "SELECT COUNT(DISTINCT supplier) as unique_suppliers
            FROM inventory 
            WHERE DATE(created_date) BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $summary['unique_suppliers'] = $row['unique_suppliers'];
    
    $stmt->close();
    return $summary;
}

function generateSummaryReport($conn, $startDate, $endDate) {
    $data = [];
    
    // Current inventory totals
    $sql = "SELECT COUNT(*) as current_items,
                   SUM(quantity) as current_quantity,
                   SUM(total_price) as current_value
            FROM inventory";
    
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $data['current_items'] = $row['current_items'];
    $data['current_quantity'] = $row['current_quantity'];
    $data['current_value'] = $row['current_value'];
    
    // Activity summary for date range
    $sql = "SELECT action, COUNT(*) as count
            FROM inventory_log 
            WHERE DATE(action_date) BETWEEN ? AND ?
            GROUP BY action";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $data[strtolower($row['action']) . '_actions'] = $row['count'];
    }
    
    // Top suppliers by value
    $sql = "SELECT supplier, SUM(total_price) as total_value
            FROM inventory 
            GROUP BY supplier
            ORDER BY total_value DESC
            LIMIT 5";
    
    $result = $conn->query($sql);
    $topSuppliers = [];
    while ($row = $result->fetch_assoc()) {
        $topSuppliers[] = $row['supplier'] . ' (LKR ' . number_format($row['total_value'], 2) . ')';
    }
    $data['top_suppliers'] = implode(', ', $topSuppliers);
    
    // Most expensive items
    $sql = "SELECT itemName, total_price
            FROM inventory 
            ORDER BY total_price DESC
            LIMIT 3";
    
    $result = $conn->query($sql);
    $expensiveItems = [];
    while ($row = $result->fetch_assoc()) {
        $expensiveItems[] = $row['itemName'] . ' (LKR ' . number_format($row['total_price'], 2) . ')';
    }
    $data['most_expensive_items'] = implode(', ', $expensiveItems);
    
    $stmt->close();
    return $data;
}
?>