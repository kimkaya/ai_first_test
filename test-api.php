<?php
// 오류 표시 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "API 테스트 시작...\n\n";

// POST 데이터 시뮬레이션
$_SERVER['REQUEST_METHOD'] = 'POST';
$testData = json_encode([
    'message' => 'Hello',
    'model' => 'mistralai/Mixtral-8x7B-Instruct-v0.1'
]);

// php://input 시뮬레이션은 어려우므로 직접 테스트
define('API_ACCESS', true);

try {
    require_once 'config.php';
    echo "✓ Config 로드 성공\n";
    echo "✓ API 키 있음: " . (HUGGINGFACE_API_KEY ? 'Yes' : 'No') . "\n";
    echo "✓ 허용된 모델 수: " . count(ALLOWED_CHAT_MODELS) . "\n";
} catch (Exception $e) {
    echo "✗ 오류: " . $e->getMessage() . "\n";
    echo "✗ 스택: " . $e->getTraceAsString() . "\n";
}
