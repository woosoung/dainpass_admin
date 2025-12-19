<?php
$sub_menu = "930550";
include_once('./_common.php');

@auth_check($auth[$sub_menu],'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

check_admin_token();

$act = isset($_POST['act']) ? trim($_POST['act']) : '';

$qstr = '';
foreach ($_POST as $key => $value) {
    if (in_array($key, ['sst', 'sod', 'sfl', 'stx', 'page'])) {
        $qstr .= '&'.$key.'='.$value;
    }
}

// 정렬순서 저장
if ($act == 'sort') {
    if (isset($_POST['sort_order']) && is_array($_POST['sort_order'])) {
        foreach ($_POST['sort_order'] as $group_id => $sort_order) {
            $group_id = (int)$group_id;
            $sort_order = (int)$sort_order;
            
            // 해당 group_id가 현재 shop_id 소유인지 확인
            $check_sql = " SELECT group_id FROM {$g5['shop_space_group_table']} 
                          WHERE group_id = {$group_id} AND shop_id = {$shop_id} ";
            $check_row = sql_fetch_pg($check_sql);
            
            if ($check_row) {
                $update_sql = " UPDATE {$g5['shop_space_group_table']} 
                               SET sort_order = {$sort_order}, updated_at = '".G5_TIME_YMDHIS."' 
                               WHERE group_id = {$group_id} ";
                sql_query_pg($update_sql);
            }
        }
    }
    
    goto_url('./shop_space_group_list.php?'.$qstr, false);
}

// 선택 삭제
if ($act == 'delete') {
    if (isset($_POST['chk']) && is_array($_POST['chk']) && count($_POST['chk']) > 0) {
        foreach ($_POST['chk'] as $group_id) {
            $group_id = (int)$group_id;
            
            // 해당 group_id가 현재 shop_id 소유인지 확인
            $check_sql = " SELECT group_id FROM {$g5['shop_space_group_table']} 
                          WHERE group_id = {$group_id} AND shop_id = {$shop_id} ";
            $check_row = sql_fetch_pg($check_sql);
            
            if ($check_row) {
                // 연관된 파일 삭제
                $file_sql = " SELECT fle_idx FROM {$g5['dain_file_table']} 
                             WHERE fle_db_tbl = 'shop_space_group' 
                             AND fle_db_idx = '{$group_id}' ";
                $file_result = sql_query_pg($file_sql);
                if ($file_result && is_object($file_result) && isset($file_result->result)) {
                    $file_idx_arr = array();
                    while ($file_row = sql_fetch_array_pg($file_result->result)) {
                        $file_idx_arr[] = $file_row['fle_idx'];
                    }
                    if (count($file_idx_arr) > 0) {
                        delete_idx_s3_file($file_idx_arr);
                    }
                }
                
                // 그룹 삭제 (CASCADE로 space_unit도 자동 삭제됨)
                $delete_sql = " DELETE FROM {$g5['shop_space_group_table']} WHERE group_id = {$group_id} ";
                sql_query_pg($delete_sql);
            }
        }
    }
    
    goto_url('./shop_space_group_list.php?'.$qstr, false);
}

alert('잘못된 요청입니다.');
?>

