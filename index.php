<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Transport Management</title>
  <link rel="stylesheet" href="style.css?v=1.0">

</head>

<body>
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
  </style>
  <div class="sidebar">
    <h2>Transport Panel</h2>
    <br>
    <br>
    <input type="text" id="searchBox" placeholder="🔍 Search by Invoice or Company..." />
    <br><br>

    <ul>
      <li class="active" onclick="showTab('driver')">Driver Details</li>
      <li onclick="showTab('vehicle')">Vehicle Details</li>
      <li onclick="showTab('route')">Route</li>
      <li onclick="showTab('fuel')">Fuel Invoice</li>
    </ul>
  </div>
  <div class="content">
    <div id="driver" class="tab active">
      <?php include('driver.php'); ?>
    </div>
    <div id="vehicle" class="tab">
      <?php include('vehicle.php'); ?>
    </div>
    <div id="route" class="tab">
      <?php include('route.php'); ?>
    </div>
    <div id="fuel" class="tab">
      <?php include('fuel.php'); ?>
    </div>
  </div>

  <script>
    function showTab(tabId) {
      // Remove active class from all tabs and sidebar items
      document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
      document.querySelectorAll('.sidebar ul li').forEach(item => item.classList.remove('active'));

      // Add active class to selected tab and corresponding sidebar item
      document.getElementById(tabId).classList.add('active');
      event.target.classList.add('active');
    }
  </script>
</body>

</html>