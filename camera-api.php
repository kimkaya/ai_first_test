<?php
/**
 * 카메라 이미지 인식 API
 * 이미지를 받아서 Vision 모델로 분석
 */

// 모든 오류 표시
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS 헤더 설정
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// OPTIONS 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit;
}

// config.php 불러오기
try {
    if (!defined('API_ACCESS')) {
        define('API_ACCESS', true);
    }
    require_once __DIR__ . '/config.php';
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Configuration error',
        'message' => $e->getMessage()
    ]);
    exit;
}

// 요청 데이터 받기
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid JSON',
        'json_error' => json_last_error_msg()
    ]);
    exit;
}

if (!isset($input['image']) || !isset($input['model'])) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Missing required parameters',
        'required' => ['image', 'model']
    ]);
    exit;
}

$imageData = $input['image'];
$model = $input['model'];

// 허용된 모델인지 확인
if (!in_array($model, ALLOWED_VISION_MODELS)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid model',
        'provided' => $model,
        'allowed' => ALLOWED_VISION_MODELS
    ]);
    exit;
}

// base64 이미지 데이터 처리
// data:image/png;base64, 부분 제거
if (strpos($imageData, 'data:image') === 0) {
    $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
}

// base64 디코딩
$imageBytes = base64_decode($imageData);

if ($imageBytes === false) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid base64 image data']);
    exit;
}

// 임시 파일로 저장
$tempFile = tempnam(sys_get_temp_dir(), 'camera_');
file_put_contents($tempFile, $imageBytes);

// Hugging Face Inference API 호출
$url = "https://api-inference.huggingface.co/models/{$model}";

// cURL로 이미지 전송
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $imageBytes);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . HUGGINGFACE_API_KEY,
    'Content-Type: application/octet-stream'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// 임시 파일 삭제
unlink($tempFile);

if (curl_errno($ch)) {
    $error = curl_error($ch);
    curl_close($ch);
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to connect to AI service',
        'details' => $error
    ]);
    exit;
}

curl_close($ch);

// HTTP 응답 코드 확인
if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo $result;
    exit;
}

// 응답 반환
echo $result;
