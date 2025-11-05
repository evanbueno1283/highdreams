<?php
// order.php

$conn = new mysqli("mysql-highdreams.alwaysdata.net", "439165", "Skyworth23", "highdreams_1");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM inquiries ORDER BY order_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>

<link rel="icon" href="image/logo1.png" type="image/png">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Orders - Shoe Store</title>
  <style>
    * {
      margin: 0;
      padding: 10;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      color: #333;
      min-height: 100vh;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 30px;
      background-color: #000000;
      color: white;
      height: 80px;
       position: fixed;
  top: 0;
  width: 100%;
  z-index: 1000;
    }

    .logo-container {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .user-options {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .home-logo img {
      height: 30px;
      width: 30px;
      transition: transform 0.3s ease-in-out;
    }

    .home-logo img:hover {
      transform: scale(1.1);
    }

    .user-options button {
      padding: 10px;
      background-color: #ffffff;
      color: #000000;
      border: none;
      cursor: pointer;
      border-radius: 20px;
    }

    .logo img,
    .second-logo img {
      height: 50px;
      box-shadow: 0 2px 10px rgb(255, 255, 255);
    }
    
  </style>
</head>
<body>

<header class="header">
  <div class="logo-container">
    <div class="logo">
      <img src="image/logo1.png" alt="Shoe Store Logo" />
    </div>
    <div class="second-logo">
      <img src="image/hdb2.png" alt="Second Logo" />
    </div>
  </div>

  <div class="user-options">
    <div class="home-logo">
      <a href="admin.php ">
        <img src="image/home.png" alt="Home Logo" />
      </a>
    </div>
    <button id="logout-button">Logout</button>
  </div>
</header>


<main style="padding: 60px 40px 20px 40px; text-align: center;">
  <h2 style="margin-bottom: 1px;">Orders Page</h2>
</main>


<main style="padding: 40px;">
  <div style="margin-bottom: 20px; text-align: center;">
    <label for="status-filter"><strong>Filter by Status:</strong></label>
    <select id="status-filter" style="padding: 8px; border-radius: 5px; margin-left: 10px;">
      <option value="pending" selected>Pending</option>
      <option value="accepted">Accepted</option>
      <option value="declined">Declined</option>
      <option value="Cancelled Order">Cancelled Order</option>
      <option value="Cancelled pending">Cancelled pending</option>
    </select>
  </div>

  <table cellspacing="0" cellpadding="10" width="100%" style="background-color: #fff; text-align: center; border-collapse: collapse; border: none;">
    <thead style="background-color: #000; color: #fff;">
      <tr>
        <th>Full Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Address</th>
        <th>Message</th>
        <th>Shoe Name</th>
        <th>Size</th>
        <th>Quantity</th>
        <th>Price</th>
        <th>Total</th>
        <th>Order Date</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody id="orders-body">
      <?php
      if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              $status = strtolower($row['status']);
              $actionButtons = "-";

              if ($status === 'pending' || $status === 'cancelled pending') {
                  $actionButtons = "
                    <div style='display: flex; justify-content: center; gap: 10px;'>
                      <button class='accept-btn' style='background-color:black; color:white;'>Accept</button>
                      <button class='decline-btn' style='background-color:black; color:white;'>Decline</button>
                    </div>";
              }

              $totalPrice = $row['price'] * $row['quantity'];
              $fullAddress = "{$row['province']}, {$row['municipality']}, {$row['barangay']}, {$row['street']}";

              echo "<tr 
                      data-id='{$row['id']}'
                      data-shoe_id='{$row['shoe_id']}'
                      data-shoe_type='{$row['shoe_type']}'
                      data-shoe_name='{$row['shoe_name']}'
                      data-size='{$row['size']}'
                      data-quantity='{$row['quantity']}'>
                    <td>" . htmlspecialchars($row['username']) . "</td>
                    <td>" . htmlspecialchars($row['email']) . "</td>
                    <td>" . htmlspecialchars($row['phone']) . "</td>
                    <td>" . htmlspecialchars($fullAddress) . "</td>
                    <td>" . htmlspecialchars($row['message']) . "</td>
                    <td>" . htmlspecialchars($row['shoe_name']) . "</td>
                    <td>" . htmlspecialchars($row['size']) . "</td>
                    <td>" . htmlspecialchars($row['quantity']) . "</td>
                    <td>₱" . number_format($row['price'], 2) . "</td>
                    <td>₱" . number_format($totalPrice, 2) . "</td>
                    <td>" . htmlspecialchars($row['order_date']) . "</td>
                    <td>" . htmlspecialchars($row['status']) . "</td>
                    <td>" . $actionButtons . "</td>
                  </tr>";
          }
      } else {
          echo "<tr><td colspan='13'>No orders found.</td></tr>";
      }

      $conn->close();
      ?>
    </tbody>
  </table>
</main>

<script>
document.querySelectorAll('.accept-btn').forEach(button => {
    button.addEventListener('click', function () {
        const row = this.closest('tr');
        const orderId = row.dataset.id;
        const currentFilter = document.getElementById('status-filter').value.toLowerCase();

        switch (currentFilter) {
            case "pending":
                handleAction(orderId, "accept");
                break;
            case "cancelled order":
                alert("This is a cancelled order. Accepting it will archive it.");
                handleAction(orderId, "archive");
                break;
            case "cancelled pending":
                if (confirm("Re-add this cancelled pending order to inventory?")) {
                    handleAction(orderId, "readd");
                }
                break;
            case "accepted":
                alert("Already accepted.");
                break;
            case "declined":
                alert("This order was declined. Cannot accept.");
                break;
            default:
                alert("Unhandled status.");
        }
    });
});

document.querySelectorAll('.decline-btn').forEach(button => {
    button.addEventListener('click', function () {
        const row = this.closest('tr');
        const orderId = row.dataset.id;
        const currentFilter = document.getElementById('status-filter').value.toLowerCase();

        switch (currentFilter) {
            case "pending":
                handleAction(orderId, "decline");
                break;
            case "cancelled pending":
                alert("You are permanently removing this cancelled pending order.");
                handleAction(orderId, "delete");
                break;
            case "accepted":
                alert("Declining an accepted order is not allowed.");
                break;
            case "declined":
                alert("Already declined.");
                break;
            default:
                alert("Unhandled status.");
        }
    });
});

function handleAction(orderId, action) {
    fetch('process_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `order_id=${orderId}&action=${action}`,
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        document.querySelector(`tr[data-id='${orderId}']`).remove();
    })
    .catch(error => {
        console.error('Error:', error);
        alert("An error occurred.");
    });
}

function filterOrdersByStatus(statusValue) {
    const rows = document.querySelectorAll('#orders-body tr');
    rows.forEach(row => {
        const statusCell = row.children[11];
        const statusText = statusCell.textContent.toLowerCase();
        row.style.display = (statusText === statusValue.toLowerCase()) ? '' : 'none';
    });
}

window.addEventListener('DOMContentLoaded', () => {
    filterOrdersByStatus("pending");
});

document.getElementById('status-filter').addEventListener('change', function () {
    filterOrdersByStatus(this.value);
});
</script>

</body>
</html>
