<?php
// DB connection
$host = "mysql-highdreams.alwaysdata.net";
$user = "439165";
$pass = "Skyworth23";
$dbname = "highdreams_1";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Add Shoe Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Shoe_name'])) {
    $shoe_type = $_POST['Shoe_type'] ?? '';
    $shoe_name = $_POST['Shoe_name'] ?? '';
    $price = floatval($_POST['Price'] ?? 0);
    $sizes = $_POST['size'] ?? [];

    // Handle image upload
if (isset($_FILES['Shoe_image']) && $_FILES['Shoe_image']['error'] === UPLOAD_ERR_OK) {
    // Set upload directory inside the current PHP file's folder
    $upload_dir = __DIR__ . '/uploads/';

    // Create folder if it doesn't exist
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            die("Failed to create upload directory. Please check server permissions.");
        }
    }

    // Check if directory is writable
    if (!is_writable($upload_dir)) {
        die("Upload directory is not writable. You need to set proper permissions.");
    }

    // Prepare file info
    $tmp_name = $_FILES['Shoe_image']['tmp_name'];
    $name = basename($_FILES['Shoe_image']['name']); // prevent path traversal

    // Move uploaded file
    $target_file = $upload_dir . $name;
    if (move_uploaded_file($tmp_name, $target_file)) {
        echo "File uploaded successfully!";
    } else {
        echo "Failed to move uploaded file. Check folder permissions.";
    }
}

        $tmp_name = $_FILES['Shoe_image']['tmp_name'];
        $original_name = basename($_FILES['Shoe_image']['name']);
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($ext, $allowed_ext)) {
            die("Invalid image file type.");
        }

        $new_file_name = uniqid('shoe_', true) . '.' . $ext;
        $destination = $upload_dir . $new_file_name;

        if (!move_uploaded_file($tmp_name, $destination)) {
            die("Failed to upload image.");
        }

        $shoe_image = 'uploads/' . $new_file_name;
    } else {
        $shoe_image = ''; // optional: default empty image
    }

    // Prepare insert statement
    $sql = "INSERT INTO inventory (
        shoe_type, shoe_name, price, shoe_image,
        s36, s37, s38, s39, s40, s41, s42, s43, s44, s45
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) die("Prepare failed: " . $conn->error);

    $s36 = $sizes[36] ?? 0;
    $s37 = $sizes[37] ?? 0;
    $s38 = $sizes[38] ?? 0;
    $s39 = $sizes[39] ?? 0;
    $s40 = $sizes[40] ?? 0;
    $s41 = $sizes[41] ?? 0;
    $s42 = $sizes[42] ?? 0;
    $s43 = $sizes[43] ?? 0;
    $s44 = $sizes[44] ?? 0;
    $s45 = $sizes[45] ?? 0;

    $stmt->bind_param(
        "ssdsiiiiiiiiii",
        $shoe_type,
        $shoe_name,
        $price,
        $shoe_image,
        $s36, $s37, $s38, $s39, $s40,
        $s41, $s42, $s43, $s44, $s45
    );

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        die("Insert failed: " . $stmt->error);
    }
}

// Handle filter
$filter_type = $_GET['shoe_type_filter'] ?? '';
if ($filter_type) {
    $stmt = $conn->prepare("SELECT * FROM inventory WHERE shoe_type = ? ORDER BY shoe_name ASC");
    $stmt->bind_param("s", $filter_type);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM inventory ORDER BY shoe_name ASC");
}

$filter_type = $_GET['shoe_type_filter'] ?? '';
$sql = "SELECT * FROM inventory";
if (!empty($filter_type)) {
    $sql .= " WHERE shoe_type='" . $conn->real_escape_string($filter_type) . "'";
}
$result = $conn->query($sql);

$types = ['Bulky', 'Slim', 'Basketball', 'Running', 'Slide', 'Classic'];

