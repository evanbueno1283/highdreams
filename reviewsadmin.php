<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Reviews</title>
  <style>
<style>
/* Global Reset */
html, body {
    overflow-x: hidden;
    max-width: 100vw;
}

/* Base Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    padding: 20px 0 40px 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    overflow-x: hidden; /* Prevent scroll globally */
    box-sizing: border-box;
}

* {
    box-sizing: border-box; /* Ensure all elements follow border-box */
}

/* HEADER */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
    background-color: #000000ff;
    color: white;
    height: auto;
    min-height: 80px;
    width: 100%;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    flex-wrap: wrap;
    box-sizing: border-box;
}

/* Logos */
.logo-container {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    max-width: 100%; /* prevent overflow */
}

.logo img,
.second-logo img {
    height: 50px;
    max-width: 100%;
    box-shadow: 0 2px 10px rgb(255, 255, 255);
    object-fit: contain;
}

/* Buttons */
.user-options {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 10px;
    justify-content: center;
    max-width: 100%; /* prevent overflow */
}

.user-options button {
    padding: 10px 16px;
    background-color: #ffffff;
    color: #000000;
    border: none;
    cursor: pointer;
    border-radius: 20px;
    font-weight: bold;
    font-size: 14px;
    white-space: nowrap; /* prevent button text wrap */
}

.user-options button:hover {
    background-color: #eaeaea;
}

/* Home Icon */
.home-logo img {
    height: 28px;
    width: 28px;
    transition: transform 0.3s ease-in-out;
    cursor: pointer;
}

.home-logo img:hover {
    transform: scale(1.1);
}

/* Reviews Container */
.reviews-container {
    background: transparent;
    padding: 25px 10px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
    max-width: 100vw;
    width: 100%;
    margin: 100px 10px 0 10px; /* horizontal margin to prevent edge overflow */
    overflow-x: hidden;
    box-sizing: border-box;
}

/* Centered Title */
.reviews-container h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #222;
    font-size: 24px;
    word-wrap: break-word; /* prevent overflow */
}

/* Table Responsive Wrapper */
.table-responsive {
    width: 100%;
    overflow-x: hidden; /* prevent scroll */
    -webkit-overflow-scrolling: touch;
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 16px;
    table-layout: fixed; /* fixed layout to prevent overflow */
    word-wrap: break-word; /* break long text */
}

thead {
    background-color: #222;
    color: #fff;
}

th, td {
    padding: 12px 10px;
    border: 1px solid #ddd;
    text-align: left;
    vertical-align: top;
    word-break: break-word; /* wrap long words */
}

/* Alternate rows */
tbody tr:nth-child(even) {
    background-color: #f5f5f5;
}

/* Hover effect */
tbody tr:hover {
    background-color: #e1f0ff;
}

/* ===============================
    ✅ RESPONSIVE STYLES BELOW
   =============================== */

/* LAPTOP & BELOW */
@media (max-width: 992px) {
    .reviews-container {
        padding: 20px 10px;
        margin-top: 120px;
    }

    .user-options button {
        padding: 8px 14px;
        font-size: 14px;
    }

    .reviews-container h2 {
        font-size: 22px;
    }
}

/* TABLETS */
@media (max-width: 768px) {
    .header {
        flex-direction: column;
        padding: 15px 10px;
        text-align: center;
    }

    .logo-container,
    .user-options {
        justify-content: center;
        margin-top: 10px;
        flex-wrap: wrap;
        max-width: 100%;
    }

    .reviews-container {
        margin-top: 140px;
    }

    .user-options button {
        font-size: 13px;
    }

    table {
        font-size: 14px;
    }

    th, td {
        padding: 10px 8px;
    }
}

/* MOBILE PHONES */
@media (max-width: 480px) {
    .user-options button {
        font-size: 12px;
        padding: 6px 10px;
        white-space: normal; /* allow wrap on very small buttons */
    }

    .reviews-container h2 {
        font-size: 20px;
    }

    table {
        font-size: 13px;
    }

    th, td {
        padding: 8px 6px;
    }

    .logo img {
        height: 40px;
    }

    .home-logo img {
        height: 24px;
        width: 24px;
    }

    .reviews-container {
        padding: 15px 8px;
        margin-top: 150px;
    }
}

/* SMALL PHONES - 320px */
@media (max-width: 320px) {
    .header {
        padding: 10px 8px;
        flex-direction: column;
    }

    .logo-container,
    .user-options {
        justify-content: center;
        max-width: 100%;
        margin-top: 5px;
    }

    .user-options {
        flex-direction: column;
        gap: 8px;
        margin-top: 5px;
    }

    .user-options button {
        font-size: 11px;
        padding: 5px 8px;
        width: 100%;
        max-width: none;
        white-space: normal; /* allow wrap */
    }

    .reviews-container h2 {
        font-size: 18px;
    }

    table {
        font-size: 12px;
        table-layout: fixed; /* keep fixed layout */
    }

    th, td {
        padding: 6px 4px;
    }
}
</style>




</head>
<body>

<header class="header">
  <div class="logo-container">
    <div class="logo">
      <img src="image/logo1.png" alt="Shoe Store Logo" />
    </div>
    <div class="second-logo">
      <img src="image/hdb2.png" alt="Second Logo" />
    </div>
  </div>

  <div class="user-options">
    <a href="admin.php" class="home-logo">
      <img src="image/home.png" alt="Home Logo" />
    </a>
    <button id="logout-button">Logout</button>
  </div>
</header>

<div class="reviews-container">
  <h2>Customer Reviews</h2>
  <table>
    <thead>
      <tr>
        <th>Email</th>
        <th>Rating</th>
        <th>Comment</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php
      // Database connection settings
      $host = "localhost";        
      $username = "root";         
      $password = ""; 
      $dbname = "fitscan_database";

      // Create connection
      $conn = new mysqli($host, $username, $password, $dbname);

      // Check connection
      if ($conn->connect_error) {
        die("<tr><td colspan='4'>Connection failed: " . $conn->connect_error . "</td></tr>");
      }

      // Query to get reviews ordered by date (newest first)
      $sql = "SELECT email, rating, comment, created_at FROM user_reviews ORDER BY created_at DESC";
      $result = $conn->query($sql);

      if ($result && $result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
          // sanitize output to prevent XSS
          $email = htmlspecialchars($row['email']);
          $ratingNumber = (int)$row['rating']; // convert rating to integer
          $comment = htmlspecialchars($row['comment']);
          $date = htmlspecialchars($row['created_at']);

          // Generate star rating (max 5 stars)
          $maxStars = 5;
          $stars = str_repeat('★', $ratingNumber) . str_repeat('☆', $maxStars - $ratingNumber);

          echo "<tr>
                  <td>$email</td>
                  <td style='color: #f39c12; font-size: 18px;'>$stars</td>
                  <td>$comment</td>
                  <td>$date</td>
                </tr>";
        }
      } else {
        echo "<tr><td colspan='4' style='text-align:center;'>No reviews found.</td></tr>";
      }

      $conn->close();
      ?>
    </tbody>
  </table>
</div>
</body>
</html>