<?php
$host = 'mysql-highdreams.alwaysdata.net';
$db = 'highdreams_1';
$user = '439165';
$pass = 'Skyworth23';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $shoe_type = $_POST['Shoe_type'];
    $shoe_name = $_POST['Shoe_name'];
    $size = (int)$_POST['Size'];
    $quantity = (int)$_POST['Quantity'];
    $price = $_POST['Price'];

    $valid_types = ['Bulky', 'Slim', 'Basketball', 'Running', 'Slide', 'Classic'];
    if (!in_array($shoe_type, $valid_types)) {
        die("Invalid shoe type.");
    }

  
    $table = strtolower($shoe_type) . "_inventory";

    
    $sql = "SELECT * FROM $table WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_data = $result->fetch_assoc();
    $stmt->close();

    
    $columns = [];
    for ($i = 36; $i <= 45; $i++) {
        $columns["s$i"] = ($i == $size) ? $quantity : $current_data["s$i"];
    }

    
    $update_columns = "shoe_name = ?, price = ?";
    foreach ($columns as $col => $val) {
        $update_columns .= ", $col = ?";
    }

    $sql = "UPDATE $table SET $update_columns WHERE id = ?";
    $stmt = $conn->prepare($sql);

    
    $params = array_merge([$shoe_name, $price], array_values($columns), [$id]);
    $types = "sd" . str_repeat("i", count($columns)) . "i";
    $stmt->bind_param($types, ...$params);

  
    if ($stmt->execute()) {
        echo "<script>
            alert('Shoe updated successfully!');
            window.location.href = '".$_SERVER['HTTP_REFERER']."';
        </script>";
    } else {
        echo "Update failed: " . $stmt->error;
    }

    $stmt->close();
}
?>
