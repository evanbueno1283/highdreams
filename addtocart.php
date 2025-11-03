<?php
session_start();
/*Logout need */
if (!isset($_SESSION['user']['id'])) {
  header("Location: login.php");
  exit();
}

// DB Connection
$mysqli = new mysqli("localhost", "root", "", "fitscan_database");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$user_id = $_SESSION['user']['id'];
$result = $mysqli->query("SELECT * FROM users WHERE id = $user_id");
$user = $result->fetch_assoc();


$user_email = $_SESSION['user']['email'] ?? null;

if (!$user_email) {
    die("No user logged in.");
}

$stmt = $mysqli->prepare("SELECT COUNT(*) FROM user_reviews WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($review_count);
$stmt->fetch();
$stmt->close();

$hasReview = $review_count > 0 ? 'true' : 'false'; // ito OK na for JS
/*Logout need */

// ======= AJAX: Remove single item =======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_remove_item'])) {
    $itemId = intval($_POST['ajax_remove_item']);
    $stmt = $mysqli->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $itemId, $user_id);
    $success = $stmt->execute();
    echo json_encode(['success' => $success]);
    exit;
}

// ======= AJAX: Remove selected items =======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_remove_selected'])) {
    $selectedItems = json_decode($_POST['selected_items'] ?? '[]', true);
    $response = ['success' => false];

    if (is_array($selectedItems) && count($selectedItems) > 0) {
        $placeholders = implode(',', array_fill(0, count($selectedItems), '?'));
        $types = str_repeat('i', count($selectedItems)) . 'i'; // user_id at end
        $params = array_merge($selectedItems, [$user_id]);

        $query = "DELETE FROM cart WHERE id IN ($placeholders) AND user_id = ?";
        $stmt = $mysqli->prepare($query);

        if ($stmt) {
            $tmp = [];
            foreach ($params as $key => $value) {
                $tmp[$key] = &$params[$key];
            }
            $stmt->bind_param($types, ...$tmp);
            $success = $stmt->execute();
            $response['success'] = $success;
        } else {
            $response['error'] = "Query preparation failed.";
        }
    } else {
        $response['error'] = "Invalid selection.";
    }

    echo json_encode($response);
    exit;
}

// ======= Checkout: Convert to inquiries =======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_items']) && isset($_POST['items'])) {

    $username     = trim($_POST['username'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $province     = trim($_POST['province'] ?? '');
    $municipality = trim($_POST['municipality'] ?? '');
    $barangay     = trim($_POST['barangay'] ?? '');
    $street       = trim($_POST['street'] ?? '');

    $selectedItems = json_decode($_POST['selected_items'], true);
    $items        = json_decode($_POST['items'], true);

    $errors = [];
    $successCount = 0;

    if (!is_array($selectedItems) || !is_array($items)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
        exit;
    }

    $stmt = $mysqli->prepare("INSERT INTO inquiries 
        (username, email, phone, province, municipality, barangay, street, message, size, quantity, shoe_id, shoe_type, shoe_name, status, order_date, price) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), ?)");

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $mysqli->error]);
        exit;
    }

    foreach ($selectedItems as $itemId) {
        if (!isset($items[$itemId])) {
            $errors[] = "Item ID $itemId not found.";
            continue;
        }

        $item = $items[$itemId];

        $shoe_id   = intval($item['shoe_id']);
        $shoe_name = $item['shoe_name'];
        $size      = $item['size'];
        $quantity  = intval($item['quantity']);
        $shoe_type = $item['shoe_type'] ?? '';
        $message   = "Cart checkout for $shoe_name.";
        $price     = floatval($item['price'] ?? 0);

        $stmt->bind_param(
            "sssssssssiissd",
            $username,
            $email,
            $phone,
            $province,
            $municipality,
            $barangay,
            $street,
            $message,
            $size,
            $quantity,
            $shoe_id,
            $shoe_type,
            $shoe_name,
            $price
        );

        if ($stmt->execute()) {
            $successCount++;
            $delete_stmt = $mysqli->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $delete_stmt->bind_param("ii", $itemId, $user_id);
            $delete_stmt->execute();
            $delete_stmt->close();
        } else {
            $errors[] = "Failed to submit $shoe_name: " . $stmt->error;
        }
    }

    $stmt->close();

    echo json_encode([
        'success' => $successCount > 0,
        'message' => "$successCount item(s) submitted.",
        'errors'  => $errors
    ]);
    exit;
}

