<?php
$sub_menu = "950100";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

// 토큰 체크
check_admin_token();

// 파라미터 수신
$action = isset($_POST['action']) ? clean_xss_tags($_POST['action']) : '';
$appointment_id = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;
$qstr = isset($_POST['qstr']) ? $_POST['qstr'] : '';

if ($action == 'status_update' && $appointment_id > 0) {
    $new_status = isset($_POST['new_status']) ? clean_xss_tags($_POST['new_status']) : '';
    
    // BOOKED 상태는 변경 불가
    if (empty($new_status) || !in_array($new_status, array('COMPLETED', 'CANCELLED'))) {
        alert('올바른 상태를 입력하세요. (COMPLETED, CANCELLED만 가능)', './shop_appointment_form.php?w=u&appointment_id=' . $appointment_id . ($qstr ? '&' . $qstr : ''));
        exit;
    }
    
    if ($new_status == 'BOOKED') {
        alert('BOOKED 상태로는 변경할 수 없습니다.', './shop_appointment_form.php?w=u&appointment_id=' . $appointment_id . ($qstr ? '&' . $qstr : ''));
        exit;
    }
    
    // 예약이 해당 가맹점의 예약인지 확인 (BOOKED 상태 제외)
    $check_sql = " SELECT sa.appointment_id 
                   FROM shop_appointments sa
                   INNER JOIN appointment_shop_detail asd ON sa.appointment_id = asd.appointment_id
                   WHERE sa.appointment_id = {$appointment_id} 
                   AND sa.status != 'BOOKED'
                   AND asd.shop_id = {$shop_id}
                   AND asd.status != 'BOOKED' ";
    $check_row = sql_fetch_pg($check_sql);
    
    if (!$check_row || !$check_row['appointment_id']) {
        alert('해당 가맹점의 예약이 아닙니다.', './shop_appointment_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    // 상태 업데이트 (BOOKED 상태는 업데이트 불가)
    $update_sql = " UPDATE shop_appointments 
                    SET status = '{$new_status}', 
                        updated_at = NOW() 
                    WHERE appointment_id = {$appointment_id} 
                    AND status != 'BOOKED' ";
    sql_query_pg($update_sql);
    
    alert('상태가 변경되었습니다.', './shop_appointment_form.php?w=u&appointment_id=' . $appointment_id . ($qstr ? '&' . $qstr : ''));
    
} else {
    alert('잘못된 요청입니다.', './shop_appointment_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
}

exit;
?>
