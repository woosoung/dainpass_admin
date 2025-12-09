<?php
$sub_menu = "940100";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

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
        
        if ($mb_1_value === '0' || $mb_1_value === '') {
            alert('업체 데이터가 없습니다.', './shop_coupons_list.php');
            exit;
        }
        
        if (!empty($mb_1_value)) {
            $shop_id_check = (int)$mb_1_value;
            $shop_sql = " SELECT shop_id, status FROM {$g5['shop_table']} WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);
            
            if ($shop_row && $shop_row['shop_id']) {
                if ($shop_row['status'] == 'pending')
                    alert('아직 승인이 되지 않았습니다.');
                if ($shop_row['status'] == 'closed')
                    alert('폐업되었습니다.');
                if ($shop_row['status'] == 'shutdown')
                    alert('접근이 제한되었습니다. 플랫폼 관리자에게 문의하세요.');
                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
            } else {
                alert('업체 데이터가 없습니다.', './shop_coupons_list.php');
                exit;
            }
        }
    }
}

if (!$has_access) {
    alert('접속할 수 없는 페이지 입니다.', './shop_coupons_list.php');
    exit;
}

// 토큰 체크
check_admin_token();

// 파라미터 수신
$w = isset($_POST['w']) ? clean_xss_tags($_POST['w']) : '';
$coupon_id = isset($_POST['coupon_id']) ? (int)$_POST['coupon_id'] : 0;
$post_shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : 0;
$qstr = isset($_POST['qstr']) ? $_POST['qstr'] : '';