// ======= Add-to-Cart =======
if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && !isset($_POST['ajax_remove_item']) 
    && !isset($_POST['ajax_remove_selected']) 
    && !isset($_POST['selected_items'])
) {
    $shoe_id    = intval($_POST['shoe_id'] ?? 0);
    $shoe_name  = trim($_POST['shoe_name'] ?? '');
    $price      = floatval($_POST['price'] ?? 0);
    $size       = trim($_POST['size'] ?? '');
    $quantity   = intval($_POST['quantity'] ?? 1);
    $shoe_image = trim($_POST['shoe_image'] ?? '');
    $shoe_type  = trim($_POST['shoe_type'] ?? ''); // ✅ FIXED

    if (!$shoe_id || !$shoe_name || !$size || $quantity < 1 || $price <= 0) {
        http_response_code(400);
        echo "Invalid input.";
        exit;
    }

    // Check if item exists
    $check_query = "SELECT id, quantity FROM cart WHERE user_id = ? AND shoe_id = ? AND size = ?";
    $stmt = $mysqli->prepare($check_query);
    $stmt->bind_param("iis", $user_id, $shoe_id, $size);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $new_quantity = $row['quantity'] + $quantity;
        $update_query = "UPDATE cart SET quantity = ? WHERE id = ?";
        $update_stmt = $mysqli->prepare($update_query);
        $update_stmt->bind_param("ii", $new_quantity, $row['id']);
        $update_stmt->execute();
        echo "Quantity updated in cart.";
    } else {
        // ✅ INSERT with shoe_type
        $insert_query = "INSERT INTO cart (user_id, shoe_id, shoe_name, price, size, quantity, shoe_image, shoe_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $mysqli->prepare($insert_query);
        $insert_stmt->bind_param("iisdsiss", $user_id, $shoe_id, $shoe_name, $price, $size, $quantity, $shoe_image, $shoe_type);
        $insert_stmt->execute();
        echo "Item added to cart.";
    }
    exit;
}

// ======= Fetch cart items for display =======
$cart_query = "SELECT * FROM cart WHERE user_id = ?";
$cart_stmt = $mysqli->prepare($cart_query);
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();

$cart_items_array = [];
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" href="image/logo1.png" type="image/png">
  <meta charset="UTF-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Cart</title>
  <style>
    /* Reset some default styles */
    * {
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      margin: 0;
      padding: 30px;
      color: #333;
    }

   .header {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 80px; /* same height mo na dati */
  background-color: #000;
  color: white;
  padding: 15px 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 15px;
  z-index: 1000; /* para laging on top */
  box-shadow: 0 2px 5px rgba(0,0,0,0.3); /* optional para may konting shadow */
  position: fixed;
  top: 0;
  width: 100%;
  z-index: 1000;
}

/* I-adjust ang body padding para hindi matakpan ng fixed header */
body {
  padding-top: 110px; /* konting dagdag sa taas (80px header + padding) */
  font-family: Arial, sans-serif;
  background: #f4f4f4;
  margin: 0;
  color: #333;
}



.home-logo {
    height: 40px;
      width: 40px; /* increase size to make it look "thicker" or bolder */
    display: block;
     margin-top:-150px;
     position: fixed;
    left: 30px;
     border: 2px solid black;  /* or any color you prefer */
    border-radius: 10px;       /* optional: for rounded corners */
    padding: 1px;
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

    .logo-container {
      display: flex;
      align-items: center;
      gap: 15px;
      flex-shrink: 0;
    }

    .logo-container img {
      height: 50px;
      box-shadow: 0 2px 10px rgba(255,255,255,0.8);
    }
    /* Header Icons */
    .header-icons,
    .user-options {
      display: flex;
      align-items: center;
      gap: 15px;
      flex-shrink: 0;
    }


    .account-logo,
    .order,
    .more-logo,
    .scanner-logo img {
      height: 40px;
      width: auto;
      cursor: pointer;
      transition: transform 0.2s ease;
    }

    .account-logo:hover,
    .order:hover,
    .more-logo:hover,
    .scanner-logo img:hover {
      transform: scale(1.1);
    }

    /* Dropdown */
    .dropdown {
      position: relative;
      display: inline-block;
    }

    .dropbtn {
      background: none;
      border: none;
      cursor: pointer;
      padding: 0;
      margin: 0;
    }
  .dropdown-content {
  display: none;
  position: absolute;
  background-color: rgba(0, 0, 0, 0.4); 
  min-width: 160px;
  box-shadow: 0px 8px 16px rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  z-index: 1;
  right: 0; /* ito dapat meron */
}

.dropdown-content a {
  color: rgb(255, 255, 255);
  padding: 12px 16px;
  display: block;
  text-decoration: none;
  border-bottom: 1px solid #ffffff;
}

.dropdown-content a:hover {
  background-color: grey;
}

.dropdown:hover .dropdown-content {
  display: block;
}

    .dropdown-content a:last-child {
      border-bottom: none;
    }

  

    .dropdown:hover .dropdown-content {
      display: block;
    }

    .home-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px;
  border-radius: 20px;
  text-decoration: none;
  transition: background-color 0.3s ease;
}

