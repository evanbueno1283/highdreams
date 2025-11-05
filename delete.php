<?php
$host = "mysql-highdreams.alwaysdata.net";
$user = "439165";
$pass = "Skyworth23";
$dbname = "highdreams_1";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'], $_GET['shoe_name'], $_GET['shoe_type'])) {
    $shoe_id = intval($_GET['id']);  // sanitize ID as integer
    $shoe_name = $_GET['shoe_name'];
    $shoe_type = $_GET['shoe_type'];

    // Prepare the DELETE statement
    $sql = "DELETE FROM inventory WHERE id = ? AND shoe_name = ? AND shoe_type = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("iss", $shoe_id, $shoe_name, $shoe_type);

    if ($stmt->execute()) {
        echo "<script>
                alert('Shoe " . addslashes($shoe_name) . " deleted successfully!');
                window.location.href = 'inventory.php';
              </script>";
    } else {
        echo "Error deleting shoe: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>
