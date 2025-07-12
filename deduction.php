<?php
  session_start();
  $difficulty = $_SESSION["level"] ?? "Unknown";
  $answer = $_POST["answer"] ?? "None";
  $username = "Guest";

  $data = [
    "username" => $username,
    "difficulty" => $difficulty,
    "answer" => $answer
  ];

  $options = [
    "http" => [
      "header" => "Content-Type: application/json",
      "method" => "POST",
      "content" => json_encode($data)
    ]
  ];

  $context = stream_context_create($options);
  $result = @file_get_contents("http://localhost/api/store_answer.php", false, $context);

  echo "<!DOCTYPE html><html><head><title>Result</title><link rel='stylesheet' href='style.css'></head><body><div class='container'>";
  echo "<h1>🔍 Your Deduction</h1>";
  echo "<p><strong>Answer:</strong> " . htmlspecialchars($answer) . "</p>";
  echo "<p>Your response has been recorded. Thank you for playing!</p>";
  echo "<a href='index.php' class='start-btn'>🔁 Play Again</a>";
  echo "</div></body></html>";
?>
