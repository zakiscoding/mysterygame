<?php
session_start();
$username = $_COOKIE["username"] ?? "";
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Cryptic Quest</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h1>Welcome to Cryptic Quest</h1>

    <form method="post" action="case.php">
      <label for="username">Enter your name:</label>
      <input type="text" id="username" name="username" value="<?=htmlspecialchars($username)?>" required>

      <label for="level">Select difficulty:</label>
      <select id="level" name="level" required>
        <option value="easy">Easy</option>
        <option value="medium">Medium</option>
        <option value="hard">Hard</option>
      </select>

      <input type="submit" value="Start Game" class="start-btn">
    </form>

    <br>
    <a href="leaderboard.php" class="start-btn">View Leaderboard</a>
  </div>
</body>
</html>
