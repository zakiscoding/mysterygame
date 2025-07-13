<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["next"])) {
    unset($_SESSION["mystery_text"], $_SESSION["correct_answer"]);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["username"])) {
    setcookie("username", $_POST["username"], time() + 86400 * 30, "/");
    $_COOKIE["username"] = $_POST["username"];
}
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["level"])) {
    $_SESSION["level"] = $_POST["level"];
}

$username   = $_COOKIE["username"] ?? "Guest";
$difficulty = $_SESSION["level"]      ?? "Easy";


if (!isset($_SESSION["mystery_text"], $_SESSION["correct_answer"])) {
    $api_key = "AIzaSyDgtLMw4rVs-OhpiZOYqawSzVCBPkF1VNw";
    $prompt  = "Write a short (about 200 word that each senetnce make sense with the previos one) mystery quest, use as much words u need to make it undertandable and leave clues in the propmt what the answer is but dont make it to long. Describe a disappearance, stolen object, or strange scene with clues that feel like part of a detective case. End the riddle with a question that fits naturally with the story — such as ‘Who took it?’, ‘Where did it go?’, or ‘Who left this behind?’ Make sure the question matches the mystery. make sure u give the choice in multple choice format is a, b, c, or d. make sure the choice matches the question. make sure the choices matches the prompt. take your time to generate this prompt itv has to be perfect evrytime. Please make the prompt make sense after ur done genrated it make sure it flows and makes sense with the answer check as much times u need and change it if u have to.At the end, make sure the answer is one of the choices then include the correct answer in this format: 'Answer: ___'";
    unset($_SESSION["score_saved"]);

    $data = [
        "contents" => [
            [ "parts" => [["text" => $prompt]] ]
        ]
    ];
    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-pro:generateContent?key={$api_key}";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS     => json_encode($data)
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);

    if ($resp !== false) {
        $res  = json_decode($resp, true);
        $text = $res["candidates"][0]["content"]["parts"][0]["text"] ?? "Mystery failed.";

        if (preg_match("/Answer:\\s*(.*)/i", $text, $m)) {
            $_SESSION["correct_answer"] = trim($m[1]);
            $_SESSION["mystery_text"]   = trim(str_replace($m[0], '', $text));
        } else {
            $_SESSION["correct_answer"] = "Unknown";
            $_SESSION["mystery_text"]   = $text;
        }
    }
}

$mystery_text   = $_SESSION["mystery_text"]   ?? "Mystery failed to load.";
$correct_answer = $_SESSION["correct_answer"] ?? "Unknown";

$user_answer = $_POST["answer"] ?? null;
$tries       = (int)($_COOKIE["tries"] ?? 0);
$message     = "";
$show_answer = false;


if ($user_answer !== null) {
    $is_correct = strtolower(trim($user_answer)) === strtolower($correct_answer);

    if ($is_correct) {
        $message = "Correct! You solved the mystery!";
        setcookie("tries", 0, time()+86400, "/");

        if (!($_SESSION["score_saved"] ?? false)) {
            $points_map = ["easy"=>5,"medium"=>10,"hard"=>15];
            $points     = $points_map[strtolower($difficulty)] ?? 5;

            $file   = 'scores.json';
            $scores = [];
            if (file_exists($file)) {
                $d = json_decode(file_get_contents($file), true);
                if (is_array($d)) $scores = $d;
            }

            $clean_name = strtolower(trim($username));
            $user_found = false;
            foreach ($scores as &$entry) {
                if (strtolower(trim($entry["username"])) === $clean_name) {
                    $entry["score"] += $points;
                    $user_found = true;
                    break;
                }
            }
            unset($entry);

            if (!$user_found && $username !== "Guest") {
                $scores[] = ["username"=>$username,"score"=>$points];
            }

            file_put_contents($file, json_encode($scores, JSON_PRETTY_PRINT));
            $_SESSION["score_saved"] = true;
        }

        unset($_SESSION["mystery_text"], $_SESSION["correct_answer"]);
    } else {
        $tries++;
        setcookie("tries", $tries, time()+86400, "/");
        if ($tries >= 3) {
            $message     = "You've reached 3 tries. The correct answer was: <strong>" . htmlspecialchars($correct_answer) . "</strong>";
            $show_answer = true;
            setcookie("tries", 0, time()+86400, "/");
            unset($_SESSION["mystery_text"], $_SESSION["correct_answer"]);
        } else {
            $remaining = 3 - $tries;
            $message   = "Incorrect. Try again. ($remaining attempt(s) left)";
        }
    }
}
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
    <h1>Mystery Case – <?=htmlspecialchars($difficulty)?> Mode</h1>
    <p><?=nl2br(htmlspecialchars($mystery_text))?></p>

    <?php if($message): ?>
      <p><?=$message?></p>
    <?php endif; ?>

    <?php if(!$show_answer): ?>
      <form method="post">
        <label for="answer">Your Answer (Enter a, b, c, d):</label>
        <input type="text" name="answer" id="answer" required>
        <input type="submit" value="Submit Answer" class="start-btn">
      </form>
    <?php endif; ?>

    <br>

    <form method="post" style="display:inline;">
      <button type="submit" name="next" class="start-btn">Next</button>
    </form>

    <a href="leaderboard.php" class="start-btn">View Leaderboard</a>
  </div>
</body>
</html>
