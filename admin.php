<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" href="image/logo1.png" type="image/png">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Shoe Store</title>
  <style>
 
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      background-image: url('image/hdb.png');
      background-size: cover;
      background-repeat: no-repeat;
      background-attachment: fixed;
      background-position: center;
      color: #000000;
      display: flex;
      flex-direction: column;
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
    }

    .logo-container {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .user-options {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 15px;
    }

    .home-logo img {
      height: 30px;
      width: 30px;
      margin-right: 10px;
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

    .user-options button:hover {
      background-color: #ffffff;
    }

    .logo img,
    .second-logo img {
      height: 50px;
      box-shadow: 0 2px 10px rgb(255, 255, 255);
    }

 
    .inventory-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 70px 130px;
      gap: 20px;
      background: #f0f0f0;
      flex-grow: 1;
    }

    .inventory1 {
      padding: 0;
    }

    .img-button,
    .img-button1 {
      display: flex;
      flex-direction: column;
      align-items: center;
      border: none;
      background-color: transparent;
      cursor: pointer;
    }

    .img-button img,
    .img-button1 img {
      width: 400px;
      height: 400px;
      object-fit: cover;
      transition: transform 0.3s ease;
      border: 5px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
      border-radius: 10px;
    }

    .img-button span,
    .img-button1 span {
      margin-top: 10px;
      font-size: 18px;
      color: #333;
      font-weight: bold;
    }

    @keyframes popIn {
      0% {
        transform: scale(0.8);
        opacity: 0;
      }
      100% {
        transform: scale(1);
        opacity: 1;
      }
    }

   
    .img-button img,
    .img-button1 img {
      animation: popIn 0.8s ease-out;
    }

   
    .img-button:hover img,
    .img-button1:hover img {
      transform: scale(1.05);
    }

   
    .center-image {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 20px;
    }

    .center-image img {
      width: 300px; 
      height: auto;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
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
        <a href="admin.php">
          <img src="image/home.png" alt="Home Logo" />
        </a>
      </div>
      <button id="logout-button">Logout</button>
     
    </div>
  </header>

 
  <div class="inventory-container">
    <div class="inventory">
      <a href="inventory.php" class="img-button">
        <img src="image/I.png" alt="Inventory" />
        <span>Inventory</span>
      </a>
    </div>

 
    <div class="center-image">
      <img src="image/all.png" alt="Center Image" />
    </div>

    <div class="inventory1">
      <a href="order.php" class="img-button1">
        <img src="image/O.png" alt="Orders" />
        <span>Orders</span>
      </a>
    </div>
  </div>
  <script>
    
    document.getElementById('logout-button').addEventListener('click', function () {
      alert("You have been logged out.");
      window.location.href = "login.php";
    });
  </script>
  
</body>
</html>
