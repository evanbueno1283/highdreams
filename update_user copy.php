<?php
session_start();
if (!isset($_SESSION['user']['id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "fitscan_database");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$user_id = $_SESSION['user']['id'];

$data = json_decode(file_get_contents("php://input"), true);

$username = $mysqli->real_escape_string($data['username']);
$phone = $mysqli->real_escape_string($data['phone']);
$email = $mysqli->real_escape_string($data['email']);
$gender = $mysqli->real_escape_string($data['gender']);
$province = $mysqli->real_escape_string($data['province']);
$municipality = $mysqli->real_escape_string($data['municipality']);
$barangay = $mysqli->real_escape_string($data['barangay']);
$street = $mysqli->real_escape_string($data['street']);

$query = "UPDATE users SET 
    username='$username',
    phone='$phone',
    email='$email',
    gender='$gender',
    province='$province',
    municipality='$municipality',
    barangay='$barangay',
    street='$street'
    WHERE id=$user_id";

if ($mysqli->query($query)) {
    echo "Success";
} else {
    http_response_code(500);
    echo "Error: " . $mysqli->error;
}
