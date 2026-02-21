<?php
require_once 'includes/db_config.php';

$conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
$stmt = $conn->query("SELECT gemini_api_key FROM empresas LIMIT 1");
$apiKey = $stmt->fetchColumn();

if (!$apiKey) die("No API Key found in DB");

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=$apiKey";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$output = "";
if (isset($data['models'])) {
    foreach ($data['models'] as $m) {
        if (strpos($m['name'], 'gemini') !== false) {
            $output .= $m['name'] . "\n";
        }
    }
} else {
    $output = "Error: " . $response;
}
file_put_contents('gemini_models_list.txt', $output);
echo "Saved to gemini_models_list.txt";
