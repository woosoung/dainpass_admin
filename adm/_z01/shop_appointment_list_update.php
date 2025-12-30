<?php
$sub_menu = "950100";
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
if (isset($_POST['fr_date']) && $_POST['fr_date']) {
    $qstr .= '&fr_date=' . urlencode($_POST['fr_date']);
}
if (isset($_POST['to_date']) && $_POST['to_date']) {
    $qstr .= '&to_date=' . urlencode($_POST['to_date']);
}

if ($action == 'status_update') {
    $chk = isset($_POST['chk']) ? $_POST['chk'] : array();
    $status_array = isset($_POST['status']) ? $_POST['status'] : array();

    // 검증
    if (empty($chk) || !is_array($chk)) {
        alert('선택된 항목이 없습니다.', './shop_appointment_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }

    if (empty($status_array) || !is_array($status_array)) {
        alert('상태 정보가 없습니다.', './shop_appointment_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }

    // 배열 크기 제한 (최대 100개)
    if (count($chk) > 100) {
        alert('한 번에 최대 100개까지만 변경할 수 있습니다.', './shop_appointment_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }

    $allowed_status = array('COMPLETED', 'CANCELLED');
    $update_data = array();

    foreach ($chk as $appointment_id) {
        $appointment_id = (int)$appointment_id;

        if ($appointment_id <= 0 || $appointment_id > 2147483647) {
            continue;
        }

        if (!isset($status_array[$appointment_id])) {
            continue;
        }

        $new_status = $status_array[$appointment_id];

        // 상태값 화이트리스트 검증
        if (!in_array($new_status, $allowed_status)) {
            continue;
        }

        // shop_id 검증 (해당 예약이 이 가맹점의 예약인지 확인, BOOKED 상태 제외)
        $check_sql = " SELECT COUNT(*) as cnt
                      FROM appointment_shop_detail asd
                      INNER JOIN shop_appointments sa ON asd.appointment_id = sa.appointment_id
                      WHERE asd.appointment_id = " . (int)$appointment_id . "
                      AND sa.status != 'BOOKED'
                      AND asd.shop_id = " . (int)$shop_id . "
                      AND asd.status != 'BOOKED' ";
        $check_row = sql_fetch_pg($check_sql);

        if ($check_row && $check_row['cnt'] > 0) {
            $update_data[] = array(
                'id' => (int)$appointment_id,
                'status' => $new_status
            );
        }
    }

    if (empty($update_data)) {
        alert('선택한 예약 중 변경 가능한 항목이 없습니다.', './shop_appointment_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }

    // 각 항목별로 상태 업데이트
    $updated_count = 0;
    foreach ($update_data as $data) {
        $update_sql = " UPDATE shop_appointments
                        SET status = '" . $data['status'] . "',
                            updated_at = NOW()
                        WHERE appointment_id = " . (int)$data['id'] . "
                        AND status != 'BOOKED'
                        AND EXISTS (
                            SELECT 1 FROM appointment_shop_detail
                            WHERE appointment_id = shop_appointments.appointment_id
                            AND shop_id = " . (int)$shop_id . "
                            AND status != 'BOOKED'
                        ) ";

        sql_query_pg($update_sql);
        $updated_count++;
    }

    alert('선택한 ' . $updated_count . '개의 예약 상태가 변경되었습니다.', './shop_appointment_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));

} else {
    alert('잘못된 요청입니다.', './shop_appointment_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
}

exit;
?>
