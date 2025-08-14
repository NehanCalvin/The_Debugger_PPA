<?php
// Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "PPA_Order";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "Connection Failed: " . $conn->connect_error]));
}

// Handle DELETE request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    // Get item details before deleting for logging
    $select_sql = "SELECT * FROM inventory WHERE id = ?";
    $select_stmt = $conn->prepare($select_sql);
    $select_stmt->bind_param("i", $id);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $item_data = $result->fetch_assoc();
    $select_stmt->close();
    
    if ($item_data) {
        $sql = "DELETE FROM inventory WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                // Log the DELETE action
                $log_sql = "INSERT INTO inventory_log (inventory_id, action, itemName, supplier, quantity, total_price, change_details) 
                           VALUES (?, 'DELETE', ?, ?, ?, ?, ?)";
                $log_stmt = $conn->prepare($log_sql);
                if ($log_stmt) {
                    $change_details = json_encode([
                        'itemName' => $item_data['itemName'],
                        'colour' => $item_data['colour'],
                        'rawprice' => $item_data['rawprice'],
                        'supplier' => $item_data['supplier'],
                        'phone' => $item_data['phone'],
                        'quantity' => $item_data['quantity'],
                        'total_price' => $item_data['total_price'],
                        'action' => 'deleted'
                    ]);
                    $log_stmt->bind_param("issids", $id, $item_data['itemName'], $item_data['supplier'], $item_data['quantity'], $item_data['total_price'], $change_details);
                    $log_stmt->execute();
                    $log_stmt->close();
                }
                
                echo json_encode(["success" => true, "message" => "Item deleted successfully"]);
            } else {
                echo json_encode(["success" => false, "error" => "Item not found or already deleted"]);
            }
        } else {
            echo json_encode(["success" => false, "error" => "Error deleting item: " . $conn->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "error" => "Item not found"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method or missing ID"]);
}

$conn->close();
?>
