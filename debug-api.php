<?php
// 모든 오류 표시
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

echo json_encode(['step' => 1, 'message' => 'Script started']);

// Step 2: Config 로드 시도
try {
    define('API_ACCESS', true);
    require_once 'config.php';
    echo json_encode(['step' => 2, 'message' => 'Config loaded', 'api_key_exists' => !empty(HUGGINGFACE_API_KEY)]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'step' => 2,
        'error' => 'Config load failed',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit;
}

echo json_encode(['step' => 3, 'message' => 'Success']);
