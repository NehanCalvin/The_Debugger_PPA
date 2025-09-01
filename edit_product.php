<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "PPA_Order";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error)
  die("Connection failed: " . $conn->connect_error);

$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'];
  $product_number = $_POST['product_number'];
  $product_name = $_POST['product_name'];
  $client = $_POST['client'];
  $size = $_POST['size'];
  $quantity = (int) $_POST['quantity'];
  $unit_price = (float) $_POST['unit_price'];
  $total_price = $quantity * $unit_price; // auto calculate
  $advance = (float) $_POST['advance'];
  $fully_paid = isset($_POST['fully_paid']) ? 1 : 0;
  $image_path = $_POST['image_path'];

  $sql = "UPDATE products 
            SET product_number=?, product_name=?, client=?, size=?, quantity=?, total_price=?, advance=?, fully_paid=?, image_path=?
            WHERE id=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param(
    "ssssiddisi",
    $product_number,
    $product_name,
    $client,
    $size,
    $quantity,
    $total_price,
    $advance,
    $fully_paid,
    $image_path,
    $id
  );
  if ($stmt->execute()) {
    header("Location: product.php");
    exit();
  } else {
    echo "Error: " . $stmt->error;
  }
}

if ($id) {
  $sql = "SELECT * FROM products WHERE id=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $product = $result->fetch_assoc();
} else {
  die("Product not found");
}

// derive unit price from stored total price
$unit_price = $product['quantity'] > 0 ? $product['total_price'] / $product['quantity'] : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Edit Product</title>
  <link rel="stylesheet" href="order.css">
  <script>
    function calculateTotal() {
      let qty = parseFloat(document.getElementById("quantity").value) || 0;
      let price = parseFloat(document.getElementById("unit_price").value) || 0;
      document.getElementById("total_price").value = (qty * price).toFixed(2);
    }
  </script>
</head>

<body>
  <h2>Edit Product</h2>
  <form method="POST">
    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">

    <label>Product Number:</label>
    <input type="text" name="product_number" value="<?php echo $product['product_number']; ?>"><br>

    <label>Product Name:</label>
    <input type="text" name="product_name" value="<?php echo $product['product_name']; ?>"><br>

    <label>Client:</label>
    <input type="text" name="client" value="<?php echo $product['client']; ?>"><br>

    <label>Size:</label>
    <input type="text" name="size" value="<?php echo $product['size']; ?>"><br>

    <label>Quantity:</label>
    <input type="number" id="quantity" name="quantity" value="<?php echo $product['quantity']; ?>"
      oninput="calculateTotal()"><br>

    <label>Unit Price:</label>
    <input type="number" step="0.01" id="unit_price" name="unit_price" value="<?php echo $unit_price; ?>"
      oninput="calculateTotal()"><br>

    <label>Total Price:</label>
    <input type="number" step="0.01" id="total_price" name="total_price" value="<?php echo $product['total_price']; ?>"
      readonly><br>

    <label>Advance:</label>
    <input type="number" step="0.01" name="advance" value="<?php echo $product['advance']; ?>"><br>

    <label>Fully Paid:</label>
    <input type="checkbox" name="fully_paid" <?php echo $product['fully_paid'] ? "checked" : ""; ?>><br>

    <label>Image Path:</label>
    <input type="text" name="image_path" value="<?php echo $product['image_path']; ?>"><br>

    <button type="submit">Save</button>
  </form>
</body>

</html>