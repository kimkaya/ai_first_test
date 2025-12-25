<?php
/**
 * API 설정 파일
 * .env 파일에서 환경 변수를 로드합니다
 */

// 직접 접근 방지
if (!defined('API_ACCESS')) {
    die('Direct access not permitted');
}

/**
 * .env 파일 로더 함수
 */
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        die('.env 파일을 찾을 수 없습니다. .env.example을 복사해서 .env 파일을 만들어주세요.');
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // 주석 무시
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // KEY=VALUE 파싱
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // 따옴표 제거
            $value = trim($value, '"\'');

            // 환경 변수로 설정
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// .env 파일 로드
loadEnv(__DIR__ . '/.env');

// 환경 변수에서 설정 읽기
define('HUGGINGFACE_API_KEY', $_ENV['HUGGINGFACE_API_KEY'] ?? '');

// API 키 검증
if (empty(HUGGINGFACE_API_KEY) || HUGGINGFACE_API_KEY === 'your_api_key_here') {
    die('Hugging Face API 키가 설정되지 않았습니다. .env 파일을 확인해주세요.');
}

// 허용된 모델 목록 (보안을 위해 제한)
// 새 API는 :fastest, :cheapest 등의 라우팅 옵션을 지원
define('ALLOWED_CHAT_MODELS', [
    'Qwen/Qwen2.5-72B-Instruct:fastest',
    'meta-llama/Llama-3.3-70B-Instruct:fastest',
    'mistralai/Mixtral-8x7B-Instruct-v0.1:fastest',
    'microsoft/Phi-3-mini-4k-instruct:fastest'
]);

define('ALLOWED_IMAGE_MODELS', [
    'stabilityai/stable-diffusion-2-1',
    'runwayml/stable-diffusion-v1-5',
    'CompVis/stable-diffusion-v1-4',
    'stabilityai/stable-diffusion-xl-base-1.0'
]);

// 허용된 Vision 모델 목록 (이미지 인식/분석)
define('ALLOWED_VISION_MODELS', [
    'Salesforce/blip-image-captioning-base',
    'nlpconnect/vit-gpt2-image-captioning',
    'microsoft/resnet-50',
    'google/vit-base-patch16-224'
]);

// CORS 설정
$allowedOrigins = $_ENV['ALLOWED_ORIGINS'] ?? 'http://localhost,http://127.0.0.1,file://';
define('ALLOWED_ORIGINS', array_map('trim', explode(',', $allowedOrigins)));

// 환경 설정
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN));
