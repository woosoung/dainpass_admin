<?php
$sub_menu = "920800";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

check_admin_token();

$action = isset($_POST['action']) ? trim($_POST['action']) : '';
$sca = isset($_POST['sca']) ? trim($_POST['sca']) : '0';

$qstr = '';
if ($sca) $qstr .= '&sca=' . urlencode($sca);

if ($action == 'add' || $action == 'edit') {
    $category_id = isset($_POST['category_id']) ? trim($_POST['category_id']) : '';
    $weekday = isset($_POST['weekday']) ? (int)$_POST['weekday'] : -1;
    $slot_seq = isset($_POST['slot_seq']) ? (int)$_POST['slot_seq'] : 0;
    $open_time = isset($_POST['open_time']) ? trim($_POST['open_time']) : '';
    $close_time = isset($_POST['close_time']) ? trim($_POST['close_time']) : '';
    $is_open = isset($_POST['is_open']) ? ($_POST['is_open'] == '1' ? 'true' : 'false') : 'false';
    
    // 기존 값 (수정 시)
    $old_category_id = isset($_POST['old_category_id']) ? trim($_POST['old_category_id']) : '';
    $old_weekday = isset($_POST['old_weekday']) ? (int)$_POST['old_weekday'] : -1;
    $old_slot_seq = isset($_POST['old_slot_seq']) ? (int)$_POST['old_slot_seq'] : 0;
    
    // 유효성 검사
    if (empty($category_id)) {
        alert('업종을 선택하세요.', './default_slot_list.php?' . $qstr);
    }
    
    if ($weekday < 0 || $weekday > 6) {
        alert('요일을 선택하세요.', './default_slot_list.php?' . $qstr);
    }
    
    if ($slot_seq < 1) {
        alert('순서는 1 이상이어야 합니다.', './default_slot_list.php?' . $qstr);
    }
    
    if (empty($open_time) || empty($close_time)) {
        alert('시작시간과 종료시간을 입력하세요.', './default_slot_list.php?' . $qstr);
    }
    
    // 시간 형식 검증
    if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $open_time)) {
        // HH:MM 형식인 경우 :00 초 추가
        if (preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $open_time)) {
            $open_time .= ':00';
        } else {
            alert('시작시간 형식이 올바르지 않습니다.', './default_slot_list.php?' . $qstr);
        }
    }
    
    if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $close_time)) {
        // HH:MM 형식인 경우 :00 초 추가
        if (preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $close_time)) {
            $close_time .= ':00';
        } else {
            alert('종료시간 형식이 올바르지 않습니다.', './default_slot_list.php?' . $qstr);
        }
    }
    
    // SQL 이스케이프
    $category_id = sql_real_escape_string($category_id);
    $open_time = sql_real_escape_string($open_time);
    $close_time = sql_real_escape_string($close_time);
    
    if ($action == 'edit') {
        // 수정 모드
        // 중복 체크 (본인 제외)
        $sql_check = " SELECT category_id, weekday, slot_seq 
                      FROM {$g5['default_business_hour_slots_table']} 
                      WHERE category_id = '{$category_id}' 
                      AND weekday = {$weekday} 
                      AND slot_seq = {$slot_seq}
                      AND NOT (category_id = '{$old_category_id}' AND weekday = {$old_weekday} AND slot_seq = {$old_slot_seq}) ";
        $row_check = sql_fetch_pg($sql_check);
        
        if ($row_check) {
            alert('이미 존재하는 시간대입니다. (category_id: ' . $category_id . ', weekday: ' . $weekday . ', slot_seq: ' . $slot_seq . ')', './default_slot_list.php?' . $qstr);
        }
        
        // 기존 레코드 삭제 후 새로 추가
        $sql_delete = " DELETE FROM {$g5['default_business_hour_slots_table']} 
                        WHERE category_id = '{$old_category_id}' 
                        AND weekday = {$old_weekday} 
                        AND slot_seq = {$old_slot_seq} ";
        sql_query_pg($sql_delete);
    } else {
        // 추가 모드
        // 중복 체크
        $sql_check = " SELECT category_id, weekday, slot_seq 
                      FROM {$g5['default_business_hour_slots_table']} 
                      WHERE category_id = '{$category_id}' 
                      AND weekday = {$weekday} 
                      AND slot_seq = {$slot_seq} ";
        $row_check = sql_fetch_pg($sql_check);
        
        if ($row_check) {
            alert('이미 존재하는 시간대입니다. (category_id: ' . $category_id . ', weekday: ' . $weekday . ', slot_seq: ' . $slot_seq . ')', './default_slot_list.php?' . $qstr);
        }
    }
    
    // INSERT
    $sql = " INSERT INTO {$g5['default_business_hour_slots_table']} 
             (category_id, weekday, slot_seq, open_time, close_time, is_open) 
             VALUES 
             ('{$category_id}', {$weekday}, {$slot_seq}, '{$open_time}', '{$close_time}', {$is_open}) ";
    
    sql_query_pg($sql);
    
    $msg = ($action == 'edit') ? '시간대가 수정되었습니다.' : '시간대가 추가되었습니다.';
    alert($msg, './default_slot_list.php?' . $qstr);
    
} else if ($action == 'delete') {
    $category_id = isset($_POST['category_id']) ? trim($_POST['category_id']) : '';
    $weekday = isset($_POST['weekday']) ? (int)$_POST['weekday'] : -1;
    $slot_seq = isset($_POST['slot_seq']) ? (int)$_POST['slot_seq'] : 0;
    
    if (empty($category_id) || $weekday < 0 || $slot_seq < 1) {
        alert('삭제할 시간대 정보가 올바르지 않습니다.', './default_slot_list.php?' . $qstr);
    }
    
    $category_id = sql_real_escape_string($category_id);
    
    $sql = " DELETE FROM {$g5['default_business_hour_slots_table']} 
             WHERE category_id = '{$category_id}' 
             AND weekday = {$weekday} 
             AND slot_seq = {$slot_seq} ";
    
    sql_query_pg($sql);
    
    alert('시간대가 삭제되었습니다.', './default_slot_list.php?' . $qstr);
    
} else {
    alert('잘못된 요청입니다.', './default_slot_list.php?' . $qstr);
}

