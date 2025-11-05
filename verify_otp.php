<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $otp_input = $_POST['otp'];

    $mysqli = new mysqli("mysql-highdreams.alwaysdata.net", "439165", "Skyworth23", "highdreams_1");

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND code = ?");
    $stmt->bind_param("ss", $email, $otp_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('OTP verified! You may now reset your password.'); window.location.href='reset_password.php?email=$email';</script>";
    } else {
        echo "<script>alert('Invalid OTP!'); window.location.href='verify_otp.php?email=$email';</script>";
    }

    $conn->close();
}
?>

 
<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
</head>
<body>
    <form method="POST">
        <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email']); ?>">
        <label>Enter OTP:</label>
        <input type="text" name="otp" required>
        <input type="submit" value="Verify">
    </form>
</body>
</html>
