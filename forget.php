<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$step = 'email';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli("highdreams.alwaysdata.net", "439165", "Skyworth23", "highdreams_1");
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

    if (isset($_POST['email']) && !isset($_POST['otp']) && !isset($_POST['new_password'])) {
       
        $email = $_POST['email'];
        $otp = rand(100000, 999999);

        $checkUser = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $checkUser->bind_param("s", $email);
        $checkUser->execute();
        $result = $checkUser->get_result();

        if ($result->num_rows === 0) {
            echo "<script>alert('Email not found.');</script>";
        } else {
            $update = $conn->prepare("UPDATE users SET code = ? WHERE email = ?");
            $update->bind_param("ss", $otp, $email);
            $update->execute();

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'highdreams552@gmail.com';
                $mail->Password = 'gmfjqsmzlfgrmbwc';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('highdreams552@gmail.com', 'HIGH DREAMS');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Your OTP Code for Password Reset';
                $mail->Body = "Here is your OTP code: <strong>$otp</strong>";

                $mail->send();
                echo "<script>alert('OTP sent to your email!');</script>";
                $step = 'otp';
            } catch (Exception $e) {
                echo "<script>alert('Mailer Error: {$mail->ErrorInfo}');</script>";
            }
        }
    } elseif (isset($_POST['otp'], $_POST['email']) && !isset($_POST['new_password'])) {
   
        $email = $_POST['email'];
        $otp = $_POST['otp'];

        $verify = $conn->prepare("SELECT * FROM users WHERE email = ? AND code = ?");
        $verify->bind_param("ss", $email, $otp);
        $verify->execute();
        $result = $verify->get_result();

        if ($result->num_rows === 1) {
            $step = 'reset';
        } else {
            echo "<script>alert('Invalid OTP');</script>";
            $step = 'otp';
        }
    } elseif (isset($_POST['new_password'], $_POST['email'])) {
       
        $email = $_POST['email'];
        $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

        $updatePassword = $conn->prepare("UPDATE users SET password = ?, code = NULL WHERE email = ?");
        $updatePassword->bind_param("ss", $newPassword, $email);
        if ($updatePassword->execute()) {
            echo "<script>alert('Password updated successfully! Redirecting to login...'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Failed to update password.');</script>";
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" href="image/logo1.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta charset="UTF-8">
  <title>Forgot Password</title>
  <style>
     *{
      margin:0;
      padding:0;
       box-sizing: border-box;
    }
    body {
      font-family: sans-serif;
      margin: 0;
      padding: 0;
      background: url('image/logo3.jpeg') no-repeat center center fixed;
      background-size: cover;
    }

    .form-container {
      display: flex;
      justify-content: center; 
      align-items: center; 
      margin-top: 100px;
      
    }

    .back-button img {
  width: 100px;   /* adjust size — smaller */
  height: 100px;
  cursor: pointer;
 
}
h2{
  text-align: center;
  margin-top: -30px;
}

    .form-box {
      background: rgba(255, 255, 255, 0.85);
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
      margin-top: 100px; 
    
    }

    input[type="email"], input[type="text"], input[type="submit"] {
      width: 100%;
      padding: 12px;
      margin-top: 12px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }

    input[type="submit"] {
      background-color: #000;
      color: white;
      border: none;
    }

    .header {
      background-color: #000;
      padding: 20px 20px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      display: flex;
      justify-content: space-between;
      align-items: center;
      
    }

    .logo-container {
      display: flex;
      align-items: center;
    }

    .logo img, .second-logo img {
      height: 50px;
      margin-right: 15px;
    }
 @media (max-width: 1440px) {
  .form-container {
    padding: 30px;
   
  }
  .form-box{
    margin-top:0px;
            width: 300px;
  }

  .back-button img{
    width: 60px;
    height: 60px;
    margin-left: 100px;
    margin-top: -100px;
  }
    .back-button {
    width: 50px;
    height: 50px;
    margin-right: 500px;
    margin-top: -150px;
  }
  h2 {
    font-size: 25px;
    margin-top:-120px;
  }
  input[type="email"],
  input[type="text"],
  input[type="submit"] {
    width: 100%;
    padding: 10px;              /* medyo lumiit para magkasya */
    margin-top: 10px;           /* spacing sa pagitan ng inputs */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    box-sizing: border-box;     /* para di lumampas sa form-box width */
  }
}

@media (max-width: 1281px) {
  .header{
    height:80px;
  }
    .form-box {
       padding:20px;
       width:50%;
    }
    h2 {
        font-size: 25px;
        margin-top: -140px;
        margin-bottom:20px;
        
    }
    label{
      font-size:20px;
    }
    .back-button img {
        width: 50px;
        height: 50px;
        margin-left: -25px;
        margin-top: -40px;
    }
     .form-container {
    padding: 20px;
   
  }
   .form-box{
    margin-top:70px;
  }
  input[type="email"],
  input[type="text"],
  input[type="submit"] {
    width: 100%;
    padding: 10px;              /* medyo lumiit para magkasya */
    margin-top: 10px;           /* spacing sa pagitan ng inputs */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 20px;
    box-sizing: border-box;     /* para di lumampas sa form-box width */
  }
}

@media (max-width: 1025px) {
  .header{
    height:80px;
  }
    .form-box {
       padding:20px;
       width:50%;
    }
    h2 {
        font-size: 25px;
        margin-top: -140px;
        margin-bottom:20px;
        
    }
    label{
      font-size:20px;
    }
    .back-button img {
        width: 50px;
        height: 50px;
        margin-left: 45px;
        margin-top: -40px;
    }
     .form-container {
    padding: 20px;
   
  }
   .form-box{
    margin-top:70px;
  }
  input[type="email"],
  input[type="text"],
  input[type="submit"] {
    width: 100%;
    padding: 10px;              /* medyo lumiit para magkasya */
    margin-top: 10px;           /* spacing sa pagitan ng inputs */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 20px;
    box-sizing: border-box;     /* para di lumampas sa form-box width */
  }
}

@media (max-width: 912px) {
  .header{
    height:100px;
  }
    .form-box {
       padding:20px;
       width:50%;
    }
    h2 {
        font-size: 25px;
        margin-top: -140px;
        margin-bottom:20px;
        
    }
    label{
      font-size:20px;
    }
    .back-button img {
        width: 50px;
        height: 50px;
        margin-left: 70px;
        margin-top: -40px;
    }
     .form-container {
    padding: 20px;
   
  }
   .form-box{
    margin-top:200px;
  }
  input[type="email"],
  input[type="text"],
  input[type="submit"] {
    width: 100%;
    padding: 10px;              /* medyo lumiit para magkasya */
    margin-top: 10px;           /* spacing sa pagitan ng inputs */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 20px;
    box-sizing: border-box;     /* para di lumampas sa form-box width */
  }
}


@media (max-width: 913px) {
  .header{
    height:100px;
  }
    .form-box {
       padding:20px;
       width:50%;
    }
    h2 {
        font-size: 25px;
        margin-top: -140px;
        margin-bottom:20px;
        
    }
    label{
      font-size:20px;
    }
    .back-button img {
        width: 50px;
        height: 50px;
        margin-left: 70px;
        margin-top: -40px;
    }
     .form-container {
    padding: 20px;
   
  }
   .form-box{
    margin-top:200px;
  }
  input[type="email"],
  input[type="text"],
  input[type="submit"] {
    width: 100%;
    padding: 10px;              /* medyo lumiit para magkasya */
    margin-top: 10px;           /* spacing sa pagitan ng inputs */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 20px;
    box-sizing: border-box;     /* para di lumampas sa form-box width */
  }
}

@media (max-width: 854px) {
  .header{
    height:100px;
  }
    .form-box {
       padding:20px;
       width:50%;
    }
    h2 {
        font-size: 25px;
        margin-top: -140px;
        margin-bottom:20px;
        
    }
    label{
      font-size:20px;
    }
    .back-button img {
        width: 50px;
        height: 50px;
        margin-left: 90px;
        margin-top: -40px;
    }
     .form-container {
    padding: 20px;
   
  }
   .form-box{
    margin-top:200px;
  }
  input[type="email"],
  input[type="text"],
  input[type="submit"] {
    width: 100%;
    padding: 10px;              /* medyo lumiit para magkasya */
    margin-top: 10px;           /* spacing sa pagitan ng inputs */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 20px;
    box-sizing: border-box;     /* para di lumampas sa form-box width */
  }
}

@media (max-width: 821px) {
  .header{
    height:100px;
  }
    .form-box {
       padding:20px;
       width:60%;
    }
    h2 {
        font-size: 25px;
        margin-top: -140px;
        margin-bottom:20px;
        
    }
    label{
      font-size:20px;
    }
    .back-button img {
        width: 50px;
        height: 50px;
        margin-left: 55px;
        margin-top: -30px;
    }
     .form-container {
    padding: 20px;
   
  }
   .form-box{
    margin-top:200px;
  }
  input[type="email"],
  input[type="text"],
  input[type="submit"] {
    width: 100%;
    padding: 10px;              /* medyo lumiit para magkasya */
    margin-top: 10px;           /* spacing sa pagitan ng inputs */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 20px;
    box-sizing: border-box;     /* para di lumampas sa form-box width */
  }
}

/* 📱 TABLET PORTRAIT: 768px and below */
@media (max-width: 769px) {
  .header{
    height:70px;
  }
    .form-box {
       padding:20px;
       width:60%;
    }
    h2 {
        font-size: 25px;
        margin-top: -140px;
        margin-bottom:20px;
        
    }
    label{
      font-size:20px;
    }
    .back-button img {
        width: 50px;
        height: 50px;
        margin-left: 70px;
        margin-top: -30px;
    }
     .form-container {
    padding: 20px;
   
  }
   .form-box{
    margin-top:200px;
  }
  input[type="email"],
  input[type="text"],
  input[type="submit"] {
    width: 100%;
    padding: 10px;              /* medyo lumiit para magkasya */
    margin-top: 10px;           /* spacing sa pagitan ng inputs */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 20px;
    box-sizing: border-box;     /* para di lumampas sa form-box width */
  }
}

@media (max-width: 541px) {
  .header{
    height:100px;
  }
    .form-box {
       padding:20px;
       width:80%;
    }
    h2 {
        font-size: 25px;
        margin-top: -140px;
        margin-bottom:20px;
        
    }
    label{
      font-size:20px;
    }
    .back-button img {
        width: 50px;
        height: 50px;
        margin-left: 90px;
        margin-top: -30px;
    }
     .form-container {
    padding: 20px;
   
  }
   .form-box{
    margin-top:100px;
  }
  input[type="email"],
  input[type="text"],
  input[type="submit"] {
    width: 100%;
    padding: 10px;              /* medyo lumiit para magkasya */
    margin-top: 10px;           /* spacing sa pagitan ng inputs */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 20px;
    box-sizing: border-box;     /* para di lumampas sa form-box width */
  }
}

@media (max-width: 431px) {
  .header{
    height:70px;
  }
    .form-box {
       padding:10px;
       margin-right:40px;
    }
    h2 {
        font-size: 20px;
        margin-top: -140px;
        margin-bottom:20px;
        
    }
    .back-button img {
        width: 40px;
        height: 40px;
        margin-left: 130px;
        margin-top: -30px;
    }
     .form-container {
    padding: 20px;
   
  }
   .form-box{
    margin-top:50px;
  }
  input[type="email"],
  input[type="text"],
  input[type="submit"] {
    width: 100%;
    padding: 10px;              /* medyo lumiit para magkasya */
    margin-top: 10px;           /* spacing sa pagitan ng inputs */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    box-sizing: border-box;     /* para di lumampas sa form-box width */
  }
}

/* 📱 MOBILE MEDIUM: 480px and below */
@media (max-width: 425px) {
 .form-container {
    padding: 20px;
   
  }
   .form-box{
    margin-top:-50px;
  }

  .back-button img{
    width: 60px;
    height: 60px;
    margin-right: 300px;
    margin-top: -350px;
  }
    .back-button {
    width: 50px;
    height: 50px;
    margin-right: 500px;
    margin-top: -170px;
  }
  h2 {
    font-size: 25px;
    margin-top:-150px;
  }
  input[type="email"],
  input[type="text"],
  input[type="submit"] {
    width: 100%;
    padding: 10px;              /* medyo lumiit para magkasya */
    margin-top: 10px;           /* spacing sa pagitan ng inputs */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    box-sizing: border-box;     /* para di lumampas sa form-box width */
  }
}
@media (max-width: 415px) {
  .header{
    height:70px;
  }
    .form-box {
       padding:10px;
       margin-right:20px;
    }
    h2 {
        font-size: 20px;
        margin-top: -140px;
        margin-bottom:20px;
        
    }
    label{
      font-size:14px;
    }
    .back-button img {
        width: 40px;
        height: 40px;
        margin-left: 145px;
        margin-top: -20px;
    }
     .form-container {
    padding: 20px;
    width:95%;
  }
   .form-box{
    margin-top:50px;
  }
  input[type="email"],
  input[type="text"],
  input[type="submit"] {
    width: 100%;
    padding: 10px;              /* medyo lumiit para magkasya */
    margin-top: 10px;           /* spacing sa pagitan ng inputs */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    box-sizing: border-box;     /* para di lumampas sa form-box width */
  }
}

@media (max-width: 413px) {
  .header{
    height:70px;
  }
    .form-box {
       padding:10px;
    }
    h2 {
        font-size: 20px;
        margin-top: -140px;
        margin-bottom:20px;
        
    }
    .back-button img {
        width: 40px;
        height: 40px;
        margin-left: 145px;
        margin-top: -20px;
    }
     .form-container {
    padding: 20px;
  }
   .form-box{
    margin-top:140px;
  }
  input[type="email"],
  input[type="text"],
  input[type="submit"] {
    width: 100%;
    padding: 10px;              /* medyo lumiit para magkasya */
    margin-top: 10px;           /* spacing sa pagitan ng inputs */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    box-sizing: border-box;     /* para di lumampas sa form-box width */
  }
}

@media (max-width: 390px) {
  .header{
    height:70px;
  }
    .form-box {
       padding:10px;
    }
    h2 {
        font-size: 20px;
        margin-top: -140px;
        margin-bottom:20px;
        
    }
    .back-button img {
        width: 40px;
        height: 40px;
        margin-left: 155px;
        margin-top: -20px;
    }
     .form-container {
    padding: 20px;
  }
   .form-box{
    margin-top:140px;
  }
  input[type="email"],
  input[type="text"],
  input[type="submit"] {
    width: 100%;
    padding: 10px;              /* medyo lumiit para magkasya */
    margin-top: 10px;           /* spacing sa pagitan ng inputs */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    box-sizing: border-box;     /* para di lumampas sa form-box width */
  }
}

@media (max-width: 376px) {
  .header{
    height:70px;
  }
    .form-box {
       padding:10px;
       margin-right:15px;
    }
    h2 {
        font-size: 20px;
        margin-top: -140px;
        margin-bottom:0px;
    }
    label{
      font-size:14px;
    }
    .back-button img {
        width: 35px;
        height: 35px;
        margin-left: 160px;
        margin-top: -20px;
    }
     .form-container {
    padding: 20px;
   width:100%;
  }
   .form-box{
    margin-top:50px;
  }
  input[type="email"],
  input[type="text"],
  input[type="submit"] {
    width: 100%;
    padding: 10px;              /* medyo lumiit para magkasya */
    margin-top: 10px;           /* spacing sa pagitan ng inputs */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    box-sizing: border-box;     /* para di lumampas sa form-box width */
  }
}

@media (max-width: 361px) {
  .header{
    height:70px;
  }
    .form-box {
       padding:10px;
         margin-top:140px;
    }
    h2 {
        font-size: 20px;
        margin-top: -140px;
        margin-bottom:20px;
        
    }
    .back-button img {
        width: 40px;
        height: 40px;
        margin-left: 155px;
        margin-top: -20px;
    }
     .form-container {
    padding: 20px;
  }
  input[type="email"],
  input[type="text"],
  input[type="submit"] {
    width: 100%;
    padding: 10px;              /* medyo lumiit para magkasya */
    margin-top: 10px;           /* spacing sa pagitan ng inputs */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    box-sizing: border-box;     /* para di lumampas sa form-box width */
  }
}

@media (max-width: 345px) {
  .header{
    height:70px;
  }
    .form-box {
      height:180px;
       padding:10px;
       width:100%;
        margin-top:200px;
        margin-left:80px;
    }
    h2 {
        font-size: 15px;
        margin-top: -90px;
        margin-bottom:19px;
    }
    label{
      font-size:12px;
    }
    .back-button img {
        width: 40px;
        height: 40px;
        margin-left: 170px;
        margin-top: 10px;
    }
     .form-container {
    padding: 0px;
   width:80%;
  }
  input[type="email"],
  input[type="text"],
  input[type="submit"] {
    width: 100%;
    padding: 10px;              /* medyo lumiit para magkasya */
    margin-top: 10px;           /* spacing sa pagitan ng inputs */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 10px;
    box-sizing: border-box;     /* para di lumampas sa form-box width */
  }
}

/* 📱 VERY SMALL PHONES: 321px and below */
@media (max-width: 321px) {
  header {
    width: 100%;
    height: auto;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    margin: 0;
    padding: 0;
  }

  .form-container {
    width: 100%;
    max-width: 320px;
    margin: 0 auto;
    padding: 20px 15px;
    background: transparent;
    box-sizing: border-box;
    position: relative;
  }

  /* ✅ Back button nasa loob ng form-container */
  .form-container .back-button {
    position: absolute;
    top: 180px;
    left: -100px;
    width: 40px;
    height: 40px;
    cursor: pointer;
    z-index: 10;
  }

  .form-container .back-button img {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }

  .form-box {
    width: 100%;
    max-width: 280px;
    margin: 70px auto 0; /* 🔽 ibinaba ng konti para may space sa back button */
    background: rgba(255, 255, 255, 0.9);
    padding: 20px 15px;
    border-radius: 10px;
    box-sizing: border-box;
  }

  h2 {
    font-size: 20px;
    text-align: center;
    margin: 10px 0 15px;
    margin-top:-20px;
  }

input[type="email"],
  input[type="text"],
  input[type="submit"] {
    width: 100%;
    padding: 10px;              /* medyo lumiit para magkasya */
    margin-top: 10px;           /* spacing sa pagitan ng inputs */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    box-sizing: border-box;     /* para di lumampas sa form-box width */
  }

  /* dagdag para sa mas maayos na appearance */
  input[type="submit"] {
    background-color: #000;
    color: #fff;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  input[type="submit"]:hover {
    background-color: #333;
  }
}

  </style>
</head>
<body>
  <header class="header">
    <div class="logo-container">
      <div class="logo"><img src="image/logo1.png" alt="Shoe Store Logo" /></div>
      <div class="second-logo"><img src="image/hdb2.png" alt="Second Logo" /></div>
    </div>
  </header>

  

  <div class="form-container">

    <div class="form-box">
      <div class="form-container">
  <a href="login.php" class="back-button">
    <img src="image/back.png" alt="Back" />
  </a>
</div>

      <h2>Forgot Password</h2>
      <form method="POST">
      <?php if ($step === 'email'): ?>
        <label>Enter your registered email:</label>
        <input type="email" name="email" placeholder="Email" required>
        <input type="submit" value="Send OTP">
      <?php elseif ($step === 'otp'): ?>
        <input type="hidden" name="email" value="<?= htmlspecialchars($_POST['email']) ?>">
        <label>Enter the OTP sent to your email:</label>
        <input type="text" name="otp" required placeholder="Enter OTP">
        <input type="submit" value="Verify OTP">
      <?php elseif ($step === 'reset'): ?>
        <input type="hidden" name="email" value="<?= htmlspecialchars($_POST['email']) ?>">

        <label>Enter your new password:</label>
        <div style="position: relative;">
          <input 
            type="password" 
            name="new_password" 
            id="new_password" 
            required 
            placeholder="New Password"
            style="width: 95%; padding: 10px; font-size: 16px; border-radius: 6px; border: 1px solid #ccc;">
          <span onclick="togglePassword('new_password', this)" style=""></span>
        </div>

        <label>Confirm your new password:</label>
        <div style="position: relative;">
          <input 
            type="password" 
            name="confirm_password" 
            id="confirm_password" 
            required 
            placeholder="Confirm Password"
            style="width: 95%; padding: 10px; font-size: 16px; border-radius: 6px; border: 1px solid #ccc;">
          <span onclick="togglePassword('confirm_password', this)" style=""></span>
        </div>

        <input type="submit" value="Reset Password">
      <?php endif; ?>
      </form>
    </div>
  </div>

  <script>
    function togglePassword(id, el) {
      const input = document.getElementById(id);
      const type = input.type === "password" ? "text" : "password";
      input.type = type;
    }
  </script>

</body>
</html>
