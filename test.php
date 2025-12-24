<?php
// 오류 표시 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP 테스트 시작...\n\n";

// .env 파일 존재 확인
if (file_exists('.env')) {
    echo "✓ .env 파일 존재함\n";
} else {
    echo "✗ .env 파일 없음\n";
}

// config.php 로드 테스트
try {
    define('API_ACCESS', true);
    require_once 'config.php';

    echo "✓ config.php 로드 성공\n";
    echo "✓ API 키: " . substr(HUGGINGFACE_API_KEY, 0, 10) . "...\n";
    echo "✓ 설정 완료!\n";
} catch (Exception $e) {
    echo "✗ 오류 발생: " . $e->getMessage() . "\n";
}
