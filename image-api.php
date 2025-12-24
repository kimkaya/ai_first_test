<?php
/**
 * 이미지 생성 API 프록시
 * 클라이언트 요청을 받아 Hugging Face Stable Diffusion API를 호출합니다
 */

// CORS 헤더 설정 (로컬 파일에서 접근 허용)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// OPTIONS 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// 오류 처리 활성화 (개발 중)
error_reporting(E_ALL);
ini_set('display_errors', 0); // 브라우저에는 표시 안 함

// config.php 불러오기
try {
    define('API_ACCESS', true);
    require_once 'config.php';
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Configuration error',
        'details' => $e->getMessage()
    ]);
    exit;
}

// 요청 데이터 받기
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['prompt']) || !isset($input['model'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

$prompt = $input['prompt'];
$model = $input['model'];

// 허용된 모델인지 확인
if (!in_array($model, ALLOWED_IMAGE_MODELS)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid model']);
    exit;
}

// Hugging Face API 호출 (새 엔드포인트)
$url = "https://router.huggingface.co/models/" . $model;

$data = [
    'inputs' => $prompt,
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
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

if (curl_errno($ch)) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Failed to connect to AI service',
        'details' => curl_error($ch)
    ]);
    exit;
}

// 에러 응답 처리
if ($httpCode !== 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo $response;
    exit;
}

// 이미지 데이터 반환
if (strpos($contentType, 'image') !== false) {
    header('Content-Type: ' . $contentType);
    echo $response;
} else {
    // JSON 에러 응답
    header('Content-Type: application/json');
    echo $response;
}