.home-button:hover {
  background-color: #eeeeee;
}

/* Home icon image styling */
.home-button img {
  width: 24px;
  height: 24px;
  display: block;
} 

   

    /* Cart Container */
/* CART */
.cart-container {
  background: #fff;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  margin-top:30px;
}

.cart-container h2 {
  text-align: center;
  font-size: 28px;
  margin-bottom: 25px;
  font-weight: 700;
}

/* Table */
.addtocart-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
}
.addtocart-table th {
  background: #000;
  color: #fff;
  padding: 12px;
  text-align: center;
}
.addtocart-table td {
  background: #fafafa;
  padding: 12px;
  text-align: center;
  vertical-align: middle;
  font-size: 16px;
}

/* Images */
.addtocart-table img {
  width: 100px;
  height:100px;
  border-radius: 8px;
}

/* Buttons */
.remove-btn,
.remove-selected-btn,
.checkout-btn {
  border: none;
  padding: 12px 20px;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.3s ease;
}

.remove-btn {
  background: #e63946;
  color: #fff;
}
.remove-btn:hover {
  background: #d62828;
}

.remove-selected-btn {
  background: #999;
  color: #fff;
  margin-left: 20px;
}
.remove-selected-btn:hover {
  background: #777;
}

.checkout-btn {
  background: #000;
  color: #fff;
  font-size: 18px;
}
.checkout-btn:hover {
  background: #333;
}

/* Checkout Section */
.checkout-container {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 20px;
}
#grand-total {
  font-size: 18px;
  font-weight: bold;
}

/* Empty Cart */
#empty-cart-message {
  text-align: center;
  color: #777;
  font-size: 20px;
  margin-top: 50px;
}

  /* ===================== RESPONSIVE ===================== */
   @media (max-width: 1441px) {
.more-logo{
  height:60px;
}
                .dropdown-content {
    display: none;
    position: absolute;
    min-width: 130px;
    box-shadow: 0px 8px 16px rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    margin-right: 10px;
    
    }
.categories{
  margin-top:70px;
}
.dropdown, .order, .scanner-logo, .logout {
        position: static;
        margin-right: -20px;
    
}
.logo img, .second-logo img {
    margin-left: 70px;
    margin-top:-5px;
}
   }
  @media (max-width: 1025px) {
    .addtocart-table img { width: 100px; }
    .back-button-container {   top: 25%;
      left: 65px;}
.addtocart-table td{
  width:10%;
  }
   .item-checkbox{
 margin-left:80px;
  }
   
  .home-button img {
    width: 30px;
    height: 30px;

}
.more-logo {
  height:60px;
}
.col1{ 
  width: 10%;  /* checkbox column */
  text-align: center;
}
          .dropdown-content {
    display: none;
    position: absolute;
    min-width: 130px;
    box-shadow: 0px 8px 16px rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    margin-right: 10px;
    
    }
}

  @media (max-width: 769px) {
    body{
      padding-top:120px;
    }
      .logo-container {
    width: 100%;
  }
 
    .logo-container img{
   height:40px;
  }
.header-icons{
gap:5px;
}
  .dropdown{
    left:15px;
  }
   .user-options {
    position:absolute;
    top:20px;
    left:100px;
    width: 100%;
    justify-content: center; /* Center logout and icons */
gap: 45px;
  } 
    .checkout-container { flex-direction: column; align-items: flex-start; }
    .addtocart-table td, .addtocart-table th { font-size: 14px; padding: 8px; }
    .more-logo{
          margin-top: -10px;
        margin-left: 450px;

 }
     .logo img, .second-logo img {
        margin-left: 36px;
        margin-top: 1px;
    }
    .home-logo {
    height: 40px;
      width: 40px; /* increase size to make it look "thicker" or bolder */
    display: block;
     margin-top:-150px;
     position: fixed;
    left: 30px;
     border: 2px solid black;  /* or any color you prefer */
    border-radius: 10px;       /* optional: for rounded corners */
    padding: 1px;
}
.cart-container {
  background: #fff;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  margin-top:10px;
}
          .dropdown-content {
    display: none;
    position: absolute;
    min-width: 130px;
    box-shadow: 0px 8px 16px rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    margin-right: 110px;
    margin-top:-5px;
    }
    
    }
  
  
   @media (max-width: 541px) {
            .header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 15px;
        height: 60px;
        background-color: #000;
    }

     .body {
        padding-top: 150px;
    }
    .logo-container img { height: 40px; }
    .logo-container{
      margin-bottom:40px;
    }
    .home-button img {
        width: 30px;
        height: 30px;
        margin-bottom:5px;
    }
  .user-options {
    position:absolute;
    top:65px;
    left:60px;
    width: 100%;
    justify-content: center; /* Center logout and icons */
gap: 30px;
  }
    .logout{
  position: relative;
        bottom:50px;
        left: -90px;
 }
  .addtocart-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed; /* ensures widths are respected */
}
.addtocart-table th.col1 {
  width: 15%; /* Checkbox column */
  height:50px;
  text-align: center;
}
    .item-checkbox {
         margin-left: 0px; 
    }

