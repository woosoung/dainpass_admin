<?php
include_once('./_common.php');
// AWS SDK autoloader: prefer Composer vendor, fallback to legacy lib/aws
do {
    $loaded = false;
    // 1) Composer 설치 사용: /vendor/autoload.php
    if (defined('G5_PATH')) {
        $composer_autoload = G5_VENDOR_PATH . '/autoload.php';
        if (is_file($composer_autoload)) {
            require_once $composer_autoload;
            $loaded = true;
            break;
        }
    }

    // 2) 예전 경로 사용: /lib/aws/autoload.php
    if (defined('G5_LIB_PATH')) {
        $legacy_autoload = G5_LIB_PATH . '/aws/autoload.php';
        if (is_file($legacy_autoload)) {
            require_once $legacy_autoload;
            $loaded = true;
            break;
        }
    }
} while (false);

if (!class_exists('Aws\\S3\\S3Client')) {
    // 오토로더 로드 실패 시 명확한 메시지
    die('AWS SDK autoloader not found. Please ensure vendor/autoload.php or lib/aws/autoload.php exists.');
}

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

// GET 파라미터로 경로와 원래 파일명 전달
$file_path = $_GET['file_path'] ?? '';
$file_name_orig = $_GET['file_name_orig'] ?? '';

if (!$file_path) {
    alert('파일 경로가 없습니다.');
}
if (!$file_name_orig) {
    alert('파일 이름이 없습니다.');
}

// $set_conf = []; // 또는 g5_config 테이블 또는 config.php 등에서 S3 설정값 로딩
$bucket = $set_conf['set_aws_bucket'];
$region = $set_conf['set_aws_region'];

$s3 = new S3Client([
    'version' => 'latest',
    'region'  => $region,
    'credentials' => [
        'key'    => $set_conf['set_s3_accesskey'],
        'secret' => $set_conf['set_s3_secretaccesskey'],
    ]
]);

$key = "{$file_path}";
// echo $key;exit;
// ContentType 확인 및 Object 요청
try {
    $head = $s3->headObject([
        'Bucket' => $bucket,
        'Key'    => $key,
    ]);

    // 파일 스트리밍
    $result = $s3->getObject([
        'Bucket' => $bucket,
        'Key'    => $key,
    ]);

    $ctype = $head['ContentType'] ?? 'application/octet-stream';
    $filesize = $head['ContentLength'] ?? 0;

    // 브라우저용 헤더
    $original = iconv('utf-8', 'euc-kr', $file_name_orig); // 필요 시 EUC-KR로 인코딩
    if (!$original) $original = $file_name_orig;

    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    header("Content-Type: {$ctype}");
    header("Content-Disposition: attachment; filename=\"{$original}\"");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: {$filesize}");
    ob_clean();
    flush();

    echo $result['Body']; // 스트리밍 전송
    exit;

} catch (S3Exception $e) {
    echo "파일 다운로드 오류: " . $e->getAwsErrorMessage();
    exit;
}