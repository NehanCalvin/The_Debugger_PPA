<?php
// DB Connection
$host = "localhost";
$user = "root"; // update if needed
$pass = "";
$db   = "PPA_Order";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Get POST data
$invoice       = $_POST['invoice'];
$company       = $_POST['company'];
$client        = $_POST['client'];
$phone         = $_POST['phone'];
$quantity      = $_POST['quantity'];
$fabric_data   = $_POST['fabric_data'];
$operations    = $_POST['operations'];
$total_price   = $_POST['total_price'];
$selling_price = $_POST['selling_price'];
$cost          = $_POST['cost'];
$profit        = $_POST['profit'];

// Insert order
$sql = "INSERT INTO orders (invoice, company, client, phone, quantity, operations, total_price, cost, selling_price, profit) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssissddd", $invoice, $company, $client, $phone, $quantity, $operations, $total_price, $cost, $selling_price, $profit);
$stmt->execute();
$order_id = $stmt->insert_id;
$stmt->close();

// Insert fabric items
$fabrics = json_decode($fabric_data, true);
if (!empty($fabrics)) {
    $sql_fabric = "INSERT INTO fabrics (order_id, itemName, shopName, billId, qty, amt, total) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_fabric = $conn->prepare($sql_fabric);

    foreach ($fabrics as $fabric) {
        $stmt_fabric->bind_param(
            "isssidd", 
            $order_id,
            $fabric['itemName'],
            $fabric['shopName'],
            $fabric['billId'],
            $fabric['qty'],
            $fabric['amt'],
            $fabric['total']
        );
        $stmt_fabric->execute();
    }
    $stmt_fabric->close();
}

$conn->close();

// Redirect
header("Location: order&sales.html?submitted=true");
exit();
?>
