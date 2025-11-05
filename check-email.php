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


$stmt = $conn->prepare("SELECT 1 FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();


if ($stmt->num_rows > 0) {
    echo 'exists';
} else {
    echo 'available';
}

$stmt->close();
$conn->close();
?>
