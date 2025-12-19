<?php
$sub_menu = "940300";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

// 토큰 체크
check_admin_token();

// action 또는 act 필드 확인
$action = isset($_POST['action']) ? clean_xss_tags($_POST['action']) : (isset($_POST['act']) ? clean_xss_tags($_POST['act']) : '');

// qstr 생성
$qstr = '';
if (isset($_POST['page']) && $_POST['page']) {
    $qstr .= '&page=' . (int)$_POST['page'];
}
if (isset($_POST['sst']) && $_POST['sst']) {
    $qstr .= '&sst=' . urlencode($_POST['sst']);
}
if (isset($_POST['sod']) && $_POST['sod']) {
    $qstr .= '&sod=' . urlencode($_POST['sod']);
}
if (isset($_POST['sfl']) && $_POST['sfl']) {
    $qstr .= '&sfl=' . urlencode($_POST['sfl']);
}
if (isset($_POST['stx']) && $_POST['stx']) {
    $qstr .= '&stx=' . urlencode($_POST['stx']);
}
if (isset($_POST['sfl2']) && $_POST['sfl2']) {
    $qstr .= '&sfl2=' . urlencode($_POST['sfl2']);
}
if (isset($_POST['sfl3']) && $_POST['sfl3']) {
    $qstr .= '&sfl3=' . urlencode($_POST['sfl3']);
}

if ($action == 'delete') {
    $chk = isset($_POST['chk']) ? $_POST['chk'] : array();
    
    if (empty($chk) || !is_array($chk)) {
        alert('선택된 항목이 없습니다.', './shop_event_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    $chk_count = count($chk);
    $chk_ids = array();
    
    foreach ($chk as $discount_id) {
        $discount_id = (int)$discount_id;
        if ($discount_id > 0) {
            // shop_id 검증
            $check_sql = " SELECT shop_id FROM shop_discounts WHERE discount_id = {$discount_id} ";
            $check_row = sql_fetch_pg($check_sql);
            
            if ($check_row && $check_row['shop_id'] == $shop_id) {
                // shop_appointment_details 테이블에서 해당 discount_id가 사용 중인지 확인
                $used_check_sql = " SELECT COUNT(*) as cnt FROM shop_appointment_details WHERE discount_id = {$discount_id} ";
                $used_check_row = sql_fetch_pg($used_check_sql);
                
                if ($used_check_row && $used_check_row['cnt'] > 0) {
                    // 사용 중인 이벤트는 삭제 불가
                    continue;
                } else {
                    // 사용되지 않은 이벤트만 삭제 가능
                    $chk_ids[] = $discount_id;
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
    
    $ids_str = implode(',', $chk_ids);
    
    // shop_id 검증 후 삭제
    $delete_sql = " DELETE FROM shop_discounts 
                    WHERE shop_id = {$shop_id} 
                    AND discount_id IN ({$ids_str}) ";
    
    sql_query_pg($delete_sql);
    
    alert('선택한 ' . count($chk_ids) . '개의 이벤트가 삭제되었습니다.', './shop_event_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
    
} else {
    alert('잘못된 요청입니다.', './shop_event_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
}

exit;
?>
