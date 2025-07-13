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

    $difficulty = strtolower($_SESSION["level"] ?? "easy");
    switch ($difficulty) {
        case "easy":
            $prompt = "Write a short (about 200 words, each sentence making sense with the previous one) mystery quest. It should be beginner-friendly and very clear. Use a simple situation like a lost or stolen object. Leave easy-to-spot clues. Format the ending as a natural question like 'Who took it?' or 'Where did it go?' followed by multiple-choice options a, b, c, or d. The answer should be obvious for an attentive reader. End the story with 'Answer: ___'. Make sure the mystery flows and makes sense with the answer.";
            break;
        case "medium":
            $prompt = "Write a medium-difficulty (about 200 words) mystery quest with logical flow. Describe a situation involving a theft, strange scene, or disappearance. Leave multiple clues and small distractions, but the answer should still be solvable. Use a story structure where the reader can deduce the answer. End with a natural question matching the case and four multiple-choice answers (a, b, c, d). Ensure the correct answer is one of the choices and end with 'Answer: ___'.";
            break;
        case "hard":
            $prompt = "Write a complex, well-structured (about 200 words or more if needed) mystery quest. The story should have subtle clues, red herrings, and high reasoning. Involve a deep case like a hidden betrayal, clever misdirection, or a sophisticated theft. Make the question at the end blend naturally into the story — such as 'Who was behind it?' or 'What explains the mystery?' Include four realistic multiple-choice answers (a, b, c, d), and end with 'Answer: ___'. The mystery must make sense, flow well, and the answer must logically match the story.";
            break;
        default:
            $prompt = "Write a general mystery story involving a theft or disappearance with clues, and provide a multiple-choice question and answer at the end. End with 'Answer: ___'.";
    }

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
$is_correct  = false;

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
    <h1>Mystery Case – <?=htmlspecialchars(ucfirst($difficulty))?> Mode</h1>
    <p><?=nl2br(htmlspecialchars($mystery_text))?></p>

    <?php if($message): ?>
      <p class="<?= $is_correct ? 'feedback-correct' : 'feedback-wrong' ?>">
        <?=$message?>
      </p>
    <?php endif; ?>

    <?php if(!$show_answer && !$is_correct): ?>
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
