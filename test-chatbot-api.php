<?php
// chatbot-api.php 전체 흐름 테스트
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== chatbot-api.php 테스트 ===\n\n";

// POST 데이터 시뮬레이션
$_SERVER['REQUEST_METHOD'] = 'POST';

// 테스트 데이터
$testInput = [
    'message' => 'Hello! Respond in one sentence.',
    'model' => 'Qwen/Qwen2.5-72B-Instruct:fastest'
];

// php://input 시뮬레이션을 위해 임시 파일 사용
$tmpFile = tmpfile();
fwrite($tmpFile, json_encode($testInput));
$tmpFilePath = stream_get_meta_data($tmpFile)['uri'];

// stdin을 임시 파일로 대체
// 실제로는 직접 API 코드를 include할 수 없으므로 cURL로 테스트

$url = 'http://localhost/ai_test/chatbot-api.php';

// 로컬 서버가 실행 중인지 확인
echo "로컬 서버 테스트 (http://localhost/ai_test/chatbot-api.php)\n";
echo "주의: 웹 서버(Apache/Nginx)가 실행 중이어야 합니다.\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testInput));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 45);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

if ($error) {
    echo "⚠️  웹 서버 연결 실패: $error\n";
    echo "\n웹 서버가 실행 중이 아니거나 경로가 잘못되었을 수 있습니다.\n";
    echo "HTML 파일을 브라우저에서 직접 열어서 테스트하세요.\n";
} else {
    echo "HTTP 코드: $httpCode\n\n";
    echo "응답:\n";
    $decoded = json_decode($result, true);
    if ($decoded) {
        print_r($decoded);

        if (isset($decoded[0]['generated_text'])) {
            echo "\n✓ 생성된 텍스트: " . $decoded[0]['generated_text'] . "\n";
        }
    } else {
        echo $result . "\n";
    }
}
