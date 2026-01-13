<?php
// check_models.php
$apiKey = 'AIzaSyBMyF0jPmSrfwFmRjzForzCDryoTCfQjM4'; 
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=$apiKey";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$result = curl_exec($ch);
curl_close($ch);

$data = json_decode($result, true);

echo "<h1>Available Models for your Key:</h1>";
echo "<pre>";
if (isset($data['models'])) {
    foreach ($data['models'] as $model) {
        // We only care about models that support "generateContent"
        if (in_array("generateContent", $model['supportedGenerationMethods'])) {
            echo "Name: " . $model['name'] . "\n";
        }
    }
} else {
    print_r($data);
}
echo "</pre>";
?>