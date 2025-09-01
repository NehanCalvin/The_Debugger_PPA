<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "PPA_Order";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Fetch all products with required fields
$sql = "SELECT id, product_number, product_name, client, size, quantity, price, total_price, fully_paid, advance, created_at 
        FROM products";
$result = $conn->query($sql);

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['total_price'] = (float) $row['total_price'];
        $row['quantity'] = (int) $row['quantity'];
        $row['advance'] = (float) $row['advance'];
        $row['fully_paid'] = (int) $row['fully_paid'];
        $row['created_at'] = $row['created_at']; // keep as string
        $products[] = $row;
    }
}

echo json_encode($products);
$conn->close();