.addtocart-table th,.addtocart-table td{
  font-size:8px;
}
    .addtocart-table img { width: 50px;height:60px; }
    .remove-btn{
    border: none;
    padding: 8px 10px;
  font-size:7px;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s 
ease;
}
    .checkout-btn { width: 60%; font-size:10px;}
    .remove-selected-btn {
    background: #999;
    color: #fff;
    margin-left: 0px; 
}
  .logo img, .second-logo img {
        margin-left: 60px;
        margin-top: -2px;
        height:20px:
    }

.home-logo {
    height: 40px;
      width: 40px; /* increase size to make it look "thicker" or bolder */
    display: block;
     margin-top:-150px;
     position: fixed;
    left: 30px;
     border: 2px solid black;  /* or any color you prefer */
    border-radius: 10px;       /* optional: for rounded corners */
    padding: 1px;
}
.cart-container {
  background: #fff;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  margin-top:-5px;
}
.dropdown{
          position: static;
        margin-right: 130px;
                margin-top: -50px;
}
          .dropdown-content {
    display: none;
    position: absolute;
    min-width: 130px;
    box-shadow: 0px 8px 16px rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    margin-right: 70px;
    margin-top:-10px;
    
    }
  }

   @media (max-width: 431px) {
            .header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 15px;
        height: 60px;
        background-color: #000;
    }

     .body {
        padding-top: 150px;
    }
    .logo-container img { height: 40px; }
    .logo-container{
      margin-bottom:40px;
    }
    .home-button img {
        width: 30px;
        height: 30px;
        margin-bottom:5px;
    }
  .user-options {
    position:absolute;
    top:65px;
    left:60px;
    width: 100%;
    justify-content: center; /* Center logout and icons */
gap: 30px;
  }
    .logout{
  position: relative;
        bottom:50px;
        left: -90px;
 }
  .addtocart-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed; /* ensures widths are respected */
}
.addtocart-table th.col1 {
  width: 15%; /* Checkbox column */
  height:50px;
  text-align: center;
}
    .item-checkbox {
         margin-left: 0px; 
    }

.addtocart-table th,.addtocart-table td{
  font-size:8px;
}
    .addtocart-table img { width: 50px;height:60px; }
    .remove-btn{
    border: none;
    padding: 8px 10px;
  font-size:7px;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s 
ease;
}
    .checkout-btn { width: 60%; font-size:10px;}
    .remove-selected-btn {
    background: #999;
    color: #fff;
    margin-left: 0px; 
}
  .logo img, .second-logo img {
        margin-left: 60px;
        margin-top: -2px;
        height:20px:
    }

.home-logo {
    height: 40px;
      width: 40px; /* increase size to make it look "thicker" or bolder */
    display: block;
     margin-top:-150px;
     position: fixed;
    left: 30px;
     border: 2px solid black;  /* or any color you prefer */
    border-radius: 10px;       /* optional: for rounded corners */
    padding: 1px;
}
.cart-container {
  background: #fff;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  margin-top:-5px;
}
.dropdown{
          position: static;
        margin-right: 230px;
                margin-top: -50px;(max-width: 426px)
    min-width: 130px;
    box-shadow: 0px 8px 16px rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    margin-right: 10px;
    
    }
        .logo img, .second-logo img {
        margin-left: 60px;
        margin-top: 1px;
        height: 40px;
    }
        .more-logo {
        margin-top: -10px;
        margin-left: 220px;
    }
  }


  @media (max-width: 426px) {
            .header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 15px;
        height: 60px;
        background-color: #000;
    }

     .body {
        padding-top: 150px;
    }
    .logo-container img { height: 40px; }
    .logo-container{
      margin-bottom:40px;
    }
    .home-button img {
        width: 30px;
        height: 30px;
        margin-bottom:5px;
    }
  .user-options {
    position:absolute;
    top:65px;
    left:60px;
    width: 100%;
    justify-content: center; /* Center logout and icons */
gap: 30px;
  }
    .logout{
  position: relative;
        bottom:50px;
        left: -90px;
 }
  .addtocart-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed; /* ensures widths are respected */
}
.addtocart-table th.col1 {
  width: 15%; /* Checkbox column */
  height:50px;
  text-align: center;
}
    .item-checkbox {
         margin-left: 0px; 
    }

