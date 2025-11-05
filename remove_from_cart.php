<?php
session_start();

if (!isset($_SESSION['user']['id'])) {
    die("Unauthorized access.");
}

$mysqli = new mysqli("mysql-highdreams.alwaysdata.net", "439165", "Skyworth23", "highdreams_1");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id'])) {
    $cart_id = $_POST['cart_id'];
    $user_id = $_SESSION['user']['id'];

    $stmt = $mysqli->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    if (!$stmt) {
        die("Delete prepare failed: " . $mysqli->error);
    }
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Redirect back to the cart page
header("Location: addtocart.php");
exit();
?>
