<?php
$sub_menu = "950100";
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
            alert('업체 데이터가 없습니다.', './shop_appointment_list.php');
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
                alert('업체 데이터가 없습니다.', './shop_appointment_list.php');
                exit;
            }
        }
    }
}

if (!$has_access) {
    alert('접속할 수 없는 페이지 입니다.', './shop_appointment_list.php');
    exit;
}

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
if (isset($_POST['fr_date']) && $_POST['fr_date']) {
    $qstr .= '&fr_date=' . urlencode($_POST['fr_date']);
}
if (isset($_POST['to_date']) && $_POST['to_date']) {
    $qstr .= '&to_date=' . urlencode($_POST['to_date']);
}

if ($action == 'status_update') {
    $chk = isset($_POST['chk']) ? $_POST['chk'] : array();
    $new_status = isset($_POST['new_status']) ? clean_xss_tags($_POST['new_status']) : '';
    
    if (empty($chk) || !is_array($chk)) {
        alert('선택된 항목이 없습니다.', './shop_appointment_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    if (empty($new_status)) {
        alert('변경할 상태를 선택하세요.', './shop_appointment_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    $chk_count = count($chk);
    $chk_ids = array();
    
    foreach ($chk as $appointment_id) {
        $appointment_id = (int)$appointment_id;
        if ($appointment_id > 0) {
            // shop_id 검증 (해당 예약이 이 가맹점의 예약인지 확인, BOOKED 상태 제외)
            $check_sql = " SELECT COUNT(*) as cnt 
                          FROM appointment_shop_detail asd
                          INNER JOIN shop_appointments sa ON asd.appointment_id = sa.appointment_id
                          WHERE asd.appointment_id = {$appointment_id} 
                          AND sa.status != 'BOOKED'
                          AND asd.shop_id = {$shop_id}
                          AND asd.status != 'BOOKED' ";
            $check_row = sql_fetch_pg($check_sql);
            
            if ($check_row && $check_row['cnt'] > 0) {
                $chk_ids[] = $appointment_id;
            }
        }
    }
    
    if (empty($chk_ids)) {
        alert('선택한 예약 중 해당 가맹점의 예약이 없습니다.', './shop_appointment_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    // 상태 변경 (BOOKED 상태는 변경 불가)
    if ($new_status == 'BOOKED') {
        alert('BOOKED 상태로는 변경할 수 없습니다.', './shop_appointment_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    $ids_str = implode(',', $chk_ids);
    
    $update_sql = " UPDATE shop_appointments 
                    SET status = '{$new_status}', 
                        updated_at = NOW() 
                    WHERE appointment_id IN ({$ids_str}) 
                    AND status != 'BOOKED'
                    AND EXISTS (
                        SELECT 1 FROM appointment_shop_detail 
                        WHERE appointment_id = shop_appointments.appointment_id 
                        AND shop_id = {$shop_id}
                        AND status != 'BOOKED'
                    ) ";
    
    sql_query_pg($update_sql);
    
    alert('선택한 ' . count($chk_ids) . '개의 예약 상태가 변경되었습니다.', './shop_appointment_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
    
} else {
    alert('잘못된 요청입니다.', './shop_appointment_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
}

exit;
?>
