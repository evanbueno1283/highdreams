
<?php
session_start();

if (!isset($_SESSION['user']['id'])) {
  header("Location: login.php");
  exit();
}

$mysqli = new mysqli("mysql-highdreams.alwaysdata.net", "439165", "Skyworth23", "highdreams_1");

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

?>

<!DOCTYPE html>
<html lang="en">  
<head>
<link rel="icon" href="image/logo1.png" type="image/png">

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shoe Store</title>
<style>
 
    * {
    margin:0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      color: #333;
      display: flex;
      flex-direction: column;
      min-height: 100vh; /* Ensure full viewport height */
    }
    .popup {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.popup-content {
  background: white;
  padding: 20px;
  border-radius: 10px;
  text-align: center;
  width: 300px;
}

.stars .star {
  font-size: 24px;
  color: #ccc;
  cursor: pointer;
}

.stars .star.selected {
  color: gold;
}

textarea {
  width: 100%;
  height: 60px;
  margin-top: 10px;
  resize: none;
}


/* Header */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 30px;
  background-color: #000000;
  color: white;
  height: 70px;
  width: 100%; /* Ensure header stretches to full width */
  position: fixed;
  top: 0;
  width: 100%;
  z-index: 1000;
}


/* Logo container styles for aligning the logos */
.logo-container {
  display: flex;
  align-items: center;
  gap: 15px;
}


