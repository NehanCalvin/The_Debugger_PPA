<?php
$conn = new mysqli("localhost", "root", "", "product_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$result = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 1");
$row = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Product Page</title>
  <style>
    .paid-box { background-color: lightgray; padding: 5px; }
    .paid-box.paid { background-color: lightgreen; }
  </style>
</head>
<body>
  <h2>Product Management</h2>

  <p><b>Product Number:</b> <?= $row['product_number'] ?></p>
  <p><b>Product Name:</b> <?= $row['product_name'] ?></p>
  <p><b>Client:</b> <?= $row['client'] ?></p>
  <p><b>Size:</b> <?= $row['size'] ?></p>
  <p><b>Price:</b> <?= $row['price'] ?></p>
  <p><b>Quantity:</b> <?= $row['quantity'] ?></p>
  <p><b>Total Price:</b> <?= $row['total_price'] ?> LKR</p>

  <img src="<?= $row['image'] ?>" width="150"><br><br>

  <div class="paid-box <?= $row['fully_paid'] ? 'paid' : '' ?>">Fully Paid</div>
  <div class="paid-box"><?= $row['advance'] ? 'Advance Paid' : 'No Advance' ?></div><br>

  <form action="create_product.html">
    <button type="submit">Edit</button>
  </form>

  <form action="delete_product.php" method="POST">
    <input type="hidden" name="id" value="<?= $row['id'] ?>">
    <button type="submit">Delete</button>
  </form>
</body>
</html>
