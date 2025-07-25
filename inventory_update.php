<?php
// Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "bugs";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "Connection Failed: " . $conn->connect_error]));
}

// Handle POST request for updating inventory
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $itemName = $_POST['itemName'];
    $colour = $_POST['colour'];
    $rawprice = $_POST['rawprice'];
    $supplier = $_POST['supplier'];
    $phone = $_POST['phone'];
    $quantity = $_POST['quantity'];
    $total_price = $_POST['total_price'];
    $img = $_POST['img'];

    $sql = "UPDATE inventory SET 
            itemName = ?, 
            colour = ?, 
            rawprice = ?, 
            supplier = ?, 
            phone = ?, 
            quantity = ?, 
            total_price = ?, 
            Img = ? 
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsssdsi", $itemName, $colour, $rawprice, $supplier, $phone, $quantity, $total_price, $img, $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Item updated successfully"]);
    } else {
        echo json_encode(["success" => false, "error" => "Error updating item: " . $conn->error]);
    }

    $stmt->close();
}

// Handle GET request for fetching single item details
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM inventory WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        echo json_encode(["success" => true, "data" => $item]);
    } else {
        echo json_encode(["success" => false, "error" => "Item not found"]);
    }

    $stmt->close();
}

$conn->close();
?>
