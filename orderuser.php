<?php
session_start();

if (!isset($_SESSION['user']['id'])) {
  header("Location: login.php");
  exit();
}

$mysqli = new mysqli("localhost", "root", "", "fitscan_database");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$user_id = $_SESSION['user']['id'];
$user_stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

$username = $mysqli->real_escape_string($user['username']);

// Clear History Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_history'])) {
    $clear_query = $mysqli->prepare("DELETE FROM inquiries WHERE username = ?");
    $clear_query->bind_param("s", $username);
    $clear_query->execute();
    $clear_query->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


$inquiry_query = $mysqli->query("SELECT * FROM inquiries WHERE username = '$username'");
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
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: Arial, sans-serif;
  background-color: #f4f4f4;
  color: #333;
  min-height: 100vh;
  padding-top: 90px; /* para hindi matakpan ng fixed header */
}

/* ===== HEADER ===== */
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

.home-logo {
    height: 40px;
      width: 40px; /* increase size to make it look "thicker" or bolder */
    display: block;   
    right: 30px;
     border: 2px solid white;  /* or any color you prefer */
    border-radius: 10px;       /* optional: for rounded corners */
    padding: 1px;
}


.home-logo img:hover {
  transform: scale(1.1);
}

.user-options button {
  padding: 10px 15px;
  background-color: #ffffff;
  color: #000000;
  border: none;
  cursor: pointer;
  border-radius: 20px;
  font-size: 16px; /* readable */
}
.logo img,
.second-logo img {  
  height: 50px; /* sukat ng logo */
  margin-left: 25px; /* pagitan sa kaliwa */
  border: 2px solid white; /* puting outline */
  border-radius: 50%; /* bilogin ang outline */
  padding: 5px; /* espasyo sa loob ng bilog */
  background-color: rgba(255, 255, 255, 0.05); /* optional subtle glow sa loob */
}

/* ===== TABLE ===== */
.inquiry-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 30px;
  font-size: 16px;
  table-layout: auto; /* para hindi dikit-dikit */
}

.inquiry-table th,
.inquiry-table td {
  padding: 12px 15px;
  border: 1px solid #ddd;
  text-align: left;
}

.inquiry-table th {
  background-color: #000000;
  color: white;
}

.inquiry-table td {
  background-color: #f9f9f9;
}

/* ===== RESPONSIVE BREAKPOINTS ===== */

/* Laptop (max-width 1200px) */
@media (max-width: 1200px) {
  .header {
    padding: 10px 20px;
  }
  .user-options button {
    font-size: 15px;
  }
  .inquiry-table {
    font-size: 15px;
  }
  .main h2, form {
       
    text-align: center
  }
}

/* Tablet (max-width 768px) */
@media (max-width: 768px) {
  .header {
    flex-direction: column;
    height: auto;
    padding: 10px;
    text-align: center;
  }
   body {
    padding-top: 50px;
  }
  .logo-container {
    margin-bottom: 10px;
            margin-right: 700px;
  }
  .user-options {
    gap: 10px;
    margin-top: -50px;
        margin-right: 50px;
  }
  .user-options button {
    padding: 8px 12px;
    font-size: 14px;
  }
  .inquiry-table {
    font-size: 14px; /* maintain readability */
  }
  .inquiry-table th,
  .inquiry-table td {
    padding: 10px;
  }
  .main h2 form{
        
    text-align: center
  }
}

/* Mobile (max-width: 480px) */
@media (max-width: 480px) {
  body {
    padding-top: 50px;
  }
  .header {
    flex-direction: column;
    height: auto;
    padding: 10px;
  }
  .logo img,
  .second-logo img {
    height: 40px;
  }
  .user-options button {
    font-size: 14px;
    padding: 6px 10px;
  }
  .inquiry-table {
    font-size: 13px; /* maliit pero readable */
    border-spacing: 0;
  }
  .inquiry-table th,
  .inquiry-table td {
    padding: 8px;
  }
  .inquiry-table th {
    font-size: 13.5px;
  }
  .main h2 form{
        
    text-align: center
  }
}  .menu-btn {
  font-size: 28px;
  background: none;
  border: none;
  color: gray;
  cursor: pointer;
  display: none;
  position: absolute;
  left: 20px;
  transition: transform 0.2s ease;
}
.sidebar {
  position: fixed;
  top: 0;
  left: -260px;
  width: 260px;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.4); /* black na may 60% transparency */
  color: white;
  transition: all 0.4s ease;
  z-index: 9999;
  padding-top: 60px;
  border-top-right-radius: 30px;
  border-bottom-right-radius: 30px;
  box-shadow: 4px 0 15px rgba(0, 0, 0, 0.5);

  /* added layout fix */
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 25px; /* space between items */
}

