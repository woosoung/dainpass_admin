<?php
include_once('./_common.php');

// 가맹점측 관리자 접근 권한 체크
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
        
        if ($mb_1_value !== '0' && !empty($mb_1_value)) {
            $shop_id_check = (int)$mb_1_value;
            $shop_sql = " SELECT shop_id, status 
                         FROM {$g5['shop_table']} 
                         WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);
            
            if ($shop_row && $shop_row['shop_id']) {
                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
            }
        }
    }
}

if (!$has_access) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('error' => '접근 권한이 없습니다.'));
    exit;
}

// JSON 응답 헤더 설정
header('Content-Type: application/json; charset=utf-8');

$user_id = isset($_POST['user_id']) ? clean_xss_tags($_POST['user_id'], 1, 1) : '';
$user_id = substr($user_id, 0, 50); // 길이 제한

if (!$user_id) {
    echo json_encode(array(
        'success' => false,
        'message' => '회원ID가 필요합니다.',
        'customer' => null,
        'shopdetails' => array()
    ));
    exit;
}

// 고객 정보 조회 (개인정보보호법 준수: 닉네임만 반환)
$user_id_escaped = sql_escape_string($user_id);
$customer_sql = " SELECT customer_id, user_id, nickname
                  FROM customers
                  WHERE user_id = '{$user_id_escaped}' ";
$customer_row = sql_fetch_pg($customer_sql);

$customer_info = null;
if ($customer_row && $customer_row['customer_id']) {
    $customer_info = array(
        'nickname' => $customer_row['nickname']
    );
}

// 해당 user_id로 결제한 가장 최근 shopdetail_id 조회 (예약일시 기준 최대 10개)
// payments 테이블과 appointment_shop_detail을 조인하여 결제한 내역만 가져옴
$shopdetail_sql = " SELECT DISTINCT
                        asd.shopdetail_id,
                        asd.appointment_id,
                        asd.appointment_datetime,
                        sa.appointment_no
                   FROM appointment_shop_detail AS asd
                   INNER JOIN shop_appointments AS sa ON asd.appointment_id = sa.appointment_id
                   INNER JOIN payments AS p ON sa.appointment_id = p.appointment_id
                   INNER JOIN customers AS c ON sa.customer_id = c.customer_id
                   WHERE c.user_id = '{$user_id_escaped}'
                     AND asd.shop_id = " . (int)$shop_id . "
                     AND (p.pay_flag IS NULL OR p.pay_flag = 'GENERAL')
                     AND p.status = 'DONE'
                   ORDER BY asd.appointment_datetime DESC
                   LIMIT 10 ";
$shopdetail_result = sql_query_pg($shopdetail_sql);

$shopdetails = array();
if ($shopdetail_result && is_object($shopdetail_result) && isset($shopdetail_result->result)) {
    while ($row = sql_fetch_array_pg($shopdetail_result->result)) {
        $appointment_datetime = $row['appointment_datetime'] ? date('Y-m-d H:i', strtotime($row['appointment_datetime'])) : '';
        $appointment_no = $row['appointment_no'] ? $row['appointment_no'] : '';
        $shopdetails[] = array(
            'shopdetail_id' => $row['shopdetail_id'],
            'appointment_id' => $row['appointment_id'],
            'appointment_datetime' => $appointment_datetime,
            'display_text' => '예약번호: ' . $appointment_no . '(세부예약ID: ' . $row['shopdetail_id'] . ')-예약일시: ' . $appointment_datetime
        );
    }
}

echo json_encode(array(
    'success' => true,
    'customer' => $customer_info,
    'shopdetails' => $shopdetails
));

exit;

