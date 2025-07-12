<?php
  session_start();
  $difficulty = isset($_SESSION["level"]) ? ucfirst($_SESSION["level"]) : "Unknown";
  $mystery_text = "Unable to load mystery.";

  $response = @file_get_contents("http://localhost/api/gemini.php");
  if ($response !== false) {
    $data = json_decode($response, true);
    if (isset($data["candidates"][0]["content"]["parts"][0]["text"])) {
      $mystery_text = $data["candidates"][0]["content"]["parts"][0]["text"];
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Cryptic Quest - Case</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="container">
    <h1>🕵️ Case File - <?php echo $difficulty; ?> Mode</h1>
    <p class="intro"><?php echo nl2br(htmlspecialchars($mystery_text)); ?></p>

    <form action="deduction.php" method="post">
      <label for="answer">Your Deduction:</label><br/>
      <input type="text" name="answer" id="answer" required placeholder="Type your guess here" />
      <br/><br/>
      <input type="submit" class="start-btn" value="Submit Answer">
    </form>
  </div>
</body>
</html>
