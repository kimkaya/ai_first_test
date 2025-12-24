<?php
// 새 API 테스트
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('API_ACCESS', true);
require_once 'config.php';

echo "=== 새 Hugging Face API 테스트 ===\n\n";

$testModel = 'Qwen/Qwen2.5-72B-Instruct:fastest';
$url = "https://router.huggingface.co/v1/chat/completions";

$data = [
    'model' => $testModel,
    'messages' => [
        [
            'role' => 'user',
            'content' => 'Hello! Say hi in one sentence.'
        ]
    ],
    'max_tokens' => 50,
    'temperature' => 0.7
];

echo "모델: $testModel\n";
echo "URL: $url\n";
echo "요청 데이터:\n";
print_r($data);
echo "\n";

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

echo "API 호출 중...\n\n";
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "=== 응답 ===\n";
echo "HTTP 코드: $httpCode\n\n";

if (curl_errno($ch)) {
    echo "cURL 오류: " . curl_error($ch) . "\n";
} else {
    $decoded = json_decode($result, true);
    if ($decoded) {
        echo "JSON 응답:\n";
        print_r($decoded);

        if (isset($decoded['choices'][0]['message']['content'])) {
            echo "\n✓ 생성된 텍스트: " . $decoded['choices'][0]['message']['content'] . "\n";
        }
    } else {
        echo "원본 응답:\n$result\n";
    }
}
