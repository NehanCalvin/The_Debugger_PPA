<?php
// Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "PPA_Order";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Connection Failed: ' . $conn->connect_error
    ]);
    exit();
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
    $imagePath = ""; // Empty by default
    if (isset($_FILES['itemImage']) && $_FILES['itemImage']['error'] == 0) {
        $uploadDir = 'uploads/';
        
        // Create uploads directory if the img doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['itemImage']['name']);
        $imagePath = $uploadDir . $fileName;
        
        // Check for file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/pjpeg', 'image/x-png'];
        $fileType = $_FILES['itemImage']['type'];
        $fileExtension = strtolower(pathinfo($_FILES['itemImage']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        
        if (in_array($fileType, $allowedTypes) || in_array($fileExtension, $allowedExtensions)) {
            if (move_uploaded_file($_FILES['itemImage']['tmp_name'], $imagePath)) {
                // Image uploaded successfully???
            } else {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to upload image'
                ]);
                exit();
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Only JPG, JPEG, PNG files are allowed'
            ]);
            exit();
        }
    }
    
    // Save data into database (created_date and updated_date are automatically handled by database)
    $sql = "INSERT INTO inventory (itemName, colour, rawprice, supplier, phone, quantity, Img, total_price) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssdssisd", $itemName, $colour, $rawprice, $supplier, $phone, $quantity, $imagePath, $total_price);
        
        if ($stmt->execute()) {
            $inventory_id = $stmt->insert_id;
            
            // Log the CREATE action
            $log_sql = "INSERT INTO inventory_log (inventory_id, action, itemName, supplier, quantity, total_price, change_details) 
                       VALUES (?, 'CREATE', ?, ?, ?, ?, ?)";
            $log_stmt = $conn->prepare($log_sql);
            if ($log_stmt) {
                $change_details = json_encode([
                    'itemName' => $itemName,
                    'colour' => $colour,
                    'rawprice' => $rawprice,
                    'supplier' => $supplier,
                    'phone' => $phone,
                    'quantity' => $quantity,
                    'total_price' => $total_price
                ]);
                $log_stmt->bind_param("issids", $inventory_id, $itemName, $supplier, $quantity, $total_price, $change_details);
                $log_stmt->execute();
                $log_stmt->close();
            }
            
            $stmt->close();
            $conn->close();
            
            // JSON success response
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Inventory saved successfully',
                'inventory_id' => $inventory_id
            ]);
            exit();
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $stmt->error
            ]);
            exit();
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Database prepare error: ' . $conn->error
        ]);
        exit();
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}
?>
