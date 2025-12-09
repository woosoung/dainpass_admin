<?php
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

$action = isset($_GET['action']) ? clean_xss_tags($_GET['action']) : '';
$shop_id = isset($_GET['shop_id']) ? (int)$_GET['shop_id'] : 0;
$customer_coupon_id = isset($_GET['customer_coupon_id']) ? (int)$_GET['customer_coupon_id'] : 0;

// 가맹점 접근 권한 체크
$has_access = false;
$current_shop_id = 0;

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
                    $current_shop_id = (int)$shop_row['shop_id'];
                }
            }
        }
    }
}

if (!$has_access) {
    echo json_encode(array('success' => false, 'message' => '접근 권한이 없습니다.'));
    exit;
}

if ($action == 'get_available_coupons') {
    // 발급 가능한 쿠폰 목록 조회 (유효기간 내이고 활성화된 쿠폰)
    $current_date = date('Y-m-d');
    
    $sql = " SELECT coupon_id, coupon_code, coupon_name, discount_type, discount_value
             FROM shop_coupons 
             WHERE shop_id = {$current_shop_id}
             AND is_active = true
             AND (valid_from IS NULL OR valid_from <= '{$current_date}')
             AND (valid_until IS NULL OR valid_until >= '{$current_date}')
             ORDER BY coupon_code ";
    
    $result = sql_query_pg($sql);
    $coupons = array();
    
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $coupons[] = array(
                'coupon_id' => $row['coupon_id'],
                'coupon_code' => $row['coupon_code'],
                'coupon_name' => $row['coupon_name'],
                'discount_type' => $row['discount_type'],
                'discount_value' => $row['discount_value']
            );
        }
    }
    
    echo json_encode(array('success' => true, 'coupons' => $coupons));
    exit;
    
} else if ($action == 'get_coupon') {
    // 특정 발급 쿠폰 정보 조회
    if (!$customer_coupon_id) {
        echo json_encode(array('success' => false, 'message' => '쿠폰 ID가 올바르지 않습니다.'));
        exit;
    }
    
    $sql = " SELECT cc.*, 
                    sc.coupon_code, 
                    sc.coupon_name, 
                    sc.discount_type, 
                    sc.discount_value,
                    c.user_id, 
                    c.name as customer_name
             FROM customer_coupons AS cc
             INNER JOIN shop_coupons AS sc ON cc.coupon_id = sc.coupon_id
             LEFT JOIN customers AS c ON cc.customer_id = c.customer_id
             WHERE cc.customer_coupon_id = {$customer_coupon_id}
             AND sc.shop_id = {$current_shop_id} ";
    
    $row = sql_fetch_pg($sql);
    
    if (!$row || !$row['customer_coupon_id']) {
        echo json_encode(array('success' => false, 'message' => '쿠폰 정보를 찾을 수 없습니다.'));
        exit;
    }
    
    $coupon = array(
        'customer_coupon_id' => $row['customer_coupon_id'],
        'coupon_id' => $row['coupon_id'],
        'coupon_code' => $row['coupon_code'],
        'coupon_name' => $row['coupon_name'],
        'customer_id' => $row['customer_id'],
        'user_id' => $row['user_id'],
        'customer_name' => $row['customer_name'],
        'status' => $row['status'],
        'issued_at' => $row['issued_at'] ? date('Y-m-d H:i', strtotime($row['issued_at'])) : '',
        'used_at' => $row['used_at'] ? date('Y-m-d H:i', strtotime($row['used_at'])) : ''
    );
    
    echo json_encode(array('success' => true, 'coupon' => $coupon));
    exit;
}

echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
exit;
?>
