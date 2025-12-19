<?php
$sub_menu = "940300";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

// 토큰 체크
check_admin_token();

// 파라미터 수신
$w = isset($_POST['w']) ? clean_xss_tags($_POST['w']) : '';
$discount_id = isset($_POST['discount_id']) ? (int)$_POST['discount_id'] : 0;
$post_shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : 0;
$qstr = isset($_POST['qstr']) ? $_POST['qstr'] : '';

// shop_id 검증
if ($post_shop_id != $shop_id) {
    alert('잘못된 가맹점 정보입니다.', './shop_event_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
    exit;
}

// 입력값 수신 및 검증
$discount_title = isset($_POST['discount_title']) ? trim(clean_xss_tags($_POST['discount_title'])) : '';
$discount_scope = isset($_POST['discount_scope']) ? clean_xss_tags($_POST['discount_scope']) : '';
$service_id = isset($_POST['service_id']) && $_POST['service_id'] !== '' ? (int)$_POST['service_id'] : null;
$discount_type = isset($_POST['discount_type']) ? clean_xss_tags($_POST['discount_type']) : '';
$discount_value = isset($_POST['discount_value']) ? (int)$_POST['discount_value'] : 0;
$start_datetime = isset($_POST['start_datetime']) ? clean_xss_tags($_POST['start_datetime']) : '';
$end_datetime = isset($_POST['end_datetime']) && $_POST['end_datetime'] !== '' ? clean_xss_tags($_POST['end_datetime']) : null;
$is_active = isset($_POST['is_active']) ? ($_POST['is_active'] == '1' ? true : false) : true;

// 필수값 검증
if (!$discount_title) {
    alert('이벤트제목을 입력해주세요.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

if (!$discount_scope || !in_array($discount_scope, array('SHOP', 'SERVICE'))) {
    alert('할인범위를 선택해주세요.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// 서비스별 할인인 경우 service_id 필수
if ($discount_scope == 'SERVICE' && (!$service_id || $service_id < 1)) {
    alert('서비스별 할인인 경우 서비스를 선택해주세요.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// 가맹점 전체 할인인 경우 service_id는 NULL
if ($discount_scope == 'SHOP') {
    $service_id = null;
}

if (!$discount_type || !in_array($discount_type, array('PERCENT', 'AMOUNT'))) {
    alert('할인유형을 선택해주세요.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

if (!$discount_value || $discount_value < 1) {
    alert('할인값을 올바르게 입력해주세요.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// 백분율 할인인 경우 할인값이 100 이하여야 함
if ($discount_type == 'PERCENT' && $discount_value > 100) {
    alert('백분율 할인은 100%를 초과할 수 없습니다.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// datetime 형식 검증 및 변환
if (!$start_datetime) {
    alert('시작일시를 올바르게 입력해주세요.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// datetime-local 형식을 PostgreSQL datetime 형식으로 변환
$start_datetime = str_replace('T', ' ', $start_datetime) . ':00';

if ($end_datetime) {
    $end_datetime = str_replace('T', ' ', $end_datetime) . ':00';
    
    // 종료일시가 시작일시보다 이전인지 확인
    if (strtotime($end_datetime) < strtotime($start_datetime)) {
        alert('종료일시는 시작일시보다 늦어야 합니다.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
        exit;
    }
}

if ($w == 'u') {
    // 수정 모드
    if (!$discount_id || $discount_id < 1) {
        alert('이벤트 ID가 올바르지 않습니다.', './shop_event_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    // 기존 데이터 확인
    $exist_sql = " SELECT * FROM shop_discounts WHERE discount_id = {$discount_id} AND shop_id = {$shop_id} ";
    $exist_row = sql_fetch_pg($exist_sql);
    
    if (!$exist_row || !$exist_row['discount_id']) {
        alert('존재하지 않는 이벤트입니다.', './shop_event_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    // UPDATE
    $update_fields = array();
    $update_fields[] = "discount_title = '" . addslashes($discount_title) . "'";
    $update_fields[] = "discount_scope = '{$discount_scope}'";
    $update_fields[] = "service_id = " . ($service_id !== null ? $service_id : "NULL");
    $update_fields[] = "discount_type = '{$discount_type}'";
    $update_fields[] = "discount_value = {$discount_value}";
    $update_fields[] = "start_datetime = '{$start_datetime}'";
    $update_fields[] = "end_datetime = " . ($end_datetime ? "'{$end_datetime}'" : "NULL");
    $update_fields[] = "is_active = " . ($is_active ? 'true' : 'false');
    $update_fields[] = "updated_at = NOW()";
    
    $update_sql = " UPDATE shop_discounts 
                    SET " . implode(", ", $update_fields) . "
                    WHERE discount_id = {$discount_id} 
                    AND shop_id = {$shop_id} ";
    
    sql_query_pg($update_sql);
    
    alert('이벤트가 수정되었습니다.', './shop_event_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
    
} else {
    // 신규 등록 모드
    // INSERT
    $insert_sql = " INSERT INTO shop_discounts (
                        shop_id, 
                        service_id,
                        discount_scope,
                        discount_type, 
                        discount_value, 
                        discount_title,
                        start_datetime, 
                        end_datetime, 
                        is_active, 
                        created_at, 
                        updated_at
                    ) VALUES (
                        {$shop_id},
                        " . ($service_id !== null ? $service_id : "NULL") . ",
                        '{$discount_scope}',
                        '{$discount_type}',
                        {$discount_value},
                        '" . addslashes($discount_title) . "',
                        '{$start_datetime}',
                        " . ($end_datetime ? "'{$end_datetime}'" : "NULL") . ",
                        " . ($is_active ? 'true' : 'false') . ",
                        NOW(),
                        NOW()
                    ) ";
    
    sql_query_pg($insert_sql);
    
    alert('이벤트가 등록되었습니다.', './shop_event_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
}

exit;
?>
