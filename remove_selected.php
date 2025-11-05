<?php
session_start();

// Check if user is logged in, else set dummy user id (for demo)
if (!isset($_SESSION['user']['id'])) {
    // Redirect to login page or set dummy user id for demo
    $_SESSION['user']['id'] = 1; // or redirect to login
}

// Connect to database using your mysqli connection
$mysqli = new mysqli("mysql-highdreams.alwaysdata.net", "439165", "Skyworth23", "highdreams_1");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['selected_items']) && is_array($_POST['selected_items'])) {
        // Sanitize IDs to integers
        $selectedItems = array_map('intval', $_POST['selected_items']);
        
        // Prepare placeholders for the query
        $placeholders = implode(',', array_fill(0, count($selectedItems), '?'));

        // Prepare statement
        $stmt = $mysqli->prepare("DELETE FROM cart WHERE id IN ($placeholders)");

        if ($stmt) {
            // Build types string and params array
            $types = str_repeat('i', count($selectedItems));
            // bind_param needs references
            $refs = [];
            foreach ($selectedItems as $key => $val) {
                $refs[$key] = &$selectedItems[$key];
            }

            // Bind parameters dynamically
            array_unshift($refs, $types);
            call_user_func_array([$stmt, 'bind_param'], $refs);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Selected items removed successfully.";
            } else {
                $_SESSION['error'] = "Failed to remove selected items.";
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = "Failed to prepare the deletion query.";
        }
    } else {
        $_SESSION['error'] = "No items were selected to remove.";
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
}

$mysqli->close();

// Redirect back to cart page (adjust if needed)
header("Location: addtocart.php");
exit;