// shop_id 검증
if ($post_shop_id != $shop_id) {
    alert('잘못된 가맹점 정보입니다.', './shop_coupons_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
    exit;
}

// 입력값 수신 및 검증
$coupon_code = isset($_POST['coupon_code']) ? trim(clean_xss_tags($_POST['coupon_code'])) : '';
$coupon_name = isset($_POST['coupon_name']) ? trim(clean_xss_tags($_POST['coupon_name'])) : '';
$description = isset($_POST['description']) ? trim(clean_xss_tags($_POST['description'])) : '';
$discount_type = isset($_POST['discount_type']) ? clean_xss_tags($_POST['discount_type']) : '';
$discount_value = isset($_POST['discount_value']) ? (int)$_POST['discount_value'] : 0;
$min_purchase_amt = isset($_POST['min_purchase_amt']) && $_POST['min_purchase_amt'] !== '' ? (int)$_POST['min_purchase_amt'] : null;
$max_discount_amt = isset($_POST['max_discount_amt']) && $_POST['max_discount_amt'] !== '' ? (int)$_POST['max_discount_amt'] : null;
$valid_from = isset($_POST['valid_from']) ? clean_xss_tags($_POST['valid_from']) : '';
$valid_until = isset($_POST['valid_until']) && $_POST['valid_until'] !== '' ? clean_xss_tags($_POST['valid_until']) : null;
$total_limit = isset($_POST['total_limit']) && $_POST['total_limit'] !== '' ? (int)$_POST['total_limit'] : null;
$issued_limit = isset($_POST['issued_limit']) ? (int)$_POST['issued_limit'] : 1;
$is_active = isset($_POST['is_active']) ? ($_POST['is_active'] == '1' ? true : false) : true;

// 필수값 검증
if (!$coupon_code) {
    alert('쿠폰코드를 입력해주세요.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// 쿠폰코드 형식 검증
if ($w != 'u') {
    // 신규 등록 모드: SHOP{shop_id}-{8자리영문숫자} 형식 검증
    $expected_pattern = '/^SHOP' . $shop_id . '-[A-Z0-9]{8}$/';
    if (!preg_match($expected_pattern, $coupon_code)) {
        alert('쿠폰코드 형식이 올바르지 않습니다. (형식: SHOP' . $shop_id . '-{8자리영문숫자})', './shop_coupons_form.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
} else {
    // 수정 모드: 기본 형식 검증만
    if (!preg_match('/^[A-Za-z0-9_-]+$/', $coupon_code)) {
        alert('쿠폰코드는 영문, 숫자, 하이픈(-), 언더스코어(_)만 사용할 수 있습니다.', './shop_coupons_form.php?w=u&coupon_id=' . $coupon_id . ($qstr ? '&' . $qstr : ''));
        exit;
    }
}

if (!$coupon_name) {
    alert('쿠폰명을 입력해주세요.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

if (!$discount_type || !in_array($discount_type, array('PERCENT', 'AMOUNT'))) {
    alert('할인유형을 선택해주세요.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

if (!$discount_value || $discount_value < 1) {
    alert('할인값을 올바르게 입력해주세요.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// 백분율 할인인 경우 할인값이 100 이하여야 함
if ($discount_type == 'PERCENT' && $discount_value > 100) {
    alert('백분율 할인은 100%를 초과할 수 없습니다.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

if (!$valid_from || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $valid_from)) {
    alert('유효기간 시작일을 올바르게 입력해주세요.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

if ($valid_until && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $valid_until)) {
    alert('유효기간 종료일을 올바르게 입력해주세요.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

if ($valid_until && $valid_until < $valid_from) {
    alert('유효기간 종료일은 시작일보다 늦어야 합니다.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

if (!$issued_limit || $issued_limit < 1) {
    alert('1인당 발급한도를 올바르게 입력해주세요.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

if ($total_limit !== null && $total_limit < 1) {
    alert('전체 발급한도는 1 이상이어야 합니다.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

if ($max_discount_amt !== null && $max_discount_amt < 0) {
    alert('최대할인금액은 0 이상이어야 합니다.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

if ($min_purchase_amt !== null && $min_purchase_amt < 0) {
    alert('최소결제금액은 0 이상이어야 합니다.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

if ($w == 'u') {
    // 수정 모드
    if (!$coupon_id || $coupon_id < 1) {
        alert('쿠폰 ID가 올바르지 않습니다.', './shop_coupons_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    // 기존 데이터 확인
    $exist_sql = " SELECT * FROM shop_coupons WHERE coupon_id = {$coupon_id} AND shop_id = {$shop_id} ";
    $exist_row = sql_fetch_pg($exist_sql);
    
    if (!$exist_row || !$exist_row['coupon_id']) {
        alert('존재하지 않는 쿠폰입니다.', './shop_coupons_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    // 쿠폰코드 중복 체크 (자기 자신 제외)
    if ($exist_row['coupon_code'] != $coupon_code) {
        $check_sql = " SELECT coupon_id FROM shop_coupons WHERE coupon_code = '{$coupon_code}' AND shop_id = {$shop_id} ";
        $check_row = sql_fetch_pg($check_sql);
        
        if ($check_row && $check_row['coupon_id']) {
            alert('이미 사용 중인 쿠폰코드입니다.', './shop_coupons_form.php?w=u&coupon_id=' . $coupon_id . ($qstr ? '&' . $qstr : ''));
            exit;
        }
    }
    
    // UPDATE
    $update_fields = array();
    $update_fields[] = "coupon_name = '" . addslashes($coupon_name) . "'";
    $update_fields[] = "description = " . ($description ? "'" . addslashes($description) . "'" : "NULL");
    $update_fields[] = "discount_type = '{$discount_type}'";
    $update_fields[] = "discount_value = {$discount_value}";
    $update_fields[] = "min_purchase_amt = " . ($min_purchase_amt !== null ? $min_purchase_amt : "NULL");
    $update_fields[] = "max_discount_amt = " . ($max_discount_amt !== null ? $max_discount_amt : "NULL");
    $update_fields[] = "valid_from = '{$valid_from}'";
    $update_fields[] = "valid_until = " . ($valid_until ? "'{$valid_until}'" : "NULL");
    $update_fields[] = "total_limit = " . ($total_limit !== null ? $total_limit : "NULL");
    $update_fields[] = "issued_limit = {$issued_limit}";
    $update_fields[] = "is_active = " . ($is_active ? 'true' : 'false');
    $update_fields[] = "updated_at = NOW()";
    
    $update_sql = " UPDATE shop_coupons 
                    SET " . implode(", ", $update_fields) . "
                    WHERE coupon_id = {$coupon_id} 
                    AND shop_id = {$shop_id} ";
    
    sql_query_pg($update_sql);
    
    alert('쿠폰이 수정되었습니다.', './shop_coupons_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
    
} else {
    // 신규 등록 모드
    // 쿠폰코드 형식 재검증 (서버 측)
    $expected_pattern = '/^SHOP' . $shop_id . '-[A-Z0-9]{8}$/';
    if (!preg_match($expected_pattern, $coupon_code)) {
        alert('쿠폰코드 형식이 올바르지 않습니다. (형식: SHOP' . $shop_id . '-{8자리영문숫자})', './shop_coupons_form.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    // 쿠폰코드 중복 체크
    $check_sql = " SELECT coupon_id FROM shop_coupons WHERE coupon_code = '{$coupon_code}' AND shop_id = {$shop_id} ";
    $check_row = sql_fetch_pg($check_sql);
    
    if ($check_row && $check_row['coupon_id']) {
        alert('이미 사용 중인 쿠폰코드입니다. 쿠폰코드를 재생성해주세요.', './shop_coupons_form.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    // INSERT
    $insert_sql = " INSERT INTO shop_coupons (
                        shop_id, 
                        coupon_code, 
                        coupon_name, 
                        description, 
                        discount_type, 
                        discount_value, 
                        min_purchase_amt, 
                        max_discount_amt, 
                        valid_from, 
                        valid_until, 
                        total_limit, 
                        issued_limit, 
                        total_issued, 
                        is_active, 
                        created_at, 
                        updated_at
                    ) VALUES (
                        {$shop_id},
                        '{$coupon_code}',
                        '" . addslashes($coupon_name) . "',
                        " . ($description ? "'" . addslashes($description) . "'" : "NULL") . ",
                        '{$discount_type}',
                        {$discount_value},
                        " . ($min_purchase_amt !== null ? $min_purchase_amt : "NULL") . ",
                        " . ($max_discount_amt !== null ? $max_discount_amt : "NULL") . ",
                        '{$valid_from}',
                        " . ($valid_until ? "'{$valid_until}'" : "NULL") . ",
                        " . ($total_limit !== null ? $total_limit : "NULL") . ",
                        {$issued_limit},
                        0,
                        " . ($is_active ? 'true' : 'false') . ",
                        NOW(),
                        NOW()
                    ) ";
    
    sql_query_pg($insert_sql);
    
    alert('쿠폰이 등록되었습니다.', './shop_coupons_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
}

exit;
?>
