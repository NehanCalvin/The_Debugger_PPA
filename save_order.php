<?php
// DB Connection
$host = "localhost";
$user = "root"; // update if needed
$pass = "";
$db = "PPA_Order";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Sanitize and validate POST data
$invoice = isset($_POST['invoice']) ? trim($_POST['invoice']) : '';
$company = isset($_POST['company']) ? trim($_POST['company']) : '';
$client = isset($_POST['client']) ? trim($_POST['client']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
$fabric_data = isset($_POST['fabric_data']) ? $_POST['fabric_data'] : '[]';
$operations = isset($_POST['operations']) ? trim($_POST['operations']) : '';
$total_price = isset($_POST['total_price']) ? floatval($_POST['total_price']) : 0;
$selling_price = isset($_POST['selling_price']) ? floatval($_POST['selling_price']) : 0;
$cost = isset($_POST['cost']) ? floatval($_POST['cost']) : 0;
$profit = isset($_POST['profit']) ? floatval($_POST['profit']) : 0;
$advance_amount = isset($_POST['advance_amount']) ? floatval($_POST['advance_amount']) : 0;

// Insert order
$sql = "INSERT INTO orders (invoice, company, client, phone, quantity, operations, total_price, cost, selling_price, profit, advance_amount, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param(
    "ssssissddds",
    $invoice,
    $company,
    $client,
    $phone,
    $quantity,
    $operations,
    $total_price,
    $cost,
    $selling_price,
    $profit,
    $advance_amount
);

if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$order_id = $stmt->insert_id;
$stmt->close();

// Insert fabric items if any
$fabrics = json_decode($fabric_data, true);
if (is_array($fabrics) && count($fabrics) > 0) {
    $sql_fabric = "INSERT INTO fabrics (order_id, itemName, shopName, billId, qty, amt, total) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_fabric = $conn->prepare($sql_fabric);
    if (!$stmt_fabric) {
        die("Prepare fabrics failed: " . $conn->error);
    }

    foreach ($fabrics as $fabric) {
        // Validate fabric fields or set default empty
        $itemName = isset($fabric['itemName']) ? $fabric['itemName'] : '';
        $shopName = isset($fabric['shopName']) ? $fabric['shopName'] : '';
        $billId = isset($fabric['billId']) ? $fabric['billId'] : '';
        $qty = isset($fabric['qty']) ? intval($fabric['qty']) : 0;
        $amt = isset($fabric['amt']) ? floatval($fabric['amt']) : 0;
        $total = isset($fabric['total']) ? floatval($fabric['total']) : 0;

        $stmt_fabric->bind_param(
            "isssidd",
            $order_id,
            $itemName,
            $shopName,
            $billId,
            $qty,
            $amt,
            $total
        );
        $stmt_fabric->execute();
    }
    $stmt_fabric->close();
}

$conn->close();

// Redirect back to orders&sales.html with success flag
header("Location: order&sales.html?submitted=true");
exit();
?>