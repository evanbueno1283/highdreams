


<?php
session_start();

if (!isset($_SESSION['user']['id'])) {
  header("Location: login.php");
  exit();
}

$mysqli = new mysqli("localhost", "root", "", "fitscan_database");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$user_id = $_SESSION['user']['id'];
$result = $mysqli->query("SELECT * FROM users WHERE id = $user_id");
$user = $result->fetch_assoc();
?>




<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" href="image/logo1.png" type="image/png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Account Information</title>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background-image: url('image/logo3.jpeg');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      background-repeat: no-repeat;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .container {
      background: rgba(255, 255, 255, 0.2);
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      width: 90%;
      max-width: 700px;
      overflow-x: auto;
      border: 1px solid rgba(255, 255, 255, 0.3);
    }

    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #000;
      font-size: 28px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      color: #fff;
    }

    th, td {
      padding: 12px 15px;
      border: 1px solid rgba(255, 255, 255, 0.3);
    }

    th {
      background-color: rgba(0, 0, 0, 0.8);
      color: #fff;
      text-align: left;
    }

    tr:nth-child(even) {
      background-color: rgba(255, 255, 255, 0.1);
    }

    .btn {
      margin-top: 20px;
      padding: 12px 25px;
      background-color: #000;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
      display: block;
      margin-left: auto;
      margin-right: auto;
      transition: background: 0.3s ease;
    }

    .btn:hover {
      background-color: #333;
    }

    input[type="text"] {
      width: 100%;
      padding: 8px;
      border: none;
      border-radius: 5px;
    }

    @media screen and (max-width: 600px) {
      th, td {
        font-size: 14px;
      }

      h2 {
        font-size: 24px;
      }

      .btn {
        font-size: 14px;
      }
    }

    #toast {
  visibility: hidden;
  min-width: 200px;
  margin-left: -100px;
  background-color: #28a745;
  color: white;
  text-align: center;
  border-radius: 8px;
  padding: 12px;
  position: fixed;
  z-index: 1;
  left: 50%;
  top: 30px;
  font-size: 16px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

#toast.show {
  visibility: visible;
  animation: fadein 0.5s, fadeout 0.5s 2.5s;
}

@keyframes fadein {
  from { top: 0; opacity: 0; }
  to { top: 30px; opacity: 1; }
}

@keyframes fadeout {
  from { top: 30px; opacity: 1; }
  to { top: 0; opacity: 0; }
}

  </style>
</head>
<body>

<div id="toast">Changes saved successfully!</div>
  <div class="container">
    <h2>Account Information</h2>
    <table>
      <tr>
        <th>Username</th>
        <td>
  <span class="text"><?php echo htmlspecialchars($user['username']); ?></span>
  <input type="text" class="input" style="display:none;" value="<?php echo htmlspecialchars($user['username']); ?>">
</td>

      </tr>
      <tr>
        <th>Contact Number</th>
        <td>
        <span class="text"><?php echo htmlspecialchars($user['phone']); ?></span>
  <input type="text" class="input" style="display:none;" value="<?php echo htmlspecialchars($user['phone']); ?>">
</td>

      </tr>
      <tr>
        <th>Email</th>
        <td><span class="text"><?php echo htmlspecialchars($user['email']); ?></span>
        <input type="text" class="input" style="display:none;" value="<?php echo htmlspecialchars($user['email']); ?>"></td>
      </tr>
      <tr>
        <th>Gender</th>
        <td><span class="text"><?php echo htmlspecialchars($user['gender']); ?></span>
        <input type="text" class="input" style="display:none;" value="<?php echo htmlspecialchars($user['gender']); ?>"></td>
      </tr>
      <tr>
        <th>Province</th>
        <td><span class="text"><?php echo htmlspecialchars($user['province']); ?></span>
        <input type="text" class="input" style="display:none;" value="<?php echo htmlspecialchars($user['province']); ?>"></td>
      </tr>
      <tr>
        <th>Municipality</th>
        <td><span class="text"><?php echo htmlspecialchars($user['municipality']); ?></span>
        <input type="text" class="input" style="display:none;" value="<?php echo htmlspecialchars($user['municipality']); ?>"></td>
      </tr>
      <tr>
        <th>Barangay</th>
        <td><span class="text"><?php echo htmlspecialchars($user['barangay']); ?></span>
        <input type="text" class="input" style="display:none;" value="<?php echo htmlspecialchars($user['barangay']); ?>"></td>
      </tr>
      <tr>
        <th>Street</th>
        <td><span class="text"><?php echo htmlspecialchars($user['street']); ?></span>
        <input type="text" class="input" style="display:none;" value="<?php echo htmlspecialchars($user['street']); ?>"></td>
      </tr>
    </table>

    <button class="btn" id="editBtn" onclick="toggleEdit()">Edit</button>
    <button class="btn" onclick="window.location.href='home.php'">Home</button>
  </div>

  


  <script>
  let isEditing = false;

  function showToast() {
    const toast = document.getElementById("toast");
    toast.className = "show";
    setTimeout(() => {
      toast.className = toast.className.replace("show", "");
    }, 3000);
  }

  function toggleEdit() {
    const texts = document.querySelectorAll('.text');
    const inputs = document.querySelectorAll('.input');
    const button = document.getElementById('editBtn');

    if (!isEditing) {
      // Enable edit mode
      texts.forEach(t => t.style.display = 'none');
      inputs.forEach(i => i.style.display = 'block');
      button.textContent = 'Save';
    } else {
      // Save changes
      const data = {
        username: inputs[0].value,
        phone: inputs[1].value,
        email: inputs[2].value,
        gender: inputs[3].value,
        province: inputs[4].value,
        municipality: inputs[5].value,
        barangay: inputs[6].value,
        street: inputs[7].value
      };

      fetch('update_user.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
      })
      .then(res => {
        if (!res.ok) throw new Error("Update failed");
        return res.text();
      })
      .then(response => {
        console.log("Updated:", response);
        texts.forEach((t, index) => {
          const newValue = inputs[index].value;
          t.textContent = newValue;
          t.style.display = 'inline';
          inputs[index].style.display = 'none';
        });
        button.textContent = 'Edit';
        isEditing = false;
        showToast();

        // Optional: auto-refresh after 3 seconds
        setTimeout(() => {
          location.reload();
        }, 3000);
      })
      .catch(err => {
        alert("Error: " + err.message);
      });

      return;
    }

    isEditing = true;
  }
</script>


</body>
</html>
