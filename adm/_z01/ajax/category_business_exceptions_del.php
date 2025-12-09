<?php
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

$response = array('success' => false, 'message' => '');

// 플랫폼 관리자 접근 권한 체크
$has_access = false;

if ($is_member && $member['mb_id']) {
    $mb_sql = " SELECT mb_id, mb_level, mb_1, mb_2, mb_leave_date, mb_intercept_date 
                FROM {$g5['member_table']} 
                WHERE mb_id = '{$member['mb_id']}' 
                AND mb_level >= 6 
                AND (mb_leave_date = '' OR mb_leave_date IS NULL)
                AND (mb_intercept_date = '' OR mb_intercept_date IS NULL) ";
    $mb_row = sql_fetch($mb_sql, 1);
    
    if ($mb_row && $mb_row['mb_id']) {
        $has_access = true;
    }
}

if (!$has_access) {
    $response['message'] = '접근 권한이 없습니다.';
    echo json_encode($response);
    exit;
}

// category_id와 date 받기
$category_id = isset($_POST['category_id']) ? trim(clean_xss_tags($_POST['category_id'])) : '';
$date = isset($_POST['date']) ? clean_xss_tags($_POST['date']) : '';

// category_id 검증 ('0'은 '업종공통'을 의미하므로 유효한 값)
if ($category_id === '' || $category_id === null) {
    $response['message'] = '업종 정보가 올바르지 않습니다.';
    echo json_encode($response);
    exit;
}

if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $response['message'] = '날짜가 올바르지 않습니다.';
    echo json_encode($response);
    exit;
}

// 해당 예외일이 존재하는지 확인
$category_id_escaped = sql_real_escape_string($category_id);
$check_sql = "
    SELECT category_id 
    FROM default_business_exceptions
    WHERE category_id = '{$category_id_escaped}'
    AND date = '{$date}'
";

$check_result = sql_query_pg($check_sql);
if (!$check_result || !is_object($check_result) || !isset($check_result->result)) {
    $response['message'] = '예외일시를 찾을 수 없습니다.';
    echo json_encode($response);
    exit;
}

$check_row = sql_fetch_array_pg($check_result->result);
if (!$check_row) {
    $response['message'] = '접근 권한이 없는 예외일시입니다.';
    echo json_encode($response);
    exit;
}

// 삭제 실행
$delete_sql = " DELETE FROM default_business_exceptions 
                WHERE category_id = '{$category_id_escaped}' 
                AND date = '{$date}' ";
$delete_result = sql_query_pg($delete_sql);

if ($delete_result) {
    $response['success'] = true;
    $response['message'] = '삭제되었습니다.';
} else {
    $response['message'] = '삭제에 실패했습니다.';
}

echo json_encode($response);
?>
