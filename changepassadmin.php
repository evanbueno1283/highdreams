<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" href="image/logo1.png" type="image/png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      height: 100vh;
      background-image: url('image/logo3.jpeg');  
      background-size: cover;  
      background-position: center center;  
      background-repeat: no-repeat;
      background-attachment: fixed;  
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
    }

    .container {
      background-color: rgba(255, 255, 255, 0.9);
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      width: 100%;
      max-width: 400px;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }

    input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    button {
      width: 100%;
      padding: 12px;
      background-color: black;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
    }

    button:hover {
      background-color: #333;
    }

    .message {
      margin-top: 15px;
      text-align: center;
    }
  </style>
</head>
<body>

  <div class="container">
    <h2>Change Password</h2>
    <form id="changePasswordForm">
      <input type="password" id="oldPassword" placeholder="Enter current password" required>
      <input type="password" id="newPassword" placeholder="Enter new password" required>
      <input type="password" id="confirmPassword" placeholder="Confirm new password" required>
      <button type="submit">Change Password</button>
    </form>
    <div class="message" id="message"></div>
  </div>

  <script>
    
    let currentPassword = localStorage.getItem("adminPassword") || "HIGHDREAMS";
  
    document.getElementById("changePasswordForm").addEventListener("submit", function(e) {
      e.preventDefault();
  
      const oldPass = document.getElementById("oldPassword").value;
      const newPass = document.getElementById("newPassword").value;
      const confirmPass = document.getElementById("confirmPassword").value;
      const message = document.getElementById("message");
  
      if (oldPass !== currentPassword) {
        message.style.color = "red";
        message.textContent = "Incorrect current password!";
        return;
      }
  
      if (newPass !== confirmPass) {
        message.style.color = "red";
        message.textContent = "New passwords do not match!";
        return;
      }
  
      localStorage.setItem("adminPassword", newPass);
      message.style.color = "green";
      message.textContent = "Password changed successfully! Redirecting...";
  
      setTimeout(() => {
        window.location.href = "adminlogin.php";
      }, 2000);
    });
  </script>
  

</body>
</html>