.addtocart-table th,.addtocart-table td{
  font-size:8px;
}
    .addtocart-table img { width: 50px;height:60px; }
    .remove-btn{
    border: none;
    padding: 8px 10px;
  font-size:7px;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s 
ease;
}
    .checkout-btn { width: 60%; font-size:10px;}
    .remove-selected-btn {
    background: #999;
    color: #fff;
    margin-left: 0px; 
}
  .logo img, .second-logo img {
        margin-left: 48px;
        margin-top: -2px;
        height:20px:
    }

.home-logo {
    height: 40px;
      width: 40px; /* increase size to make it look "thicker" or bolder */
    display: block;
     margin-top:-150px;
     position: fixed;
    left: 30px;
     border: 2px solid black;  /* or any color you prefer */
    border-radius: 10px;       /* optional: for rounded corners */
    padding: 1px;
}
.cart-container {
  background: #fff;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  margin-top:-5px;
}
.dropdown{
          position: static;
        margin-right: 230px;
                margin-top: -50px;
}
          .dropdown-content {
    display: none;
    position: absolute;
    min-width: 130px;
    box-shadow: 0px 8px 16px rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    margin-right: 70px;
    margin-top:-10px;
    
    
    }
     .more-logo {
        margin-top: -10px;
        margin-left: 430px;
    }
  }

  @media (max-width: 376px) {
    body { padding-top:150px; }
     .user-options {
    position:absolute;
    top:65px;
    left:60px;
    width: 100%;
    justify-content: center; /* Center logout and icons */
gap: 25px;
  }
    .cart-container h2 {
    text-align: center;
    font-size: 18px;
    margin-bottom: 25px;
    font-weight: 700;
}
    .addtocart-table td {font-size:5px;  }
        .addtocart-table img {
        width: 40px;
        height: 50px;
    }
        .remove-btn {
        border: none;
        padding: 4px 5px;
        font-size: 7px;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s 
ease;
    }
    .checkout-container { gap: 10px; }
    .logo img, .second-logo img {
        margin-left: 60px;
        margin-top: 1px;
        height: 40px;
    }

    .home-logo {
    height: 40px;
      width: 40px; /* increase size to make it look "thicker" or bolder */
    display: block;
     margin-top:-135px;
     position: fixed;
    left: 30px;
     border: 2px solid black;  /* or any color you prefer */
    border-radius: 10px;       /* optional: for rounded corners */
    padding: 1px;
}
.cart-container {
  background: #fff;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  margin-top:-30px;
}
.dropdown{
          position: static;
        margin-right: 280px;
                margin-top: -50px;
}
          .dropdown-content {
    display: none;
    position: absolute;
    min-width: 130px;
    box-shadow: 0px 8px 16px rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    margin-right: 70px;
    margin-top:-10px;
    
    }
     .more-logo {
        margin-top: -10px;
        margin-left: 440px;
    }
  }

  @media (max-width: 321px) {
    body { padding-top: 150px; }
     .logo-container{
      margin-top:20px;
            margin-left:20px;

    }
        .logo img, .second-logo img {
        margin-left: 60px;
        margin-top: 1px;
        height: 40px;
    }

    .logo-container img { height: 40px; }
     .user-options {
    position:absolute;
    top:65px;
    left:50px;
    width: 100%;
    justify-content: center; /* Center logout and icons */
gap:13px;
  }
  
   .logout{
  position: relative;
        bottom:50px;
        left: -100px;
 }
 .addtocart-table th{
  font-size:6px;
  padding-top:0px;
   padding-bottom:0px;
    padding-right:2px;
     padding-left:2px;
 }
    .addtocart-table img { width: 30px; height:40px;}
    .addtocart-table td { font-size: 5px; }
    .remove-btn {
        border: none;
        padding: 4px 5px;
        font-size: 5px;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s 
ease;
    }
    .checkout-btn { font-size: 10px; padding: 10px; width: 60%; }
        .remove-selected-btn {
      font-size:7px;
    }
    .home-logo {
    height: 40px;
      width: 40px; /* increase size to make it look "thicker" or bolder */
    display: block;
     margin-top:-135px;
     position: fixed;
    left: 30px;
     border: 2px solid black;  /* or any color you prefer */
    border-radius: 10px;       /* optional: for rounded corners */
    padding: 1px;
}
.cart-container {
  background: #fff;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  margin-top:-30px;
}
.dropdown{
          position: static;
        margin-right: 290px;
                margin-top: -50px;
}
          .dropdown-content {
    display: none;
    position: absolute;
    min-width: 130px;
    box-shadow: 0px 8px 16px rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    margin-right: 60px;
    margin-top:-10px;
    }
     .more-logo {
        margin-top: -10px;
        margin-left: 430px;
    }
  } 
  .menu-btn {
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
  
</a>

      </div>
      <div class="second-logo">
        <img src="image/logo1.png" alt="Shoe Store Logo" />
      </div>
    </div>

    <div class="header-icons user-options">
    
      
      <div class="dropdown">
        <button class="dropbtn" aria-label="More Options">
          <img src="image/more.png" alt="More Options" class="more-logo" />
        </button>
        <div class="dropdown-content">
          <a href="classic.php">Classic Shoes</a>
          <a href="basketball.php">Basketball Shoes</a>
          <a href="running.php">Running Shoes</a>
          <a href="slide.php">Slides</a>
        </div>
      </div>

     
      <!-- USER REVIEW START -->
<!-- Review Modal -->
<div id="reviewModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999;">
  <div style="background: rgba(0, 0, 0, 0.2);

 padding:20px; max-width:400px; margin: 150px auto; border-radius:8px; text-align:center; position:relative; z-index:10000;">
    
    <!-- Close button -->
    <button id="closeModal" style="position:absolute; top:10px; right:10px; background:none; border:none; color:white; font-size:24px; cursor:pointer;">&times;</button>

    <h2>Rate your experience</h2>
    
    <!-- 5-star rating -->
    <div id="starRating" style="font-size:30px; cursor:pointer; color:yellow;">
      <span data-value="1">&#9734;</span>
      <span data-value="2">&#9734;</span>
      <span data-value="3">&#9734;</span>
      <span data-value="4">&#9734;</span>
      <span data-value="5">&#9734;</span>
    </div>
    
    <textarea id="comment" placeholder="Write your comment here..." style="width:100%; margin-top:15px;" rows="4"></textarea>
    
    <button id="submitReview" style="margin-top:15px;">Submit Review & Logout</button>
  </div>
</div>
<!-- USER REVIEW END -->

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

  // LOG OUT FUNCTION START
  document.addEventListener("DOMContentLoaded", function () {
    const email = "<?php echo $_SESSION['user']['email']; ?>";
    const hasReview = <?php echo $hasReview === 'true' ? 'true' : 'false'; ?>;

    const logoutBtn = document.getElementById('logout-button');
    const modal = document.getElementById('reviewModal');
    const stars = document.querySelectorAll('#starRating span');
    const commentInput = document.getElementById('comment');
    const submitBtn = document.getElementById('submitReview');
    const closeModalBtn = document.getElementById('closeModal');

    let selectedRating = 0;

    logoutBtn.addEventListener('click', function (e) {
      e.preventDefault(); // Prevent default behavior

      if (hasReview) {
        // If user already reviewed, confirm logout
        const confirmLogout = confirm("Do you want to logout?");
        if (confirmLogout) {
          window.location.href = "login.php";
        }
        // Else: do nothing (modal won't show)
      } else {
        // If user has NOT reviewed, show the modal
        modal.style.display = 'block';
      }
    });

    // Close modal on clicking close button
    closeModalBtn.addEventListener('click', () => {
      modal.style.display = 'none';
    });

    // Star rating logic
    stars.forEach(star => {
      star.addEventListener('mouseover', () => {
        const val = star.getAttribute('data-value');
        highlightStars(val);
      });
      star.addEventListener('mouseout', () => {
        highlightStars(selectedRating);
      });
      star.addEventListener('click', () => {
        selectedRating = star.getAttribute('data-value');
        highlightStars(selectedRating);
      });
    });

    function highlightStars(rating) {
      stars.forEach(star => {
        star.innerHTML = star.getAttribute('data-value') <= rating ? '★' : '☆';
        star.style.color = star.getAttribute('data-value') <= rating ? 'gold' : 'gray';
      });
    }

    // Submit review and logout
    submitBtn.addEventListener('click', () => {
      if (selectedRating === 0) {
        alert('Please select a star rating.');
        return;
      }
      const comment = commentInput.value.trim();

      // Send review to server (optional: add fetch/AJAX here)
      fetch('user_reviews.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          email: email,
          rating: selectedRating,
          comment: comment
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert("Thank you for your review! You will now be logged out.");
          window.location.href = "login.php";
        } else {
          alert("Failed to submit review.");
        }
      })
      .catch(err => {
        alert("Error submitting review.");
        console.error(err);
      });
    });
  });
  // LOG OUT FUNCTION END
