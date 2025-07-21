<!-- Handles form submission and stores data in your MySQL database.php -->

 <?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "debuggers_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Upload image
$image_path = '';
if ($_FILES['product_image']['name']) {
    $image_path = 'uploads/' . basename($_FILES['product_image']['name']);
    move_uploaded_file($_FILES['product_image']['tmp_name'], $image_path);
}

// Get form values
$product_number = $_POST['product_number'];
$product_name = $_POST['product_name'];
$client = $_POST['client'];
$size = $_POST['size'];
$price = $_POST['price'];
$quantity = $_POST['quantity'];
$total_price = $price * $quantity;
$fully_paid = isset($_POST['fully_paid']) ? 1 : 0;
$advance = isset($_POST['advance']) ? 1 : 0;

// Insert into database
$sql = "INSERT INTO products (product_number, product_name, client, size, price, quantity, total_price, image_path, fully_paid, advance)
VALUES ('$product_number', '$product_name', '$client', '$size', '$price', '$quantity', '$total_price', '$image_path', '$fully_paid', '$advance')";

if ($conn->query($sql) === TRUE) {
    header("Location: product_page.php");
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();
?>