.sidebar .close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  left: auto !important; /* Force it to the right */
  background: none;
  border: none;
  color: white;
  font-size: 26px;
  cursor: pointer;
  transition: transform 0.2s ease;
  margin: 0;
  padding: 0;
  width: auto;
  height: auto;
}

.sidebar .close-btn:hover {
  transform: rotate(90deg);
  color: #ffffff;
}
.sidebar.active {
  left: 0;
  backdrop-filter: blur(5px);
}

.sidebar a {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 5px;
  width: 80%;
  padding: 10px 0;
  text-decoration: none;
  color: white;
  font-size: 15px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
  border-left: 4px solid transparent;
  border-radius: 10px;
  transition: all 0.3s ease;
}

.sidebar a img {
  width: 30px;
  height: 30px;
}
.menu-btn {
  font-size: 28px;
  background: none;
  border: none;
  color: #bebcbaff;
  cursor: pointer;
  display: none;
  position: absolute;
  left: 20px;
  transition: transform 0.2s ease;
}
.sidebar a:hover {
  background-color: rgba(255,255,255,0.1);
  border-left: 4px solid white;
  transform: translateX(5px);
}
.menu-btn:hover {
  transform: scale(1.2);
  color: white;
}

@media (max-width: 1441px) {
  .header{
    height:60px;
  }
  .second-logo img {
        height: 40px;
        margin-top:5px;
        margin-left:35px;
        margin-right:0px;
    }
    .home-logo {
    height: 40px;
    width: 40px;
    display: block; 
    border: 2px solid white;
    border-radius: 10px;
    padding: 1px;
    margin-right:-5px;
    margin-top:-0px;
}

  .inquiry-table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
    border: 1px solid #ddd; /* optional, just to keep border visible */
  }

  .inquiry-table th,
  .inquiry-table td {
    white-space: nowrap;
    padding: 8px;
    font-size: 12px; /* smaller text for small screen */
  }
}

@media (max-width: 1281px) {
  .header{
    height:60px;
  }
  .second-logo img {
        height: 40px;
        margin-top:5px;
        margin-left:35px;
        margin-right:0px;
    }
    .home-logo {
    height: 40px;
    width: 40px;
    display: block; 
    border: 2px solid white;
    border-radius: 10px;
    padding: 1px;
    margin-right:-5px;
    margin-top:-0px;
}

  .inquiry-table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
    border: 1px solid #ddd; /* optional, just to keep border visible */
  }

  .inquiry-table th,
  .inquiry-table td {
    white-space: nowrap;
    padding: 8px;
    font-size: 12px; /* smaller text for small screen */
  }
}

@media (max-width: 1025px) {
  .header{
    height:60px;
  }
  .second-logo img {
        height: 40px;
        margin-top:5px;
        margin-left:45px;
        margin-right:0px;
    }
    .home-logo {
    height: 40px;
    width: 40px;
    display: block;
    right: 30px;
    border: 2px solid white;
    border-radius: 10px;
    padding: 1px;
    margin-left:50px;
    margin-top:-0px;
}

  .inquiry-table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
    border: 1px solid #ddd; /* optional, just to keep border visible */
  }

  .inquiry-table th,
  .inquiry-table td {
    white-space: nowrap;
    padding: 8px;
    font-size: 12px; /* smaller text for small screen */
  }
}

@media (max-width: 821px) {
  .header{
    height:60px;
  }
  .second-logo img {
        height: 40px;
        margin-top:5px;
        margin-left:45px;
        margin-right:0px;
    }
    .home-logo {
    height: 40px;
    width: 40px;
    display: block;
    right: 30px;
    border: 2px solid white;
    border-radius: 10px;
    padding: 1px;
    margin-left:50px;
    margin-top:-0px;
}

  .inquiry-table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
    border: 1px solid #ddd; /* optional, just to keep border visible */
  }

  .inquiry-table th,
  .inquiry-table td {
    white-space: nowrap;
    padding: 8px;
    font-size: 12px; /* smaller text for small screen */
  }
}

@media (max-width: 769px) {
  .header{
    height:60px;
  }
  .second-logo img {
        height: 40px;
        margin-left:0px;
        margin-right:600px;
        margin-top:0px;
    }
    .home-logo {
    height: 40px;
    width: 40px;
    display: block;
    border: 2px solid white;
    border-radius: 10px;
    padding: 1px;
    margin-left:680px;
    margin-top:-45px;
}

  .inquiry-table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
    border: 1px solid #ddd; /* optional, just to keep border visible */
  }

  .inquiry-table th,
  .inquiry-table td {
    white-space: nowrap;
    padding: 8px;
    font-size: 12px; /* smaller text for small screen */
  }
}

