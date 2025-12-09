<?php
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

// 토큰 체크는 다른 AJAX 파일들과 동일하게 제거
// 대신 접근 권한 체크로 보안 유지

// 가맹점 접근 권한 체크
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
            $shop_sql = " SELECT shop_id, status FROM {$g5['shop_table']} WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);
            
            if ($shop_row && $shop_row['shop_id']) {
                if ($shop_row['status'] != 'pending' && $shop_row['status'] != 'closed' && $shop_row['status'] != 'shutdown') {
                    $has_access = true;
                    $shop_id = (int)$shop_row['shop_id'];
                }
            }
        }
    }
}

if (!$has_access) {
    echo json_encode(array('success' => false, 'message' => '접근 권한이 없습니다.'));
    exit;
}

$act = isset($_POST['act']) ? clean_xss_tags($_POST['act']) : '';

if ($act != 'delete') {
    echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
    exit;
}

$chk = isset($_POST['chk']) ? $_POST['chk'] : array();

if (empty($chk) || !is_array($chk)) {
    echo json_encode(array('success' => false, 'message' => '선택된 항목이 없습니다.'));
    exit;
}

$chk_ids = array();
$cannot_delete_ids = array();
$cannot_delete_messages = array();

foreach ($chk as $customer_coupon_id) {
    $customer_coupon_id = (int)$customer_coupon_id;
    if ($customer_coupon_id <= 0) {
        continue;
    }
    
    // 해당 customer_coupon_id가 appointment_shop_detail 테이블에 존재하는지 확인
    $check_sql = " SELECT COUNT(*) as cnt 
                  FROM appointment_shop_detail 
                  WHERE customer_coupon_id = {$customer_coupon_id} ";
    $check_row = sql_fetch_pg($check_sql);
    
    if ($check_row && $check_row['cnt'] > 0) {
        // appointment_shop_detail에 존재하면 삭제 불가
        $cannot_delete_ids[] = $customer_coupon_id;
        $cannot_delete_messages[] = 'ID: ' . $customer_coupon_id . ' (예약 내역에 사용된 쿠폰)';
    } else {
        // shop_id 검증
        $verify_sql = " SELECT cc.customer_coupon_id 
                       FROM customer_coupons AS cc
                       INNER JOIN shop_coupons AS sc ON cc.coupon_id = sc.coupon_id
                       WHERE cc.customer_coupon_id = {$customer_coupon_id}
                       AND sc.shop_id = {$shop_id} ";
        $verify_row = sql_fetch_pg($verify_sql);
        
        if ($verify_row && $verify_row['customer_coupon_id']) {
            $chk_ids[] = $customer_coupon_id;
        }
    }
}

if (!empty($cannot_delete_ids)) {
    $message = '다음 쿠폰은 예약 내역에 사용되어 삭제할 수 없습니다:\n\n';
    $message .= implode('\n', $cannot_delete_messages);
    $message .= '\n\n총 ' . count($cannot_delete_ids) . '개의 쿠폰이 삭제되지 않았습니다.';
    echo json_encode(array('success' => false, 'message' => $message));
    exit;
}

if (empty($chk_ids)) {
    echo json_encode(array('success' => false, 'message' => '삭제할 수 있는 항목이 없습니다.'));
    exit;
}

$ids_str = implode(',', $chk_ids);

// customer_coupons 테이블에서 삭제 (shop_id 검증 포함)
$delete_sql = " DELETE FROM customer_coupons 
                WHERE customer_coupon_id IN ({$ids_str})
                AND coupon_id IN (
                    SELECT coupon_id FROM shop_coupons WHERE shop_id = {$shop_id}
                ) ";

sql_query_pg($delete_sql);

echo json_encode(array('success' => true, 'message' => '선택한 ' . count($chk_ids) . '개의 쿠폰이 삭제되었습니다.'));
exit;
?>
