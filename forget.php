<?php
require 'vendor/autoload.php';
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use GuzzleHttp\Client;

$step = 'email';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli("mysql-highdreams.alwaysdata.net", "439165", "Skyworth23", "highdreams_1");
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

    // Step 1: Send OTP
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

            // Send OTP via Brevo
            $config = Configuration::getDefaultConfiguration()
                ->setApiKey('api-key', 'xkeysib-abcd1234efgh5678ijkl90mnopqrstuvwx-1234567890abcdef'); // palitan ng real API key
            $apiInstance = new TransactionalEmailsApi(new Client(), $config);

            $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail([
                'to' => [['email' => $email]],
                'sender' => ['email' => 'jwee8802@gmail.com', 'name' => 'HIGH DREAMS'],
                'subject' => 'Your OTP Code for Password Reset',
                'htmlContent' => "Here is your OTP code: <strong>$otp</strong>",
            ]);

            try {
                $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
                echo "<script>alert('OTP sent to your email!');</script>";
                $step = 'otp';
            } catch (Exception $e) {
                echo "<script>alert('Mailer Error: {$e->getMessage()}');</script>";
            }
        }
    }

    // Step 2: Verify OTP
    elseif (isset($_POST['otp'], $_POST['email']) && !isset($_POST['new_password'])) {
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
    }

    // Step 3: Reset Password
    elseif (isset($_POST['new_password'], $_POST['email'])) {
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
    /* 👇 ORIGINAL CSS START — unchanged */
    *{margin:0;padding:0;box-sizing: border-box;}
    body { font-family: sans-serif; margin: 0; padding: 0; background: url('image/logo3.jpeg') no-repeat center center fixed; background-size: cover; }
    .form-container { display: flex; justify-content: center; align-items: center; margin-top: 100px; }
    .back-button img { width: 100px; height: 100px; cursor: pointer; }
    h2{ text-align: center; margin-top: -30px; }
    .form-box { background: rgba(255,255,255,0.85); padding:30px; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.2); margin-top:100px; }
    input[type="email"], input[type="text"], input[type="submit"], input[type="password"] { width: 100%; padding: 12px; margin-top:12px; border-radius:6px; border:1px solid #ccc; }
    input[type="submit"] { background-color:#000; color:white; border:none; cursor:pointer; }
    .header { background-color:#000; padding:20px; box-shadow:0 2px 4px rgba(0,0,0,0.1); display:flex; justify-content:space-between; align-items:center; }
    .logo-container { display:flex; align-items:center; }
    .logo img, .second-logo img { height:50px; margin-right:15px; }
    /* 👆 ORIGINAL CSS END — unchanged */
    /* Keep all your original media queries here (I truncated in this snippet for brevity) */
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
          <input type="password" name="new_password" placeholder="New Password" required>
          <label>Confirm your new password:</label>
          <input type="password" name="confirm_password" placeholder="Confirm Password" required>
          <input type="submit" value="Reset Password">
        <?php endif; ?>
      </form>
    </div>
  </div>

  <script>
    // Password toggle if you want later
    function togglePassword(id, el) {
      const input = document.getElementById(id);
      input.type = input.type === "password" ? "text" : "password";
    }
  </script>

</body>
</html>
