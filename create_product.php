<?php
$host = "localhost";
$user = "root";
$password = "";
$db = "PPA_Order";

$conn = new mysqli($host, $user, $password, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form data safely
$product_number = $_POST['product_number'] ?? '';
$product_name = $_POST['product_name'] ?? '';
$client = $_POST['client'] ?? '';
$sizes = $_POST['sizes'] ?? [];
$quantities = $_POST['quantity'] ?? [];
$price = (float) ($_POST['price'] ?? 0);
$total_price = (float) ($_POST['total_price'] ?? 0);
$payment_option = $_POST['payment_option'] ?? '';
$advance = isset($_POST['advance']) ? (float) $_POST['advance'] : 0;

// Format sizes & quantities into comma-separated strings
$sizeString = "";
$quantityString = "";
foreach ($sizes as $size) {
    $qty = isset($quantities[$size]) ? (int) $quantities[$size] : 0;
    $sizeString .= $size . ",";
    $quantityString .= $qty . ",";
}
$sizeString = rtrim($sizeString, ",");
$quantityString = rtrim($quantityString, ",");

// Handle image upload
$imagePath = "";
if (!empty($_FILES["product_image"]["name"])) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    $imagePath = $targetDir . basename($_FILES["product_image"]["name"]);
    move_uploaded_file($_FILES["product_image"]["tmp_name"], $imagePath);
}

// Insert query (11 columns, 10 placeholders + NOW())
$sql = "INSERT INTO products 
    (product_number, product_name, client, size, quantity, price, total_price, image_path, fully_paid, advance, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// Determine payment status
$fully_paid = ($payment_option === "Fully Paid") ? 1 : 0;

// Bind parameters (s = string, d = double/float, i = integer)
$stmt->bind_param(
    "sssssddssi",
    $product_number,   // s
    $product_name,     // s
    $client,           // s
    $sizeString,       // s
    $quantityString,   // s
    $price,            // d
    $total_price,      // d
    $imagePath,        // s
    $fully_paid,       // i
    $advance           // i (advance is numeric, store as integer or float, better int for consistency)
);

if ($stmt->execute()) {
    header("Location: product.php");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>