<?php
session_start();
require 'vendor/autoload.php';

use SendinBlue\Client\Configuration;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use GuzzleHttp\Client;

// DB connection
$conn = new mysqli("mysql-highdreams.alwaysdata.net", "439165", "Skyworth23", "highdreams_1");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Current step (default = email)
$step = $_SESSION['step'] ?? 'email';

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // STEP 1: SEND OTP
    if (isset($_POST['email']) && !isset($_POST['otp']) && !isset($_POST['new_password'])) {
        $email = trim($_POST['email']);
        $otp = rand(100000, 999999);

        // Check user
        $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows === 0) {
            echo "<script>alert('❌ Email not found!');</script>";
        } else {
            // Save OTP to database
            $save = $conn->prepare("UPDATE users SET code = ? WHERE email = ?");
            $save->bind_param("ss", $otp, $email);
            $save->execute();

            // ✅ Configure Brevo
            $config = Configuration::getDefaultConfiguration()
                ->setApiKey('api-key', 'BREVO_SMTP_KEY'); // <-- your real API key
            $apiInstance = new TransactionalEmailsApi(new Client(), $config);

            // ✅ Email content
            $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail([
                'sender' => ['email' => 'jwee8802@gmail.com', 'name' => 'HIGH DREAMS'],
                'to' => [['email' => $email]],
                'subject' => 'HIGH DREAMS Password Reset OTP',
                'htmlContent' => "
                    <div style='font-family:sans-serif;'>
                        <h2>Your OTP Code</h2>
                        <p>Hello! Here is your verification code for resetting your password:</p>
                        <h1 style='letter-spacing:5px;'>$otp</h1>
                        <p>This code will expire soon. Please do not share it with anyone.</p>
                    </div>
                "
            ]);

            // ✅ Try sending
            try {
                $response = $apiInstance->sendTransacEmail($sendSmtpEmail);
                echo "<script>alert('✅ OTP has been sent to your email!');</script>";
                $step = 'otp';
                $_SESSION['step'] = 'otp';
                $_SESSION['email'] = $email;
            } catch (Exception $e) {
                echo "<script>alert('❌ Failed to send email: " . addslashes($e->getMessage()) . "');</script>";
            }
        }
    }

    // STEP 2: VERIFY OTP
    elseif (isset($_POST['otp'])) {
        $email = $_SESSION['email'];
        $otp = trim($_POST['otp']);

        $verify = $conn->prepare("SELECT * FROM users WHERE email = ? AND code = ?");
        $verify->bind_param("ss", $email, $otp);
        $verify->execute();
        $result = $verify->get_result();

        if ($result->num_rows === 1) {
            echo "<script>alert('✅ OTP verified! Please enter your new password.');</script>";
            $step = 'reset';
            $_SESSION['step'] = 'reset';
        } else {
            echo "<script>alert('❌ Invalid OTP, please try again.');</script>";
            $step = 'otp';
        }
    }

    // STEP 3: RESET PASSWORD
    elseif (isset($_POST['new_password'], $_POST['confirm_password'])) {
        $email = $_SESSION['email'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        if ($new !== $confirm) {
            echo "<script>alert('❌ Passwords do not match!');</script>";
            $step = 'reset';
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password=?, code=NULL WHERE email=?");
            $update->bind_param("ss", $hashed, $email);
            if ($update->execute()) {
                echo "<script>alert('✅ Password successfully updated! Redirecting to login...'); window.location='login.php';</script>";
                session_destroy();
                exit;
            } else {
                echo "<script>alert('❌ Failed to update password.');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password</title>
  <link rel="icon" href="image/logo1.png" type="image/png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: sans-serif;
      background: url('image/logo3.jpeg') no-repeat center center fixed;
      background-size: cover;
    }
    .form-container {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-top: 120px;
    }
    .form-box {
      background: rgba(255,255,255,0.9);
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 12px rgba(0,0,0,0.3);
      width: 320px;
    }
    input {
      width: 100%;
      padding: 12px;
      margin-top: 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    input[type=submit] {
      background: #000;
      color: #fff;
      border: none;
      cursor: pointer;
    }
  </style>
</head>
<body>

  <div class="form-container">
    <div class="form-box">
      <a href="login.php"><img src="image/back.png" width="40" height="40" style="cursor:pointer; margin-bottom:10px;"></a>
      <h2 style="text-align:center;">Forgot Password</h2>
      <form method="POST">
        <?php if ($step === 'email'): ?>
          <label>Enter your registered email:</label>
          <input type="email" name="email" required placeholder="Enter Email">
          <input type="submit" value="Send OTP">
        <?php elseif ($step === 'otp'): ?>
          <label>Enter the OTP sent to your email:</label>
          <input type="text" name="otp" required placeholder="Enter OTP">
          <input type="submit" value="Verify OTP">
        <?php elseif ($step === 'reset'): ?>
          <label>Enter your new password:</label>
          <input type="password" name="new_password" required placeholder="New Password">
          <label>Confirm your new password:</label>
          <input type="password" name="confirm_password" required placeholder="Confirm Password">
          <input type="submit" value="Reset Password">
        <?php endif; ?>
      </form>
    </div>
  </div>

</body>
</html>
