<?php
  session_start();
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION["level"] = $_POST["level"];
    header("Location: case.php");
    exit();
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Cryptic Quest - Home</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="container">
    <h1>🔍 Welcome to Cryptic Quest</h1>
    <p class="intro">Use your logic to solve mysterious cases. Choose your difficulty and begin the quest.</p>

    <form action="index.php" method="post">
      <div class="difficulty">
        <label for="level">Select Difficulty:</label>
        <select name="level" id="level" required>
          <option value="easy">Easy</option>
          <option value="medium">Medium</option>
          <option value="hard">Hard</option>
        </select>
      </div>

      <div class="mystery-prompt">
        <p><strong>Today's Mystery:</strong> A priceless painting has vanished from the city museum. Can you solve it?</p>
      </div>

      <input type="submit" class="start-btn" value="▶️ Start Game">
    </form>
  </div>
</body>
</html>
