<?php
$sub_menu = "930400";
include_once("./_common.php");
include_once(G5_LIB_PATH."/register.lib.php");

@auth_check($auth[$sub_menu], 'w');

// 가맹점측 관리자 접근 권한 체크
$has_access = false;
$shop_id = 0;

if ($is_member && $member['mb_id']) {
    // MySQL에서 회원 정보 확인
    // 플랫폼 관리자(mb_level >= 6)는 mb_2 = 'N'일 수 있으므로 mb_2 조건을 다르게 적용
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
        
        // mb_1 = '0'인 경우: 플랫폼 관리자
        if ($mb_1_value === '0' || $mb_1_value === '') {
            // 플랫폼 관리자는 shop_id = 0에 해당하는 레코드가 없으므로 '업체 데이터가 없습니다.' 표시
            alert('업체 데이터가 없습니다.');
        }
        
        // mb_1에 shop_id 값이 있는 경우: 해당 shop_id로 shop 테이블 조회
        if (!empty($mb_1_value)) {
            // PostgreSQL에서 shop_id 확인 (shop_id는 bigint이므로 정수로 비교)
            $shop_id_check = (int)$mb_1_value;
            $shop_sql = " SELECT shop_id FROM {$g5['shop_table']} WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);
            
            if ($shop_row && $shop_row['shop_id']) {
                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
            } else {
                // shop_id에 해당하는 레코드가 없는 경우
                alert('업체 데이터가 없습니다.');
            }
        }
    }
}

// 접근 권한이 없으면 메시지 표시
if (!$has_access) {
    alert('접속할 수 없는 페이지 입니다.');
}

// 입력 기본값 안전 초기화
$post_shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : (isset($_REQUEST['shop_id']) ? (int)$_REQUEST['shop_id'] : 0);
$steps_id = isset($_POST['steps_id']) ? (int)$_POST['steps_id'] : (isset($_REQUEST['steps_id']) ? (int)$_REQUEST['steps_id'] : 0);

// 가맹점측 관리자는 자신의 가맹점만 수정 가능
if ($post_shop_id != $shop_id) {
    alert('접속할 수 없는 페이지 입니다.');
}

if (!$steps_id) {
    alert('직원 정보가 없습니다.');
}

// 해당 직원이 해당 가맹점의 것인지 확인
$check_sql = " SELECT steps_id FROM staff WHERE steps_id = {$steps_id} AND store_id = {$shop_id} ";
$check_row = sql_fetch_pg($check_sql);

if (!$check_row || !$check_row['steps_id']) {
    alert('존재하지 않는 직원자료입니다.');
}

check_demo();

// 삭제할 직원별 서비스 처리
if (isset($_POST['delete_staff_service_id']) && is_array($_POST['delete_staff_service_id'])) {
    foreach ($_POST['delete_staff_service_id'] as $delete_id) {
        $delete_id = (int)$delete_id;
        if ($delete_id > 0) {
            // 해당 직원별 서비스가 해당 가맹점의 것인지 확인
            $check_del_sql = " SELECT staff_service_id FROM staff_services 
                               WHERE staff_service_id = {$delete_id} 
                                 AND steps_id = {$steps_id} 
                                 AND shop_id = {$shop_id} ";
            $check_del_row = sql_fetch_pg($check_del_sql);
            
            if ($check_del_row && $check_del_row['staff_service_id']) {
                $delete_sql = " DELETE FROM staff_services 
                                WHERE staff_service_id = {$delete_id} 
                                  AND steps_id = {$steps_id} 
                                  AND shop_id = {$shop_id} ";
                sql_query_pg($delete_sql);
            }
        }
    }
}

