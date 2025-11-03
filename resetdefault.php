<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" href="image/logo1.png" type="image/png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password</title>
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
      background-color: rgb(255, 255, 255);
      color: rgb(255, 255, 255);
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
    }

    button:hover {
      background-color: #ffffff;
    }

    .reset-button {
      background-color: transparent;
      border: 1px solid #ffffff;
      margin-top: 10px;
      color: rgb(0, 0, 0);
    }

    .reset-button:hover {
      background-color: #ffffff;
    }

    .message {
      margin-top: 15px;
      text-align: center;
    }
  </style>
</head>
<body>

  <div class="container">
    <h2>Reset Password</h2>
    
   
    <input type="text" id="defaultCodeInput" placeholder="Enter your default code" required>

  
    <button type="button" class="reset-button" id="resetPasswordBtn">
      Reset to Default Password
    </button>

    
    <div class="message" id="message"></div>
  </div>

  <script>
   
    const defaultCode = "HIGHDREAMS2025";
    const newDefaultPassword = "HIGHDREAMS";

    
    document.getElementById("resetPasswordBtn").addEventListener("click", function() {
      const enteredCode = document.getElementById("defaultCodeInput").value;
      const message = document.getElementById("message");

      if (enteredCode === defaultCode) {
        
        localStorage.setItem("adminPassword", newDefaultPassword);
        message.style.color = "green";
        message.textContent = "Password has been reset to default (HIGHDREAMS). Redirecting to login...";

        
        setTimeout(function() {
          window.location.href = "adminlogin.php";
        }, 2000);
      } else {
        
        message.style.color = "red";
        message.textContent = "Incorrect code entered. Please try again.";
      }
    });
  </script>

</body>
</html>
