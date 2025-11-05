<?php
session_start();


if (!isset($_SESSION['email'])) {
    $_SESSION['email'] = 'test@example.com';
    error_log("Session email set to test@example.com");
}

header('Content-Type: application/json');

$host = 'mysql-highdreams.alwaysdata.net';
$db   = 'highdreams_1';
$user = '439165';
$pass = 'Skyworth23';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    error_log("DB connection failed: " . $conn->connect_error);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

if (!isset($_SESSION['email'])) {
    error_log("Session email not set");
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

$email = $_SESSION['email'];
error_log("Session email: $email");

$inputJSON = file_get_contents('php://input');
error_log("Raw input JSON: " . $inputJSON);

$data = json_decode($inputJSON, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("JSON decode error: " . json_last_error_msg());
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
    exit;
}

$rating = isset($data['rating']) ? intval($data['rating']) : 0;
if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid rating']);
    exit;
}

$comment = isset($data['comment']) ? trim($data['comment']) : '';
if (strlen($comment) > 1000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Comment too long']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO reviews (email, rating, comment) VALUES (?, ?, ?)");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Prepare failed']);
    exit;
}

$stmt->bind_param("sis", $email, $rating, $comment);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    error_log("Execute failed: " . $stmt->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Execute failed']);
}

$stmt->close();
$conn->close();
?>
