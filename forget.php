<?php
session_start();
require 'vendor/autoload.php';
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use GuzzleHttp\Client;

// Use session to track OTP step
$step = $_SESSION['step'] ?? 'request';
$message = '';

// Function to generate 6-digit OTP
function generate_otp($length = 6) {
    $num = random_int(0, (int) pow(10, $length) - 1);
    return str_pad((string)$num, $length, '0', STR_PAD_LEFT);
}

// Function to send OTP using Brevo (SendinBlue) API
function send_otp_mail($to_email, $otp) {
    $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', getenv('HD_HD'));
    $apiInstance = new TransactionalEmailsApi(new Client(), $config);

    $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail([
        'to' => [[ 'email' => $to_email ]],
        'templateId' => null,
        'subject' => 'Your Verification Code (OTP)',
        'htmlContent' => "<p>Kumusta,</p><p>Ang iyong OTP code ay: <strong>{$otp}</strong></p><p>Mag-expire ito sa 5 minuto.</p>",
        'sender' => ['name' => 'Your App Name', 'email' => 'noreply@yourapp.com']
    ]);

    try {
        $apiInstance->sendTransacEmail($sendSmtpEmail);
        return true;
    } catch (Exception $e) {
        error_log('Brevo Error: ' . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'request' && isset($_POST['email'])) {
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            $message = 'Please enter a valid email address.';
        } else {
            $otp = generate_otp(6);
            if (send_otp_mail($email, $otp)) {
                $_SESSION['otp_code'] = $otp;
                $_SESSION['otp_email'] = $email;
                $_SESSION['otp_expires'] = time() + 300; // 5 min
                $message = 'OTP sent to ' . htmlspecialchars($email) . '. Please check your inbox.';
            } else {
                $message = 'Failed to send OTP. Check server logs or API key.';
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'verify' && isset($_POST['otp_input'])) {
        $user_otp = trim($_POST['otp_input']);
        $stored = $_SESSION['otp_code'] ?? null;
        $expires = $_SESSION['otp_expires'] ?? 0;

        if (!$stored || time() > $expires) {
            $message = 'OTP expired or not requested. Please request a new code.';
        } elseif (hash_equals($stored, $user_otp)) {
            unset($_SESSION['otp_code'], $_SESSION['otp_expires']);
            $message = 'OTP verified successfully!';
        } else {
            $message = 'Invalid OTP. Please try again.';
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Brevo OTP Interface</title>
<style>
body { font-family: Arial, sans-serif; background: #f6f8fa; padding: 30px; }
.card { background: #fff; max-width: 420px; margin: 0 auto; padding: 20px; border-radius: 8px; box-shadow: 0 6px 18px rgba(0,0,0,0.06); }
input[type="email"], input[type="text"] { width:100%; padding:10px; margin:8px 0; border-radius:4px; border:1px solid #ddd; }
button { padding:10px 14px; border-radius:6px; border:0; cursor:pointer; }
.btn-primary { background:#2563eb; color:#fff; }
.message { margin:10px 0; padding:8px; background:#eef2ff; border-radius:4px; }
</style>
</head>
<body>
<div class="card">
<h2>Send OTP via Brevo</h2>
<?php if ($message): ?>
  <div class="message"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if (empty($_SESSION['otp_code'])): ?>
<form method="post">
  <input type="hidden" name="action" value="request">
  <label for="email">Email address</label>
  <input type="email" id="email" name="email" required placeholder="you@example.com">
  <button type="submit" class="btn-primary">Send OTP</button>
</form>
<?php else: ?>
<form method="post">
  <input type="hidden" name="action" value="verify">
  <label for="otp_input">Enter OTP</label>
  <input type="text" id="otp_input" name="otp_input" required placeholder="6-digit code">
  <button type="submit" class="btn-primary">Verify OTP</button>
</form>
<form method="post" style="margin-top:10px;">
  <input type="hidden" name="action" value="request">
  <button type="submit">Resend OTP</button>
</form>
<?php endif; ?>
<hr>
<small>Make sure your Brevo API key is saved in environment variable <code>HD_HD</code>. No other setup is required.</small>
</div>
</body>
</html>
