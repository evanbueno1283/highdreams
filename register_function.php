<?php
$servername = "mysql-highdreams.alwaysdata.net";
$username = "439165";
$password = "Skyworth23"; 
$dbname = "highdreams_1";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}


$email = $_POST['email'];
$rawPassword = $_POST['password'];
$confirmPassword = $_POST['confirm_password'];
$gender = $_POST['gender'];
$province = $_POST['province'];
$municipality = $_POST['municipality'];
$barangay = $_POST['barangay'];
$street = $_POST['street'];
$phone = $_POST['phone'];
$username = $_POST['username'];


if ($rawPassword !== $confirmPassword) {
  die("Passwords do not match.");
}


$query = "SELECT email FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {

  echo "<script>alert('This email is already registered. Please use a different email.'); window.location.href='register.php';</script>";
  exit();
}

$stmt->close();


$hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT); 


$stmt = $conn->prepare("INSERT INTO users (email, username, password, gender, province, municipality, barangay, street, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssss", $email, $username, $hashedPassword, $gender, $province, $municipality, $barangay, $street, $phone);

if ($stmt->execute()) {
    echo "<script>alert('Registration Successful!'); window.location.href='login.php';</script>";
    exit();
} else {
    error_log("Error executing statement: " . $stmt->error); 
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
