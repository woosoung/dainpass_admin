<?php
$sub_menu = "930300";
include_once("./_common.php");

@auth_check($auth[$sub_menu], 'u');

// 가맹점측 관리자 접근 권한 체크
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
            alert('업체 데이터가 없습니다.');
        }
        
        if (!empty($mb_1_value)) {
            $shop_id_check = (int)$mb_1_value;
            $shop_sql = " SELECT shop_id FROM {$g5['shop_table']} WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);
            
            if ($shop_row && $shop_row['shop_id']) {
                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
            } else {
                alert('업체 데이터가 없습니다.');
            }
        }
    }
}

if (!$has_access) {
    alert('접속할 수 없는 페이지 입니다.');
}

$w = isset($_POST['w']) ? trim($_POST['w']) : '';
$post_shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : 0;

// 가맹점측 관리자는 자신의 가맹점만 수정 가능
if ($post_shop_id != $shop_id) {
    alert('접속할 수 없는 페이지 입니다.');
}

$sst = isset($_POST['sst']) ? trim($_POST['sst']) : '';
$sod = isset($_POST['sod']) ? trim($_POST['sod']) : '';
$sfl = isset($_POST['sfl']) ? trim($_POST['sfl']) : '';
$stx = isset($_POST['stx']) ? trim($_POST['stx']) : '';
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;

$qstr = '';
if ($sfl) $qstr .= '&sfl='.urlencode($sfl);
if ($stx) $qstr .= '&stx='.urlencode($stx);
if ($sst) $qstr .= '&sst='.urlencode($sst);
if ($sod) $qstr .= '&sod='.urlencode($sod);
if ($page > 1) $qstr .= '&page='.$page;

$act_button = isset($_POST['act_button']) ? trim($_POST['act_button']) : '';

if ($act_button == '선택수정') {
    check_demo();
    
    if (!isset($_POST['chk']) || !is_array($_POST['chk']) || count($_POST['chk']) == 0) {
        alert('수정할 항목을 선택해 주세요.');
    }
    
    $chk = $_POST['chk'];
    
    // 선택된 직원들 업데이트
    if (isset($_POST['steps_id']) && is_array($_POST['steps_id'])) {
        foreach ($_POST['steps_id'] as $idx => $steps_id_val) {
            $steps_id_val = (int)$steps_id_val;
            
            // 선택된 직원만 처리
            if (!in_array($steps_id_val, $chk)) {
                continue;
            }
            
            // 해당 직원이 해당 가맹점의 것인지 확인
            $check_sql = " SELECT steps_id FROM staff WHERE steps_id = {$steps_id_val} AND store_id = {$shop_id} ";
            $check_row = sql_fetch_pg($check_sql);
            
            if ($check_row && $check_row['steps_id']) {
                $max_customers_per_slot = isset($_POST['max_customers_per_slot'][$idx]) ? (int)$_POST['max_customers_per_slot'][$idx] : 1;
                if ($max_customers_per_slot < 1) $max_customers_per_slot = 1;
                
                $update_sql = " UPDATE staff SET 
                                max_customers_per_slot = {$max_customers_per_slot},
                                updated_at = '".G5_TIME_YMDHIS."'
                            WHERE steps_id = {$steps_id_val} AND store_id = {$shop_id} ";
                sql_query_pg($update_sql);
            }
        }
    }
    
    alert('선택한 항목을 수정했습니다.', './staff_list.php?'.$qstr);
}

if ($act_button == '선택삭제') {
    check_demo();
    
    if (!isset($_POST['chk']) || !is_array($_POST['chk']) || count($_POST['chk']) == 0) {
        alert('삭제할 항목을 선택해 주세요.');
    }
    
    $chk = $_POST['chk'];
    foreach ($chk as $steps_id) {
        $steps_id = (int)$steps_id;
        
        // 해당 직원이 해당 가맹점의 것인지 확인
        $check_sql = " SELECT steps_id FROM staff WHERE steps_id = {$steps_id} AND store_id = {$shop_id} ";
        $check_row = sql_fetch_pg($check_sql);
        
        if ($check_row && $check_row['steps_id']) {
            // 관련 이미지 파일 삭제
            $img_sql = " SELECT fle_idx FROM {$g5['dain_file_table']}
                         WHERE fle_db_tbl = 'staff'
                           AND fle_db_idx = '{$steps_id}'
                           AND fle_type = 'stfi'
                           AND fle_dir = 'shop/staff_img' ";
            $img_result = sql_query_pg($img_sql);
            if ($img_result && is_object($img_result) && isset($img_result->result)) {
                $del_arr = array();
                while ($img_row = sql_fetch_array_pg($img_result->result)) {
                    $del_arr[] = $img_row['fle_idx'];
                }
                if (count($del_arr) > 0) {
                    delete_idx_s3_file($del_arr);
                }
            }
            
            // 직원 삭제
            $delete_sql = " DELETE FROM staff WHERE steps_id = {$steps_id} AND store_id = {$shop_id} ";
            sql_query_pg($delete_sql);
        }
    }
    
    alert('선택한 항목을 삭제했습니다.', './staff_list.php?'.$qstr);
}

goto_url('./staff_list.php?'.$qstr);
?>

