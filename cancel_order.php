<?php
session_start();

if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit();
}

$mysqli = new mysqli("mysql-highdreams.alwaysdata.net", "439165", "Skyworth23", "highdreams_1");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}


$order_id = $_POST['order_id'] ?? '';

echo "ORDER_ID: $order_id<br>";






$stmt = $mysqli->prepare("UPDATE inquiries SET status = 'Cancelled pending' WHERE id = ?");
$stmt->bind_param("i", $order_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        header("Location: orderuser.php");
        exit();
    } else {
        echo "❌ No matching record found or already canceled.";
    }
} else {
    echo "❌ Error executing query: " . $stmt->error;
}

$stmt->close();
$mysqli->close();
?>
