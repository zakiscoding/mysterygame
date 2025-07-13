<?php
$scores = file_exists('scores.json')
    ? json_decode(file_get_contents('scores.json'), true)
    : [];

if (is_array($scores)) {
    usort($scores, fn($a, $b) => $b['score'] <=> $a['score']);
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Leaderboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h1>Global Leaderboard</h1>
    <table>
      <tr><th>Username</th><th>Score</th></tr>
      <?php if (!empty($scores)): foreach ($scores as $e): ?>
      <tr>
        <td><?= htmlspecialchars($e['username']) ?></td>
        <td><?= $e['score'] ?></td>
      </tr>
      <?php endforeach; else: ?>
      <tr><td colspan="2">No scores yet.</td></tr>
      <?php endif; ?>
    </table>
    <br>

    <form method="post" action="case.php" style="display:inline;">
      <button type="submit" name="next" class="start-btn">Back to Game</button>
    </form>

    <a href="index.php" class="start-btn">Back to Login</a>
  </div>
</body>
</html>