@media (max-width: 541px) {
  .header{
    height:60px;
  }
  .second-logo img {
        height: 40px;
        margin-left:0px;
        margin-right:370px;
        margin-top:0px;
    }
    .home-logo {
    height: 40px;
    width: 40px;
    display: block;
    right: 30px;
    border: 2px solid white;
    border-radius: 10px;
    padding: 1px;
    margin-left:455px;
    margin-top:-45px;
}

  .inquiry-table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
    border: 1px solid #ddd; /* optional, just to keep border visible */
  }

  .inquiry-table th,
  .inquiry-table td {
    white-space: nowrap;
    padding: 8px;
    font-size: 12px; /* smaller text for small screen */
  }
}

@media (max-width: 431px) {
  .header{
    height:60px;
  }
  .second-logo img {
        height: 40px;
        margin-left:0px;
        margin-right:260px;
    }
    .home-logo {
    height: 40px;
    width: 40px;
    display: block;
    right: 30px;
    border: 2px solid white;
    border-radius: 10px;
    padding: 1px;
    margin-left:340px;
    margin-top:-45px;
}

  .inquiry-table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
    border: 1px solid #ddd; /* optional, just to keep border visible */
  }

  .inquiry-table th,
  .inquiry-table td {
    white-space: nowrap;
    padding: 8px;
    font-size: 12px; /* smaller text for small screen */
  }
}

@media (max-width: 415px) {
  .header{
    height:60px;
  }
  .second-logo img {
        height: 40px;
        margin-left:0px;
        margin-right:250px;
    }
    .home-logo {
    height: 40px;
    width: 40px;
    display: block;
    right: 30px;
    border: 2px solid white;
    border-radius: 10px;
    padding: 1px;
    margin-left:330px;
    margin-top:-45px;
}

  .inquiry-table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
    border: 1px solid #ddd; /* optional, just to keep border visible */
  }

  .inquiry-table th,
  .inquiry-table td {
    white-space: nowrap;
    padding: 8px;
    font-size: 12px; /* smaller text for small screen */
  }
}

@media (max-width: 391px) {
  .header{
    height:60px;
  }
  .second-logo img {
        height: 40px;
        margin-left:0px;
        margin-right:220px;
    }
    .home-logo {
    height: 40px;
    width: 40px;
    display: block;
    right: 30px;
    border: 2px solid white;
    border-radius: 10px;
    padding: 1px;
    margin-left:310px;
    margin-top:-45px;
}

  .inquiry-table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
    border: 1px solid #ddd; /* optional, just to keep border visible */
  }

  .inquiry-table th,
  .inquiry-table td {
    white-space: nowrap;
    padding: 8px;
    font-size: 12px; /* smaller text for small screen */
  }
}


@media (max-width: 376px) {
  .header{
    height:60px;
  }
  .second-logo img {
        height: 40px;
        margin-left:0px;
        margin-right:200px;
    }
    .home-logo {
    height: 40px;
    width: 40px;
    display: block;
    right: 30px;
    border: 2px solid white;
    border-radius: 10px;
    padding: 1px;
    margin-left:290px;
    margin-top:-45px;
}

  .inquiry-table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
    border: 1px solid #ddd; /* optional, just to keep border visible */
  }

  .inquiry-table th,
  .inquiry-table td {
    white-space: nowrap;
    padding: 8px;
    font-size: 12px; /* smaller text for small screen */
  }
}

@media (max-width: 361px) {
  .header{
    height:60px;
  }
  .second-logo img {
        height: 40px;
        margin-left:0px;
        margin-right:190px;
    }
    .home-logo {
    height: 40px;
    width: 40px;
    display: block;
    right: 30px;
    border: 2px solid white;
    border-radius: 10px;
    padding: 1px;
    margin-left:270px;
    margin-top:-45px;
}

  .inquiry-table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
    border: 1px solid #ddd; /* optional, just to keep border visible */
  }

  .inquiry-table th,
  .inquiry-table td {
    white-space: nowrap;
    padding: 8px;
    font-size: 12px; /* smaller text for small screen */
  }
}

@media (max-width: 345px) {
  .header{
    height:60px;
  }
  .second-logo img {
        height: 40px;
        margin-left:0px;
        margin-right:180px;
    }
    .home-logo {
    height: 40px;
    width: 40px;
    display: block;
    right: 30px;
    border: 2px solid white;
    border-radius: 10px;
    padding: 1px;
    margin-left:270px;
    margin-top:-45px;
}

  .inquiry-table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
    border: 1px solid #ddd; /* optional, just to keep border visible */
  }

  .inquiry-table th,
  .inquiry-table td {
    white-space: nowrap;
    padding: 8px;
    font-size: 12px; /* smaller text for small screen */
  }
}

