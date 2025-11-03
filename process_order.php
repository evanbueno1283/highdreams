<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 

$host = 'localhost';
$db = 'fitscan_database';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$orderId = $_POST['order_id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$orderId || !$action) {
    echo "Missing parameters.";
    exit;
}

$sql = "SELECT * FROM inquiries WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo "Order not found.";
    exit;
}

// Whitelist allowed sizes (column names)
$allowed_sizes = ['s36', 's37', 's38', 's39', 's40', 's41', 's42', 's43', 's44', 's45'];

// Helper function to send email
function sendEmail($to, $toName, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'highdreams552@gmail.com'; // your SMTP username
        $mail->Password = 'gmfjqsmzlfgrmbwc'; // your SMTP password or app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('no-reply@highdreams.com', 'HIGH DREAMS');
        $mail->addAddress($to, $toName);

        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}

if ($action === "accept") {
    $updateStatus = $conn->prepare("UPDATE inquiries SET status = 'Accepted' WHERE id = ?");
    $updateStatus->bind_param("i", $orderId);
    $updateStatus->execute();
    $updateStatus->close();

    $shoe_id = $order['shoe_id'];
    $shoe_type = $order['shoe_type'];
    $shoe_name = $order['shoe_name'];
    $size = $order['size'];
    $quantity = $order['quantity'];

    if (!in_array($size, $allowed_sizes)) {
        die("Invalid size column.");
    }

    // Deduct inventory
    $sql = "
        UPDATE inventory
        SET `$size` = `$size` - ?
        WHERE id = ? AND shoe_type = ? AND shoe_name = ?
    ";

    $deductInventory = $conn->prepare($sql);
    if (!$deductInventory) {
        die("Prepare failed: " . $conn->error);
    }

    $deductInventory->bind_param("isss", $quantity, $shoe_id, $shoe_type, $shoe_name);

    if (!$deductInventory->execute()) {
        die("Execute failed: " . $deductInventory->error);
    }

    $deductInventory->close();

    $userEmail = $order['email'];
    $username = $order['username'];
    $subject = "Order Accepted - HIGH DREAMS";
    $message = "Dear $username,\n\nYour order for \"$shoe_name\" has been approved and is being processed. Thank you for shopping with us! You may claim your order within 3 days.\n\nBest regards,\nHIGH DREAMS Team";

    $mailResult = sendEmail($userEmail, $username, $subject, $message);
    if ($mailResult === true) {
        echo "Order accepted, stock updated, and email notification sent.";
    } else {
        echo "Order accepted and stock updated, but email failed to send. Error: $mailResult";
    }

} elseif ($action === "decline") {
    $updateStatus = $conn->prepare("UPDATE inquiries SET status = 'Declined' WHERE id = ?");
    $updateStatus->bind_param("i", $orderId);
    $updateStatus->execute();
    $updateStatus->close();

    $userEmail = $order['email'];
    $username = $order['username'];
    $shoe_name = $order['shoe_name'];
    $subject = "Order Declined - HIGH DREAMS";
    $message = "Dear $username,\n\nWe regret to inform you that your order for \"$shoe_name\" has been declined due to the unavailability of the selected size. We apologize for the inconvenience and encourage you to check for other sizes or products.\n\nThank you for your understanding.\n\nBest regards,\nHIGH DREAMS Team";

    $mailResult = sendEmail($userEmail, $username, $subject, $message);
    if ($mailResult === true) {
        echo "Order declined, and email notification sent.";
    } else {
        echo "Order declined, but email failed to send. Error: $mailResult";
    }

} elseif ($action === "readd") {
    $updateStatus = $conn->prepare("UPDATE inquiries SET status = 'Cancelled Order' WHERE id = ?");
    $updateStatus->bind_param("i", $orderId);
    $updateStatus->execute();
    $updateStatus->close();

    $shoe_id = $order['shoe_id'];
    $shoe_type = $order['shoe_type'];
    $shoe_name = $order['shoe_name'];
    $size = $order['size'];
    $quantity = $order['quantity'];

    if (!in_array($size, $allowed_sizes)) {
        die("Invalid size column.");
    }

    // Add inventory back
    $sql = "
        UPDATE inventory
        SET `$size` = `$size` + ?
        WHERE id = ? AND shoe_type = ? AND shoe_name = ?
    ";

    $addInventory = $conn->prepare($sql);
    if (!$addInventory) {
        die("Prepare failed: " . $conn->error);
    }

    $addInventory->bind_param("isss", $quantity, $shoe_id, $shoe_type, $shoe_name);

    if (!$addInventory->execute()) {
        die("Execute failed: " . $addInventory->error);
    }

    $addInventory->close();

    echo "Cancelled pending order has been accepted and stock added.";

} elseif ($action === "archive") {
    $updateStatus = $conn->prepare("UPDATE inquiries SET status = 'Archived' WHERE id = ?");
    $updateStatus->bind_param("i", $orderId);
    $updateStatus->execute();
    $updateStatus->close();

    echo "Order archived.";

} elseif ($action === "delete") {
    $deleteOrder = $conn->prepare("DELETE FROM inquiries WHERE id = ?");
    $deleteOrder->bind_param("i", $orderId);
    $deleteOrder->execute();
    $deleteOrder->close();

    echo "Order deleted.";

} else {
    echo "Invalid action.";
}

$conn->close();
?>
