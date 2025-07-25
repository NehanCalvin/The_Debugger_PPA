<?php
// Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "bugs";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get POST data
    $itemName = mysqli_real_escape_string($conn, $_POST['itemName']);
    $colour = mysqli_real_escape_string($conn, $_POST['colour']);
    $rawprice = floatval($_POST['rawprice']);
    $supplier = mysqli_real_escape_string($conn, $_POST['supplier']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $quantity = intval($_POST['quantity']);
    
    // Calculate total price
    $total_price = $rawprice * $quantity;
    
    // Handle image upload
    $imagePath = "";
    if (isset($_FILES['itemImage']) && $_FILES['itemImage']['error'] == 0) {
        $uploadDir = 'uploads/';
        
        // Create uploads directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['itemImage']['name']);
        $imagePath = $uploadDir . $fileName;
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (in_array($_FILES['itemImage']['type'], $allowedTypes)) {
            if (move_uploaded_file($_FILES['itemImage']['tmp_name'], $imagePath)) {
                // Image uploaded successfully
            } else {
                die("Error: Failed to upload image.");
            }
        } else {
            die("Error: Only JPG, JPEG, PNG files are allowed.");
        }
    }
    
    // Insert data into database
    $sql = "INSERT INTO inventory (itemName, colour, rawprice, supplier, phone, quantity, Img, total_price) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssdssids", $itemName, $colour, $rawprice, $supplier, $phone, $quantity, $imagePath, $total_price);
        
        if ($stmt->execute()) {
            $inventory_id = $stmt->insert_id;
            $stmt->close();
            $conn->close();
            
            // Redirect back to form
            header("Location: inventory_create.html?success=true");
            exit();
        } else {
            die("Error: " . $stmt->error);
        }
    } else {
        die("Error: " . $conn->error);
    }
} else {
    die("Error: Invalid request method.");
}
?>
