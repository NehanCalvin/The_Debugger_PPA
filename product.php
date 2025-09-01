<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "PPA_Order"; // Ensure this matches your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch all products
$sql = "SELECT * FROM products ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Product Management</title>
  <link rel="stylesheet" href="product.css">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <style>
    #searchBox {
      position: fixed;
      top: 20px;
      left: 250px;
      width: calc(100% - 270px);
      padding: 12px 16px;
      font-size: 16px;
      border-radius: 6px;
      border: none;
      background: #333;
      color: #fff;
      outline: none;
      margin-bottom: 20px;
      box-shadow: 0 0 0 1px #444 inset;
    }

    .modal {
      display: none;
      /* hide modal initially */
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.6);
      justify-content: center;
      align-items: center;
      padding: 10px;
      z-index: 9999;
    }

    .modal-content {
      background: #222;
      width: 500px;
      max-width: 100%;
      max-height: 90vh;
      border-radius: 10px;
      color: #fff;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      /* padding: 16px 20px; */
      padding: 0px;
      border-bottom: 1px solid #444;
    }

    .modal-body {
      padding: 16px 20px;
      overflow-y: auto;
      flex: 1 1 auto;
      /* scrollable */
    }

    .modal-footer {
      position: sticky;
      bottom: 0;
      background: #1c1c1c;
      padding: 12px 20px;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      border-top: 1px solid #444;
    }

    .btn {
      padding: 8px 16px;
      border-radius: 6px;
      cursor: pointer;
      border: none;
      font-weight: 600;
    }

    .btn-save {
      background: #28a745;
      color: #fff;
    }

    .btn-save:hover {
      background: #218838;
    }

    .btn-cancel {
      background: #333;
      color: #fff;
      border: 1px solid #555;
    }

    .btn-cancel:hover {
      background: #444;
    }

    body.modal-open {
      overflow: hidden;
    }
  </style>
</head>

