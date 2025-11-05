<?php
// Database config
$host = 'mysql-highdreams.alwaysdata.net';
$db   = 'highdreams_1';
$user = '439165';
$pass = 'Skyworth23';

// Connect to DB
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit;
}

// Get POST data (expects JSON)
$data = json_decode(file_get_contents('php://input'), true);
$rating = isset($data['rating']) ? intval($data['rating']) : 0;
$review_text = isset($data['reviewText']) ? trim($data['reviewText']) : '';

// Validate data
if ($rating < 1 || $rating > 5 || empty($review_text)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid rating or empty review text'
    ]);
    exit;
}

// Prepare SQL statement with correct table name "review"
$stmt = $conn->prepare("INSERT INTO review (rating, review_text) VALUES (?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Prepare failed: ' . $conn->error
    ]);
    exit;
}

// Bind parameters and execute
$stmt->bind_param("is", $rating, $review_text);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Execute failed: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
