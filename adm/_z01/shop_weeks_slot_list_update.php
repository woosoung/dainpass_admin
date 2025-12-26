<?php
$sub_menu = "930700";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

@auth_check($auth[$sub_menu], 'w');

check_admin_token();

$action = isset($_POST['action']) ? trim($_POST['action']) : '';

// action 화이트리스트 검증
$action_whitelist = ['add', 'edit', 'delete'];
if (!in_array($action, $action_whitelist)) {
    alert('잘못된 요청입니다.');
}

if ($action == 'add' || $action == 'edit') {
    $weekday = isset($_POST['weekday']) ? (int)$_POST['weekday'] : -1;
    $slot_seq = isset($_POST['slot_seq']) ? (int)$_POST['slot_seq'] : 0;
    $open_time = isset($_POST['open_time']) ? trim($_POST['open_time']) : '';
    $close_time = isset($_POST['close_time']) ? trim($_POST['close_time']) : '';
    $is_open_input = isset($_POST['is_open']) ? trim($_POST['is_open']) : '';

    // 기존 값 (수정 시)
    $old_weekday = isset($_POST['old_weekday']) ? (int)$_POST['old_weekday'] : -1;
    $old_slot_seq = isset($_POST['old_slot_seq']) ? (int)$_POST['old_slot_seq'] : 0;

    // 필수 입력값 검증
    if (!isset($_POST['weekday'])) {
        alert('요일을 선택해 주세요.');
    }

    if (!isset($_POST['slot_seq'])) {
        alert('순서를 입력해 주세요.');
    }

    if (!isset($_POST['open_time']) || $open_time === '') {
        alert('시작시간을 입력해 주세요.');
    }

    if (!isset($_POST['close_time']) || $close_time === '') {
        alert('종료시간을 입력해 주세요.');
    }

    if (!isset($_POST['is_open'])) {
        alert('영업여부를 선택해 주세요.');
    }

    // 요일 범위 검증
    if ($weekday < 0 || $weekday > 6) {
        alert('올바른 요일을 선택해 주세요.');
    }

    // 순서 범위 검증 (1-99)
    if ($slot_seq < 1 || $slot_seq > 99) {
        alert('순서는 1 이상 99 이하로 입력해 주세요.');
    }

    // is_open 값 검증 (0 또는 1만 허용)
    if ($is_open_input !== '0' && $is_open_input !== '1') {
        alert('올바른 영업여부 값이 아닙니다.');
    }

    $is_open = $is_open_input == '1' ? 'true' : 'false';

    // 시간 형식 검증
    if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $open_time)) {
        // HH:MM 형식인 경우 :00 초 추가
        if (preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $open_time)) {
            $open_time .= ':00';
        } else {
            alert('시작시간 형식이 올바르지 않습니다. (예: 09:00 또는 09:00:00)');
        }
    }

    if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $close_time)) {
        // HH:MM 형식인 경우 :00 초 추가
        if (preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $close_time)) {
            $close_time .= ':00';
        } else {
            alert('종료시간 형식이 올바르지 않습니다. (예: 18:00 또는 18:00:00)');
        }
    }

    // 수정 모드일 경우 기존 값 검증
    if ($action == 'edit') {
        if ($old_weekday < 0 || $old_weekday > 6) {
            alert('기존 요일 정보가 올바르지 않습니다.');
        }
        if ($old_slot_seq < 1 || $old_slot_seq > 99) {
            alert('기존 순서 정보가 올바르지 않습니다.');
        }
    }

    // SQL 이스케이프
    $open_time = sql_escape_string($open_time);
    $close_time = sql_escape_string($close_time);

    if ($action == 'edit') {
        // 수정 모드
        // 중복 체크 (본인 제외)
        $sql_check = " SELECT shop_id, weekday, slot_seq
                      FROM business_hour_slots
                      WHERE shop_id = {$shop_id}
                      AND weekday = {$weekday}
                      AND slot_seq = {$slot_seq}
                      AND NOT (shop_id = {$shop_id} AND weekday = {$old_weekday} AND slot_seq = {$old_slot_seq}) ";
        $row_check = sql_fetch_pg($sql_check);

        if ($row_check) {
            alert('이미 존재하는 시간대입니다.');
        }

        // 기존 레코드 삭제 후 새로 추가
        $sql_delete = " DELETE FROM business_hour_slots
                        WHERE shop_id = {$shop_id}
                        AND weekday = {$old_weekday}
                        AND slot_seq = {$old_slot_seq} ";
        sql_query_pg($sql_delete);
    } else {
        // 추가 모드
        // 중복 체크
        $sql_check = " SELECT shop_id, weekday, slot_seq
                      FROM business_hour_slots
                      WHERE shop_id = {$shop_id}
                      AND weekday = {$weekday}
                      AND slot_seq = {$slot_seq} ";
        $row_check = sql_fetch_pg($sql_check);

        if ($row_check) {
            alert('이미 존재하는 시간대입니다.');
        }
    }
    
    // INSERT
    $sql = " INSERT INTO business_hour_slots 
             (shop_id, weekday, slot_seq, open_time, close_time, is_open) 
             VALUES 
             ({$shop_id}, {$weekday}, {$slot_seq}, '{$open_time}', '{$close_time}', {$is_open}) ";
    
    sql_query_pg($sql);
    
    $msg = ($action == 'edit') ? '시간대가 수정되었습니다.' : '시간대가 추가되었습니다.';
    alert($msg, './shop_weeks_slot_list.php');
    
} else if ($action == 'delete') {
    $weekday = isset($_POST['weekday']) ? (int)$_POST['weekday'] : -1;
    $slot_seq = isset($_POST['slot_seq']) ? (int)$_POST['slot_seq'] : 0;

    // 필수 입력값 검증
    if (!isset($_POST['weekday'])) {
        alert('삭제할 요일 정보가 없습니다.');
    }

    if (!isset($_POST['slot_seq'])) {
        alert('삭제할 순서 정보가 없습니다.');
    }

    // 유효성 검사
    if ($weekday < 0 || $weekday > 6) {
        alert('올바른 요일 정보가 아닙니다.');
    }

    if ($slot_seq < 1 || $slot_seq > 99) {
        alert('올바른 순서 정보가 아닙니다.');
    }

    $sql = " DELETE FROM business_hour_slots
             WHERE shop_id = {$shop_id}
             AND weekday = {$weekday}
             AND slot_seq = {$slot_seq} ";

    sql_query_pg($sql);

    alert('시간대가 삭제되었습니다.', './shop_weeks_slot_list.php');

} else {
    alert('잘못된 요청입니다.');
}

