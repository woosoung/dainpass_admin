<?php
$sub_menu = "930550";
include_once('./_common.php');

@auth_check($auth[$sub_menu],'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

check_admin_token();

$act = isset($_POST['act']) ? trim($_POST['act']) : '';

// act 값 화이트리스트 검증
$act_whitelist = ['sort', 'delete'];
if (!in_array($act, $act_whitelist)) {
    alert('잘못된 요청입니다.');
}

// qstr 생성 시 화이트리스트 검증 및 이스케이프
$qstr = '';
$allowed_params = ['sst', 'sod', 'sfl', 'stx', 'page'];

foreach ($_POST as $key => $value) {
    if (in_array($key, $allowed_params)) {
        if ($key == 'stx') {
            $qstr .= '&'.$key.'='.urlencode($value);
        } else if ($key == 'page') {
            $qstr .= '&'.$key.'='.(int)$value;
        } else {
            $qstr .= '&'.$key.'='.clean_xss_tags($value);
        }
    }
}

// 정렬순서 저장
if ($act == 'sort') {
    if (isset($_POST['sort_order']) && is_array($_POST['sort_order'])) {
        foreach ($_POST['sort_order'] as $group_id => $sort_order) {
            $group_id = (int)$group_id;
            $sort_order = (int)$sort_order;

            // group_id 유효성 검증
            if ($group_id <= 0) {
                continue;
            }

            // sort_order 범위 검증 (0 이상 9999 이하)
            if ($sort_order < 0 || $sort_order > 9999) {
                alert('정렬순서는 0 이상 9999 이하로 입력해 주세요.');
            }

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
    @auth_check($auth[$sub_menu],'d');
    if (isset($_POST['chk']) && is_array($_POST['chk']) && count($_POST['chk']) > 0) {
        foreach ($_POST['chk'] as $group_id) {
            $group_id = (int)$group_id;

            // group_id 유효성 검증
            if ($group_id <= 0) {
                continue;
            }

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

