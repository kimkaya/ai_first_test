<?php
// 실제 API 호출 테스트
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('API_ACCESS', true);
require_once 'config.php';

echo "=== Hugging Face API 연결 테스트 ===\n\n";

// 챗봇 API 테스트
$model = 'mistralai/Mixtral-8x7B-Instruct-v0.1';
$url = "https://router.huggingface.co/models/" . $model;

$data = [
    'inputs' => 'Hello, how are you?',
    'parameters' => [
        'max_new_tokens' => 50,
        'temperature' => 0.7,
        'return_full_text' => false
    ]
];

echo "모델: $model\n";
echo "URL: $url\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . HUGGINGFACE_API_KEY
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

echo "API 호출 중...\n";
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

echo "\n=== 응답 결과 ===\n";
echo "HTTP 코드: $httpCode\n";

if ($error) {
    echo "cURL 오류: $error\n";
} else {
    echo "응답:\n";
    $decoded = json_decode($result, true);
    if ($decoded) {
        print_r($decoded);
    } else {
        echo $result;
    }
}

curl_close($ch);
