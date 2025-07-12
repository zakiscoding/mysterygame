<?php
$api_key = "AIzaSyC6aSx8nIJWuSdUgbvyE-HLcmRmpjzdBiM";
$prompt = "Create a new mystery story involving a stolen artifact.";

$data = [
  "contents" => [
    [
      "parts" => [["text" => $prompt]]
    ]
  ]
];

$curl = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $api_key);
curl_setopt_array($curl, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
  CURLOPT_POSTFIELDS => json_encode($data)
]);

$response = curl_exec($curl);
curl_close($curl);

header("Content-Type: application/json");
echo $response;
?>
