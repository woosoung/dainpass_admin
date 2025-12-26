<?php
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

// 에러 발생 시 JSON으로 응답하도록 설정
error_reporting(E_ALL);
ini_set('display_errors', 0);
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => '서버 오류가 발생했습니다: ' . $error['message']], JSON_UNESCAPED_UNICODE);
        exit;
    }
});

// 플랫폼 관리자 권한 체크
if ($is_member && $member['mb_id']) {
    $mb_sql = " SELECT mb_id, mb_level, mb_1 ".
              " FROM {$g5['member_table']} ".
              " WHERE mb_id = '{$member['mb_id']}' ".
              " AND mb_level >= 6 ".
              " AND mb_1 = '0' ";
    $mb_row = sql_fetch($mb_sql, 1);

    if (!$mb_row || !$mb_row['mb_id']) {
        echo json_encode(['success' => false, 'message' => '플랫폼 관리자만 접근할 수 있습니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// TODO: 통합 대시보드 데이터 조회 로직 구현

echo json_encode([
    'success' => true,
    'data' => [
        // TODO: 실제 데이터 구조 구현
    ]
], JSON_UNESCAPED_UNICODE);

