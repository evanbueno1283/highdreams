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

// --- Cart start ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_items'])) {

    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $province = $_POST['province'] ?? '';
    $municipality = $_POST['municipality'] ?? '';
    $barangay = $_POST['barangay'] ?? '';
    $street = $_POST['street'] ?? '';

    $selectedItems = $_POST['selected_items'];
    $items = $_POST['items'] ?? [];

    $errors = [];
    $successCount = 0;

    // Prepare the INSERT query once, with placeholders
    $stmt = $mysqli->prepare("INSERT INTO inquiries 
        (username, email, phone, province, municipality, barangay, street, message, size, quantity, shoe_id, shoe_type, shoe_name) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => "Prepare failed: " . $mysqli->error
        ]);
        exit();
    }

    foreach ($selectedItems as $itemId) {
        if (!isset($items[$itemId])) {
            $errors[] = "Item ID $itemId not found.";
            continue;
        }

        $item = $items[$itemId];

        $shoe_id = intval($item['shoe_id']);
        $shoe_name = $item['shoe_name'];
        $size = $item['size'];  // size as string, no escaping needed since bound param
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']);  // not used here, but can be logged if needed
        $shoe_type = 'From Cart';

        $message = "Cart checkout for $shoe_name.";

        // Bind parameters: all strings except quantity and shoe_id are ints
        // size is string
        $stmt->bind_param(
            "ssssssssiisss",
            $username, $email, $phone, $province, $municipality, $barangay, $street,
            $message, $size, $quantity, $shoe_id, $shoe_type, $shoe_name
        );

        if ($stmt->execute()) {
            $successCount++;
        } else {
            $errors[] = "Failed to add $shoe_name: " . $stmt->error;
        }
    }

    $stmt->close();

    if ($successCount > 0) {
        echo json_encode([
            'success' => true,
            'message' => "$successCount item(s) successfully submitted.",
            'errors' => $errors
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No items were submitted.',
            'errors' => $errors
        ]);
    }

    exit();
}


// --- YOUR ORIGINAL SINGLE-INQUIRY CODE (MODIFIED TO INCLUDE PRICE) ---

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
 
    $username = $mysqli->real_escape_string($_POST['username']);
    $email = $mysqli->real_escape_string($_POST['email']);
    $phone = $mysqli->real_escape_string($_POST['phone']);
    $province = $mysqli->real_escape_string($_POST['province']);
    $municipality = $mysqli->real_escape_string($_POST['municipality']);
    $barangay = $mysqli->real_escape_string($_POST['barangay']);
    $street = $mysqli->real_escape_string($_POST['street']);
    $message = $mysqli->real_escape_string($_POST['message']);
    $size = $mysqli->real_escape_string($_POST['size']);
    $quantity = intval($_POST['quantity']);
    $shoe_id = intval($_POST['shoe_id']);
    $shoe_type = $mysqli->real_escape_string($_POST['shoe_type']);
    $shoe_name = $mysqli->real_escape_string($_POST['shoe_name']);
    
    // New price field included and sanitized
    $price = floatval($_POST['price']);

    // Update your DB schema to include price if it doesn't already!
    $query = "INSERT INTO inquiries (username, email, phone, province, municipality, barangay, street, message, size, quantity, shoe_id, shoe_type, shoe_name, price) 
              VALUES ('$username', '$email', '$phone', '$province', '$municipality', '$barangay', '$street', '$message', '$size', $quantity, $shoe_id, '$shoe_type', '$shoe_name', $price)";

    if ($mysqli->query($query)) {
        echo "<script>alert('Your inquiry has been successfully submitted!'); window.location.href='home.php';</script>";
    } else {
        echo "<script>alert('Error: Could not submit your inquiry. Please try again.'); window.location.href='home.php';</script>";
    }

} else {
    header("Location: index.php");
    exit();
}
?>


