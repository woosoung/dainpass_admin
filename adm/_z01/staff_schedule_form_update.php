<?php
$sub_menu = "930500";
include_once('./_common.php');

// 접근 권한 체크
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
        
        if (!empty($mb_1_value) && $mb_1_value !== '0') {
            $shop_id_check = (int)$mb_1_value;
            $shop_sql = " SELECT shop_id FROM {$g5['shop_table']} WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);
            
            if ($shop_row && $shop_row['shop_id']) {
                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
            }
        }
    }
}

if (!$has_access) {
    alert('접근 권한이 없습니다.', G5_ADMIN_URL);
}

// POST 데이터 받기
$w = isset($_POST['w']) ? trim($_POST['w']) : '';
$schedule_id = isset($_POST['schedule_id']) ? (int)$_POST['schedule_id'] : 0;
$staff_id = isset($_POST['staff_id']) ? (int)$_POST['staff_id'] : 0;
$work_date = isset($_POST['work_date']) ? trim($_POST['work_date']) : '';
$start_time = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';
$end_time = isset($_POST['end_time']) ? trim($_POST['end_time']) : '';

// 검색 조건
$ser_staff_id = isset($_POST['ser_staff_id']) ? (int)$_POST['ser_staff_id'] : 0;
$ser_date_from = isset($_POST['ser_date_from']) ? trim($_POST['ser_date_from']) : '';
$ser_date_to = isset($_POST['ser_date_to']) ? trim($_POST['ser_date_to']) : '';
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;

$qstr = '';
if ($ser_staff_id) $qstr .= '&ser_staff_id='.$ser_staff_id;
if ($ser_date_from) $qstr .= '&ser_date_from='.$ser_date_from;
if ($ser_date_to) $qstr .= '&ser_date_to='.$ser_date_to;
if ($page > 1) $qstr .= '&page='.$page;

// 유효성 검사
if (!$staff_id) {
    alert('직원을 선택해주세요.');
}

if (!$work_date) {
    alert('근무 날짜를 입력해주세요.');
}

if (!$start_time || !$end_time) {
    alert('근무 시간을 입력해주세요.');
}

// 날짜 형식 검증
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $work_date)) {
    alert('올바른 날짜 형식이 아닙니다.');
}

// 시간 형식 검증
if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $start_time) || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $end_time)) {
    alert('올바른 시간 형식이 아닙니다.');
}

// 시간에 초가 없으면 추가
if (strlen($start_time) == 5) {
    $start_time .= ':00';
}
if (strlen($end_time) == 5) {
    $end_time .= ':00';
}

// 시간 검증
$start_timestamp = strtotime($start_time);
$end_timestamp = strtotime($end_time);

if ($end_timestamp <= $start_timestamp) {
    alert('근무 종료 시간이 시작 시간보다 늦어야 합니다.');
}

// 직원이 해당 가맹점 소속인지 확인
$staff_check_sql = "
    SELECT staff_id 
    FROM staff 
    WHERE staff_id = {$staff_id} 
    AND store_id = {$shop_id}
";

$staff_check_result = sql_query_pg($staff_check_sql);
if (!$staff_check_result || !is_object($staff_check_result) || !isset($staff_check_result->result)) {
    alert('직원 정보를 확인할 수 없습니다.');
}

$staff_check_row = sql_fetch_array_pg($staff_check_result->result);
if (!$staff_check_row) {
    alert('해당 직원은 귀하의 가맹점 소속이 아닙니다.');
}

if ($w == '') {
    // 추가 모드
    
    // 중복 체크 (같은 직원, 같은 날짜, 시간이 겹치는 경우)
    $duplicate_check_sql = "
        SELECT schedule_id 
        FROM staff_schedules 
        WHERE staff_id = {$staff_id}
        AND work_date = '{$work_date}'
        AND (
            (start_time <= '{$start_time}' AND end_time > '{$start_time}')
            OR (start_time < '{$end_time}' AND end_time >= '{$end_time}')
            OR (start_time >= '{$start_time}' AND end_time <= '{$end_time}')
        )
    ";
    
    $duplicate_result = sql_query_pg($duplicate_check_sql);
    if ($duplicate_result && is_object($duplicate_result) && isset($duplicate_result->result)) {
        $duplicate_row = sql_fetch_array_pg($duplicate_result->result);
        if ($duplicate_row) {
            alert('해당 직원의 근무 시간이 이미 등록된 스케줄과 겹칩니다.');
        }
    }
    
    // 추가 실행
    $insert_sql = "
        INSERT INTO staff_schedules 
        (staff_id, work_date, start_time, end_time, created_at) 
        VALUES 
        ({$staff_id}, '{$work_date}', '{$start_time}', '{$end_time}', NOW())
    ";
    
    $insert_result = sql_query_pg($insert_sql);
    
    if ($insert_result) {
        alert('등록되었습니다.', './staff_schedule_list.php?'.ltrim($qstr, '&'));
    } else {
        alert('등록에 실패했습니다.');
    }
    
} else if ($w == 'u') {
    // 수정 모드
    
    if (!$schedule_id) {
        alert('스케줄 ID가 없습니다.');
    }
    
    // 기존 스케줄 확인 및 권한 체크
    $check_sql = "
        SELECT ss.schedule_id, s.store_id
        FROM staff_schedules ss
        INNER JOIN staff s ON ss.staff_id = s.staff_id
        WHERE ss.schedule_id = {$schedule_id}
    ";
    
    $check_result = sql_query_pg($check_sql);
    if (!$check_result || !is_object($check_result) || !isset($check_result->result)) {
        alert('스케줄을 찾을 수 없습니다.');
    }
    
    $check_row = sql_fetch_array_pg($check_result->result);
    if (!$check_row) {
        alert('존재하지 않는 스케줄입니다.');
    }
    
    if ($check_row['store_id'] != $shop_id) {
        alert('접근 권한이 없습니다.');
    }
    
    // 중복 체크 (자신을 제외한 다른 스케줄과의 겹침)
    $duplicate_check_sql = "
        SELECT schedule_id 
        FROM staff_schedules 
        WHERE staff_id = {$staff_id}
        AND work_date = '{$work_date}'
        AND schedule_id != {$schedule_id}
        AND (
            (start_time <= '{$start_time}' AND end_time > '{$start_time}')
            OR (start_time < '{$end_time}' AND end_time >= '{$end_time}')
            OR (start_time >= '{$start_time}' AND end_time <= '{$end_time}')
        )
    ";
    
    $duplicate_result = sql_query_pg($duplicate_check_sql);
    if ($duplicate_result && is_object($duplicate_result) && isset($duplicate_result->result)) {
        $duplicate_row = sql_fetch_array_pg($duplicate_result->result);
        if ($duplicate_row) {
            alert('해당 직원의 근무 시간이 이미 등록된 스케줄과 겹칩니다.');
        }
    }
    
    // 수정 실행
    $update_sql = "
        UPDATE staff_schedules 
        SET 
            staff_id = {$staff_id},
            work_date = '{$work_date}',
            start_time = '{$start_time}',
            end_time = '{$end_time}'
        WHERE schedule_id = {$schedule_id}
    ";
    
    $update_result = sql_query_pg($update_sql);
    
    if ($update_result) {
        alert('수정되었습니다.', './staff_schedule_list.php?'.ltrim($qstr, '&'));
    } else {
        alert('수정에 실패했습니다.');
    }
    
} else {
    alert('잘못된 접근입니다.');
}
?>

