<?php
$data = json_decode(file_get_contents("php://input"), true);

$url = "https://yfbzdswgnfazaqiypbre.supabase.co/rest/v1/answers";
$apiKey = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlmYnpkc3dnbmZhemFxaXlwYnJlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTIyOTkyMTksImV4cCI6MjA2Nzg3NTIxOX0.z5ber40oSX_BHlAcgMCK6KBXHZmUMpb8UKzu7cz8VcU";

$payload = json_encode([
  "username" => $data["username"] ?? "Guest",
  "difficulty" => $data["difficulty"],
  "answer" => $data["answer"],
  "timestamp" => date("c")
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_HTTPHEADER => [
    "Content-Type: application/json",
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey",
    "Prefer: return=representation"
  ],
  CURLOPT_POSTFIELDS => $payload
]);

$response = curl_exec($ch);
curl_close($ch);

header("Content-Type: application/json");
echo $response;
?>
