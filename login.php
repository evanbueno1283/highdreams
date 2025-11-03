<?php
session_start(); 
$host = "localhost";
$dbname = "fitscan_database";
$user = "root";
$pass = "";

$loginMessage = ""; 

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = trim(strtolower($_POST['username']));
  $password = $_POST['password'];

  
  if ($username === 'highdreams' && $password === 'HIGHDREAMS') {
      $_SESSION['admin'] = true;
      header("Location: admin.php"); 
      exit();
  }

  $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
        
          $_SESSION['user'] = [
              'id' => $user['id'],
              'username' => $user['username'],
              'email' => $user['email'],
              'phone' => $user['phone'],
              'gender' => $user['gender'],
              'province' => $user['province'],
              'municipality' => $user['municipality'],
              'barangay' => $user['barangay'],
              'street' => $user['street']
          ];
      
          $loginMessage = "<span class='success'>Login successful! Redirecting...</span>";
          header("refresh:1;url=home.php"); 
          exit();
        } else {
            $loginMessage = "<span class='error'>Incorrect password.</span>";
        }
    } else {
        $loginMessage = "<span class='error'>User not found.</span>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" href="image/logo1.png" type="image/png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Interface</title>
  <style>
    *{
      margin:0;
      padding:0;
       box-sizing: border-box;
    }
    
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


body::after {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.3); 
  z-index: -1; 
}


.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  width: 60%; 
  opacity: 0;
  animation: fadeInLoginContainer 2s forwards; 
}


