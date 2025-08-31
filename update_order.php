<?php
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['order_id'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$order_id = $data['order_id'];
$invoice = $data['invoice'];
$company = $data['company'];
$client = $data['client'];
$phone = $data['phone'];
$quantity = $data['quantity'];
$operations = $data['operations'];
$total_price = $data['total_price'];
$cost = $data['cost'];
$selling_price = $data['selling_price'];
$profit = $data['profit'];
$advance_amount = $data['advance_amount'];
$advanced_paid = $data['advanced_paid'];
$full_paid = $data['full_paid'];

$stmt = $conn->prepare("UPDATE orders SET invoice=?, company=?, client=?, phone=?, quantity=?, operations=?, total_price=?, cost=?, selling_price=?, profit=?, advance_amount=?, advanced_paid=?, full_paid=? WHERE order_id=?");

if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param(
    "ssssissdddddii",
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
    $advance_amount,
    $advanced_paid,
    $full_paid,
    $order_id
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => $stmt->error]);
}
?>