// ----------------------
// Handle Edit Shoe (Update)
// ----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_shoe_name'])) {
    $id = intval($_POST['id']);
    $shoe_type = $_POST['shoe_type'];
    $original_shoe_name = $_POST['original_shoe_name'];
    $new_shoe_name = $_POST['new_shoe_name'];
    $price = floatval($_POST['price']);
    $sizes = $_POST['size'] ?? [];

    $sql = "UPDATE inventory SET shoe_name=?, price=?, 
            s36=?, s37=?, s38=?, s39=?, s40=?, 
            s41=?, s42=?, s43=?, s44=?, s45=? 
            WHERE id=? AND shoe_type=? AND shoe_name=?";

    if ($stmt = $conn->prepare($sql)) {
      $s36 = $sizes[36] ?? 0;
    $s37 = $sizes[37] ?? 0;
    $s38 = $sizes[38] ?? 0;
    $s39 = $sizes[39] ?? 0;
    $s40 = $sizes[40] ?? 0;
    $s41 = $sizes[41] ?? 0;
    $s42 = $sizes[42] ?? 0;
    $s43 = $sizes[43] ?? 0;
    $s44 = $sizes[44] ?? 0;
    $s45 = $sizes[45] ?? 0;

        $stmt->bind_param(
            "sdiiiiiiiiiiiss",
            $new_shoe_name, $price,
           $s36, $s37, $s38, $s39, $s40,
        $s41, $s42, $s43, $s44, $s45,
            $id, $shoe_type, $original_shoe_name
        );
        if ($stmt->execute()) {
            echo "<script>alert('Shoe updated successfully!'); window.location='inventory.php';</script>";
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" href="image/logo1.png" type="image/png">
  <meta charset="UTF-8">
  <title>Shoe Store</title>
  <style>
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      color: #333;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 30px;
      background-color: #000;
      color: white;
      height: 80px;
       position: fixed;
  top: 0;
  width: 100%;
  z-index: 1000;
    }

    .logo-container img {
      margin-right: 15px;
    }

    .user-options {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .home-logo img {
      height: 30px;
      width: 30px;
      transition: transform 0.3s ease-in-out;
    }

    .home-logo img:hover {
      transform: scale(1.1);
    }

    .user-options button {
      padding: 10px 20px;
      background-color: #fff;
      color: #000;
      border: none;
      cursor: pointer;
      border-radius: 20px;
    }

    .inventory {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 40px;
      background: #f0f0f0;
      flex-grow: 1;
    }

    table {
      width: 80%;
      border-collapse: collapse;
      background-color: #fff;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      margin-top: 20px;
    }

    th, td {
      padding: 12px;
      text-align: center;
    }

    th {
      background-color: #000;
      color: #fff;
    }

    input[type="text"],
    input[type="number"] {
      padding: 6px;
      width: 90%;
      border-radius: 5px;
      border: 1px solid #000;
    }

    .btn {
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      margin: 25px;
    }

    .btn:hover {
      transform: scale(1.1);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .btn-add,
    .btn-edit,
    .btn-delete {
      background-color: #000;
      color: white;
    }

    .shoe-form,
    .popup-modal-content {
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .popup-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      justify-content: center;
      align-items: center;
    }

    .popup-modal-content {
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      width: 60%;
      align-items: center;
      justify-content: center;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 100%;
    }

    .form-group input,
    .form-group select {
      padding: 8px 10px;
      border: 1px solid #000;
      border-radius: 6px;
      width: 80%;
    }

    .form-actions {
      display: flex;
      justify-content: center;
      width: 100%;
    }

    .close-btn {
      align-self: flex-end;
      cursor: pointer;
      font-size: 20px;
      font-weight: bold;
      margin-bottom: 20px;
    }

    .popup-modal {
      display: none; 
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      justify-content: center;
      align-items: center;
    }
    .size-inputs {
      display: flex;
      flex-wrap: wrap;
      gap: 5px; 
      justify-content: space-between;
    }

    .size-column {
      flex: 1 1 30%; 
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .size-column input {
      width: 50%; 
      padding: 5px; 
    }

  </style>
</head>
<body>

<header class="header">
  <div class="logo-container">
    <img src="image/logo1.png" alt="Shoe Store Logo" style="height: 60px;">
    <img src="image/hdb2.png" alt="Second Logo" style="height: 60px;">
  </div>
  <div class="user-options">
    <a href="admin.php" class="home-logo">
      <img src="image/home.png" alt="Home Logo">
    </a>
    <button id="logout-button">Logout</button>
  </div>
</header>

<div class="inventory">
  <h2>Inventory</h2>

  <button class="btn btn-add" id="add-shoe-btn">Add New Shoe</button>

  <form method="GET" style="margin-top: 20px;">
    <label for="shoe_type_filter">Filter by Shoe Type:</label>
    <select name="shoe_type_filter" id="shoe_type_filter" onchange="this.form.submit()">
      <option value="">All Shoes</option>
      <?php
        $types = ['Bulky', 'Slim', 'Basketball', 'Running', 'Slide', 'Classic'];
        foreach ($types as $type) {
          $selected = ($filter_type === $type) ? 'selected' : '';
          echo "<option value=\"$type\" $selected>$type</option>";
        }
      ?>
    </select>
  </form>

  <!-- Add Modal -->
  <div class="popup-modal" id="popup-modal" role="dialog" aria-modal="true" aria-labelledby="add-shoe-title">
    <div class="popup-modal-content">
      <span class="close-btn" id="close-btn" aria-label="Close Add Shoe">&times;</span>
      <h3 id="add-shoe-title">Add New Shoe</h3>
      <form class="shoe-form" method="POST" enctype="multipart/form-data" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <div class="form-group">
          <label for="Shoe_image">Upload Image</label>
          <input type="file" name="Shoe_image" id="Shoe_image" accept="image/*" required>
        </div>
        <div class="form-group">
          <label for="Shoe_type">Shoe Type</label>
          <select name="Shoe_type" id="Shoe_type" required>
            <option value="">Select Type</option>
            <?php foreach ($types as $type): ?>
              <option value="<?= $type ?>"><?= $type ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="Shoe_name">Shoe Name</label>
          <input type="text" name="Shoe_name" id="Shoe_name" placeholder="Shoe name" required>
        </div>
        <div class="form-group">
          <label>Sizes</label>
          <div class="size-inputs">
            <?php for ($i = 36; $i <= 45; $i++): ?>
              <div class="size-column">
                <label for="size-<?= $i ?>"><?= $i ?></label>
                <input type="number" name="size[<?= $i ?>]" id="size-<?= $i ?>" min="0" value="0">
              </div>
            <?php endfor; ?>
          </div>
        </div>
        <div class="form-group">
          <label for="Price">Price</label>
          <input type="number" name="Price" id="Price" placeholder="Price" required step="0.01" min="0">
        </div>
        <div class="form-actions">
          <button class="btn btn-add" type="submit">Add</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Modal -->
  <div class="popup-modal" id="edit-modal" role="dialog" aria-modal="true" aria-labelledby="edit-shoe-title">
    <div class="popup-modal-content">
      <span class="close-btn" id="close-btn-edit" aria-label="Close Edit Shoe">&times;</span>
      <h3 id="edit-shoe-title">Edit Shoe</h3>
      <form id="edit-form" class="shoe-form" method="POST" action="edit.php" enctype="multipart/form-data">
        <input type="hidden" name="id" id="edit-id">
        <input type="hidden" name="original_shoe_name" id="edit-original-name">
        <input type="hidden" name="shoe_type" id="edit-type">

        <div class="form-group">
          <label for="edit-name">New Shoe Name</label>
          <input type="text" name="new_shoe_name" id="edit-name" required>
        </div>

        <div class="form-group">
          <label for="edit-price">Price</label>
          <input type="number" name="price" id="edit-price" required step="0.01" min="0">
        </div>

        <div class="form-group">
          <label>Sizes</label>
          <div class="size-inputs">
            <?php for ($i = 36; $i <= 45; $i++): ?>
              <div class="size-column">
                <label for="edit-size-<?= $i ?>"><?= $i ?></label>
                <input type="number" name="size[<?= $i ?>]" id="edit-size-<?= $i ?>" min="0" value="0">
              </div>
            <?php endfor; ?>
          </div>
        </div>

        <div class="form-actions">
          <button class="btn btn-add" type="submit">Update Shoe</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Inventory Table -->
  <table>
    <thead>
      <tr>
        <th>Shoe Name</th>
        <?php for ($i = 36; $i <= 45; $i++) echo "<th>Size $i</th>"; ?>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if ($result && $result->num_rows > 0):
        while ($row = $result->fetch_assoc()):
          $sizes = [];
          for ($i = 36; $i <= 45; $i++) {
            $sizes[$i] = (int)$row["s$i"];
          }
          $sizes_json = htmlspecialchars(json_encode($sizes));
      ?>
      <tr>
        <td><strong><?= htmlspecialchars($row['shoe_name']) ?></strong></td>
        <?php for ($i = 36; $i <= 45; $i++): ?>
          <td><?= (int)$row["s$i"] ?></td>
        <?php endfor; ?>
        <td style="display: flex; gap: 5px;">
          <button
            type="button"
            class="btn btn-edit"
            onclick='openEditModal(
              "<?= htmlspecialchars($row['shoe_type']) ?>",
              "<?= htmlspecialchars($row['shoe_name']) ?>",
              <?= (int)$row['id'] ?>,
              <?= (float)$row['price'] ?>,
              <?= $sizes_json ?>
            )'>
            Edit
          </button>
          <button
            type="button"
            class="btn btn-delete"
            onclick="deleteShoe(<?= (int)$row['id'] ?>, '<?= htmlspecialchars($row['shoe_name']) ?>', '<?= htmlspecialchars($row['shoe_type']) ?>')">
            Delete
          </button>
        </td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="13">No inventory found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const addButton = document.getElementById('add-shoe-btn');
  const popupModal = document.getElementById('popup-modal');
  const closeButton = document.getElementById('close-btn');
  const inventoryTable = document.querySelector('table');

  addButton.addEventListener('click', () => {
    popupModal.style.display = 'flex';
    inventoryTable.style.display = 'none';
  });

  closeButton.addEventListener('click', () => {
    popupModal.style.display = 'none';
    inventoryTable.style.display = 'table';
    // Reset add form
    document.querySelector('#popup-modal form').reset();
  });

  // Edit modal close button
  document.getElementById('close-btn-edit').addEventListener('click', function () {
    document.getElementById('edit-modal').style.display = 'none';
  });
});

function openEditModal(shoeType, shoeName, shoeId, price, sizes) {
  const editModal = document.getElementById('edit-modal');
  editModal.style.display = 'flex';

  document.getElementById('edit-id').value = shoeId;
  document.getElementById('edit-type').value = shoeType;
  document.getElementById('edit-original-name').value = shoeName;

  document.getElementById('edit-name').value = shoeName;
  document.getElementById('edit-price').value = price;

  for (let i = 36; i <= 45; i++) {
    const sizeInput = document.getElementById(`edit-size-${i}`);
    if (sizeInput) {
      sizeInput.value = sizes[i] ?? 0;
    }
  }
}

function deleteShoe(shoeId, shoeName, shoeType) {
  const confirmation = confirm(`Are you sure you want to delete the shoe ${shoeName}?`);
  if (confirmation) {
    window.location.href = `delete.php?id=${shoeId}&shoe_name=${encodeURIComponent(shoeName)}&shoe_type=${encodeURIComponent(shoeType)}`;
  }
}
</script>

</body>
</html>

