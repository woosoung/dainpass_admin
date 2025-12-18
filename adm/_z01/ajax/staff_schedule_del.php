<?php
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

$response = array('success' => false, 'message' => '');

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

// schedule_id 받기
$schedule_id = isset($_POST['schedule_id']) ? (int)$_POST['schedule_id'] : 0;

if (!$schedule_id) {
    $response['message'] = '스케줄 ID가 없습니다.';
    echo json_encode($response);
    exit;
}

// 해당 스케줄이 현재 가맹점의 것인지 확인
$check_sql = "
    SELECT ss.schedule_id 
    FROM staff_schedules ss
    INNER JOIN staff s ON ss.staff_id = s.staff_id
    WHERE ss.schedule_id = {$schedule_id}
    AND s.store_id = {$shop_id}
";

$check_result = sql_query_pg($check_sql);
if (!$check_result || !is_object($check_result) || !isset($check_result->result)) {
    $response['message'] = '스케줄을 찾을 수 없습니다.';
    echo json_encode($response);
    exit;
}

$check_row = sql_fetch_array_pg($check_result->result);
if (!$check_row) {
    $response['message'] = '접근 권한이 없는 스케줄입니다.';
    echo json_encode($response);
    exit;
}

// 삭제 실행
$delete_sql = " DELETE FROM staff_schedules WHERE schedule_id = {$schedule_id} ";
$delete_result = sql_query_pg($delete_sql);

if ($delete_result) {
    $response['success'] = true;
    $response['message'] = '삭제되었습니다.';
} else {
    $response['message'] = '삭제에 실패했습니다.';
}

echo json_encode($response);
?>

