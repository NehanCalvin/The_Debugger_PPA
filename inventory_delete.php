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
    
    $sql = "DELETE FROM inventory WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Item deleted successfully"]);
        } else {
            echo json_encode(["success" => false, "error" => "Item not found or already deleted"]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Error deleting item: " . $conn->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method or missing ID"]);
}

$conn->close();
?>