<body>
  <div class="sidebar">
    <a href="create_product.html"><i class="fas fa-plus-circle"></i> Create Product</a>
    <a class="active" href="product.php"><i class="fas fa-chart-line"></i> Products</a>
    <a href="productReport.html"><i class="fas fa-file-alt"></i> Reports</a>
    <a href="admin.html" class="dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
  </div>
  </div>

  <main class="main-content">
    <input type="text" id="searchBox" placeholder="🔍 Search by Product ID or Company..." />
    <br><br>
    <h2>Product Management</h2>

    <?php
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $due = 0;
        $amountPaid = 0;
        if ($row['fully_paid'] == 1) {
          $amountPaid = $row['total_price'];
        } elseif (!empty($row['advance'])) {
          $amountPaid = $row['advance'];
          $due = $row['total_price'] - $amountPaid;
        }

        $unitPrice = $row['quantity'] > 0 ? $row['total_price'] / $row['quantity'] : 0;

        echo '<div class="card product-card" data-invoice="' . strtolower($row['product_number']) . '" data-client="' . strtolower($row['client']) . '">';
        echo '<img src="' . $row['image_path'] . '" alt="Product Image">';
        echo '<div class="info">';
        echo '<p><strong>Product number:</strong> ' . $row['product_number'] . '</p>';
        echo '<p><strong>Item:</strong> ' . $row['product_name'] . '</p>';
        echo '<p><strong>Client:</strong> ' . $row['client'] . '</p>';
        echo '<p><strong>Size:</strong> ' . $row['size'] . '</p>';
        echo '<p><strong>Quantity:</strong> ' . $row['quantity'] . '</p>';
        echo '<p><strong>Amount paid:</strong> ' . number_format($amountPaid, 2) . ' LKR</p>';
        echo '<p><strong>Created At:</strong> ' . date("Y-m-d", strtotime($row['created_at'])) . '</p>';

        echo '<div class="payment">';
        echo '<label class="' . ($row['fully_paid'] == 1 ? 'checked-green' : '') . '">';
        echo '<input type="checkbox" ' . ($row['fully_paid'] == 1 ? 'checked' : '') . ' disabled> Fully paid';
        echo '</label>';
        echo '<label class="' . (!empty($row['advance']) && $row['fully_paid'] != 1 ? 'checked-green' : '') . '">';
        echo '<input type="checkbox" ' . (!empty($row['advance']) && $row['fully_paid'] != 1 ? 'checked' : '') . ' disabled> Advance';
        echo ' Advance';
        echo '</label>';
        echo '</div>';

        if (!empty($row['advance']) && $row['fully_paid'] != 1) {
          echo '<div style="margin-top: 10px;">';
          echo '<label>Amount: ' . number_format($row['advance'], 2) . ' LKR</label>';
          echo '<p class="due-text">Due Amount: ' . number_format($due, 2) . ' LKR</p>';
          echo '</div>';
        }

        echo '<div class="buttons">';
        echo '<button class="edit-btn" onclick=\'openModal(' . json_encode([
          "id" => $row["id"],
          "product_number" => $row["product_number"],
          "product_name" => $row["product_name"],
          "client" => $row["client"],
          "size" => $row["size"],
          "quantity" => $row["quantity"],
          "unit_price" => $unitPrice,
          "total_price" => $row["total_price"],
          "advance" => $row["advance"],
          "fully_paid" => $row["fully_paid"],
          "image_path" => $row["image_path"]
        ]) . ')\'>Edit</button>';

        echo '<form action="delete_product.php" method="POST" style="display:inline;">
                <input type="hidden" name="id" value="' . $row['id'] . '">
                <button type="submit" class="delete-btn" onclick="return confirm(\'Are you sure you want to delete this product?\')">Delete</button>
              </form>';
        echo '</div>';

        echo '</div></div>';
      }
    } else {
      echo "<p style='color: white;'>No products found.</p>";
    }

    $conn->close();
    ?>
  </main>

  <!-- Edit Product Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Edit Product</h2>
        <span class="close" onclick="closeModal()">&times;</span>
      </div>

      <form id="editForm" method="POST" action="edit_product.php">
        <input type="hidden" name="id" id="edit_id">

        <div class="modal-body">
          <label>Product Number:</label>
          <input type="text" name="product_number" id="edit_product_number">

          <label>Product Name:</label>
          <input type="text" name="product_name" id="edit_product_name">

          <label>Client:</label>
          <input type="text" name="client" id="edit_client">

          <label>Size:</label>
          <input type="text" name="size" id="edit_size">

          <div class="row-2">
            <div>
              <label>Quantity:</label>
              <input type="number" id="edit_quantity" name="quantity" oninput="calculateTotal()">
            </div>
            <div>
              <label>Unit Price:</label>
              <input type="number" step="0.01" id="edit_unit_price" name="unit_price" oninput="calculateTotal()">
            </div>
          </div>

          <label>Total Price:</label>
          <input type="number" step="0.01" id="edit_total_price" name="total_price" readonly>

          <label>Advance:</label>
          <input type="number" step="0.01" name="advance" id="edit_advance">

          <div class="checkbox-row">
            <input type="checkbox" name="fully_paid" id="edit_fully_paid">
            <label for="edit_fully_paid">Fully Paid</label>
          </div>

          <label>Image Path:</label>
          <input type="text" name="image_path" id="edit_image_path">
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" onclick="closeModal()">Cancel</button>
          <button type="submit" class="btn btn-save">Save</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openModal(product) {
      document.getElementById("edit_id").value = product.id;
      document.getElementById("edit_product_number").value = product.product_number;
      document.getElementById("edit_product_name").value = product.product_name;
      document.getElementById("edit_client").value = product.client;
      document.getElementById("edit_size").value = product.size;
      document.getElementById("edit_quantity").value = product.quantity;
      document.getElementById("edit_unit_price").value = product.unit_price;
      document.getElementById("edit_total_price").value = product.total_price;
      document.getElementById("edit_advance").value = product.advance;
      document.getElementById("edit_fully_paid").checked = product.fully_paid == 1;
      document.getElementById("edit_image_path").value = product.image_path;

      document.getElementById("editModal").style.display = "flex";
      document.body.classList.add("modal-open");
    }

    function closeModal() {
      document.getElementById("editModal").style.display = "none";
      document.body.classList.remove("modal-open");
    }
    function calculateTotal() {
      let qty = parseFloat(document.getElementById("edit_quantity").value) || 0;
      let price = parseFloat(document.getElementById("edit_unit_price").value) || 0;
      document.getElementById("edit_total_price").value = (qty * price).toFixed(2);
    }

    // Close modal on outside click
    window.onclick = function (event) {
      if (event.target == document.getElementById("editModal")) closeModal();
    }

    // Close modal on ESC key
    document.addEventListener("keydown", e => { if (e.key === "Escape") closeModal(); });

    // Search filter
    document.getElementById('searchBox').addEventListener('input', function () {
      const query = this.value.toLowerCase();
      const products = document.querySelectorAll('.product-card');
      products.forEach(card => {
        const invoice = card.getAttribute('data-invoice');
        const client = card.getAttribute('data-client');
        if (invoice.includes(query) || client.includes(query)) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    });
  </script>
</body>

</html>