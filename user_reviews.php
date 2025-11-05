<?php
session_start();
header('Content-Type: application/json');

$mysqli = new mysqli("mysql-highdreams.alwaysdata.net", "439165", "Skyworth23", "highdreams_1");
if ($mysqli->connect_error) {
    echo json_encode(["success" => false, "error" => "DB connection failed"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email'], $data['rating'], $data['comment'])) {
    echo json_encode(["success" => false, "error" => "Missing fields"]);
    exit();
}

$email = $mysqli->real_escape_string($data['email']);
$rating = (int)$data['rating'];
$comment = $mysqli->real_escape_string($data['comment']);

// Insert review
$query = "INSERT INTO user_reviews (email, rating, comment) VALUES ('$email', $rating, '$comment')";
if ($mysqli->query($query)) {
    // ✅ Destroy session so user is logged out
    session_unset();
    session_destroy();
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $mysqli->error]);
}
?>