</script>
    </div>
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


<div class="cart-container">
  <h2>Your Shopping Cart</h2>

   <a href="home.php">
    <img src="image/home1.png" alt="home-logo" class="home-logo">
  </a>

  <div id="cart-content">
    <?php if ($cart_result && $cart_result->num_rows > 0): ?>

      <form id="cart-checkout-form">
        <table class="addtocart-table" border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
          <thead>
            <tr>
              <th class="col1">
                Select All<input type="checkbox" id="select-all-checkbox" title="Select All"> 
              </th>
              <th class="col2">Image</th>
              <th class="col3">Details</th>
              <th>Total Price</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $cart_items_array = [];
            while ($row = $cart_result->fetch_assoc()):
              $cart_items_array[$row['id']] = [
                'shoe_id'   => $row['shoe_id'],
                'shoe_name' => $row['shoe_name'],
                'price'     => $row['price'],
                'size'      => $row['size'],
                'quantity'  => $row['quantity'],
                'shoe_type' => $row['shoe_type'] ?? '',  // <-- Added shoe_type here
              ];
              $item_total = $row['price'] * $row['quantity'];
            ?>
              <tr data-id="<?php echo $row['id']; ?>">
                <td class="col1">
                  <input type="checkbox" class="item-checkbox" name="item_ids[]" value="<?php echo $row['id']; ?>">
                </td>
                <td class="col2">
                  <?php
                    $imagePath = trim($row['shoe_image']);
                    $serverPath = __DIR__ . "/" . $imagePath;
                    if (!empty($imagePath) && file_exists($serverPath)):
                  ?>
                    <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($row['shoe_name']); ?>" style="" />
                  <?php else: ?>
                    <div style="width:80px;height:80px;background:#ccc;display:flex;align-items:center;justify-content:center;">No Image</div>
                  <?php endif; ?>
                </td>
                <td class="col3"><h4>Name: <?php echo htmlspecialchars($row['shoe_name']); ?>
             <br> Size: <?php echo htmlspecialchars($row['size']); ?>
            <br>Quantity: <?php echo htmlspecialchars($row['quantity']); ?></h4></td>
                <td data-item-total="<?php echo $item_total; ?>" class="item-total">₱<?php echo number_format($item_total, 2); ?></td>
                <td><button type="button" class="remove-btn ajax-remove-btn" data-id="<?php echo $row['id']; ?>">Remove</button></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>

        <!-- Hidden user info -->
        <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
        <input type="hidden" name="province" value="<?php echo htmlspecialchars($user['province']); ?>">
        <input type="hidden" name="municipality" value="<?php echo htmlspecialchars($user['municipality']); ?>">
        <input type="hidden" name="barangay" value="<?php echo htmlspecialchars($user['barangay']); ?>">
        <input type="hidden" name="street" value="<?php echo htmlspecialchars($user['street']); ?>">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
        <input type="hidden" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">

        <div class="checkout-container" style="margin-top: 15px; display: flex; align-items: center;">
          <button type="submit" name="action" value="checkout" class="checkout-btn">Proceed to Checkout</button>
          <span id="grand-total" style="">Total: ₱0.00</span>
          <button type="button" class="remove-selected-btn" style="">Remove Selected Items</button>
        </div>
      </form>

    <?php else: ?>
      <p>Your cart is empty.</p>
    <?php endif; ?>
  </div>

  <p id="empty-cart-message" style="display:none;">Your cart is empty.</p>
