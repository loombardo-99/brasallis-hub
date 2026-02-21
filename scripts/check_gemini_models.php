<?php
// scripts/check_gemini_models.php
require_once __DIR__ . '/../includes/db_config.php';

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    
    // Get API Key (Assuming ID 1 for test, or user input)
    // We'll just grab the first one found
    $stmt = $conn->query("SELECT gemini_api_key FROM empresas LIMIT 1");
    $apiKey = $stmt->fetchColumn();
    
    if (!$apiKey) {
        $apiKey = getenv('GEMINI_API_KEY_GLOBAL');
    }

    if (!$apiKey) die("No API Key found.\n");

    echo "Using API Key: " . substr($apiKey, 0, 5) . "...\n";

    $url = "https://generativelanguage.googleapis.com/v1beta/models?key=$apiKey";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    $output = "";
    if (isset($data['models'])) {
        $output .= "Available Models:\n";
        foreach ($data['models'] as $model) {
            if (strpos($model['name'], 'gemini') !== false) {
                 $output .= "- " . $model['name'] . " (" . implode(', ', $model['supportedGenerationMethods']) . ")\n";
            }
        }
    } else {
        $output .= "Error: " . json_encode($data, JSON_PRETTY_PRINT);
    }
    
    file_put_contents(__DIR__ . '/../models_list_utf8.txt', $output);
    echo "Written to models_list_utf8.txt";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
