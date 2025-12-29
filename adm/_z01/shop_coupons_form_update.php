<?php
$sub_menu = "940100";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = (int)$result['shop_id'];

// 토큰 체크
check_admin_token();

// 파라미터 수신 - 화이트리스트 검증
// w (모드: u=수정, 그 외=신규)
$w_input = isset($_POST['w']) ? clean_xss_tags($_POST['w']) : '';
$allowed_w = array('u');
$w = in_array($w_input, $allowed_w) ? $w_input : '';

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

// 쿠폰코드에서 하이픈 제거 (DB에는 하이픈 없이 저장)
$coupon_code = str_replace('-', '', $coupon_code);

$coupon_name = isset($_POST['coupon_name']) ? trim(clean_xss_tags($_POST['coupon_name'])) : '';
$description = isset($_POST['description']) ? trim(clean_xss_tags($_POST['description'])) : '';

// coupon_name 길이 검증 (최대 50자)
if ($coupon_name && mb_strlen($coupon_name) > 50) {
    alert('쿠폰명은 최대 50자까지 입력 가능합니다.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// description 길이 검증 (최대 1000자)
if ($description && mb_strlen($description) > 1000) {
    alert('상세설명은 최대 1000자까지 입력 가능합니다.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

$discount_type = isset($_POST['discount_type']) ? clean_xss_tags($_POST['discount_type']) : '';
$discount_value = isset($_POST['discount_value']) ? (int)$_POST['discount_value'] : 0;
$min_purchase_amt = isset($_POST['min_purchase_amt']) && $_POST['min_purchase_amt'] !== '' ? (int)$_POST['min_purchase_amt'] : null;
$max_discount_amt = isset($_POST['max_discount_amt']) && $_POST['max_discount_amt'] !== '' ? (int)$_POST['max_discount_amt'] : null;
$valid_from = isset($_POST['valid_from']) ? clean_xss_tags($_POST['valid_from']) : '';
$valid_until = isset($_POST['valid_until']) && $_POST['valid_until'] !== '' ? clean_xss_tags($_POST['valid_until']) : null;
$total_limit = isset($_POST['total_limit']) && $_POST['total_limit'] !== '' ? (int)$_POST['total_limit'] : null;
$issued_limit = isset($_POST['issued_limit']) ? (int)$_POST['issued_limit'] : 1;

// is_active 검증 - '0' 또는 '1'만 허용
$is_active_input = isset($_POST['is_active']) ? $_POST['is_active'] : '1';
$allowed_is_active = array('0', '1');
$is_active = in_array($is_active_input, $allowed_is_active) ? ($is_active_input == '1') : true;

// 필수값 검증
if (!$coupon_code) {
    alert('쿠폰코드를 입력해주세요.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// coupon_code 길이 검증 (최대 50자)
if (strlen($coupon_code) > 50) {
    alert('쿠폰코드는 최대 50자까지 입력 가능합니다.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// 쿠폰코드 형식 검증
if ($w != 'u') {
    // 신규 등록 모드: 12자리 영문숫자 형식 검증 (혼동되는 문자 제외: I, O, 0, 1)
    if (!preg_match('/^[ABCDEFGHJKLMNPQRSTUVWXYZ23456789]{12}$/', $coupon_code)) {
        alert('쿠폰코드 형식이 올바르지 않습니다. (형식: 12자리 영문숫자)', './shop_coupons_form.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
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

// 백분율 할인인 경우 할인값이 1~100 범위여야 함
if ($discount_type == 'PERCENT') {
    if ($discount_value < 1 || $discount_value > 100) {
        alert('백분율 할인은 1%에서 100% 사이여야 합니다.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
        exit;
    }
}

// 정액 할인인 경우 합리적인 범위 검증 (최대 1억원)
if ($discount_type == 'AMOUNT') {
    if ($discount_value > 100000000) {
        alert('할인금액은 1억원을 초과할 수 없습니다.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
        exit;
    }
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

// 1인당 발급한도 최대값 검증 (최대 1000장)
if ($issued_limit > 1000) {
    alert('1인당 발급한도는 최대 1,000장까지 가능합니다.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

if ($total_limit !== null && $total_limit < 1) {
    alert('전체 발급한도는 1 이상이어야 합니다.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// 전체 발급한도 최대값 검증 (최대 100만장)
if ($total_limit !== null && $total_limit > 1000000) {
    alert('전체 발급한도는 최대 1,000,000장까지 가능합니다.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

if ($max_discount_amt !== null && $max_discount_amt < 0) {
    alert('최대할인금액은 0 이상이어야 합니다.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// 최대할인금액 최대값 검증 (최대 1억원)
if ($max_discount_amt !== null && $max_discount_amt > 100000000) {
    alert('최대할인금액은 1억원을 초과할 수 없습니다.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

if ($min_purchase_amt !== null && $min_purchase_amt < 0) {
    alert('최소결제금액은 0 이상이어야 합니다.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// 최소결제금액 최대값 검증 (최대 1억원)
if ($min_purchase_amt !== null && $min_purchase_amt > 100000000) {
    alert('최소결제금액은 1억원을 초과할 수 없습니다.', './shop_coupons_form.php?w=' . $w . ($coupon_id ? '&coupon_id=' . $coupon_id : '') . ($qstr ? '&' . $qstr : ''));
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
    
    // 쿠폰코드 중복 체크 (자기 자신 제외, 전체 시스템에서 유일해야 함)
    if ($exist_row['coupon_code'] != $coupon_code) {
        $check_sql = " SELECT coupon_id FROM shop_coupons WHERE coupon_code = '{$coupon_code}' ";
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
    if (!preg_match('/^[ABCDEFGHJKLMNPQRSTUVWXYZ23456789]{12}$/', $coupon_code)) {
        alert('쿠폰코드 형식이 올바르지 않습니다. (형식: 12자리 영문숫자)', './shop_coupons_form.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }

    // 쿠폰코드 중복 체크 (전체 시스템에서 유일해야 함)
    $check_sql = " SELECT coupon_id FROM shop_coupons WHERE coupon_code = '{$coupon_code}' ";
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
