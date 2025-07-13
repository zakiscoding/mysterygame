<?php
session_start();
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
    <h1>Welcome to Mystery Quest</h1>
    <form method="post" action="case.php">
      <label for="username">Enter your name:</label>
      <input type="text" id="username" name="username" required>
      <label for="level">Select difficulty:</label>
      <select id="level" name="level">
        <option value="Easy">Easy</option>
        <option value="Medium">Medium</option>
        <option value="Hard">Hard</option>
      </select>
      <input type="submit" value="Start Game" class="start-btn">
    </form>
  </div>
</body>
</html>