</div>

<script>
// Convert PHP cart items array to JS object
const cartItems = <?php echo json_encode($cart_items_array); ?>;

function checkIfCartIsEmpty() {
  const cartContent = document.getElementById('cart-content');
  const emptyMessage = document.getElementById('empty-cart-message');
  const tbody = cartContent.querySelector('tbody');
  const hasRows = tbody && tbody.querySelectorAll('tr').length > 0;

  if (!hasRows) {
    cartContent.style.display = 'none';
    emptyMessage.style.display = 'block';
  } else {
    cartContent.style.display = 'block';
    emptyMessage.style.display = 'none';
  }
}

// Currency formatting
function formatCurrency(amount) {
  return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Update grand total
function updateGrandTotal() {
  let total = 0;
  document.querySelectorAll('.item-checkbox:checked').forEach(function(checkbox) {
    const row = checkbox.closest('tr');
    const totalCell = row.querySelector('.item-total');
    if (totalCell) {
      const itemTotal = parseFloat(totalCell.getAttribute('data-item-total')) || 0;
      total += itemTotal;
    }
  });

  const grandTotalElem = document.getElementById('grand-total');
  if (grandTotalElem) {
    grandTotalElem.textContent = 'Total: ' + formatCurrency(total);
  }
}

function updateAfterItemRemoval() {
  updateSelectAllCheckbox();
  updateGrandTotal();
}

// Initial check (in case cart is empty on page load)
checkIfCartIsEmpty();
updateGrandTotal();

// Remove single item (AJAX)
document.querySelectorAll('.ajax-remove-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const itemId = btn.dataset.id;
    if (!confirm('Remove this item from cart?')) return;

    fetch('addtocart.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({'ajax_remove_item': itemId})
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const row = document.querySelector(`tr[data-id='${itemId}']`);
        if (row) row.remove();
        delete cartItems[itemId];
        checkIfCartIsEmpty();
        updateAfterItemRemoval();
      } else {
        alert('Failed to remove item.');
      }
    })
    .catch(() => alert('Error removing item.'));
  });
});