@media (max-width: 2560px) {
  .menu-btn {
     display: block; 
    }
}.logout-btn {
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 0 auto;
  padding: 10px 25px;
  background-color: transparent; /* totally transparent */
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.7); /* manipis na puting border */
  border-radius: 50px; /* oblong shape */
  cursor: pointer;
  font-weight: bold;
  text-align: center;
  font-size: 14px;
  transition: all 0.3s ease;
}
.logout-btn:hover {
  background-color: rgba(255, 255, 255, 0.1); /* light white tint on hover */
  transform: scale(1.05);
}


  </style>
</head>
<body>
 <header class="header">
        <button class="menu-btn" onclick="toggleSidebar()">☰</button>

      <div class="second-logo">
        <img src="image/logo1.png" alt="Shoe Store Logo" />
      </div>

    <a href="home.php">
    <img src="image/home.png" alt="home-logo" class="home-logo">
  </a>
  
</header>

  <div id="sidebar" class="sidebar">
  <button class="close-btn" onclick="toggleSidebar()">×</button>
  <a href="angular-ar/index.html"><img src="image/try1.png" alt="Try On"><span>Try On</span></a>
  <a href="addtocart.php"><img src="image/cart.png" alt="Cart"><span>Add to Cart</span></a>
  <a href="order.php"><img src="image/orders.png" alt="Orders"><span>Orders</span></a>
  <a href="scan.php"><img src="image/logo4.png" alt="Scan"><span>Scan</span></a>
  <a href="account.php"><img src="image/account.png" alt="Profile"><span>Profile</span></a>
   <div class="logout-container">
  <button class="logout-btn" id="logout-button">Logout</button>
</div>

</div>

<main style="padding: 40px; text-align: center;">
<h2>Client Inquiry Details</h2>

<form method="post" class="clear-history-form" onsubmit="return confirm('Are you sure you want to clear your order history?')">
  <button type="submit" name="clear_history">Clear History</button>
</form>

<table class="inquiry-table">
  <thead>
    <tr>
      <th>Full Name</th>
      <th>Email</th>
      <th>Phone</th>
      <th>Address</th>
      <th>Message</th>
      <th>Size</th>
      <th>Quantity</th>
      <th>Price</th> <!-- ✅ New column -->
      <th>Shoe Name</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Assuming $inquiry_query is your result set from the database
    if ($inquiry_query->num_rows > 0) {
      while($row = $inquiry_query->fetch_assoc()) {
        $address = htmlspecialchars($row['province'] . ', ' . $row['municipality'] . ', ' . $row['barangay'] . ', ' . $row['street']);
        
        // ✅ Calculate total price
        $price = (float)($row['price'] ?? 0);
        $quantity = (int)($row['quantity']);
        $totalPrice = $price * $quantity;

        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
        echo "<td>" . $address . "</td>";
        echo "<td>" . htmlspecialchars($row['message']) . "</td>";
        echo "<td>" . htmlspecialchars($row['size']) . "</td>";
        echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
        echo "<td>₱" . number_format($totalPrice, 2) . "</td>"; // ✅ Display price
        echo "<td>" . htmlspecialchars($row['shoe_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>
          <form method='post' action='cancel_order.php' onsubmit='return confirm(\"Are you sure you want to cancel this order?\")'>
            <input type='hidden' name='order_id' value='" . htmlspecialchars($row['id']) . "'>
            <input type='hidden' name='shoe_id' value='" . htmlspecialchars($row['shoe_id']) . "'>
            <input type='hidden' name='shoe_name' value='" . htmlspecialchars($row['shoe_name']) . "'>
            <input type='hidden' name='shoe_type' value='" . htmlspecialchars($row['shoe_type']) . "'>
            <button type='submit'>Cancel Order</button>
          </form>
        </td>";
        echo "</tr>";
      }
    } else {
      echo "<tr><td colspan='11'>No inquiries found</td></tr>";
    }
    ?>
  </tbody>
</table>

<script>
  
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("active");
}
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("active");
}

// === Auto close kapag nag-click sa labas ng sidebar ===
document.addEventListener("click", function(event) {
  const sidebar = document.getElementById("sidebar");
  const menuBtn = document.querySelector(".menu-btn");

  // kung active ang sidebar at hindi sa loob ng sidebar o menu button nag-click
  if (
    sidebar.classList.contains("active") &&
    !sidebar.contains(event.target) &&
    !menuBtn.contains(event.target)
  ) {
    sidebar.classList.remove("active");
  }
});

  document.getElementById('logout-button')?.addEventListener('click', function () {
    alert("You have been logged out.");
    window.location.href = "login.php";
  });
</script>

</body>
</html>

<?php
$mysqli->close();
?>