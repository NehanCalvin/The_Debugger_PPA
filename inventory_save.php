<?php
// DB Connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Getting POST data from inventory_create
$itemName       = $_POST['itemName'];
$colour         = $_POST['colour'];
$rawprice       =$_POST['rawprice'];
$supplier       = $_POST['supplier'];
$phone          = $_POST['phone'];
$quantity       = $_POST['quantity'];
$total_price    = $_POST['total_price'];

//insert to table
$sql = "INSERT INTO inventory (itemName, colour, rawprice, supplier, phone, quantity, total_price) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssissddd", $itemName, $colour, $rawprice, $supplier, $phone, $quantity, $total_price);
$stmt->execute();
$inventory_id = $stmt->insert_id;
$stmt->close();

$conn->close();

// Redirect
header("Location: inventory_management.html?submitted=true");
exit();
?>