@keyframes fadeInLoginContainer {
  0% {
    opacity: 0;
    transform: scale(0.8);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}


.side-panel {
  width: 35%;  
  height: 100vh; 
  background-image: url('C:/Users/Vanzxc/Desktop/fitscan/image/logo1.png'); 
  background-size: contain;  
  background-position: center center; 
  background-repeat: no-repeat; 
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  color: white;
  padding: 20px;
  border-radius: 10px 0 0 10px; 
  margin-right: 50px; 
  opacity: 0;
  animation: fadeInLogo 2s forwards; 
  transition: transform 0.3s ease-in-out; 
}


@keyframes fadeInLogo {
  0% {
    opacity: 0;
    transform: scale(0.8);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}


.side-panel:hover {
  transform: translateX(10px) translateY(-10px); 
}

.login-form {
  width: 60%;  
  padding: 30px;  
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  background-color: rgba(255, 255, 255, 0.8); 
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}


.login-form h2 {
  margin-bottom: 20px; 
  color: #333;
}


.input-field {
  width: 100%;
  padding: 10px;
  margin: 10px 0;
  border: 1px solid rgba(0, 0, 0, 0.3);
  border-radius: 5px;
  background-color: rgba(255, 255, 255, 0.9);
  font-size: 16px;
  color: #333;
}

.input-field::placeholder {
  color: #aaa;
}


.btn {
  width: 100%;
  padding: 12px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 16px;
}

.btn:hover {
  background-color: #45a049;
}


.show-password-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  margin-top: 10px;
}


.links {
  text-align: center;
  margin-top: 10px;
}

.links a {
  color: #4CAF50;
  text-decoration: none;
  margin: 0 10px;
}

.links a:hover {
  text-decoration: underline;
}
@media (max-width: 480px) {
  .side-panel {
    height: 30vh; 
    margin-right: 0;  
  }

  .login-form {
    width: 100%;
    padding: 10px;
  }
}

    .input-field {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid rgba(0, 0, 0, 0.3);
      border-radius: 5px;
      background-color: rgba(255, 255, 255, 0.9);
      font-size: 16px;
      color: #333;
    }

    .input-field::placeholder {
      color: #aaa;
    }

    .btn {
      width: 100%;
      padding: 12px;
      background-color: #000000;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
    }

    .btn:hover {
      background-color: #000000;
    }

    .show-password-container {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      margin-top: 10px;
    }

    .show-password {
      display: flex;
      align-items: center;
    }

    .links {
      text-align: center;
      margin-top: 10px;
    }

    .links a {
      color: #000000;
      text-decoration: none;
      margin: 0 10px;
    }

    .links a:hover {
      text-decoration: underline;
    }
    @media (max-width: 480px) {
      .side-panel {
        height: 30vh; 
      }

      .login-form {
        
        padding: 10px;
      }
    }

    .login-message {
  margin-bottom: 15px;
  font-size: 16px;
  text-align: center;
  padding: 10px 15px;
  border-radius: 5px;
}

.login-message .success {
  color: #155724;
  background-color: #d4edda;
  border: 1px solid #c3e6cb;
}

.login-message .error {
  color: #721c24;
  background-color: #f8d7da;
  border: 1px solid #f5c6cb;
}


@media (max-width: 658px) {
  .login-container {
    width: 90%; 
  }

  .login-form {
   
    padding: 20px;  
  }

  .input-field {
    width: 100%; 
    padding: 10px;
    margin: 8px 0; 
    border: 1px solid rgba(0, 0, 0, 0.3);
    border-radius: 5px;
    background-color: rgba(255, 255, 255, 0.9);
    font-size: 16px;
    color: #333;
    box-sizing: border-box; 
  }

  .side-panel {
    width: 100%; 
    height: 40vh; 
    background-size: cover;
    margin-right: 0; 
  }
}
.side-panel {
  width: 35%;  
  height: 100vh; 
  background-image: url('image/logo1.png'); 
  background-size: contain;  
  background-position: center center; 
  background-repeat: no-repeat; 
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  color: white;
  padding: 20px;
  border-radius: 10px 0 0 10px; 
  margin-right: 50px;
  opacity: 0;
  animation: fadeInLogo 2s forwards; 
  transition: transform 0.3s ease-in-out; 
}
@media (max-width: 1025px) {
 .side-panel{
    position:absolute;
    margin-bottom:500px;
    left:210px;
    height:160px;
  }
      .login-form {
        width: 80%;
      
        padding-top:30px;
        padding-bottom:30px;
        padding-left:5px;
        padding-right:5px;
        margin-left:22px;
        margin-top:90px;
    }
    .input-field {
    width: 100%;
    padding: 15px;
    margin: 10px 0;
    border: 1px solid rgba(0, 0, 0, 0.3);
    border-radius: 5px;
    background-color: rgba(255, 255, 255, 0.9);
    font-size: 16px;
    color: #333;
    box-sizing: border-box; 
  }
}
@media (max-width: 913px) {
  .side-panel{
    position:absolute;
    margin-bottom:450px;
    left:190px;
    height:170px;
  }
      .login-form {
        width: 80%;
      
        padding-top:15px;
        padding-bottom:15px;
        padding-left:25px;
        padding-right:25px;
        margin-left:22px;
        margin-top:90px;
    }
    .input-field {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid rgba(0, 0, 0, 0.3);
    border-radius: 5px;
    background-color: rgba(255, 255, 255, 0.9);
    font-size: 16px;
    color: #333;
    box-sizing: border-box; 
  }
}
@media (max-width: 853px) {
 .side-panel{
    position:absolute;
    margin-bottom:420px;
    left:170px;
    height:140px;
  }
      .login-form {
        width: 80%;
      
        padding-top:15px;
        padding-bottom:15px;
        padding-left:25px;
        padding-right:25px;
        margin-left:22px;
        margin-top:90px;
    }
    .input-field {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid rgba(0, 0, 0, 0.3);
    border-radius: 5px;
    background-color: rgba(255, 255, 255, 0.9);
    font-size: 16px;
    color: #333;
    box-sizing: border-box; 
  }
}
@media (max-width: 821px) {
 .side-panel{
    position:absolute;
    margin-bottom:430px;
    left:170px;
    height:140px;
  }
      .login-form {
        width: 80%;
      
        padding-top:15px;
        padding-bottom:15px;
        padding-left:25px;
        padding-right:25px;
        margin-left:22px;
        margin-top:90px;
    }
    .input-field {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid rgba(0, 0, 0, 0.3);
    border-radius: 5px;
    background-color: rgba(255, 255, 255, 0.9);
    font-size: 16px;
    color: #333;
    box-sizing: border-box; 
  }
}
@media (max-width: 769px) {
  .login-container {
   
    width: 55%;  
    padding: 10px; 
  }
  .input-field {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid rgba(0, 0, 0, 0.3);
    border-radius: 5px;
    background-color: rgba(255, 255, 255, 0.9);
    font-size: 16px;
    color: #333;
    box-sizing: border-box; 
  }

  .btn {
    width: 100%;
    padding: 12px;
    background-color: #000000;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
  }
 .side-panel{
    position:absolute;
    margin-bottom:430px;
    left:140px;
    height:140px;
  }
      .login-form {
        width: 80%;
      
        padding-top:15px;
        padding-bottom:15px;
        padding-left:20px;
        padding-right:25px;
        margin-left:22px;
        margin-top:90px;
    }
}
@media (max-width: 541px) {
 .side-panel{
    position:absolute;
    margin-bottom:400px;
    left:180px;
    height:130px;
  }
   .login-container {
    width: 100%;  
  }
      .login-form {
        width: 80%;
        padding: 15px;
        margin-left:0px;
        margin-top:90px;
    }
}
@media (max-width: 480px) {
 

  .login-form {
    width: 100%;
    padding: 10px;
  }

  .login-container {
   
    width: 100%;  
  }
}
@media (max-width: 480px) {
  .side-panel {
    height: 30vh; 
    width: 35%;
    background-position: center center; 
    background-repeat: no-repeat;
  }

  .login-form {
    width: 100%;
    padding: 10px;
  }

  .login-container {
    width: 100%;  
  }
}
@media (max-width: 431px) {
 .side-panel{
    position:absolute;
    margin-bottom:400px;
    left:140px;
    height:130px;
  }
      .login-form {
        width: 80%;
        padding: 15px;
        margin-left:0px;
        margin-top:90px;
    }
}
@media (max-width: 426px) {
  .side-panel{
    position:absolute;
    top:30px;
    left:120px;
    height:60px;
  }
      .login-form {
        width: 80%;
        padding: 15px;
        margin-left:25px;
        margin-top:90px;
    }
}
@media (max-width: 415px) {
   .side-panel{
    position:absolute;
    margin-top:150px;
    left:135px;
    height:120px;
  }
      .login-form {
        width: 80%;
        padding: 15px;
        margin-left:0px;
        margin-top:90px;
    }
}
@media (max-width: 413px) {
 .side-panel{
    position:absolute;
    margin-top:160px;
    left:135px;
    height:130px;
  }
      .login-form {
        width: 80%;
        padding: 15px;
        margin-left:0px;
        margin-top:90px;
    }
}
@media (max-width: 398px) {
  .side-panel {
    margin-right: 5px;
  }
}
@media (max-width: 394px) {
  .side-panel{
    position:absolute;
    margin-top:140px;
    left:125px;
    height:120px;
  }
      .login-form {
        width: 80%;
        padding: 15px;
        margin-left:0px;
        margin-top:90px;
    }
}
@media (max-width: 391px) {
  .side-panel{
    position:absolute;
    margin-top:140px;
    left:125px;
    height:120px;
  }
      .login-form {
        width: 80%;
        padding: 15px;
        margin-left:0px;
        margin-top:90px;
    }
}
@media (max-width: 376px) {
  .side-panel{
    position:absolute;
    margin-top:70px;
    left:120px;
    height:100px;
  }
      .login-form {
        width: 80%;
        padding: 15px;
        margin-left:0px;
        margin-top:90px;
    }
}
@media (max-width: 361px) {
 .side-panel{
    position:absolute;
    margin-top:80px;
    left:120px;
    height:130px;
  }
      .login-form {
        width: 80%;
        padding: 15px;
        margin-left:0px;
        margin-top:90px;
    }
}
@media (max-width: 345px) {
  .side-panel{
    position:absolute;
 margin-top:150px;
    left:115px;
    height:120px;
  }
      .login-form {
        width: 80%;
        padding: 15px;
        margin-left:0px;
        margin-top:90px;
    }
    .login-form h2{
      margin:5px;
    }
    .links a{
     font-size:18px;

    }
}
@media (max-width: 321px) {
  .side-panel{
    position:absolute;
    top:30px;
    left:88px;
    height:60px;
  }
      .login-form {
        width: 80%;
        padding: 15px;
        margin-left:16px;
        margin-top:90px;
    }
    .login-form h2{
      margin:5px;
    }
}
  </style>
</head>
<body>

 <div class="login-container">
  <div class="side-panel">
    <!-- Optional content for side panel -->
  </div>

  <div class="login-form">
    <h2>Login</h2>

    <?php if (!empty($loginMessage)) : ?>
      <div class="login-message">
        <?php echo $loginMessage; ?>
      </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
      <input type="text" class="input-field" name="username" placeholder="Username" required>

      <input type="password" class="input-field" name="password" placeholder="Password" required id="password">

      <!-- Show Password Checkbox -->
      <div style="margin: 10px 0;">
        <label>
          <input type="checkbox" id="show-password"> Show Password
        </label>
      </div>

      <button type="submit" class="btn">Login</button>
    </form>

    <div class="links">
      <a href="forget.php" class="forgot">Forgot Password?</a>
      <a href="register.php" class="regis">Register</a>
    </div>
  </div>
</div>

<script>
  const showPasswordCheckbox = document.getElementById('show-password');
  const passwordField = document.getElementById('password');

  showPasswordCheckbox.addEventListener('change', function () {
    passwordField.type = this.checked ? 'text' : 'password';
  });
</script>


</body>
</html>