// 기존 직원별 서비스 업데이트
if (isset($_POST['staff_service_id']) && is_array($_POST['staff_service_id'])) {
    foreach ($_POST['staff_service_id'] as $idx => $staff_service_id_val) {
        $staff_service_id_val = (int)$staff_service_id_val;
        
        if ($staff_service_id_val > 0) {
            // 해당 직원별 서비스가 해당 가맹점의 것인지 확인
            $check_update_sql = " SELECT staff_service_id FROM staff_services 
                                   WHERE staff_service_id = {$staff_service_id_val} 
                                     AND steps_id = {$steps_id} 
                                     AND shop_id = {$shop_id} ";
            $check_update_row = sql_fetch_pg($check_update_sql);
            
            if ($check_update_row && $check_update_row['staff_service_id']) {
                $service_time = isset($_POST['service_time'][$idx]) ? (int)$_POST['service_time'][$idx] : 0;
                $slot_max_persons_cnt = isset($_POST['slot_max_persons_cnt'][$idx]) ? (int)$_POST['slot_max_persons_cnt'][$idx] : 1;
                $status = isset($_POST['status'][$idx]) ? trim($_POST['status'][$idx]) : 'ok';

                // 입력값 범위 검증
                if ($service_time < 0 || $service_time > 1440) {
                    alert('서비스시간은 0분 이상 1440분(24시간) 이하로 입력해 주세요.');
                }

                if ($slot_max_persons_cnt < 1 || $slot_max_persons_cnt > 100) {
                    alert('슬롯당 고객수는 1명 이상 100명 이하로 입력해 주세요.');
                }

                // select 값 화이트리스트 검증
                if (!in_array($status, array('ok', 'pending'))) $status = 'ok';
                
                $update_sql = " UPDATE staff_services SET 
                                service_time = {$service_time},
                                slot_max_persons_cnt = {$slot_max_persons_cnt},
                                status = '{$status}'
                            WHERE staff_service_id = {$staff_service_id_val} 
                              AND steps_id = {$steps_id} 
                              AND shop_id = {$shop_id} ";
                sql_query_pg($update_sql);
            }
        }
    }
}

// 새로운 직원별 서비스 추가
if (isset($_POST['service_id']) && is_array($_POST['service_id'])) {
    foreach ($_POST['service_id'] as $idx => $service_id_val) {
        $service_id_val = (int)$service_id_val;
        
        // staff_service_id가 없으면 새로 추가
        $has_staff_service_id = false;
        if (isset($_POST['staff_service_id'][$idx]) && (int)$_POST['staff_service_id'][$idx] > 0) {
            $has_staff_service_id = true;
        }
        
        if (!$has_staff_service_id && $service_id_val > 0) {
            // 해당 서비스가 해당 가맹점의 것인지 확인
            $check_service_sql = " SELECT service_id FROM shop_services 
                                  WHERE service_id = {$service_id_val} 
                                    AND shop_id = {$shop_id} ";
            $check_service_row = sql_fetch_pg($check_service_sql);
            
            if ($check_service_row && $check_service_row['service_id']) {
                // 이미 등록되어 있는지 확인
                $check_exist_sql = " SELECT staff_service_id FROM staff_services 
                                     WHERE steps_id = {$steps_id} 
                                       AND service_id = {$service_id_val} 
                                       AND shop_id = {$shop_id} ";
                $check_exist_row = sql_fetch_pg($check_exist_sql);
                
                if (!$check_exist_row || !$check_exist_row['staff_service_id']) {
                    $service_time = isset($_POST['service_time'][$idx]) ? (int)$_POST['service_time'][$idx] : 0;
                    $slot_max_persons_cnt = isset($_POST['slot_max_persons_cnt'][$idx]) ? (int)$_POST['slot_max_persons_cnt'][$idx] : 1;
                    $status = isset($_POST['status'][$idx]) ? trim($_POST['status'][$idx]) : 'ok';

                    // 입력값 범위 검증
                    if ($service_time < 0 || $service_time > 1440) {
                        alert('서비스시간은 0분 이상 1440분(24시간) 이하로 입력해 주세요.');
                    }

                    if ($slot_max_persons_cnt < 1 || $slot_max_persons_cnt > 100) {
                        alert('슬롯당 고객수는 1명 이상 100명 이하로 입력해 주세요.');
                    }

                    // select 값 화이트리스트 검증
                    if (!in_array($status, array('ok', 'pending'))) $status = 'ok';
                    
                    $insert_sql = " INSERT INTO staff_services (
                                    shop_id,
                                    steps_id,
                                    service_id,
                                    service_time,
                                    slot_max_persons_cnt,
                                    status
                                ) VALUES (
                                    {$shop_id},
                                    {$steps_id},
                                    {$service_id_val},
                                    {$service_time},
                                    {$slot_max_persons_cnt},
                                    '{$status}'
                                ) ";
                    sql_query_pg($insert_sql);
                }
            }
        }
    }
}

$qstr = '';
$sfl = isset($_POST['sfl']) ? trim($_POST['sfl']) : '';
$stx = isset($_POST['stx']) ? trim($_POST['stx']) : '';
$sst = isset($_POST['sst']) ? trim($_POST['sst']) : '';
$sod = isset($_POST['sod']) ? trim($_POST['sod']) : '';
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;

if ($sfl) $qstr .= '&sfl='.urlencode($sfl);
if ($stx) $qstr .= '&stx='.urlencode($stx);
if ($sst) $qstr .= '&sst='.urlencode($sst);
if ($sod) $qstr .= '&sod='.urlencode($sod);
if ($page > 1) $qstr .= '&page='.$page;

alert('저장되었습니다.', './staff_services_form.php?'.$qstr.'&steps_id='.$steps_id, false);
?>

