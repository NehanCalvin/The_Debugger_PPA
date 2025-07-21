<?php
include 'db.php';

$sql = "SELECT * FROM products ORDER BY id DESC LIMIT 1"; // get latest
$result = $conn->query($sql);
$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="orders.css"/>
  <title>Product Page</title>
  <style>
    .green-box { background-color: #c8f7c5; padding: 10px; border-radius: 5px; }
    .advance-box { display: none; margin-top: 10px; }
    .payment-section { margin-top: 20px; }
  </style>
</head>
<body>

<div class="sidebar">
  <h2>Product Management</h2>
  <ul>
    <li><a href="create_product.html">Create Product</a></li>
    <li><a href="product.html" class="active">Product</a></li>
    <li><a href="report.html">Reports</a></li>
  </ul>
  <button class="dashboard-btn">Dashboard</button>
</div>

<div class="main-content">
  <h2>Product Details</h2>
  <?php if ($product): ?>
    <div class="product-box" id="productBox">
      <p><strong>Product Number:</strong> <?= $product['product_number'] ?></p>
      <p><strong>Product Name:</strong> <?= $product['product_name'] ?></p>
      <p><strong>Client:</strong> <?= $product['client'] ?></p>
      <p><strong>Size:</strong> <?= $product['size'] ?></p>
      <p><strong>Price per Item:</strong> <?= $product['price'] ?> LKR</p>
      <p><strong>Quantity:</strong> <?= $product['quantity'] ?></p>
      <p><strong>Total Price:</strong> <?= $product['total_price'] ?> LKR</p>
      <img src="<?= $product['image_path'] ?>" width="150"/>

      <div class="payment-section">
        <label><input type="radio" name="payment" value="fully_paid"> Fully Paid</label>
        <label><input type="radio" name="payment" value="advance"> Advance</label>

        <div class="advance-box" id="advanceBox">
          <input type="number" id="advanceAmount" placeholder="Enter advance amount"/>
          <p id="dueAmount">Due Amount: 0.00 LKR</p>
        </div>
      </div>

      <br>
      <button onclick="location.href='create_product.php?id=<?= $product['id'] ?>'">Edit</button>
    </div>
  <?php else: ?>
    <p>No product found.</p>
  <?php endif; ?>
</div>

<script>
document.querySelectorAll('input[name="payment"]').forEach(radio => {
  radio.addEventListener('change', function() {
    const box = document.getElementById('productBox');
    const advanceBox = document.getElementById('advanceBox');
    if (this.value === 'fully_paid') {
      box.classList.add('green-box');
      advanceBox.style.display = 'none';
    } else {
      box.classList.remove('green-box');
      advanceBox.style.display = 'block';
    }
  });
});

document.getElementById('advanceAmount').addEventListener('input', function() {
  const entered = parseFloat(this.value) || 0;
  const total = <?= $product['total_price'] ?>;
  const due = total - entered;
  document.getElementById('dueAmount').innerText = 'Due Amount: ' + due.toFixed(2) + ' LKR';
});
</script>

</body>
</html>
