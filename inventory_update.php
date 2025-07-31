<?php
// Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "PPA_Order";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "Connection Failed: " . $conn->connect_error]));
}

// Handle POST request for updating inventory
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $itemName = mysqli_real_escape_string($conn, $_POST['itemName']);
    $colour = mysqli_real_escape_string($conn, $_POST['colour']);
    $rawprice = floatval($_POST['rawprice']);
    $supplier = mysqli_real_escape_string($conn, $_POST['supplier']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $quantity = intval($_POST['quantity']);
    $total_price = floatval($_POST['total_price']);
    
    // Get current image path from database first
    $currentImgSql = "SELECT Img FROM inventory WHERE id = ?";
    $currentImgStmt = $conn->prepare($currentImgSql);
    $currentImgStmt->bind_param("i", $id);
    $currentImgStmt->execute();
    $currentImgResult = $currentImgStmt->get_result();
    $currentItem = $currentImgResult->fetch_assoc();
    $currentImgStmt->close();
    
    $imagePath = $currentItem['Img']; // Keep current image by default
    
    // Handle image upload if a new file is provided
    if (isset($_FILES['itemImage']) && $_FILES['itemImage']['error'] == 0) {
        $uploadDir = 'uploads/';
        
        // Create uploads directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['itemImage']['name']);
        $newImagePath = $uploadDir . $fileName;
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/pjpeg', 'image/x-png'];
        $fileType = $_FILES['itemImage']['type'];
        $fileExtension = strtolower(pathinfo($_FILES['itemImage']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        
        if (in_array($fileType, $allowedTypes) || in_array($fileExtension, $allowedExtensions)) {
            if (move_uploaded_file($_FILES['itemImage']['tmp_name'], $newImagePath)) {
                // Delete old image file if it exists and is not empty
                if (!empty($imagePath) && file_exists($imagePath) && $imagePath !== $newImagePath) {
                    unlink($imagePath);
                }
                $imagePath = $newImagePath; // Update to new image path
            } else {
                echo json_encode(["success" => false, "error" => "Error: Failed to upload image."]);
                exit();
            }
        } else {
            echo json_encode(["success" => false, "error" => "Error: Only JPG, JPEG, PNG files are allowed."]);
            exit();
        }
    }

    $sql = "UPDATE inventory SET 
            itemName = ?, 
            colour = ?, 
            rawprice = ?, 
            supplier = ?, 
            phone = ?, 
            quantity = ?, 
            total_price = ?, 
            Img = ? 
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdssidsi", $itemName, $colour, $rawprice, $supplier, $phone, $quantity, $total_price, $imagePath, $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Item updated successfully"]);
    } else {
        echo json_encode(["success" => false, "error" => "Error updating item: " . $conn->error]);
    }

    $stmt->close();
}

// Handle GET request for fetching single item details
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM inventory WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        echo json_encode(["success" => true, "data" => $item]);
    } else {
        echo json_encode(["success" => false, "error" => "Item not found"]);
    }

    $stmt->close();
}

$conn->close();
?>
