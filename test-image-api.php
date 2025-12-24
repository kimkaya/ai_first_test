<?php
// 이미지 API 테스트
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('API_ACCESS', true);
require_once 'config.php';

echo "=== 이미지 생성 API 테스트 ===\n\n";

$model = 'stabilityai/stable-diffusion-2-1';
$url = "https://router.huggingface.co/models/" . $model;

$data = [
    'inputs' => 'a cute cat',
    'options' => [
        'wait_for_model' => true
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
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

echo "API 호출 중 (최대 60초)...\n\n";
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

echo "=== 응답 ===\n";
echo "HTTP 코드: $httpCode\n";
echo "Content-Type: $contentType\n\n";

if (curl_errno($ch)) {
    echo "cURL 오류: " . curl_error($ch) . "\n";
} else {
    if ($httpCode == 200 && strpos($contentType, 'image') !== false) {
        echo "✓ 이미지 생성 성공!\n";
        echo "이미지 크기: " . strlen($result) . " bytes\n";

        // 이미지를 파일로 저장
        $filename = 'test-generated-image.png';
        file_put_contents($filename, $result);
        echo "✓ 이미지 저장됨: $filename\n";
    } else {
        $decoded = json_decode($result, true);
        if ($decoded) {
            echo "JSON 응답:\n";
            print_r($decoded);
        } else {
            echo "응답 (처음 500자):\n" . substr($result, 0, 500) . "\n";
        }
    }
}
