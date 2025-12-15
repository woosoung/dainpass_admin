<?php
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

// 권한 체크
if (!$is_member || !$member['mb_id']) {
    echo json_encode(array('success' => false, 'message' => '로그인이 필요합니다.'));
    exit;
}

// POST 데이터 읽기
$appointment_id = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;
$new_status = isset($_POST['new_status']) ? clean_xss_tags($_POST['new_status']) : '';

if ($appointment_id <= 0) {
    echo json_encode(array('success' => false, 'message' => '예약 ID가 올바르지 않습니다.'));
    exit;
}

// BOOKED 상태는 변경 불가
if (empty($new_status) || !in_array($new_status, array('COMPLETED', 'CANCELLED'))) {
    echo json_encode(array('success' => false, 'message' => '올바른 상태를 입력하세요. (COMPLETED, CANCELLED만 가능)'));
    exit;
}

if ($new_status == 'BOOKED') {
    echo json_encode(array('success' => false, 'message' => 'BOOKED 상태로는 변경할 수 없습니다.'));
    exit;
}

// 가맹점 접근 권한 체크
$has_access = false;
$shop_id = 0;

$mb_sql = " SELECT mb_id, mb_level, mb_1, mb_2 
            FROM {$g5['member_table']} 
            WHERE mb_id = '{$member['mb_id']}' 
            AND mb_level >= 4 
            AND (
                mb_level >= 6 
                OR (mb_level < 6 AND mb_2 = 'Y')
            ) ";
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

if (!$has_access) {
    echo json_encode(array('success' => false, 'message' => '접근 권한이 없습니다.'));
    exit;
}

// 예약이 해당 가맹점의 예약인지 확인 (BOOKED 상태 제외)
$check_sql = " SELECT sa.appointment_id 
               FROM shop_appointments sa
               INNER JOIN appointment_shop_detail asd ON sa.appointment_id = asd.appointment_id
               WHERE sa.appointment_id = {$appointment_id} 
               AND sa.status != 'BOOKED'
               AND asd.shop_id = {$shop_id}
               AND asd.status != 'BOOKED' ";
$check_row = sql_fetch_pg($check_sql);

if (!$check_row || !$check_row['appointment_id']) {
    echo json_encode(array('success' => false, 'message' => '해당 가맹점의 예약이 아닙니다.'));
    exit;
}

// 상태 업데이트 (BOOKED 상태는 업데이트 불가)
$update_sql = " UPDATE shop_appointments 
                SET status = '{$new_status}', 
                    updated_at = NOW() 
                WHERE appointment_id = {$appointment_id} 
                AND status != 'BOOKED' ";
sql_query_pg($update_sql);

echo json_encode(array('success' => true, 'message' => '상태가 변경되었습니다.'));
exit;
?>
