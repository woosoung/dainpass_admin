<?php
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

// 권한 체크
if (!$is_member || !$member['mb_id']) {
    echo json_encode(array('success' => false, 'message' => '로그인이 필요합니다.'));
    exit;
}

$appointment_id = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : 0;

if ($appointment_id <= 0) {
    echo json_encode(array('success' => false, 'message' => '예약 ID가 올바르지 않습니다.'));
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

// 예약 기본 정보 조회 (BOOKED 상태 제외)
$appointment_sql = " SELECT sa.*, 
                            c.user_id, 
                            c.name as customer_name, 
                            c.phone as customer_phone,
                            c.email as customer_email,
                            p.amount as payment_amount,
                            p.payment_method,
                            p.paid_at
                     FROM shop_appointments sa
                     LEFT JOIN customers c ON sa.customer_id = c.customer_id
                     LEFT JOIN payments p ON sa.appointment_id = p.appointment_id AND (p.pay_flag IS NULL OR p.pay_flag = 'GENERAL')
                     WHERE sa.appointment_id = {$appointment_id} 
                     AND sa.status != 'BOOKED'
                     AND EXISTS (
                         SELECT 1 FROM appointment_shop_detail 
                         WHERE appointment_id = sa.appointment_id 
                         AND shop_id = {$shop_id}
                         AND status != 'BOOKED'
                     ) ";
$appointment = sql_fetch_pg($appointment_sql);

if (!$appointment || !$appointment['appointment_id']) {
    echo json_encode(array('success' => false, 'message' => '예약 정보를 찾을 수 없습니다.'));
    exit;
}

// 가맹점별 예약 상세 조회 (BOOKED 상태 제외)
$shop_details_sql = " SELECT asd.*, s.shop_name, s.name as shop_name_alt
                      FROM appointment_shop_detail asd
                      INNER JOIN shop s ON asd.shop_id = s.shop_id
                      WHERE asd.appointment_id = {$appointment_id} 
                      AND asd.shop_id = {$shop_id}
                      AND asd.status != 'BOOKED'
                      ORDER BY asd.appointment_datetime ";
$shop_details_result = sql_query_pg($shop_details_sql);
$shop_details = array();
if ($shop_details_result && is_object($shop_details_result) && isset($shop_details_result->result)) {
    while ($row = sql_fetch_array_pg($shop_details_result->result)) {
        $shop_details[] = $row;
    }
}

// 응답 데이터 구성
$response_data = array(
    'appointment' => $appointment,
    'shop_details' => $shop_details
);

echo json_encode(array('success' => true, 'data' => $response_data));
exit;
?>
