<?php
$sub_menu = "930700";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

@auth_check($auth[$sub_menu], 'w');

check_admin_token();

$action = isset($_POST['action']) ? trim($_POST['action']) : '';

if ($action == 'add' || $action == 'edit') {
    $shop_id = isset($_POST['shop_id']) ? trim($_POST['shop_id']) : '';
    $weekday = isset($_POST['weekday']) ? (int)$_POST['weekday'] : -1;
    $slot_seq = isset($_POST['slot_seq']) ? (int)$_POST['slot_seq'] : 0;
    $open_time = isset($_POST['open_time']) ? trim($_POST['open_time']) : '';
    $close_time = isset($_POST['close_time']) ? trim($_POST['close_time']) : '';
    $is_open = isset($_POST['is_open']) ? ($_POST['is_open'] == '1' ? 'true' : 'false') : 'false';
    
    // 기존 값 (수정 시)
    $old_shop_id = isset($_POST['old_shop_id']) ? trim($_POST['old_shop_id']) : '';
    $old_weekday = isset($_POST['old_weekday']) ? (int)$_POST['old_weekday'] : -1;
    $old_slot_seq = isset($_POST['old_slot_seq']) ? (int)$_POST['old_slot_seq'] : 0;
    
    // SQL 이스케이프 및 정수 변환
    $shop_id = (int)$shop_id;
    
    // 가맹점측 관리자는 자신의 가맹점만 수정 가능
    if ($shop_id != $user_shop_id) {
        alert('접속할 수 없는 페이지 입니다.');
    }
    
    // 유효성 검사
    if ($shop_id < 1) {
        alert('가맹점 정보가 올바르지 않습니다.');
    }
    
    if ($weekday < 0 || $weekday > 6) {
        alert('요일을 선택하세요.');
    }
    
    if ($slot_seq < 1) {
        alert('순서는 1 이상이어야 합니다.');
    }
    
    if (empty($open_time) || empty($close_time)) {
        alert('시작시간과 종료시간을 입력하세요.');
    }
    
    // 시간 형식 검증
    if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $open_time)) {
        // HH:MM 형식인 경우 :00 초 추가
        if (preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $open_time)) {
            $open_time .= ':00';
        } else {
            alert('시작시간 형식이 올바르지 않습니다.');
        }
    }
    
    if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $close_time)) {
        // HH:MM 형식인 경우 :00 초 추가
        if (preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $close_time)) {
            $close_time .= ':00';
        } else {
            alert('종료시간 형식이 올바르지 않습니다.');
        }
    }
    
    // SQL 이스케이프
    $open_time = sql_real_escape_string($open_time);
    $close_time = sql_real_escape_string($close_time);
    
    if ($action == 'edit') {
        // 수정 모드
        // 중복 체크 (본인 제외)
        $old_shop_id_int = (int)$old_shop_id;
        $sql_check = " SELECT shop_id, weekday, slot_seq 
                      FROM business_hour_slots 
                      WHERE shop_id = {$shop_id} 
                      AND weekday = {$weekday} 
                      AND slot_seq = {$slot_seq}
                      AND NOT (shop_id = {$old_shop_id_int} AND weekday = {$old_weekday} AND slot_seq = {$old_slot_seq}) ";
        $row_check = sql_fetch_pg($sql_check);
        
        if ($row_check) {
            alert('이미 존재하는 시간대입니다. (shop_id: ' . $shop_id . ', weekday: ' . $weekday . ', slot_seq: ' . $slot_seq . ')');
        }
        
        // 가맹점측 관리자는 자신의 가맹점만 수정 가능
        if ($old_shop_id_int != $user_shop_id) {
            alert('접속할 수 없는 페이지 입니다.');
        }
        
        // 기존 레코드 삭제 후 새로 추가
        $sql_delete = " DELETE FROM business_hour_slots 
                        WHERE shop_id = {$old_shop_id_int} 
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
            alert('이미 존재하는 시간대입니다. (shop_id: ' . $shop_id . ', weekday: ' . $weekday . ', slot_seq: ' . $slot_seq . ')');
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
    $shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : 0;
    $weekday = isset($_POST['weekday']) ? (int)$_POST['weekday'] : -1;
    $slot_seq = isset($_POST['slot_seq']) ? (int)$_POST['slot_seq'] : 0;
    
    if ($shop_id < 1 || $weekday < 0 || $slot_seq < 1) {
        alert('삭제할 시간대 정보가 올바르지 않습니다.');
    }
    
    // 가맹점측 관리자는 자신의 가맹점만 삭제 가능
    if ($shop_id != $user_shop_id) {
        alert('접속할 수 없는 페이지 입니다.');
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

