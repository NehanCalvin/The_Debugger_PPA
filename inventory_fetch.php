<?php
// Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "PPA_Order";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection Failed: " . $conn->connect_error]));
}

// Fetch from database
$sql = "SELECT * FROM inventory ORDER BY id DESC";
$result = $conn->query($sql);

$inventoryData = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Ensure image path is properly formatted
        if (!empty($row['Img']) && !filter_var($row['Img'], FILTER_VALIDATE_URL)) {
            $row['Img'] = $row['Img'];
        } else if (empty($row['Img'])) {
            $row['Img'] = 'https://via.placeholder.com/200x150?text=No+Image';
        }
        $inventoryData[] = $row;
    }
}

$conn->close();

// JSON response
header('Content-Type: application/json');
echo json_encode($inventoryData);
exit();
?>
