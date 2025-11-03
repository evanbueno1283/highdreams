<?php
// Database connection setup
$host = 'localhost';
$db = 'fitscan_database';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle POST request to update shoe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $shoe_type = $_POST['shoe_type'] ?? '';
    $original_shoe_name = $_POST['original_shoe_name'] ?? '';
    $new_shoe_name = $_POST['new_shoe_name'] ?? '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $sizes = $_POST['size'] ?? [];

    // Validate shoe type against allowed values
    $valid_types = ['Bulky', 'Slim', 'Basketball', 'Running', 'Slide', 'Classic'];
    if (!in_array($shoe_type, $valid_types)) {
        die("Invalid shoe type.");
    }

    // Validate required fields
    if ($id <= 0 || empty($original_shoe_name) || empty($new_shoe_name) || $price < 0) {
        die("Invalid input data.");
    }

    // Get sizes (default to 0 if not set)
    $s36 = intval($sizes[36] ?? 0);
    $s37 = intval($sizes[37] ?? 0);
    $s38 = intval($sizes[38] ?? 0);
    $s39 = intval($sizes[39] ?? 0);
    $s40 = intval($sizes[40] ?? 0);
    $s41 = intval($sizes[41] ?? 0);
    $s42 = intval($sizes[42] ?? 0);
    $s43 = intval($sizes[43] ?? 0);
    $s44 = intval($sizes[44] ?? 0);
    $s45 = intval($sizes[45] ?? 0);

    $table_name = 'inventory';

    // Prepare SQL update statement
    $sql = "UPDATE $table_name SET shoe_name = ?, price = ?, 
            s36 = ?, s37 = ?, s38 = ?, s39 = ?, s40 = ?, 
            s41 = ?, s42 = ?, s43 = ?, s44 = ?, s45 = ?
            WHERE id = ? AND shoe_type = ? AND shoe_name = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param(
            "sdiiiiiiiiiiiss",
            $new_shoe_name,
            $price,
            $s36, $s37, $s38, $s39, $s40,
            $s41, $s42, $s43, $s44, $s45,
            $id,
            $shoe_type,
            $original_shoe_name
        );

        if ($stmt->execute()) {
            echo "<script>
                    alert('Shoe updated successfully!');
                    window.location.href = 'inventory.php';
                  </script>";
        } else {
            echo "Error updating record: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Failed to prepare the update statement.";
    }
}

$conn->close();
?>
