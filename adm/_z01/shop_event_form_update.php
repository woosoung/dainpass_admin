<?php
$sub_menu = "940300";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

// 토큰 체크
check_admin_token();

// 화이트리스트 정의
$allowed_w = array('', 'u');

// 파라미터 수신 및 검증
$w = isset($_POST['w']) ? clean_xss_tags($_POST['w']) : '';
$w = in_array($w, $allowed_w) ? $w : '';

$discount_id = isset($_POST['discount_id']) ? (int)$_POST['discount_id'] : 0;
$discount_id = ($discount_id > 0 && $discount_id <= 2147483647) ? $discount_id : 0;

$post_shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : 0;
$qstr = isset($_POST['qstr']) ? $_POST['qstr'] : '';

// shop_id 엄격한 검증
if ((int)$post_shop_id !== (int)$shop_id) {
    alert('잘못된 가맹점 정보입니다.', './shop_event_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
    exit;
}

// 입력값 수신 및 검증
$discount_title = isset($_POST['discount_title']) ? trim(clean_xss_tags($_POST['discount_title'])) : '';
$discount_title = substr($discount_title, 0, 100); // 최대 길이 제한

$allowed_scope = array('SHOP', 'SERVICE');
$discount_scope = isset($_POST['discount_scope']) ? clean_xss_tags($_POST['discount_scope']) : '';
$discount_scope = in_array($discount_scope, $allowed_scope) ? $discount_scope : '';

$service_id = isset($_POST['service_id']) && $_POST['service_id'] !== '' ? (int)$_POST['service_id'] : null;
$service_id = ($service_id !== null && $service_id > 0 && $service_id <= 2147483647) ? $service_id : null;

$allowed_type = array('PERCENT', 'AMOUNT');
$discount_type = isset($_POST['discount_type']) ? clean_xss_tags($_POST['discount_type']) : '';
$discount_type = in_array($discount_type, $allowed_type) ? $discount_type : '';

$discount_value = isset($_POST['discount_value']) ? (int)$_POST['discount_value'] : 0;
// 할인값 최대값 제한 (정액 할인: 최대 10억, 백분율: 최대 100)
$discount_value = ($discount_value > 0 && $discount_value <= 1000000000) ? $discount_value : 0;

$start_datetime = isset($_POST['start_datetime']) ? clean_xss_tags($_POST['start_datetime']) : '';
$end_datetime = isset($_POST['end_datetime']) && $_POST['end_datetime'] !== '' ? clean_xss_tags($_POST['end_datetime']) : null;

$allowed_is_active = array('0', '1');
$is_active_value = isset($_POST['is_active']) ? clean_xss_tags($_POST['is_active']) : '1';
$is_active_value = in_array($is_active_value, $allowed_is_active) ? $is_active_value : '1';
$is_active = ($is_active_value == '1' ? true : false);

// 필수값 검증
if (empty($discount_title) || strlen($discount_title) < 1) {
    alert('이벤트제목을 입력해주세요.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// 이미 화이트리스트 검증되었으므로 빈값만 체크
if (empty($discount_scope)) {
    alert('할인범위를 선택해주세요.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// 서비스별 할인인 경우 service_id 필수
if ($discount_scope == 'SERVICE' && (!$service_id || $service_id < 1)) {
    alert('서비스별 할인인 경우 서비스를 선택해주세요.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// 서비스별 할인인 경우 service_id가 해당 가맹점의 서비스인지 검증
if ($discount_scope == 'SERVICE' && $service_id) {
    $service_check_sql = " SELECT COUNT(*) as cnt FROM shop_services WHERE service_id = " . (int)$service_id . " AND shop_id = " . (int)$shop_id . " ";
    $service_check_row = sql_fetch_pg($service_check_sql);
    if (!$service_check_row || $service_check_row['cnt'] < 1) {
        alert('유효하지 않은 서비스입니다.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
        exit;
    }
}

// 가맹점 전체 할인인 경우 service_id는 NULL
if ($discount_scope == 'SHOP') {
    $service_id = null;
}

// 이미 화이트리스트 검증되었으므로 빈값만 체크
if (empty($discount_type)) {
    alert('할인유형을 선택해주세요.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

if ($discount_value < 1) {
    alert('할인값을 올바르게 입력해주세요.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// 백분율 할인인 경우 할인값이 100 이하여야 함
if ($discount_type == 'PERCENT' && $discount_value > 100) {
    alert('백분율 할인은 100%를 초과할 수 없습니다.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// 정액 할인인 경우 최대값 제한
if ($discount_type == 'AMOUNT' && $discount_value > 100000000) {
    alert('정액 할인은 최대 1억원까지 설정할 수 있습니다.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// datetime 형식 검증 및 변환
if (empty($start_datetime)) {
    alert('시작일시를 올바르게 입력해주세요.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// datetime-local 형식 검증 (YYYY-MM-DDTHH:MM)
if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $start_datetime)) {
    alert('시작일시 형식이 올바르지 않습니다.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

// datetime-local 형식을 PostgreSQL datetime 형식으로 변환
$start_datetime = str_replace('T', ' ', $start_datetime) . ':00';

// 날짜 유효성 추가 검증
if (strtotime($start_datetime) === false) {
    alert('시작일시가 유효하지 않습니다.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
    exit;
}

if ($end_datetime) {
    // datetime-local 형식 검증
    if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $end_datetime)) {
        alert('종료일시 형식이 올바르지 않습니다.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
        exit;
    }

    $end_datetime = str_replace('T', ' ', $end_datetime) . ':00';

    // 날짜 유효성 추가 검증
    if (strtotime($end_datetime) === false) {
        alert('종료일시가 유효하지 않습니다.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
        exit;
    }

    // 종료일시가 시작일시보다 이전인지 확인
    if (strtotime($end_datetime) <= strtotime($start_datetime)) {
        alert('종료일시는 시작일시보다 늦어야 합니다.', './shop_event_form.php?w=' . $w . ($discount_id ? '&discount_id=' . $discount_id : '') . ($qstr ? '&' . $qstr : ''));
        exit;
    }
}

if ($w == 'u') {
    // 수정 모드
    if ($discount_id < 1) {
        alert('이벤트 ID가 올바르지 않습니다.', './shop_event_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }

    // 기존 데이터 확인 - SQL injection 방지
    $exist_sql = " SELECT * FROM shop_discounts WHERE discount_id = " . (int)$discount_id . " AND shop_id = " . (int)$shop_id . " ";
    $exist_row = sql_fetch_pg($exist_sql);

    if (!$exist_row || !$exist_row['discount_id']) {
        alert('존재하지 않는 이벤트입니다.', './shop_event_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }

    // UPDATE - SQL injection 방지를 위해 모든 값을 안전하게 처리
    $update_fields = array();
    $update_fields[] = "discount_title = '" . addslashes($discount_title) . "'";
    $update_fields[] = "discount_scope = '" . $discount_scope . "'"; // 이미 화이트리스트 검증됨
    $update_fields[] = "service_id = " . ($service_id !== null ? (int)$service_id : "NULL");
    $update_fields[] = "discount_type = '" . $discount_type . "'"; // 이미 화이트리스트 검증됨
    $update_fields[] = "discount_value = " . (int)$discount_value;
    $update_fields[] = "start_datetime = '" . addslashes($start_datetime) . "'";
    $update_fields[] = "end_datetime = " . ($end_datetime ? "'" . addslashes($end_datetime) . "'" : "NULL");
    $update_fields[] = "is_active = " . ($is_active ? 'true' : 'false');
    $update_fields[] = "updated_at = NOW()";

    $update_sql = " UPDATE shop_discounts
                    SET " . implode(", ", $update_fields) . "
                    WHERE discount_id = " . (int)$discount_id . "
                    AND shop_id = " . (int)$shop_id . " ";

    sql_query_pg($update_sql);

    alert('이벤트가 수정되었습니다.', './shop_event_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));

} else {
    // 신규 등록 모드
    // INSERT - SQL injection 방지를 위해 모든 값을 안전하게 처리
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
                        " . (int)$shop_id . ",
                        " . ($service_id !== null ? (int)$service_id : "NULL") . ",
                        '" . $discount_scope . "',
                        '" . $discount_type . "',
                        " . (int)$discount_value . ",
                        '" . addslashes($discount_title) . "',
                        '" . addslashes($start_datetime) . "',
                        " . ($end_datetime ? "'" . addslashes($end_datetime) . "'" : "NULL") . ",
                        " . ($is_active ? 'true' : 'false') . ",
                        NOW(),
                        NOW()
                    ) ";

    sql_query_pg($insert_sql);

    alert('이벤트가 등록되었습니다.', './shop_event_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
}

exit;
?>
