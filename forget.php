<?php
session_start(); // ✅ Always start session first

require 'vendor/autoload.php';
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use GuzzleHttp\Client;

// Use session to track current step
$step = $_SESSION['step'] ?? 'email';

// Database connection
$conn = new mysqli("mysql-highdreams.alwaysdata.net", "439165", "Skyworth23", "highdreams_1");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // STEP 1: Send OTP
    if (isset($_POST['email']) && !isset($_POST['otp']) && !isset($_POST['new_password'])) {
        $email = $_POST['email'];
        $otp = rand(100000, 999999);

        // Check if user exists
        $checkUser = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $checkUser->bind_param("s", $email);
        $checkUser->execute();
        $result = $checkUser->get_result();

        if ($result->num_rows === 0) {
            echo "<script>alert('Email not found.');</script>";
        } else {
            // Save OTP in database
            $update = $conn->prepare("UPDATE users SET code = ? WHERE email = ?");
            $update->bind_param("ss", $otp, $email);
            $update->execute();

            // ✅ Send OTP using Brevo API
            $config = Configuration::getDefaultConfiguration()
                ->setApiKey('api-key', getenv('HD_HD')); // Make sure HD_HD env variable has your API key
            $apiInstance = new TransactionalEmailsApi(new Client(), $config);

            $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail([
                'to' => [['email' => $email]],
                'sender' => ['email' => 'jwee8802@gmail.com', 'name' => 'HIGH DREAMS'],
                'subject' => 'Your OTP Code for Password Reset',
                'htmlContent' => "Here is your OTP code: <strong>$otp</strong>",
            ]);

            try {
                $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
                $_SESSION['step'] = 'otp';
                $step = 'otp';
                echo "<script>alert('OTP sent to your email!');</script>";
            } catch (Exception $e) {
                error_log('Mailer Error: '.$e->getMessage());
                echo "<script>alert('Mailer Error. Check server logs.');</script>";
            }
        }
    } 

    // STEP 2: Verify OTP
    elseif (isset($_POST['otp'], $_POST['email']) && !isset($_POST['new_password'])) {
        $email = $_POST['email'];
        $otp = $_POST['otp'];

        $verify = $conn->prepare("SELECT * FROM users WHERE email = ? AND code = ?");
        $verify->bind_param("ss", $email, $otp);
        $verify->execute();
        $result = $verify->get_result();

        if ($result->num_rows === 1) {
            $_SESSION['step'] = 'reset';
            $step = 'reset';
        } else {
            echo "<script>alert('Invalid OTP');</script>";
            $_SESSION['step'] = 'otp';
            $step = 'otp';
        }
    } 

    // STEP 3: Reset Password
    elseif (isset($_POST['new_password'], $_POST['confirm_password'], $_POST['email'])) {
        $email = $_POST['email'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if ($newPassword !== $confirmPassword) {
            echo "<script>alert('Passwords do not match');</script>";
            $_SESSION['step'] = 'reset';
            $step = 'reset';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updatePassword = $conn->prepare("UPDATE users SET password = ?, code = NULL WHERE email = ?");
            $updatePassword->bind_param("ss", $hashedPassword, $email);

            if ($updatePassword->execute()) {
                unset($_SESSION['step']); // Clear step after reset
                echo "<script>alert('Password updated successfully! Redirecting to login...'); window.location.href='login.php';</script>";
            } else {
                echo "<script>alert('Failed to update password.');</script>";
            }
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password</title>
<link rel="icon" href="image/logo1.png" type="image/png">

<style>
/* ======= Basic Styling ======= */
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:sans-serif;background:url('image/logo3.jpeg') no-repeat center center fixed;background-size:cover;}
.form-container{display:flex;justify-content:center;align-items:center;margin-top:100px;}
.form-box{background:rgba(255,255,255,0.85);padding:30px;border-radius:10px;box-shadow:0 0 15px rgba(0,0,0,0.2);margin-top:100px;}
input[type="email"],input[type="text"],input[type="password"],input[type="submit"]{width:100%;padding:12px;margin-top:12px;border-radius:6px;border:1px solid #ccc;}
input[type="submit"]{background-color:#000;color:white;border:none;cursor:pointer;}
input[type="submit"]:hover{background-color:#333;}
.header{background-color:#000;padding:20px;display:flex;justify-content:space-between;align-items:center;}
.logo-container{display:flex;align-items:center;}
.logo img,.second-logo img{height:50px;margin-right:15px;}
h2{text-align:center;margin-top:-30px;}
.back-button img{width:100px;height:100px;cursor:pointer;}
/* ======= Responsive ======= */
/* You can keep all your existing media queries here */
</style>
</head>
<body>

<header class="header">
  <div class="logo-container">
    <div class="logo"><img src="image/logo1.png" alt="Logo"></div>
    <div class="second-logo"><img src="image/hdb2.png" alt="Logo 2"></div>
  </div>
</header>

<div class="form-container">
  <div class="form-box">
    <a href="login.php" class="back-button"><img src="image/back.png" alt="Back"></a>
    <h2>Forgot Password</h2>
    <form method="POST">
      <?php if ($step === 'email'): ?>
        <label>Enter your registered email:</label>
        <input type="email" name="email" placeholder="Email" required>
        <input type="submit" value="Send OTP">
      <?php elseif ($step === 'otp'): ?>
        <input type="hidden" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <label>Enter the OTP sent to your email:</label>
        <input type="text" name="otp" required placeholder="Enter OTP">
        <input type="submit" value="Verify OTP">
      <?php elseif ($step === 'reset'): ?>
        <input type="hidden" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <label>New Password:</label>
        <input type="password" name="new_password" required placeholder="New Password">
        <label>Confirm Password:</label>
        <input type="password" name="confirm_password" required placeholder="Confirm Password">
        <input type="submit" value="Reset Password">
      <?php endif; ?>
    </form>
  </div>
</div>

</body>
</html>