.home-logo {
    height: 40px;
      width: 40px; /* increase size to make it look "thicker" or bolder */
    display: block;
     margin-top:10px;
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

.header-icons{  display: flex;
  flex-wrap: nowrap; /* prevents images from going to next line */
  justify-content: center; /* optional */
  align-items: center;
  
  
}



.more-logo,{
  height: 100px;
  width: auto;
  margin: 0 5px 0 0; /* 5px right margin for more-logo only */

}
.account-logo {
  height: 40px; /* Set the scanner logo height to match other logos */
  width: auto;  /* Maintain aspect ratio */
  margin-right: -10px; /* Space between the logo and the logout button */
}


/* Dropdown Styles */
.dropdown {

  display: flex;
  flex-direction: row;   /* stay side-by-side */
  flex-wrap: nowrap;     /* don't drop to next line */

  
  
}
.dropbtn {
 
  background: none; /* Remove background */
  color: #000000;
  border: none; /* Remove border */
  cursor: pointer;
}

.dropbtn:hover {
  background-color: transparent; /* Ensure no background on hover */
}
  .order {
  height: 50px; /* Set the scanner logo height to match other logos */
  width: auto;  /* Maintain aspect ratio */
  margin-right: -15px; /* Space between the logo and the logout button */
}


.more-logo {
  height: 70px; /* Increase the logo height */
  width: auto;  /* Maintain aspect ratio */
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

.scanner-logo img {
  height: 50px; /* Set the scanner logo height to match other logos */
  width: auto;  /* Maintain aspect ratio */
  margin-right: 10px; /* Space between the logo and the logout button */
}

.search-bar {
  display: flex;
  align-items: center;
  justify-content: center; /* Center the search bar */
  gap: 10px; /* Push the search bar to the right */
  flex-grow: 1; /* Make the search bar take up the available space */
}

.search-bar input {
  padding: 10px;
  width: 300px; /* Adjust width to fit the screen better */
  border-radius: 20px;
  border: none;
}

.search-bar button {
  padding: 10px;
  background-color: #000000;
  color: #fdfdfd;
  border: none;
  cursor: pointer;
  border-radius: 20px;
}

.search-bar button:hover {
  background-color: #333333;
}
/* HEADER icons BOOTSTRAP  */
    .order, .more-logo, .account-logo, .scanner-logo img {
      margin: 0 10px; /* Adjust spacing between icons */
}

/* User Options Container */
.user-options {
  display: flex;
  align-items: center;
   flex-direction: row;   /* stay side-by-side */
  flex-wrap: nowrap;
  gap: 20px; /* Space between the scanner logo and the logout button */
}

.user-options button {
  padding: 10px;
  background-color: #ffffff;
  color: #000000;
  border: none;
  cursor: pointer;
  border-radius: 20px;
}

.user-options button:hover {
  background-color: #eeeeee;
}
/*Header */
/* Updated Category Styling // Updated Category Styling */
.categories {
  background-color: #f9f9f9;
  padding: 30px;
  text-align: center;
  margin-top: 50px;
}

.categories h2 {
  font-size: 2rem;
  margin-bottom: 20px;
}

.category-list {
  display: flex;
  justify-content: center; /* Center the categories horizontally */
  gap: 30px; /* Space between items */
  flex-wrap: wrap; /* Allow wrapping for small screens */
}

.category-item {
  text-align: center;
  flex: 1 1 200px; /* Allow items to grow and shrink */
  max-width: 250px; /* Set a maximum width */
  cursor: pointer; /* Indicate the items are clickable */
  display: flex;
  flex-direction: column;
  align-items: center;
}

.category-item a {
  display: block; /* Make the whole area clickable */
  text-decoration: none; /* Remove underline from the text */
  color: inherit; /* Inherit the text color */
}

.category-item img {
  width: 150px; /* Set a fixed width for the images */
  height: 150px; /* Set a fixed height for the images */
  object-fit: contain; /* Ensure the logo fits inside the given dimensions */
  border-radius: 10px;
  transition: transform 0.3s ease-in-out; /* Transition effect for zoom */
}

.category-item img:hover {
  transform: scale(1.05); /* Slight zoom effect on hover */
}

.category-item p {
  margin-top: 10px; /* Adjust margin to ensure both texts align */
  font-size: 1.2rem;
  color: #333;
}


    /* Product Styling */
    .featured-products {
      background-color: #f9f9f9;
      padding: 30px;
      text-align: center;
    }

    .featured-products h2 {
      font-size: 2rem;
      margin-bottom: 20px;
    }

    .product-list {
      display: flex;
      justify-content: center;
      gap: 30px;
      flex-wrap: wrap;
    }

    .product-item {
      width: 200px;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgb(0, 0, 0);
      padding: 15px;
      text-align: center;
      opacity: 0; /* Start invisible */
      transform: translateY(30px); /* Start slightly below */
      animation: fadeInUp 0.6s ease-out forwards; /* Fade in and slide up animation */
    }

    .product-item img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 10px;
      transition: transform 0.3s ease-in-out; /* Transition effect for the zoom */
    }

    .product-item img:hover {
      transform: scale(1.1); /* Scale image on hover (zoom effect) */
    }

    .product-item h3 {
      margin-top: 10px;
      font-size: 1.2rem;
    }

    .product-item p {
      margin-top: 5px;
      color: #000000;
      font-size: 1rem;
    }

    .product-item button {
      margin-top: 10px;
      padding: 10px;
      background-color: #ff0000;
      box-shadow: 0 2px 10px rgb(0, 0, 0);
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      width: 100%;
    }

    .product-item button:hover {
      background-color: #000000;
    }

    /* Pre-Order Form (Modal) */
    .preorder-form {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .preorder-form .form-container {
      background-color: white;
      padding: 30px;
      border-radius: 10px;
      width: 400px;
      text-align: center;
    }

    .quantity-selector {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 10px 0;
  gap: 10px;
}

.quantity-selector .qty-btn {
  background-color: #000;
  color: #fff;
  border: none;
  width: 25px;
  height: 25px;
  font-size: 16px;
  cursor: pointer;
  border-radius: 4px;
  line-height: 1;
  padding: 0;
}

.quantity-selector input {
  width: 40px;
  text-align: center; /* <-- ito ang binago */
  border: 1px solid #ccc;
  border-radius: 4px;
  height: 25px;
  background-color: #f9f9f9;
}



/* 2:58 AM */
.user-info {
    display: flex;
    flex-direction: column;
   
}

.user-info textarea {
   width: 210px;
}

.user-info-item {
    font-size: 14px;
    color: #000;
   
    padding: 5px;
    border: 1px solid #000; /* Black border */
    margin-bottom: 5px; /* Optional: Adds space between items */
    border-radius: 5px;
}

/* 2:58 AM */
    
    .preorder-form button {
      width: 100%;
      padding: 10px;
      margin-bottom: 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    .preorder-form button {
      background-color: #000000;
      color: white;
      cursor: pointer;
    }

    .preorder-form button:hover {
      background-color: #333;
    }

    .close-btn {
      background-color: red;
      color: white;
      border: none;
      padding: 10px;
      cursor: pointer;
      margin-top: 10px;
      width: 100%;
    }
  
.dropbtn:hover .order {
  color: white; /* Ensure text remains white on hover */
}
select{
  width:60%;
}


    .footer {
  background-color: #000000;  /* Black background */
  color: white;  /* White text color */
  text-align: center;  /* Center text */
  padding: 20px 0;  /* Add some padding */
  margin-top: auto;  /* Push the footer to the bottom of the page */
  width: 100%;  /* Ensure it spans the full width */
}


    /* Animation for product items */
    @keyframes fadeInUp {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
@media (max-width:2560px){
   /* Product Bootstrap */
.product-list {
 display: flex
;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
}
}
    @media (max-width: 1441px) {
       .dropdown-content {
   
  margin-top:60px;
}
    .menu-btn {
  font-size: 26px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  display: none; /* hidden default */
  position: absolute;
  left: 15px;
}

/* ===== LOGO ===== */
.logo {
  margin: 0 auto;
  font-size: 20px;
  font-weight: bold;
}

/* ===== SIDEBAR ===== */
.sidebar {
  position: fixed;
  top: 0;
  left: -260px; /* hidden by default */
  width: 260px;
  height: 100vh;
  background-color: #111;
  color: white;
  transition: left 0.3s ease;
  z-index: 999;
  padding-top: 60px;
}

.sidebar.active {
  left: 0; /* show when active */
}

.sidebar a {
  display: block;
  padding: 14px 24px;
  text-decoration: none;
  color: white;
  font-size: 16px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar a:hover {
  background-color: #333;
}

/* ===== CLOSE BUTTON ===== */
.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
}
  .search-bar input {
        margin-left:40px;
        width: 40%;
  }
  .logo img, .second-logo img {
    height: 50px;
    margin-left: 300px;
}
.search-bar {
       margin-left: -310px;
       
  }
   /* Product Bootstrap */
.product-list {
 display: flex
;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
}
.product-item{
  width:250px;
  height:auto;
}
}
    @media (max-width: 1040px) { 
      /* HEADER icons BOOTSTRAP  */
    .search-bar input {
        margin-left:40px;
        width: 80%;
  }
      .menu-btn {
  font-size: 26px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  display: none; /* hidden default */
  position: absolute;
  left: 15px;
}

/* ===== LOGO ===== */
.logo {
  margin: 0 auto;
  font-size: 20px;
  font-weight: bold;
}

/* ===== SIDEBAR ===== */
.sidebar {
  position: fixed;
  top: 0;
  left: -260px; /* hidden by default */
  width: 260px;
  height: 100vh;
  background-color: #111;
  color: white;
  transition: left 0.3s ease;
  z-index: 999;
  padding-top: 60px;
}

.sidebar.active {
  left: 0; /* show when active */
}

.sidebar a {
  display: block;
  padding: 14px 24px;
  text-decoration: none;
  color: white;
  font-size: 16px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar a:hover {
  background-color: #333;
}

/* ===== CLOSE BUTTON ===== */
.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
}

}
 @media (max-width: 1025px) { 
    .dropdown-content {
  margin-top:60px;
}
    .menu-btn {
  font-size: 26px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  display: none; /* hidden default */
  position: absolute;
  left: 15px;
}

/* ===== LOGO ===== */
.logo {
  margin: 0 auto;
  font-size: 20px;
  font-weight: bold;
}

/* ===== SIDEBAR ===== */
.sidebar {
  position: fixed;
  top: 0;
  left: -260px; /* hidden by default */
  width: 260px;
  height: 100vh;
  background-color: #111;
  color: white;
  transition: left 0.3s ease;
  z-index: 999;
  padding-top: 60px;
}

.sidebar.active {
  left: 0; /* show when active */
}

.sidebar a {
  display: block;
  padding: 14px 24px;
  text-decoration: none;
  color: white;
  font-size: 16px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar a:hover {
  background-color: #333;
}

/* ===== CLOSE BUTTON ===== */
.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
}
.categories{
  margin-top:70px;
}
.dropdown, .order, .scanner-logo, .logout {
        position: static;
        margin-right: -20px;
}
.logo img, .second-logo img {
    margin-left: 180px;
}
 .search-bar {
       margin-left: -70px;
       
  }
  .search-bar input {
        margin-left:-10px;
        width: 55%;
  }
}
    @media (max-width: 980px) { 
      /* HEADER icons BOOTSTRAP  */
 .search-bar input {
    width: 60%; /* Set width to 80% on smaller screens */
  }
      .menu-btn {
  font-size: 26px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  display: none; /* hidden default */
  position: absolute;
  left: 15px;
}

/* ===== LOGO ===== */
.logo {
  margin: 0 auto;
  font-size: 20px;
  font-weight: bold;
}

/* ===== SIDEBAR ===== */
.sidebar {
  position: fixed;
  top: 0;
  left: -260px; /* hidden by default */
  width: 260px;
  height: 100vh;
  background-color: #111;
  color: white;
  transition: left 0.3s ease;
  z-index: 999;
  padding-top: 60px;
}

.sidebar.active {
  left: 0; /* show when active */
}

.sidebar a {
  display: block;
  padding: 14px 24px;
  text-decoration: none;
  color: white;
  font-size: 16px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar a:hover {
  background-color: #333;
}

/* ===== CLOSE BUTTON ===== */
.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
}

}

/* RESPONSIVE STYLES */
@media (max-width: 935px) { 
 
     .menu-btn {
  font-size: 26px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  display: none; /* hidden default */
  position: absolute;
  left: 15px;
}

/* ===== LOGO ===== */
.logo {
  margin: 0 auto;
  font-size: 20px;
  font-weight: bold;
}

/* ===== SIDEBAR ===== */
.sidebar {
  position: fixed;
  top: 0;
  left: -260px; /* hidden by default */
  width: 260px;
  height: 100vh;
  background-color: #111;
  color: white;
  transition: left 0.3s ease;
  z-index: 999;
  padding-top: 60px;
}

.sidebar.active {
  left: 0; /* show when active */
}

.sidebar a {
  display: block;
  padding: 14px 24px;
  text-decoration: none;
  color: white;
  font-size: 16px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar a:hover {
  background-color: #333;
}

/* ===== CLOSE BUTTON ===== */
.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
}


  /* DROPDOWN RESPONSIVE */
  .dropdown-content {
    position: absolute;
 
   
    z-index: 9999;
  }

  .dropdown-content a {
    color: #fff;
    padding: 12px 16px;
    display: block;
    text-decoration: none;
    border-bottom: 1px solid #ffffff;
  }

  /* Ensure logout is not hidden */
  .dropdown-content a.logout {
    margin-top: 5px;
    background: #222;
    color: #ff6961;
  }
}
@media (max-width: 913px) { 
search-bar input {
        margin-left:-10px;
        width: 40%;
  }
    }
    @media (max-width: 853px) { 
.search-bar input {
    width: 60%; /* Full width for smaller screens */
    max-width: 300px;
  }
}
@media (max-width: 769px) { 
  /* HEADER icons BOOTSTRAP  */
  .header {
    padding-bottom: 50px;
  }
    .logo-container {
    justify-content: center;
    width: 100%;
    margin-bottom: 10px;
  }
  .search-bar {
    position: absolute;
    top: 20px;
    margin-left: 170px;
    z-index: 10;
    width: 60%;
  }
    .search-bar input {
    width: 80%; /* Full width for smaller screens */
    max-width: 400px;
  }
  .home-logo {
   
    margin-top: -90px;

  }
  .categories h2{
    margin-top:-80px;
  }
  .more-logo {
                    margin-top: 30px;
        height: 70px;
        margin-right: -10px;
  }
  .logo img, .second-logo img {
    height: 60px;
    margin-left: 75px;
    margin-top:35px;
  }
  .menu-btn {
  margin-top:30px;
      font-size: 45px;
}
.header-icons{
gap:5px;
}
  .dropdown{
    left:15px;
  }
   .dropdown-content {
       position: absolute;
        min-width: 130px;
        box-shadow: 0px 8px 16px rgba(255, 255, 255, 0.2);
        border-radius: 8px;
                margin-top: 90px;
        margin-right: 10px;
}
  
   .user-options {
    width: 100%;
    justify-content: center; /* Center logout and icons */
    gap: 15px;
  }
  /* HEADER icons BOOTSTRAP  */
  .categories {
   
    padding-top: 100px;
}


.product-item {
  width:200px;
  height:300px;
}
.product-item img{
  height:170px;
}
.product-item h3 {
    margin-top: 5px;
    margin-bottom:5px;
    font-size: 1.2rem;
}
.product-item p {
    margin-top: 2px;
    color: #000000;
    font-size: 1rem;
}
.preorder-form .form-container {
   height:450px;
  
    padding-top: 20px;
    padding-right: 20px;
    padding-bottom: 20px;
    padding-left: 20px;
}
 h3 {
   font-size:15px;
   margin-top:5px;
    margin-bottom:5px;
}
.user-info-item {
    font-size: 12px;
    color: #000;
    padding: 3px;
    border: 1px solid #000;
    margin-bottom: 5px;
    border-radius: 5px;
}
.preorder-form button {
    width: 100%;
    padding: 5px;
    margin-bottom: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
}
}

@media (max-width: 695px) { 
  /* HEADER icons BOOTSTRAP  */
  .account-logo {
    padding-left: 20px;
  }
  .search-bar {
   
    left: -10x;
    z-index: 10;
    width: 60%;
  }
  .user-options {
    
    gap: 10px;
}
}

@media (max-width: 630px) { 
  /* HEADER icons BOOTSTRAP  */
  .logo-container img,
  .account-logo,
  .order {
    height: 40px; /* Reduce logo size */
  }
 
  .more-logo,
   .scanner-logo img{
    height: 45px; /* Reduce logo size */
  }
      .search-bar {
        position: absolute;
        left: 30px;
        z-index: 10;
        width: 50%;
    }

.user-options button {
    padding: 5px;
   
}
.user-options  button {
  font-size: 11px; /* Or any smaller size you prefer */
}
.search-bar button {
  font-size: 12px; /* Or any smaller size you prefer */
}
}
@media (max-width: 590px) {
  /* Categories Bootstrap */
 .category-list {
    display: flex;
    justify-content: center;
    gap: 90px;
    flex-wrap: nowrap;
    flex-direction: row;

}
  .category-item img {
    width: 200px;
    height: 150px;
    object-fit: contain;
    border-radius: 10px;
    transition: transform 0.3sease-in-out;
}
.categories h2 {
    font-size: 20px;
    margin-bottom: 20px;
}
.category-item {
  
    max-width: 100px;
}
  /* Product Bootstrap */
.featured-products h2 {
    font-size: 40px;
    margin-bottom: 20px;
}
 /* Product Bootstrap */
.product-list {
 display: flex
;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
}
.product-item {
    width: auto;
   height: 250px;
}
.product-item img {
    width: 100%;
    height: 150px;
   
   
}
.product-item h3 {
    margin-top: 1px;
    font-size: 12px;
}
.product-item p {
    margin-top: 5px;
    color: #000000;
    font-size: 12px;
}
.product-item button {
   
    padding: 5px;
  
}
/* pre-order form Bootstrap */
.preorder-form .form-container {
    
    width: 320px;
}
.form-container h3{
  margin-bottom: 10px;
  font-size:12px;
}
}
@media (max-width: 550px) {
   /* HEADER icons BOOTSTRAP  */
   .logo-container img,
  .account-logo,
  .order {
    height: 30px; /* Reduce logo size */
  }
 
  .more-logo,
   .scanner-logo img{
    height: 40px; /* Reduce logo size */
  }
  .account-logo
   {
    margin-left:15px;
  }
  .user-options {
   
   gap: 0px;
  }
 }
 @media (max-width: 490px) { 
  /* Categories Bootstrap */
 .category-list {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: nowrap;
    flex-direction: row;
}
  .category-item img {
    width: 100px;
    height: 100px;
    object-fit: contain;
    border-radius: 10px;
    transition: transform 0.3sease-in-out;
}
.categories h2 {
    font-size: 20px;
    margin-bottom: 20px;
}
.category-item {
  
    max-width: 100px;
}
 }
@media (max-width: 480px) { 
  /* HEADER icons BOOTSTRAP  */
  .logo-container img,
  .account-logo,
  .order {
    height: 30px; /* Reduce logo size */
  }
 
  .more-logo,
   .scanner-logo img{
    height: 35px; /* Reduce logo size */
  }
  .account-logo
   {
    margin-left:15px;
  }
  .user-options {
   
   gap: 0px;
  }
  .search-bar {
   
   left: 90px;
   z-index: 10;
   width: 60%;
 }
}
@media (max-width: 455px) { 
  /* HEADER icons BOOTSTRAP  */
  .logo-container img,
  .account-logo,
  .order {
    height: 30px; /* Reduce logo size */
  }

  .ar {
    height: 30px; /* Reduce logo size */
  }
 
  .more-logo,
   .scanner-logo img{
    height: 35px; /* Reduce logo size */
  }
  .logo-container img,
  .account-logo,
  .order, .more-logo,
  .scanner-logo img{
    margin-right:5px;
  }
  .account-logo
   {
    margin-left:5px;
  }
  .search-bar {
   
   left: -10x;
   z-index: 10;
   width: 60%;
 }
 .user-options {
   
   gap: 5px;
  }
     .menu-btn {
  font-size: 26px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  display: none; /* hidden default */
  position: absolute;
  left: 15px;
}

/* ===== LOGO ===== */
.logo {
  margin: 0 auto;
  font-size: 20px;
  font-weight: bold;
}

/* ===== SIDEBAR ===== */
.sidebar {
  position: fixed;
  top: 0;
  left: -260px; /* hidden by default */
  width: 260px;
  height: 100vh;
  background-color: #111;
  color: white;
  transition: left 0.3s ease;
  z-index: 999;
  padding-top: 60px;
}

.sidebar.active {
  left: 0; /* show when active */
}

.sidebar a {
  display: block;
  padding: 14px 24px;
  text-decoration: none;
  color: white;
  font-size: 16px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar a:hover {
  background-color: #333;
}

/* ===== CLOSE BUTTON ===== */
.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
}

}
@media (max-width: 440px) { 
   /* HEADER icons BOOTSTRAP  */
   .logo-container img,
  .account-logo,
  .order {
    height: 30px; /* Reduce logo size */
  }
 
  .more-logo,
   .scanner-logo img{
    height: 35px; /* Reduce logo size */
  }

  .account-logo
   {
    margin-left:5px;
  }
  .user-options {
   
    gap: 10px;
}
.search-bar {
   
   left: -10x;
   z-index: 10;
   width: 60%;
 }
 .logo-container img, .account-logo, .order, .more-logo, .scanner-logo img {
        margin-right: 0px;
    }
}
@media (max-width: 430px) { 
.logo img, .second-logo img {
    height: 40px;
    margin-left: 50px;
    margin-top: 40px;
  }
  .more-logo {
               margin-top: -40px;
        height: 60px;
        margin-right: -75px;
  }
    .logo-container img, .account-logo, .order {
             margin-right: 190px;
        margin-top: 80px;
        height: 40px;
    }
    .categories h2{
        font-size: 20px;
        margin-top: 5px;
    }

    
        .menu-btn {
  font-size: 26px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  display: none; /* hidden default */
  position: absolute;
  left: 15px;
 margin-top: 35px;
}
.home-logo {
   margin-top: -40px;
        left: 20px;
    }
    .search-bar input {
  width:70%;
  font-size:12px;
   border-radius: 12px;
    }
            .search-bar button {
        font-size: 12px;
                margin-left: -10px;
    }
     .search-bar {
           width: 60%;
        margin-left: 40px;
      top: 20px
 }

 .order{
  top:55px;
  right:145px;
position:absolute;
 }
 .dropdown{
position: relative;
        top: 40px;
        left: -55px;
 }
  .dropdown-content {
    margin-top:40px;
  }
    .dropdown-content a {
       
        font-size:12px;
    }
    .dropdown-content {
    display: none;
    position: absolute;
    background-color: #000000;
    min-width: 130px;
    box-shadow: 0px 8px 16px rgba(255, 255, 255, 0.2);
    border-radius: 8px;
}

 .user-options .scanner-logo {
position: relative;
        top: 40px;
        left: -50px;
 }
    .menu-btn {
  font-size: 26px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  display: none; /* hidden default */
  position: absolute;
  left: 15px;
}

/* ===== LOGO ===== */
.logo {
  margin: 0 auto;
  font-size: 20px;
  font-weight: bold;
}

/* ===== SIDEBAR ===== */
.sidebar {
  position: fixed;
  top: 0;
  left: -260px; /* hidden by default */
  width: 260px;
  height: 100vh;
  background-color: #111;
  color: white;
  transition: left 0.3s ease;
  z-index: 999;
  padding-top: 60px;
}

.sidebar.active {
  left: 0; /* show when active */
}

.sidebar a {
  display: block;
  padding: 14px 24px;
  text-decoration: none;
  color: white;
  font-size: 16px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar a:hover {
  background-color: #333;
}

/* ===== CLOSE BUTTON ===== */
.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
}

 .logout{
  position: relative;
        top: 0px;
        left: -100px;
 }
  /* HEADER icons BOOTSTRAP  */

  .categories {
      margin-top: 60px;
      padding-top: 50px;
     
}
.product-item img {
  height: 120px;
}
.product-item button {
  margin-top:-90px;
  padding:3px;
}
.product-item {
        width: auto;
        height: 220px;
    }
        .product-item p {
        margin-top: 5px;
        margin-bottom: 5px;
        color: #000000;
        font-size: 12px;
    }
    .featured-products {
    background-color: #f9f9f9;
   
    padding-top: 10px;
    padding-right: 30px;
    padding-bottom: 30px;
    padding-left: 30px;
    text-align: center;
}
  }
  media (max-width: 540px) { 
  /* HEADER icons BOOTSTRAP  */
  
.logo img, .second-logo img {
    height: 40px;
    margin-left: 50px;
    margin-top: 40px;
  }
  .more-logo {
               margin-top: -40px;
        height: 60px;
        margin-right: -75px;
  }
    .logo-container img, .account-logo, .order {
             margin-right: 190px;
        margin-top: 80px;
        height: 40px;
    }
    .categories h2{
        font-size: 20px;
        margin-top: 5px;
    }

    
        .menu-btn {
  font-size: 26px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  display: none; /* hidden default */
  position: absolute;
  left: 15px;
 margin-top: 35px;
}
.home-logo {
   margin-top: -40px;
        left: 20px;
    }
    .search-bar input {
  width:70%;
  font-size:12px;
   border-radius: 12px;
    }
            .search-bar button {
        font-size: 12px;
                margin-left: -10px;
                margin-top:30px;
    }
     .search-bar {
           width: 60%;
        margin-left: 40px;
      top: 20px
 }

 .order{
  top:55px;
  right:145px;
position:absolute;
 }
 .dropdown{
position: relative;
        top: 40px;
        left: -55px;
 }
  .dropdown-content {
    margin-top:40px;
  }
    .dropdown-content a {
       
        font-size:12px;
    }
    .dropdown-content {
    display: none;
    position: absolute;
    background-color: #000000;
    min-width: 130px;
    box-shadow: 0px 8px 16px rgba(255, 255, 255, 0.2);
    border-radius: 8px;
}

 .user-options .scanner-logo {
position: relative;
        top: 40px;
        left: -50px;
 }
    .menu-btn {
  font-size: 26px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  display: none; /* hidden default */
  position: absolute;
  left: 15px;
}

/* ===== LOGO ===== */
.logo {
  margin: 0 auto;
  font-size: 20px;
  font-weight: bold;
}

/* ===== SIDEBAR ===== */
.sidebar {
  position: fixed;
  top: 0;
  left: -260px; /* hidden by default */
  width: 260px;
  height: 100vh;
  background-color: #111;
  color: white;
  transition: left 0.3s ease;
  z-index: 999;
  padding-top: 60px;
}

.sidebar.active {
  left: 0; /* show when active */
}

.sidebar a {
  display: block;
  padding: 14px 24px;
  text-decoration: none;
  color: white;
  font-size: 16px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar a:hover {
  background-color: #333;
}

/* ===== CLOSE BUTTON ===== */
.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
}

 .logout{
  position: relative;
        top: 0px;
        left: -100px;
 }
  /* HEADER icons BOOTSTRAP  */

  .categories {
      margin-top: 60px;
      padding-top: 50px;
     
}
.product-item img {
  height: 120px;
}
.product-item button {
  margin-top:-90px;
  padding:3px;
}
.product-item {
        width: auto;
        height: 220px;
    }
        .product-item p {
        margin-top: 5px;
        margin-bottom: 5px;
        color: #000000;
        font-size: 12px;
    }
    .featured-products {
    background-color: #f9f9f9;
   
    padding-top: 10px;
    padding-right: 30px;
    padding-bottom: 30px;
    padding-left: 30px;
    text-align: center;
}
  }
@media (max-width: 426px) { 
  /* HEADER icons BOOTSTRAP  */
  
.logo img, .second-logo img {
    height: 40px;
    margin-left: 50px;
    margin-top: 40px;
  }
  .more-logo {
               margin-top: -40px;
        height: 60px;
        margin-right: -75px;
  }
    .logo-container img, .account-logo, .order {
             margin-right: 190px;
        margin-top: 80px;
        height: 40px;
    }
    .categories h2{
        font-size: 20px;
        margin-top: 5px;
    }

    
        .menu-btn {
  font-size: 26px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  display: none; /* hidden default */
  position: absolute;
  left: 15px;
 margin-top: 35px;
}
.home-logo {
   margin-top: -40px;
        left: 20px;
    }
    .search-bar input {
  width:70%;
  font-size:12px;
   border-radius: 12px;
    }
            .search-bar button {
        font-size: 12px;
                margin-left: -10px;
    }
     .search-bar {
           width: 60%;
        margin-left: 40px;
      top: 20px
 }

 .order{
  top:55px;
  right:145px;
position:absolute;
 }
 .dropdown{
position: relative;
        top: 40px;
        left: -55px;
 }
  .dropdown-content{
    position: absolute;
        min-width: 130px;
        box-shadow: 0px 8px 16px rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        margin-top: 20px;
        margin-right: -60px;
}
    .dropdown-content a {
       
        font-size:12px;
    }
    
 .user-options .scanner-logo {
position: relative;
        top: 40px;
        left: -50px;
 }
    .menu-btn {
  font-size: 26px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  display: none; /* hidden default */
  position: absolute;
  left: 15px;
}

/* ===== LOGO ===== */
.logo {
  margin: 0 auto;
  font-size: 20px;
  font-weight: bold;
}

/* ===== SIDEBAR ===== */
.sidebar {
  position: fixed;
  top: 0;
  left: -260px; /* hidden by default */
  width: 260px;
  height: 100vh;
  background-color: #111;
  color: white;
  transition: left 0.3s ease;
  z-index: 999;
  padding-top: 60px;
}

.sidebar.active {
  left: 0; /* show when active */
}

.sidebar a {
  display: block;
  padding: 14px 24px;
  text-decoration: none;
  color: white;
  font-size: 16px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar a:hover {
  background-color: #333;
}

/* ===== CLOSE BUTTON ===== */
.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
}

 .logout{
  position: relative;
        top: 0px;
        left: -100px;
 }
  /* HEADER icons BOOTSTRAP  */

  .categories {
      margin-top: 60px;
      padding-top: 50px;
     
}
.product-item img {
  height: 120px;
}
.product-item button {
  margin-top:-90px;
  padding:3px;
}
.product-item {
        width: auto;
        height: 220px;
    }
        .product-item p {
        margin-top: 5px;
        margin-bottom: 5px;
        color: #000000;
        font-size: 12px;
    }
    .featured-products {
    background-color: #f9f9f9;
   
    padding-top: 10px;
    padding-right: 30px;
    padding-bottom: 30px;
    padding-left: 30px;
    text-align: center;
}
  }
@media (max-width: 410px) { 
  /* HEADER icons BOOTSTRAP  */
.logo-container img, .account-logo, .order, .more-logo, .scanner-logo img {
        margin-right: -10px;
    }
        .menu-btn {
  font-size: 26px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  display: none; /* hidden default */
  position: absolute;
  left: 15px;
}

/* ===== LOGO ===== */
.logo {
  margin: 0 auto;
  font-size: 20px;
  font-weight: bold;
}

/* ===== SIDEBAR ===== */
.sidebar {
  position: fixed;
  top: 0;
  left: -260px; /* hidden by default */
  width: 260px;
  height: 100vh;
  background-color: #111;
  color: white;
  transition: left 0.3s ease;
  z-index: 999;
  padding-top: 60px;
}

.sidebar.active {
  left: 0; /* show when active */
}

.sidebar a {
  display: block;
  padding: 14px 24px;
  text-decoration: none;
  color: white;
  font-size: 16px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar a:hover {
  background-color: #333;
}

/* ===== CLOSE BUTTON ===== */
.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
}

}
@media (max-width: 395px) { 
  /* HEADER icons BOOTSTRAP  */
  .logo-container img
   {
   margin-left:0px;
  }
  .logo-container img,
  .account-logo,
  .order {
    height: 30px; /* Reduce logo size */
  }
     .menu-btn {
  font-size: 26px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  display: none; /* hidden default */
  position: absolute;
  left: 15px;
}

/* ===== LOGO ===== */
.logo {
  margin: 0 auto;
  font-size: 20px;
  font-weight: bold;
}

/* ===== SIDEBAR ===== */
.sidebar {
  position: fixed;
  top: 0;
  left: -260px; /* hidden by default */
  width: 260px;
  height: 100vh;
  background-color: #111;
  color: white;
  transition: left 0.3s ease;
  z-index: 999;
  padding-top: 60px;
}

.sidebar.active {
  left: 0; /* show when active */
}

.sidebar a {
  display: block;
  padding: 14px 24px;
  text-decoration: none;
  color: white;
  font-size: 16px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar a:hover {
  background-color: #333;
}

/* ===== CLOSE BUTTON ===== */
.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
}

  .more-logo,
   .scanner-logo img{
    height: 35px; /* Reduce logo size */
  }

  .account-logo
   {
    margin-left:5px;
  }
  .user-options {
   
    gap: 5px;
}
 .category-list {
    margin:10px;
  }

.search-bar {
   
   left: 70px;
   z-index: 10;
   width: 60%;
 }
 /* Categories Bootstrap */
 .category-list {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: nowrap;
    flex-direction: row;
}
  .category-item img {
    width: 100px;
    height: 100px;
    object-fit: contain;
    border-radius: 10px;
    transition: transform 0.3sease-in-out;
}
.categories h2 {
    font-size: 20px;
    margin-bottom: 20px;
}
.category-item {
  
    max-width: 100px;
}

}
@media (max-width: 391px) { 
  /* HEADER icons BOOTSTRAP  */
  .header{
    height: 1px;
  }
   .search-bar input {
        padding: 7px;
    }
      .logo-container {
        margin-bottom: 50px;
    }
    .header-icons{
       position: absolute;
  top: 60px;
  left: 100px;
    }
  .logo img, .second-logo img {
    height: 40px;
    margin-left: 35px;
    margin-top: 40px;
  }
  .more-logo {
        margin-top: -30px;
       height: 60px;
               margin-left: 90px;
  }
    .logo-container img, .account-logo, .order {
             margin-right: 190px;
        margin-top: 80px;
        height: 40px;
    }
    .categories h2{
        font-size: 20px;
        margin-top: 10px;
    }

    
        .menu-btn {
  font-size: 26px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  display: none; /* hidden default */
  position: absolute;
  left: 15px;
 margin-top: 35px;
}
.home-logo {
   margin-top: -40px;
        left: 20px;
        
    }
    .search-bar input {
        font-size: 9px;
        margin-left: 50px;
        width: 50%;
        border-radius: 12px;
        }
            .search-bar button {
        font-size: 10px;
                margin-left: -10px;
    }
     .search-bar {
           width: 60%;
        margin-left:0px;
      top: 20px
 }

/* ===== LOGO ===== */
.logo {
  margin: 0 auto;
  font-size: 20px;
  font-weight: bold;
}


/* ===== CLOSE BUTTON ===== */
.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
}
     .order {
        top: 0px;
        right: -45px;
        position: absolute;
    }
     .logout{
position: relative;
        top: -20px;
        left:10px;
 }
  .user-options .scanner-logo{
position: relative;
        top: -20px;
        left: 0px;
 }
   .dropdown{
            position: absolute;
                top: 30px;
        left: 240px;
 }
    .dropdown-content {
           margin-top: 20px;
        margin-left: -60px;
    }
  }
  @media (max-width: 345px) { 
.more-logo {
       margin-right: 50px;
        height: 50px;
        margin-top: -5px;
    }
  }
@media (max-width: 376px) { 
  /*Header*/
 .dropdown{
top:40px;
right:75px;
gap:10px;
 }
 .dropdown-content a {
       
        font-size:12px;
    }
    .dropdown-content{
    position: absolute;
        min-width: 130px;
        box-shadow: 0px 8px 16px rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        margin-top: 20px;
        margin-right: -50px;
    

}
   .order {
        top: 55px;
        right: 138px;
        position: absolute;
    }
        .more-logo {
       margin-left: 80px;
        height: 50px;
        margin-top: -30px;
    }
        .logout{
  margin-left:15px;
 }
  /*Header*/
  .product-item img {
        height: 100px;
    }
        .product-item {
        width: auto;
        height: 200px;
    }
    .featured-products {
  

    padding-top: 10px;
    padding-right: 30px;
    padding-bottom: 30px;
    padding-left: 30px;
    text-align: center;
}
.categories {
   
    padding-top: 30px;
    padding-right: 30px;
    padding-bottom: 10px;
    padding-left: 30px;
    text-align: center;
    margin-top: 50px;
}
.featured-products h2 {
        font-size: 20px;
        margin-bottom: 20px;
    }
    .menu-btn {
  font-size: 26px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  display: none; /* hidden default */
  position: absolute;
  left: 15px;
}

/* ===== LOGO ===== */
.logo {
  margin: 0 auto;
  font-size: 20px;
  font-weight: bold;
}

/* ===== SIDEBAR ===== */
.sidebar {
  position: fixed;
  top: 0;
  left: -260px; /* hidden by default */
  width: 260px;
  height: 100vh;
  background-color: #111;
  color: black;
  transition: left 0.3s ease;
  z-index: 999;
  padding-top: 60px;
}

.sidebar.active {
  left: 0; /* show when active */
}

.sidebar a {
  display: block;
  padding: 14px 24px;
  text-decoration: none;
  color: white;
  font-size: 16px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar a:hover {
  background-color: #333;
}

/* ===== CLOSE BUTTON ===== */
.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
}

}
@media (max-width: 376px) { 
    
 
  /* Product Bootstrap */
.featured-products h2 {
    font-size: 20px;
    margin-bottom: 20px;
}
.product-list {
  display: grid;
    grid-template-columns: repeat(2, 1fr); /* 3 columns */
    grid-template-rows: repeat(3, auto);   /* 3 rows */
    gap: 20px;
    justify-content: center;
}

/* pre-order form Bootstrap */
.preorder-form .form-container {
    
    width: 320px;
}
.form-container h3{
  margin-bottom: 10px;
  font-size:12px;
}
.categories {
      margin-top: 100px;
}
  .logo img, .second-logo img {
    height: 40px;
    margin-left: 35px;
    margin-top: 40px;
  }
  
    .logo-container img, .account-logo, .order {
             margin-right: 190px;
        margin-top: 80px;
        height: 40px;
    }
    .categories h2{
        font-size: 20px;
        margin-top: 20px;
    }

    
        .menu-btn {
  font-size: 26px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  display: none; /* hidden default */
  position: absolute;
  left: 15px;
 margin-top: 35px;
}
.home-logo {
   margin-top: -30px;
        left: 20px;
    }
    .search-bar input {
  width:70%;
  font-size:12px;
   border-radius: 12px;
    }
            .search-bar button {
        font-size: 12px;
                margin-left: -10px;
    }
     .search-bar {
           width: 60%;
                margin-left: 10px;
      top: 20px
 }

}
@media (max-width:321px){
  .header{
    height: 1px;
  }
   .search-bar input {
        padding: 7px;
    }
      .logo-container {
        margin-bottom: 50px;
    }
    .header-icons{
       position: absolute;
  top: 60px;
  left: 100px;
    }
  .logo img, .second-logo img {
    height: 40px;
    margin-left: 35px;
    margin-top: 40px;
  }
  .more-logo {
        margin-top: 30px;
       height: 60px;
  }
    .logo-container img, .account-logo, .order {
             margin-right: 190px;
        margin-top: 80px;
        height: 40px;
    }
    .categories h2{
        font-size: 20px;
        margin-top: 10px;
    }

    
        .menu-btn {
  font-size: 26px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  display: none; /* hidden default */
  position: absolute;
  left: 15px;
 margin-top: 35px;
}
.home-logo {
   margin-top: -40px;
        left: 20px;
    }
    .search-bar input {
        font-size: 9px;
        margin-left: 50px;
        width: 50%;
        border-radius: 12px;
        }
            .search-bar button {
        font-size: 10px;
                margin-left: -10px;
    }
     .search-bar {
           width: 60%;
        margin-left:0px;
      top: 20px
 }

/* ===== LOGO ===== */
.logo {
  margin: 0 auto;
  font-size: 20px;
  font-weight: bold;
}


/* ===== CLOSE BUTTON ===== */
.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
}
     .order {
        top: 0px;
        right: -45px;
        position: absolute;
    }
     .logout{
position: relative;
        top: -20px;
        left:10px;
 }
  .user-options .scanner-logo{
position: relative;
        top: -20px;
        left: 0px;
 }
   .dropdown{
            position: absolute;
                top: 30px;
        left: 240px;
 }
    .dropdown-content {
                   position: absolute;
        min-width: 130px;
        box-shadow: 0px 8px 16px rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        margin-top: 50px;
        margin-right: 10px;
    }
 /*header*/
     .categories {
        margin-top: 130px;
    }
     .product-item {
        width: 130px;
        height: 190px;
        
    }
        .product-item button {
        padding: 3px;
    }
     .product-item img {
    
        height: 80px;
    }
    .product-item p {
        margin-top: 5px;
        color: #000000;
        font-size: 12px;
    }
        .preorder-form .form-container {
        width: 280px;
    }
        .categories {
    margin-top: 80px;
        }
* ===== SIDEBAR (vertical layout fix) ===== */
      }
@media (max-width: 2560px) {
  .menu-btn { display: block; }

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
}
@media (max-width: 2560px) {
  .menu-btn {
     display: block; 
    }
.logout-btn {
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

   <form class="search-bar" method="GET" action="">
  <input type="text" name="search" placeholder="Search for shoes..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
  <button type="submit">Search</button>
</form>



  
  <!-- Order Button -->
  <div class="dropdown">
       <button class="dropbtn">
    <img src="image/more.png" alt="More Options" class="more-logo">
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
  <a href="orderuser.php"><img src="image/orders.png" alt="Orders"><span>Orders</span></a>
  <a href="scan.php"><img src="image/logo4.png" alt="Scan"><span>Scan</span></a>
  <a href="profile.php"><img src="image/account.png" alt="Profile"><span>Profile</span></a>
  <div class="logout-container">
  <button class="logout-btn" id="logout-button">Logout</button>
</div>
</div>



<!-- Recommended for Your Feet Section -->
<section class="categories">

<a href="home.php">
    <img src="image/home1.png" alt="home-logo" class="home-logo">
  </a>
  <h2>Recommended for Your Feet</h2>
  <div class="category-list">
    <div class="category-item">
      <a href="bulky.php"> <!-- Add the link to the Bulky Feet page -->
        <img src="image/f1.png" alt="Bulky Feet"> <!-- Make image clickable by wrapping with anchor tag -->
        <p>Bulky Feet</p>
      </a>
    </div>

    <div class="category-item">
      <a href="slim.php"> <!-- Add the link to the Slim Feet page -->
        <img src="image/f2.png" alt="Slim Feet"> <!-- Make image clickable by wrapping with anchor tag -->
        <p>Slim Feet</p>
      </a>
    </div>
  </div>
</section>

<?php
// Connect to DB (Assuming $mysqli is already set)
$search = isset($_GET['search']) ? $mysqli->real_escape_string($_GET['search']) : '';
$displayed_products = array(); 
$products = array();

$query = !empty($search) 
    ? "SELECT * FROM inventory WHERE shoe_name LIKE '%$search%'" 
    : "SELECT * FROM inventory";

$result = $mysqli->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (!in_array($row['shoe_name'], $displayed_products)) {
            $products[] = $row;
            $displayed_products[] = $row['shoe_name'];
        }
    }
}
?>
<!-- === Featured Products Section === -->
<section class="featured-products">
  <h2>Featured Shoes</h2>
  <div class="product-list">
  <?php
    if (count($products) > 0) {
        foreach ($products as $product) {
            echo '
            <div class="product-item">
              <img src="' . htmlspecialchars($product['shoe_image']) . '" alt="' . htmlspecialchars($product['shoe_name']) . '">
              <h3>' . htmlspecialchars($product['shoe_name']) . '</h3>
              <p>SRP: ' . htmlspecialchars($product['price']) . '</p>
             
              <!-- Hidden Fields -->
              <span class="shoe-id" hidden>' . htmlspecialchars($product['id']) . '</span>
              <span class="shoe-type" hidden>' . htmlspecialchars($product['shoe_type']) . '</span>

              <button class="inquire-btn" 
                data-id="' . htmlspecialchars($product['id']) . '"
                data-name="' . htmlspecialchars($product['shoe_name']) . '"
                data-type="' . htmlspecialchars($product['shoe_type']) . '"
                data-price="' . htmlspecialchars($product['price']) . '" 
                data-image="' . htmlspecialchars($product['shoe_image']) . '">
                Inquire
              </button>
            </div>';
        }
    } else {
        echo '<p>No featured products available at the moment.</p>';
    }
  ?>
  </div>
</section>

<!-- === Pre-Order Form Modal === -->
<div class="preorder-form" id="preorder-form" style="display:none;">
  <div class="form-container">
    <h3>Pre-Order for <span id="product-name"></span></h3>
    <form id="preorder-form-content" method="POST" action="submit_inquiry.php">
      <div class="user-info">
        <div class="user-info-item">
          <span class="text">Name: <?php echo htmlspecialchars($user['username']); ?></span>
          <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
          <input type="hidden" name="province" value="<?php echo htmlspecialchars($user['province']); ?>">
          <input type="hidden" name="municipality" value="<?php echo htmlspecialchars($user['municipality']); ?>">
          <input type="hidden" name="barangay" value="<?php echo htmlspecialchars($user['barangay']); ?>">
          <input type="hidden" name="street" value="<?php echo htmlspecialchars($user['street']); ?>">
        </div>
        <div class="user-info-item">
          <span class="text">Email: <?php echo htmlspecialchars($user['email']); ?></span>
          <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
        </div>
        <div class="user-info-item">
          <span class="text">Contact Number: <?php echo htmlspecialchars($user['phone']); ?></span>
          <input type="hidden" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
        </div>

        <!-- Unit Price Display -->
        <div class="user-info-item">
          <span class="text">Unit Price: ₱<span id="unit-price-display">0.00</span></span>
        </div>

        <!-- Total Price Display -->
        <div class="user-info-item">
          <span class="text"><strong>Total Price: ₱<span id="total-price">0.00</span></strong></span>
        </div>

        <div class="user-info-item1">
          <textarea name="message" placeholder="Your Message"></textarea>
        </div>

        <!-- Shoe Size Dropdown Placeholder (will be replaced dynamically) -->
         
        <select name="size" required>
          <option value="" disabled selected>Select Shoe Size</option>
        </select>

        <!-- Quantity Control -->
        <div class="quantity-selector">
          <button type="button" class="qty-btn" onclick="decreaseQty()">−</button>
          <input type="number" id="quantity" name="quantity" value="1" min="1" readonly>
          <button type="button" class="qty-btn" onclick="increaseQty()">+</button>
        </div>
      </div>

      <!-- Hidden Fields -->
      <input type="hidden" id="shoe_id" name="shoe_id">
      <input type="hidden" id="shoe_type" name="shoe_type">
      <input type="hidden" id="shoe_name" name="shoe_name">
      <input type="hidden" id="price" name="price">
      <input type="hidden" id="shoe_image" name="shoe_image">
      <!--Single inquiry -->
      <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($_SERVER['HTTP_REFERER'] ?? 'home.php'); ?>">

      <button type="submit">Submit Pre-Order</button>
      <button type="button" id="add-to-cart-modal-btn">Add to Cart</button>
    </form>

    <button class="close-btn" onclick="closeForm()">Close</button>
  </div>
</div>

<!-- === Footer === -->
<footer class="footer">
  <p>&copy; DEFINE YOUR STYLE WALK WITH CONFIDENCE.</p>
</footer>

<!-- === JavaScript === -->
<script>



function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("active");
}



let maxStock = 1;

function increaseQty() {
  const qtyInput = document.getElementById('quantity');
  const currentQty = parseInt(qtyInput.value);
  if (currentQty < maxStock) {
    qtyInput.value = currentQty + 1;
    updateTotalPrice();
  }
}

function decreaseQty() {
  const qtyInput = document.getElementById('quantity');
  const currentQty = parseInt(qtyInput.value);
  if (currentQty > 1) {
    qtyInput.value = currentQty - 1;
    updateTotalPrice();
  }
}

function updateTotalPrice() {
  const price = parseFloat(document.getElementById('price').value) || 0;
  const quantity = parseInt(document.getElementById('quantity').value);
  const total = price * quantity;
  document.getElementById('total-price').innerText = total.toFixed(2);
  document.getElementById('unit-price-display').innerText = price.toFixed(2);
}

document.addEventListener("DOMContentLoaded", () => {
  document.getElementById('add-to-cart-modal-btn').addEventListener('click', () => {
    const shoeId = document.getElementById('shoe_id').value;
    const shoeName = document.getElementById('shoe_name').value;
    const shoeType = document.getElementById('shoe_type').value;
    const sizeSelect = document.querySelector('#preorder-form select[name="size"]');
    const quantity = document.getElementById('quantity').value;
    const price = document.getElementById('price').value;
    const shoeImage = document.getElementById('shoe_image').value;

    if (!sizeSelect || !sizeSelect.value) {
      alert("Please select a size before adding to cart.");
      return;
    }

    const size = sizeSelect.value;

    const formData = new FormData();
    formData.append('shoe_id', shoeId);
    formData.append('shoe_name', shoeName);
    formData.append('shoe_type', shoeType);
    formData.append('size', size);
    formData.append('quantity', quantity);
    formData.append('price', price);
    formData.append('shoe_image', shoeImage);

    fetch('addtocart.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.text())
    .then(responseText => {
      alert(`${shoeName} (Size: ${size}, Qty: ${quantity}) has been added to your cart.`);
      document.getElementById('preorder-form').style.display = 'none';
    })
    .catch(error => {
      console.error('Error adding to cart:', error);
      alert('Failed to add to cart. Please try again.');
    });
  });

  // Inquire Button Click Handler
  const inquireButtons = document.querySelectorAll('.inquire-btn');
  const preorderForm = document.getElementById('preorder-form');
  const productNameSpan = document.getElementById('product-name');

  inquireButtons.forEach(button => {
    button.addEventListener('click', async () => {
      const shoeId = button.dataset.id;
      const shoeName = button.dataset.name;
      const shoeType = button.dataset.type;
      const price = button.dataset.price;
      const image = button.dataset.image;

      productNameSpan.textContent = shoeName;

      try {
        const response = await fetch(`get_sizes.php?shoe_id=${shoeId}&shoename=${encodeURIComponent(shoeName)}&shoetype=${encodeURIComponent(shoeType)}`);
        if (!response.ok) throw new Error("Failed to fetch sizes.");
        const sizesHTML = await response.text();

        const form = document.querySelector('#preorder-form form');
        const existingDropdown = form.querySelector('select[name="size"]');
        if (existingDropdown) existingDropdown.remove();

        const messageBox = form.querySelector('textarea');
        if (messageBox) {
          messageBox.insertAdjacentHTML('beforebegin', sizesHTML);
        }

        // Set form values
        form.querySelector('#shoe_id').value = shoeId;
        form.querySelector('#shoe_type').value = shoeType;
        form.querySelector('#shoe_name').value = shoeName;
        form.querySelector('#price').value = price;
        form.querySelector('#shoe_image').value = image;

        document.getElementById('quantity').value = 1;
        maxStock = 1; // reset
        updateTotalPrice();
        preorderForm.style.display = 'flex';

        // Add size change handler to update max stock & reset qty
        const sizeSelect = form.querySelector('select[name="size"]');
        sizeSelect.addEventListener('change', function () {
          const selectedOption = sizeSelect.selectedOptions[0];
          maxStock = parseInt(selectedOption.dataset.stock || "1");
          document.getElementById("quantity").value = 1;
          updateTotalPrice();
        });

      } catch (error) {
        alert('Error loading sizes. Please try again.');
        console.error(error);
      }
    });
  });
});

function closeForm() {
  document.getElementById('preorder-form').style.display = 'none';
}
</script>



</body>
</html>
