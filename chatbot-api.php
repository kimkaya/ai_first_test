<?php
/**
 * 챗봇 API 프록시 - 단순화된 버전
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
    echo json_encode(['error' => 'Method not allowed. Use POST.', 'method' => $_SERVER['REQUEST_METHOD']]);
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
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
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

if (!isset($input['message']) || !isset($input['model'])) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Missing required parameters',
        'required' => ['message', 'model'],
        'received' => array_keys($input ?? [])
    ]);
    exit;
}

$message = $input['message'];
$model = $input['model'];

// 허용된 모델인지 확인
if (!in_array($model, ALLOWED_CHAT_MODELS)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid model',
        'provided' => $model,
        'allowed' => ALLOWED_CHAT_MODELS
    ]);
    exit;
}

// Hugging Face API 호출 (새로운 OpenAI 호환 형식)
$url = "https://router.huggingface.co/v1/chat/completions";

$data = [
    'model' => $model,
    'messages' => [
        [
            'role' => 'user',
            'content' => $message
        ]
    ],
    'max_tokens' => 250,
    'temperature' => 0.7,
    'top_p' => 0.95
];

// cURL 사용 (더 안정적)
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
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    $error = curl_error($ch);
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

// OpenAI 형식 응답을 구 형식으로 변환
$response = json_decode($result, true);

if (isset($response['choices'][0]['message']['content'])) {
    // OpenAI 형식의 응답을 구 Hugging Face 형식으로 변환
    echo json_encode([
        [
            'generated_text' => $response['choices'][0]['message']['content']
        ]
    ]);
} else {
    // 변환 실패 시 원본 응답 반환
    echo $result;
}
