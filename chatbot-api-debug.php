<?php
// 모든 오류 표시
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS 헤더
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// 로그 함수
function debug_log($step, $data) {
    file_put_contents(__DIR__ . '/debug.log',
        date('Y-m-d H:i:s') . " - Step $step: " . json_encode($data) . "\n",
        FILE_APPEND
    );
}

debug_log(1, 'Script started');

// OPTIONS 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    debug_log(2, 'OPTIONS request');
    exit(0);
}

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    debug_log(3, 'Not POST: ' . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed', 'method' => $_SERVER['REQUEST_METHOD']]);
    exit;
}

debug_log(4, 'POST request received');

// config.php 불러오기
try {
    define('API_ACCESS', true);
    require_once 'config.php';
    debug_log(5, 'Config loaded');
} catch (Throwable $e) {
    debug_log(6, 'Config error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Configuration error',
        'details' => $e->getMessage()
    ]);
    exit;
}

// 요청 데이터 받기
try {
    $rawInput = file_get_contents('php://input');
    debug_log(7, 'Raw input: ' . $rawInput);

    $input = json_decode($rawInput, true);
    debug_log(8, 'Parsed input: ' . json_encode($input));
} catch (Throwable $e) {
    debug_log(9, 'Input parsing error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to parse input',
        'details' => $e->getMessage()
    ]);
    exit;
}

if (!isset($input['message']) || !isset($input['model'])) {
    debug_log(10, 'Missing parameters');
    http_response_code(400);
    echo json_encode([
        'error' => 'Missing required parameters',
        'received' => $input
    ]);
    exit;
}

$message = $input['message'];
$model = $input['model'];

debug_log(11, "Message: $message, Model: $model");

// 허용된 모델인지 확인
if (!in_array($model, ALLOWED_CHAT_MODELS)) {
    debug_log(12, 'Invalid model: ' . $model);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid model']);
    exit;
}

debug_log(13, 'Model validated');

// 성공 응답 (실제 API 호출 전)
echo json_encode([
    'success' => true,
    'message' => 'Debug: All checks passed',
    'received_message' => $message,
    'model' => $model
]);

debug_log(14, 'Response sent');
