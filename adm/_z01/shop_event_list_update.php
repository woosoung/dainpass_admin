<?php
$sub_menu = "940300";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

// 토큰 체크
check_admin_token();

// 화이트리스트로 허용값 정의
$allowed_actions = array('delete');
$allowed_sst = array('discount_id', 'discount_title', 'discount_scope', 'discount_type', 'start_datetime', 'end_datetime', 'is_active', 'created_at');
$allowed_sod = array('asc', 'desc');
$allowed_sfl = array('', 'discount_title');
$allowed_sfl2 = array('', 'active', 'inactive');
$allowed_sfl3 = array('', 'SHOP', 'SERVICE');

// action 또는 act 필드 확인 및 검증
$action = isset($_POST['action']) ? clean_xss_tags($_POST['action']) : (isset($_POST['act']) ? clean_xss_tags($_POST['act']) : '');
$action = in_array($action, $allowed_actions) ? $action : '';

// qstr 생성 - 입력값 검증 강화
$qstr = '';
if (isset($_POST['page']) && $_POST['page']) {
    $page = (int)$_POST['page'];
    $page = ($page > 0 && $page <= 10000) ? $page : 1;
    $qstr .= '&page=' . $page;
}
if (isset($_POST['sst']) && $_POST['sst']) {
    $sst = clean_xss_tags($_POST['sst']);
    if (in_array($sst, $allowed_sst)) {
        $qstr .= '&sst=' . urlencode($sst);
    }
}
if (isset($_POST['sod']) && $_POST['sod']) {
    $sod = clean_xss_tags($_POST['sod']);
    if (in_array($sod, $allowed_sod)) {
        $qstr .= '&sod=' . urlencode($sod);
    }
}
if (isset($_POST['sfl']) && $_POST['sfl']) {
    $sfl = clean_xss_tags($_POST['sfl']);
    if (in_array($sfl, $allowed_sfl)) {
        $qstr .= '&sfl=' . urlencode($sfl);
    }
}
if (isset($_POST['stx']) && $_POST['stx']) {
    $stx = clean_xss_tags($_POST['stx']);
    $stx = substr($stx, 0, 100);
    $qstr .= '&stx=' . urlencode($stx);
}
if (isset($_POST['sfl2']) && $_POST['sfl2']) {
    $sfl2 = clean_xss_tags($_POST['sfl2']);
    if (in_array($sfl2, $allowed_sfl2)) {
        $qstr .= '&sfl2=' . urlencode($sfl2);
    }
}
if (isset($_POST['sfl3']) && $_POST['sfl3']) {
    $sfl3 = clean_xss_tags($_POST['sfl3']);
    if (in_array($sfl3, $allowed_sfl3)) {
        $qstr .= '&sfl3=' . urlencode($sfl3);
    }
}

if ($action == 'delete') {
    $chk = isset($_POST['chk']) ? $_POST['chk'] : array();

    if (empty($chk) || !is_array($chk)) {
        alert('선택된 항목이 없습니다.', './shop_event_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }

    // 배열 크기 제한 (최대 100개)
    if (count($chk) > 100) {
        alert('한 번에 최대 100개까지만 삭제할 수 있습니다.', './shop_event_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }

    $chk_count = count($chk);
    $chk_ids = array();

    foreach ($chk as $discount_id) {
        $discount_id = (int)$discount_id;
        // 양수이고 정상 범위 내의 ID만 허용
        if ($discount_id > 0 && $discount_id <= 2147483647) {
            // shop_id 검증 - SQL injection 방지
            $check_sql = " SELECT shop_id FROM shop_discounts WHERE discount_id = " . (int)$discount_id . " ";
            $check_row = sql_fetch_pg($check_sql);

            if ($check_row && (int)$check_row['shop_id'] === (int)$shop_id) {
                // shop_appointment_details 테이블에서 해당 discount_id가 사용 중인지 확인
                $used_check_sql = " SELECT COUNT(*) as cnt FROM shop_appointment_details WHERE discount_id = " . (int)$discount_id . " ";
                $used_check_row = sql_fetch_pg($used_check_sql);

                if ($used_check_row && $used_check_row['cnt'] > 0) {
                    // 사용 중인 이벤트는 삭제 불가
                    continue;
                } else {
                    // 사용되지 않은 이벤트만 삭제 가능
                    $chk_ids[] = (int)$discount_id;
                }
            }
        }
    }

    if (empty($chk_ids)) {
        if ($chk_count > 0) {
            alert('선택한 이벤트 중 사용 중인 이벤트가 있어 삭제할 수 없습니다.', './shop_event_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        } else {
            alert('선택된 항목이 없습니다.', './shop_event_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        }
        exit;
    }

    // 정수형 배열을 안전하게 문자열로 변환
    $ids_str = implode(',', array_map('intval', $chk_ids));

    // shop_id 검증 후 삭제
    $delete_sql = " DELETE FROM shop_discounts
                    WHERE shop_id = " . (int)$shop_id . "
                    AND discount_id IN (" . $ids_str . ") ";

    sql_query_pg($delete_sql);

    alert('선택한 ' . count($chk_ids) . '개의 이벤트가 삭제되었습니다.', './shop_event_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));

} else {
    alert('잘못된 요청입니다.', './shop_event_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
}

exit;
?>
