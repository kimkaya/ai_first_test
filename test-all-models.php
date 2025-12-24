<?php
// 모든 허용된 모델 테스트
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('API_ACCESS', true);
require_once 'config.php';

echo "=== 모든 챗봇 모델 테스트 ===\n\n";

foreach (ALLOWED_CHAT_MODELS as $model) {
    echo "테스트 중: $model\n";

    $url = "https://router.huggingface.co/models/" . $model;

    $data = [
        'inputs' => 'Hello',
        'parameters' => [
            'max_new_tokens' => 50,
            'temperature' => 0.7,
            'return_full_text' => false
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . HUGGINGFACE_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    echo "  HTTP 코드: $httpCode\n";

    if ($httpCode == 200) {
        echo "  ✓ 성공!\n";
        $decoded = json_decode($result, true);
        if ($decoded) {
            echo "  응답: " . print_r($decoded, true) . "\n";
        }
    } else {
        $decoded = json_decode($result, true);
        if ($decoded && isset($decoded['error'])) {
            echo "  ✗ 오류: " . $decoded['error'] . "\n";
        } else {
            echo "  ✗ 응답: " . substr($result, 0, 200) . "\n";
        }
    }

    echo "\n";
}

echo "\n=== 이미지 모델 테스트 (첫 번째만) ===\n\n";
$imageModel = ALLOWED_IMAGE_MODELS[0];
echo "테스트 중: $imageModel\n";

$url = "https://router.huggingface.co/models/" . $imageModel;
$data = [
    'inputs' => 'a cute cat',
    'options' => [
        'wait_for_model' => true
    ]
];

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

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

echo "  HTTP 코드: $httpCode\n";
echo "  Content-Type: $contentType\n";

if ($httpCode == 200 && strpos($contentType, 'image') !== false) {
    echo "  ✓ 이미지 생성 성공! (바이너리 데이터 받음)\n";
} else {
    $decoded = json_decode($result, true);
    if ($decoded && isset($decoded['error'])) {
        echo "  ✗ 오류: " . $decoded['error'] . "\n";
    } else {
        echo "  ✗ 응답: " . substr($result, 0, 200) . "\n";
    }
}
