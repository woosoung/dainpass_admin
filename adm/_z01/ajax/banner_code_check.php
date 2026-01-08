<?php
// 에러 출력 방지 (500 에러 방지)
error_reporting(0);
ini_set('display_errors', 0);

include_once('./_common.php');

// 응답을 텍스트로 고정
header('Content-Type: text/plain; charset=utf-8');

// 기본 응답: 0 (사용불가)
$status = '0';

// 1) 우선 x-www-form-urlencoded(표준 폼)에서 수신
$bng_code = isset($_POST['bng_code']) ? trim($_POST['bng_code']) : '';
$bng_id   = isset($_POST['bng_id'])   ? trim($_POST['bng_id'])   : '';
$w        = isset($_POST['w'])        ? trim($_POST['w'])        : '';

// 2) 폴백: JSON으로 전달된 경우 지원
if ($bng_code === '' && empty($_POST)) {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $d = json_decode($raw, true);
        if (is_array($d)) {
            $bng_code = isset($d['bng_code']) ? trim($d['bng_code']) : $bng_code;
            $bng_id   = isset($d['bng_id'])   ? trim($d['bng_id'])   : $bng_id;
            $w        = isset($d['w'])        ? trim($d['w'])        : $w;
        }
    }
}

// 서버측 유효성 검사 (영문 시작, 영문/숫자/_만 허용)
if ($bng_code === '' || !preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $bng_code)) {
    echo $status; // 형식이 틀리면 사용불가 처리
    exit;
}

// 업데이트 모드에서는 본인 배너그룹 ID는 제외하고 중복 검사
$bng_id_where = '';
if ($w === 'u' && $bng_id !== '' && is_numeric($bng_id)) {
    $bng_id_where = " AND bng_id <> '".(int)$bng_id."' ";
}

// 존재 여부 조회 (에러 발생 시 false 반환되도록 처리)
try {
    $sql = " SELECT bng_id FROM banner_group WHERE bng_code = '".addslashes($bng_code)."' {$bng_id_where} LIMIT 1 ";
    
    // 에러 표시 비활성화하여 쿼리 에러 시에도 false 반환
    // @ 연산자로 에러 억제
    $result = @sql_query_pg($sql, 0);
    
    // sql_query_pg가 false/null을 반환하거나 객체가 아닌 경우
    if (!$result || !is_object($result) || !isset($result->result)) {
        // 쿼리 실패 시 사용 가능으로 처리 (테이블이 없거나 에러인 경우)
        $status = '1';
    } else {
        // 결과가 정상적으로 반환된 경우
        $row = @sql_fetch_array_pg($result->result);
        
        // sql_fetch_array_pg가 false를 반환할 수 있으므로 안전하게 처리
        if (!$row || !is_array($row) || empty($row)) {
            // 결과가 없으면 사용 가능
            $status = '1';
        } else {
            // 결과가 있으면 중복 (사용 불가)
            $existing_bng_id = isset($row['bng_id']) ? (int)$row['bng_id'] : 0;
            $status = ($existing_bng_id > 0) ? '0' : '1';
        }
    }
} catch (Exception $e) {
    // 에러 발생 시 사용 가능으로 처리 (서버 오류는 사용자에게 알리지 않음)
    $status = '1';
} catch (Error $e) {
    // PHP 7+ Fatal Error 처리
    $status = '1';
} catch (Throwable $e) {
    // 모든 예외 처리
    $status = '1';
}

echo $status;
exit;

