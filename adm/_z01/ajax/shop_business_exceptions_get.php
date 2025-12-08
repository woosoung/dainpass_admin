<?php
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

$response = array('success' => false, 'message' => '', 'data' => null);

// 접근 권한 체크
$has_access = false;
$shop_id = 0;

if ($is_member && $member['mb_id']) {
    $mb_sql = " SELECT mb_id, mb_level, mb_1, mb_2, mb_leave_date, mb_intercept_date 
                FROM {$g5['member_table']} 
                WHERE mb_id = '{$member['mb_id']}' 
                AND mb_level >= 4 
                AND (
                    mb_level >= 6 
                    OR (mb_level < 6 AND mb_2 = 'Y')
                )
                AND (mb_leave_date = '' OR mb_leave_date IS NULL)
                AND (mb_intercept_date = '' OR mb_intercept_date IS NULL) ";
    $mb_row = sql_fetch($mb_sql, 1);
    
    if ($mb_row && $mb_row['mb_id']) {
        $mb_1_value = trim($mb_row['mb_1']);
        
        if (!empty($mb_1_value) && $mb_1_value !== '0') {
            $shop_id_check = (int)$mb_1_value;
            $shop_sql = " SELECT shop_id FROM {$g5['shop_table']} WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);
            
            if ($shop_row && $shop_row['shop_id']) {
                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
            }
        }
    }
}

if (!$has_access) {
    $response['message'] = '접근 권한이 없습니다.';
    echo json_encode($response);
    exit;
}

// date 받기
$date = isset($_POST['date']) ? clean_xss_tags($_POST['date']) : '';

if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $response['message'] = '날짜가 올바르지 않습니다.';
    echo json_encode($response);
    exit;
}

// 해당 예외일 데이터 가져오기
$get_sql = "
    SELECT 
        shop_id,
        date,
        is_open,
        open_time,
        close_time,
        reason
    FROM business_exceptions
    WHERE shop_id = {$shop_id}
    AND date = '{$date}'
";

$get_result = sql_query_pg($get_sql);
if (!$get_result) {
    $response['message'] = '쿼리 실행에 실패했습니다.';
    echo json_encode($response);
    exit;
}

if (!is_object($get_result) || !isset($get_result->result)) {
    $response['message'] = '데이터를 찾을 수 없습니다.';
    echo json_encode($response);
    exit;
}

$get_row = sql_fetch_array_pg($get_result->result);
if (!$get_row || !isset($get_row['date'])) {
    $response['message'] = '해당 날짜의 데이터가 없습니다.';
    echo json_encode($response);
    exit;
}

// PostgreSQL boolean 값 처리
$is_open = isset($get_row['is_open']) && ($get_row['is_open'] == 't' || $get_row['is_open'] === true || $get_row['is_open'] == '1' || $get_row['is_open'] === 'true');

$response['success'] = true;
$response['data'] = array(
    'date' => $get_row['date'],
    'is_open' => $is_open,
    'open_time' => $get_row['open_time'] ? substr($get_row['open_time'], 0, 5) : '',
    'close_time' => $get_row['close_time'] ? substr($get_row['close_time'], 0, 5) : '',
    'reason' => $get_row['reason'] ? $get_row['reason'] : ''
);

echo json_encode($response);
?>