// Remove selected items
document.querySelector('.remove-selected-btn')?.addEventListener('click', () => {
  const checkedBoxes = [...document.querySelectorAll('.item-checkbox:checked')];
  if (checkedBoxes.length === 0) {
    alert('No items selected.');
    return;
  }

  if (!confirm(`Remove ${checkedBoxes.length} selected item(s)?`)) return;

  const selectedIds = checkedBoxes.map(cb => cb.value);

  fetch('addtocart.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: new URLSearchParams({
      'ajax_remove_selected': 1,
      'selected_items': JSON.stringify(selectedIds)
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      selectedIds.forEach(id => {
        const row = document.querySelector(`tr[data-id='${id}']`);
        if (row) row.remove();
        delete cartItems[id];
      });
      checkIfCartIsEmpty();
      updateAfterItemRemoval();
    } else {
      alert(data.error || 'Failed to remove selected items.');
    }
  })
  .catch(() => alert('Error removing selected items.'));
});

// Checkout
document.getElementById('cart-checkout-form')?.addEventListener('submit', function(e) {
  e.preventDefault();

  const selectedCheckboxes = [...document.querySelectorAll('.item-checkbox:checked')];
  if (selectedCheckboxes.length === 0) {
    alert('Please select at least one item to checkout.');
    return;
  }

  const selectedIds = selectedCheckboxes.map(cb => cb.value);
  const formData = new FormData();

  ['username', 'email', 'phone', 'province', 'municipality', 'barangay', 'street'].forEach(name => {
    const input = this.querySelector(`input[name="${name}"]`);
    if (input) formData.append(name, input.value);
  });

  formData.append('selected_items', JSON.stringify(selectedIds));
  formData.append('items', JSON.stringify(cartItems));

  fetch('addtocart.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert(data.message || 'Checkout successful!');
      selectedIds.forEach(id => {
        const row = document.querySelector(`tr[data-id='${id}']`);
        if (row) row.remove();
        delete cartItems[id];
      });
      checkIfCartIsEmpty();
      updateAfterItemRemoval();
    } else {
      alert('Checkout failed: ' + (data.message || 'Unknown error'));
      if (data.errors && data.errors.length) {
        console.error('Errors:', data.errors);
      }
    }
  })
  .catch(() => alert('Error during checkout.'));
});

// Select All checkbox
const selectAllCheckbox = document.getElementById('select-all-checkbox');

const itemCheckboxes = () => Array.from(document.querySelectorAll('.item-checkbox'));

selectAllCheckbox.addEventListener('change', () => {
  itemCheckboxes().forEach(cb => cb.checked = selectAllCheckbox.checked);
  updateGrandTotal();
});

document.getElementById('cart-content').addEventListener('change', (e) => {
  if (e.target.classList.contains('item-checkbox')) {
    updateSelectAllCheckbox();
    updateGrandTotal();
  }
});

function updateSelectAllCheckbox() {
  const allChecked = itemCheckboxes().length > 0 && itemCheckboxes().every(cb => cb.checked);
  const someChecked = itemCheckboxes().some(cb => cb.checked);
  selectAllCheckbox.checked = allChecked;
  selectAllCheckbox.indeterminate = !allChecked && someChecked;
}

updateSelectAllCheckbox();
</script>


</body>
</html>
