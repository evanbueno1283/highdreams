<?php
$host = "mysql-highdreams.alwaysdata.net";
$user = "439165";
$pass = "Skyworth23";
$dbname = "highdreams_1";

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
