<?php

$url = 'https://graph.facebook.com/v22.0/863152823546123/messages';
$token = 'EAAL8ZAKOhgmMBP41AEaN0Ukumu5vWIleqcOG8sJo4chGbJjAYM2A49TAK6il6p58GPVEitO4ms0YOVULcbASYLCerlrqni7vwk6IA2BdjV3dXXwN6TCXvdE9qO08cFPKsOqTZBQMETgrziQYg4xFcAVm3OGRl8R6v8pQLlZCapViNMBWCCirK4OZCinNBlL2fvGiUM6KXjUoP3aT8LoZCTeLQ0v87BH78mMYZBZBTY4aUg3tNkQ2iXtpIn4bRUm0jEkZBGqks5kHQ1qT4gvxLaXbhgZDZD';
$data = [
    "messaging_product" => "whatsapp",
    "to" => "5581996170430",
    "type" => "template",
    "template" => [
        "name" => "hello_world",
        "language" => ["code" => "en_US"]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bypass SSL check for local dev // Enable verbose output to stderr

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response Body: '" . $response . "'\n";
if ($error) {
    echo "Curl Error: " . $error . "\n";
}
