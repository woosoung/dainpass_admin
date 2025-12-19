<?php
$sub_menu = "940200";
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

@auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

// 토큰 체크
check_admin_token();

// 파라미터 수신
$action = isset($_POST['action']) ? clean_xss_tags($_POST['action']) : '';
$post_shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : 0;

// shop_id 검증
if ($post_shop_id != $shop_id) {
    echo json_encode(array('success' => false, 'message' => '잘못된 가맹점 정보입니다.'));
    exit;
}

if ($action == 'add') {
    // 신규 쿠폰 발급
    $coupon_id = isset($_POST['coupon_id']) ? (int)$_POST['coupon_id'] : 0;
    $user_id = isset($_POST['user_id']) ? trim(clean_xss_tags($_POST['user_id'])) : '';
    
    // 필수값 검증
    if (!$coupon_id || $coupon_id < 1) {
        echo json_encode(array('success' => false, 'message' => '쿠폰을 선택해주세요.'));
        exit;
    }
    
    if (!$user_id) {
        echo json_encode(array('success' => false, 'message' => '회원ID를 입력해주세요.'));
        exit;
    }
    
    // 쿠폰이 해당 가맹점의 쿠폰인지 확인 (issued_limit, total_limit 포함)
    $coupon_sql = " SELECT coupon_id, shop_id, is_active, valid_from, valid_until, issued_limit, total_limit
                    FROM shop_coupons 
                    WHERE coupon_id = {$coupon_id} 
                    AND shop_id = {$shop_id} ";
    $coupon_row = sql_fetch_pg($coupon_sql);
    
    if (!$coupon_row || !$coupon_row['coupon_id']) {
        echo json_encode(array('success' => false, 'message' => '쿠폰 정보를 찾을 수 없습니다.'));
        exit;
    }
    
    // 쿠폰이 활성화되어 있고 유효기간 내인지 확인
    $current_date = date('Y-m-d');
    $is_active = ($coupon_row['is_active'] == 't' || $coupon_row['is_active'] === true || $coupon_row['is_active'] == '1' || $coupon_row['is_active'] === 'true');
    
    if (!$is_active) {
        echo json_encode(array('success' => false, 'message' => '비활성화된 쿠폰입니다.'));
        exit;
    }
    
    if ($coupon_row['valid_from'] && $coupon_row['valid_from'] > $current_date) {
        echo json_encode(array('success' => false, 'message' => '아직 유효기간이 시작되지 않은 쿠폰입니다.'));
        exit;
    }
    
    if ($coupon_row['valid_until'] && $coupon_row['valid_until'] < $current_date) {
        echo json_encode(array('success' => false, 'message' => '유효기간이 만료된 쿠폰입니다.'));
        exit;
    }
    
    // 회원 확인 (status가 'active'인 회원만)
    $customer_sql = " SELECT customer_id 
                      FROM customers 
                      WHERE user_id = '".addslashes($user_id)."' 
                      AND status = 'active' 
                      LIMIT 1 ";
    $customer_row = sql_fetch_pg($customer_sql);
    
    if (!$customer_row || !$customer_row['customer_id']) {
        echo json_encode(array('success' => false, 'message' => '존재하지 않거나 비활성화된 회원입니다.'));
        exit;
    }
    
    $customer_id = (int)$customer_row['customer_id'];
    
    // 1인당 발급한도 확인
    $issued_limit = isset($coupon_row['issued_limit']) ? (int)$coupon_row['issued_limit'] : 1;
    
    // 해당 회원이 이미 발급받은 해당 쿠폰의 개수 확인
    $issued_count_sql = " SELECT COUNT(*) as cnt 
                         FROM customer_coupons 
                         WHERE coupon_id = {$coupon_id} 
                         AND customer_id = {$customer_id} 
                         AND status = 'ISSUED' ";
    $issued_count_row = sql_fetch_pg($issued_count_sql);
    $issued_count = isset($issued_count_row['cnt']) ? (int)$issued_count_row['cnt'] : 0;
    
    // 1인당 발급한도 초과 확인
    if ($issued_count >= $issued_limit) {
        echo json_encode(array('success' => false, 'message' => '1인당 발급한도를 초과했습니다. (현재: ' . $issued_count . '장 / 한도: ' . $issued_limit . '장)'));
        exit;
    }
    
    // 전체 발급한도 확인
    $total_limit = isset($coupon_row['total_limit']) && $coupon_row['total_limit'] !== null ? (int)$coupon_row['total_limit'] : null;
    
    if ($total_limit !== null && $total_limit > 0) {
        // 해당 쿠폰의 전체 발급 개수 확인
        $total_issued_sql = " SELECT COUNT(*) as cnt 
                              FROM customer_coupons 
                              WHERE coupon_id = {$coupon_id} ";
        $total_issued_row = sql_fetch_pg($total_issued_sql);
        $total_issued_count = isset($total_issued_row['cnt']) ? (int)$total_issued_row['cnt'] : 0;
        
        // 현재 등록하려는 데이터를 포함한 개수가 전체발급한도를 초과하는지 확인
        if (($total_issued_count + 1) > $total_limit) {
            echo json_encode(array('success' => false, 'message' => '전체 발급한도를 초과했습니다. (현재: ' . $total_issued_count . '장 / 한도: ' . $total_limit . '장)'));
            exit;
        }
    }
    
    // customer_coupons 테이블에 INSERT
    $insert_sql = " INSERT INTO customer_coupons (
                        coupon_id, 
                        customer_id, 
                        status, 
                        issued_at
                    ) VALUES (
                        {$coupon_id},
                        {$customer_id},
                        'ISSUED',
                        NOW()
                    ) ";
    
    sql_query_pg($insert_sql);
    
    // shop_coupons 테이블의 total_issued 증가
    $update_issued_sql = " UPDATE shop_coupons 
                           SET total_issued = COALESCE(total_issued, 0) + 1 
                           WHERE coupon_id = {$coupon_id} ";
    sql_query_pg($update_issued_sql);
    
    echo json_encode(array('success' => true, 'message' => '쿠폰이 발급되었습니다.'));
    exit;
    
} else if ($action == 'edit') {
    // 쿠폰 발급 정보 수정 (상태만 수정 가능)
    $customer_coupon_id = isset($_POST['customer_coupon_id']) ? (int)$_POST['customer_coupon_id'] : 0;
    $status = isset($_POST['status']) ? clean_xss_tags($_POST['status']) : '';
    
    // 필수값 검증
    if (!$customer_coupon_id || $customer_coupon_id < 1) {
        echo json_encode(array('success' => false, 'message' => '쿠폰 ID가 올바르지 않습니다.'));
        exit;
    }
    
    if (!$status || !in_array($status, array('ISSUED', 'USED', 'EXPIRED'))) {
        echo json_encode(array('success' => false, 'message' => '상태값이 올바르지 않습니다.'));
        exit;
    }
    
    // 기존 데이터 확인 (쿠폰 정보 포함)
    $exist_sql = " SELECT cc.*, sc.shop_id, sc.is_active, sc.valid_from, sc.valid_until
                   FROM customer_coupons AS cc
                   INNER JOIN shop_coupons AS sc ON cc.coupon_id = sc.coupon_id
                   WHERE cc.customer_coupon_id = {$customer_coupon_id} ";
    $exist_row = sql_fetch_pg($exist_sql);
    
    if (!$exist_row || !$exist_row['customer_coupon_id']) {
        echo json_encode(array('success' => false, 'message' => '존재하지 않는 쿠폰입니다.'));
        exit;
    }
    
    // shop_id 검증
    if ($exist_row['shop_id'] != $shop_id) {
        echo json_encode(array('success' => false, 'message' => '접근 권한이 없습니다.'));
        exit;
    }
    
    $old_status = $exist_row['status'];
    
    // 상태 변경 규칙 검증
    // ISSUED -> USED 또는 EXPIRED로 변경 불가
    if ($old_status == 'ISSUED' && ($status == 'USED' || $status == 'EXPIRED')) {
        echo json_encode(array('success' => false, 'message' => 'ISSUED 상태는 USED나 EXPIRED로 변경할 수 없습니다.'));
        exit;
    }
    
    // EXPIRED -> ISSUED로 변경 불가
    if ($old_status == 'EXPIRED' && $status == 'ISSUED') {
        echo json_encode(array('success' => false, 'message' => 'EXPIRED 상태는 ISSUED로 변경할 수 없습니다.'));
        exit;
    }
    
    // USED -> ISSUED로만 변경 가능
    if ($old_status == 'USED' && $status != 'ISSUED') {
        echo json_encode(array('success' => false, 'message' => 'USED 상태는 ISSUED로만 변경할 수 있습니다.'));
        exit;
    }
    
    // USED -> ISSUED로 변경하는 경우 유효성 검사
    if ($old_status == 'USED' && $status == 'ISSUED') {
        $current_date = date('Y-m-d');
        $is_active = ($exist_row['is_active'] == 't' || $exist_row['is_active'] === true || $exist_row['is_active'] == '1' || $exist_row['is_active'] === 'true');
        
        // 쿠폰 활성 상태 확인
        if (!$is_active) {
            echo json_encode(array('success' => false, 'message' => '비활성화된 쿠폰은 ISSUED 상태로 변경할 수 없습니다.'));
            exit;
        }
        
        // 유효기간 확인
        if ($exist_row['valid_from'] && $exist_row['valid_from'] > $current_date) {
            echo json_encode(array('success' => false, 'message' => '아직 유효기간이 시작되지 않은 쿠폰은 ISSUED 상태로 변경할 수 없습니다.'));
            exit;
        }
        
        if ($exist_row['valid_until'] && $exist_row['valid_until'] < $current_date) {
            echo json_encode(array('success' => false, 'message' => '유효기간이 만료된 쿠폰은 ISSUED 상태로 변경할 수 없습니다.'));
            exit;
        }
    }
    
    // UPDATE 처리
    $update_fields = array();
    $update_fields[] = "status = '{$status}'";
    
    // USED -> ISSUED로 변경하는 경우 used_at을 NULL로 변경
    if ($old_status == 'USED' && $status == 'ISSUED') {
        $update_fields[] = "used_at = NULL";
    }
    
    $update_sql = " UPDATE customer_coupons 
                    SET " . implode(", ", $update_fields) . "
                    WHERE customer_coupon_id = {$customer_coupon_id} ";
    
    sql_query_pg($update_sql);
    
    echo json_encode(array('success' => true, 'message' => '쿠폰 정보가 수정되었습니다.'));
    exit;
}

echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
exit;
?>
