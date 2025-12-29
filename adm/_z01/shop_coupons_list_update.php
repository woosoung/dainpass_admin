<?php
$sub_menu = "940100";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

// 토큰 체크
check_admin_token();

// action 또는 act 필드 확인 - 화이트리스트 검증
$action_input = isset($_POST['action']) ? clean_xss_tags($_POST['action']) : (isset($_POST['act']) ? clean_xss_tags($_POST['act']) : '');
$allowed_actions = array('delete');
$action = in_array($action_input, $allowed_actions) ? $action_input : '';

// qstr 생성 - 화이트리스트 검증
$qstr = '';

// page
if (isset($_POST['page']) && $_POST['page']) {
    $page = (int)$_POST['page'];
    if ($page > 0) {
        $qstr .= '&page=' . $page;
    }
}

// sst (정렬 필드)
$allowed_sst = array('coupon_id', 'coupon_code', 'coupon_name', 'discount_type', 'valid_from', 'valid_until', 'is_active', 'created_at');
if (isset($_POST['sst']) && in_array($_POST['sst'], $allowed_sst)) {
    $qstr .= '&sst=' . urlencode($_POST['sst']);
}

// sod (정렬 방향)
$allowed_sod = array('asc', 'desc');
if (isset($_POST['sod']) && in_array($_POST['sod'], $allowed_sod)) {
    $qstr .= '&sod=' . urlencode($_POST['sod']);
}

// sfl (검색 필드)
$allowed_sfl = array('', 'coupon_code', 'coupon_name', 'description');
if (isset($_POST['sfl']) && in_array($_POST['sfl'], $allowed_sfl)) {
    $qstr .= '&sfl=' . urlencode($_POST['sfl']);
}

// stx (검색어)
if (isset($_POST['stx']) && $_POST['stx']) {
    $qstr .= '&stx=' . urlencode(clean_xss_tags($_POST['stx']));
}

// sfl2 (활성화 상태 필터)
$allowed_sfl2 = array('', 'active', 'inactive');
if (isset($_POST['sfl2']) && in_array($_POST['sfl2'], $allowed_sfl2)) {
    $qstr .= '&sfl2=' . urlencode($_POST['sfl2']);
}

if ($action == 'delete') {
    $chk = isset($_POST['chk']) ? $_POST['chk'] : array();
    
    if (empty($chk) || !is_array($chk)) {
        alert('선택된 항목이 없습니다.', './shop_coupons_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    $chk_count = count($chk);
    $chk_ids = array();
    $used_coupon_ids = array();
    $used_coupon_codes = array();
    
    foreach ($chk as $coupon_id) {
        $coupon_id = (int)$coupon_id;
        if ($coupon_id > 0) {
            // shop_id 검증
            $check_sql = " SELECT shop_id, coupon_code FROM shop_coupons WHERE coupon_id = {$coupon_id} ";
            $check_row = sql_fetch_pg($check_sql);
            
            if ($check_row && $check_row['shop_id'] == $shop_id) {
                // customer_coupons 테이블에서 해당 coupon_id가 사용 중인지 확인
                $used_check_sql = " SELECT COUNT(*) as cnt FROM customer_coupons WHERE coupon_id = {$coupon_id} ";
                $used_check_row = sql_fetch_pg($used_check_sql);
                
                if ($used_check_row && $used_check_row['cnt'] > 0) {
                    // 사용 중인 쿠폰
                    $used_coupon_ids[] = $coupon_id;
                    $used_coupon_codes[] = $check_row['coupon_code'];
                } else {
                    // 사용되지 않은 쿠폰만 삭제 가능
                    $chk_ids[] = $coupon_id;
                }
            }
        }
    }
    
    // 사용 중인 쿠폰이 하나라도 있으면 전체 삭제 불가
    if (!empty($used_coupon_ids)) {
        $used_count = count($used_coupon_ids);
        $used_codes_str = implode(', ', array_slice($used_coupon_codes, 0, 5)); // 최대 5개만 표시
        if ($used_count > 5) {
            $used_codes_str .= ' 외 ' . ($used_count - 5) . '개';
        }
        alert('고객이 발급받은 쿠폰은 삭제할 수 없습니다.\n\n사용 중인 쿠폰: ' . $used_codes_str . '\n\n총 ' . $used_count . '개의 쿠폰이 고객에게 발급되어 있어 삭제할 수 없습니다.', './shop_coupons_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    if (empty($chk_ids)) {
        alert('선택된 항목이 없습니다.', './shop_coupons_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    $ids_str = implode(',', $chk_ids);
    
    // shop_id 검증 후 삭제
    $delete_sql = " DELETE FROM shop_coupons 
                    WHERE shop_id = {$shop_id} 
                    AND coupon_id IN ({$ids_str}) ";
    
    sql_query_pg($delete_sql);
    
    alert('선택한 ' . count($chk_ids) . '개의 쿠폰이 삭제되었습니다.', './shop_coupons_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
    
} else {
    alert('잘못된 요청입니다.', './shop_coupons_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
}

exit;
?>
