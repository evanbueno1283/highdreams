<?php
session_start(); // ✅ Always start session first

require 'vendor/autoload.php';
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use GuzzleHttp\Client;

// Use session to track the current step
$step = $_SESSION['step'] ?? 'email';

// Database connection
$conn = new mysqli("mysql-highdreams.alwaysdata.net", "439165", "Skyworth23", "highdreams_1");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Step 1: Email input to send OTP
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Sending OTP
    if (isset($_POST['email']) && !isset($_POST['otp']) && !isset($_POST['new_password'])) {
        $email = $_POST['email'];

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('Invalid email format');</script>";
        } else {
            $otp = rand(100000, 999999);

            // Check if email exists
            $checkUser = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $checkUser->bind_param("s", $email);
            $checkUser->execute();
            $result = $checkUser->get_result();

            if ($result->num_rows === 0) {
                echo "<script>alert('Email not found.');</script>";
            } else {
                // Update OTP in DB
                $update = $conn->prepare("UPDATE users SET code = ? WHERE email = ?");
                $update->bind_param("ss", $otp, $email);
                $update->execute();

                // Send OTP using Brevo API
                $config = Configuration::getDefaultConfiguration()
                     ->setApiKey('api-key', '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');
                $apiInstance = new TransactionalEmailsApi(new Client(), $config);

                $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail([
                    'to' => [['email' => $email]],
                    'sender' => ['email' => 'jwee8802@gmail.com', 'name' => 'HIGH DREAMS'],
                    'subject' => 'Your OTP Code for Password Reset',
                    'htmlContent' => "Here is your OTP code: <strong>$otp</strong>",
                ]);

                try {
                    $apiInstance->sendTransacEmail($sendSmtpEmail);
                    $_SESSION['step'] = 'otp';
                    $_SESSION['email'] = $email;
                    echo "<script>alert('OTP sent to your email!'); window.location.href='".$_SERVER['PHP_SELF']."';</script>";
                    exit();
                } catch (Exception $e) {
                    error_log("Brevo API Error: " . $e->getMessage());
                    echo "<script>alert('Failed to send OTP. Please check your API key and internet connection.');</script>";
                }
            }
        }
    }

    // Step 2: Verify OTP
    elseif (isset($_POST['otp']) && isset($_SESSION['email'])) {
        $email = $_SESSION['email'];
        $otp = $_POST['otp'];

        $verify = $conn->prepare("SELECT * FROM users WHERE email = ? AND code = ?");
        $verify->bind_param("ss", $email, $otp);
        $verify->execute();
        $result = $verify->get_result();

        if ($result->num_rows === 1) {
            $_SESSION['step'] = 'reset';
            echo "<script>alert('OTP verified!'); window.location.href='".$_SERVER['PHP_SELF']."';</script>";
            exit();
        } else {
            echo "<script>alert('Invalid OTP');</script>";
        }
    }

    // Step 3: Reset password
    elseif (isset($_POST['new_password'], $_POST['confirm_password']) && isset($_SESSION['email'])) {
        $email = $_SESSION['email'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if ($newPassword !== $confirmPassword) {
            echo "<script>alert('Passwords do not match');</script>";
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $updatePassword = $conn->prepare("UPDATE users SET password = ?, code = NULL WHERE email = ?");
            $updatePassword->bind_param("ss", $hashedPassword, $email);

            if ($updatePassword->execute()) {
                $_SESSION['step'] = 'email';
                unset($_SESSION['email']);
                echo "<script>alert('Password updated successfully! Redirecting to login...'); window.location.href='login.php';</script>";
                exit();
            } else {
                echo "<script>alert('Failed to update password.');</script>";
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password</title>
<link rel="icon" href="image/logo1.png" type="image/png">
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
  width: 100px;  
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

input[type="email"], input[type="text"], input[type="password"], input[type="submit"] {
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
  cursor: pointer;
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

/* ------------------ RESPONSIVE CSS ------------------ */
<?php
// Paste here ALL your original media queries and responsive styles
?>
</style>
</head>
<body>
<header class="header">
  <div class="logo-container">
    <div class="logo"><img src="image/logo1.png" alt="Logo" /></div>
    <div class="second-logo"><img src="image/hdb2.png" alt="Second Logo" /></div>
  </div>
</header>

<div class="form-container">
  <div class="form-box">
    <a href="login.php" class="back-button"><img src="image/back.png" alt="Back" /></a>
    <h2>Forgot Password</h2>
    <form method="POST">
    <?php
      $step = $_SESSION['step'] ?? 'email';
      if ($step === 'email'): ?>
        <label>Enter your registered email:</label>
        <input type="email" name="email" placeholder="Email" required>
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
