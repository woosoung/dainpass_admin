<?php
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

// 권한 체크
if (!$is_member || !$member['mb_id']) {
    echo json_encode(array('success' => false, 'message' => '로그인이 필요합니다.'));
    exit;
}

$appointment_id = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : 0;
$shopdetail_id = isset($_GET['shopdetail_id']) ? (int)$_GET['shopdetail_id'] : 0;

if ($appointment_id <= 0 || $shopdetail_id <= 0) {
    echo json_encode(array('success' => false, 'message' => '잘못된 파라미터입니다.'));
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

// shopdetail_id가 해당 shop_id의 예약인지 확인 (BOOKED 상태 제외)
$check_sql = " SELECT asd.shopdetail_id, asd.shop_id, asd.appointment_id
               FROM appointment_shop_detail asd
               INNER JOIN shop_appointments sa ON asd.appointment_id = sa.appointment_id
               WHERE asd.shopdetail_id = {$shopdetail_id} 
               AND asd.shop_id = {$shop_id}
               AND asd.appointment_id = {$appointment_id}
               AND sa.status != 'BOOKED'
               AND asd.status != 'BOOKED' ";
$check_row = sql_fetch_pg($check_sql);

if (!$check_row || !$check_row['shopdetail_id']) {
    echo json_encode(array('success' => false, 'message' => '해당 가맹점의 예약이 아닙니다.'));
    exit;
}

// 서비스별 예약 상세 조회
$sql = " SELECT sad.detail_id,
                sad.service_id,
                sad.shopdetail_id,
                sad.org_quantity,
                sad.quantity,
                sad.price,
                sad.net_amount,
                ss.service_name
         FROM shop_appointment_details sad
         INNER JOIN shop_services ss ON sad.service_id = ss.service_id
         WHERE sad.shopdetail_id = {$shopdetail_id}
         ORDER BY sad.detail_id ";
$result = sql_query_pg($sql);

$details = array();
if ($result && is_object($result) && isset($result->result)) {
    while ($row = sql_fetch_array_pg($result->result)) {
        $details[] = array(
            'detail_id' => (int)$row['detail_id'],
            'service_id' => (int)$row['service_id'],
            'shopdetail_id' => (int)$row['shopdetail_id'],
            'org_quantity' => (int)$row['org_quantity'],
            'quantity' => (int)$row['quantity'],
            'price' => (int)$row['price'],
            'net_amount' => (int)$row['net_amount'],
            'service_name' => $row['service_name']
        );
    }
}

echo json_encode(array('success' => true, 'data' => $details));
exit;
?>
