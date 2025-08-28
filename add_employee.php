<?php
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$pass = "";
$db = "ppa_order";

$conn = new mysqli($host, $user, $pass, $db,3307);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB connection failed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "No data received"]);
    exit;
}

$name = $conn->real_escape_string($data['name'] ?? '');
$address = $conn->real_escape_string($data['address'] ?? '');
$phone = $conn->real_escape_string($data['phone'] ?? '');
$jobRole = $conn->real_escape_string($data['jobRole'] ?? '');
$bankName = $conn->real_escape_string($data['bankName'] ?? '');
$branch = $conn->real_escape_string($data['branch'] ?? '');
$accountNo = $conn->real_escape_string($data['accountNo'] ?? '');
$accountHolder = $conn->real_escape_string($data['accountHolder'] ?? '');

$sql = "INSERT INTO employee (name, address, phone, job_role, bank_name, branch, account_no, account_holder)
        VALUES ('$name', '$address', '$phone', '$jobRole', '$bankName', '$branch', '$accountNo', '$accountHolder')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}

$conn->close();
?>