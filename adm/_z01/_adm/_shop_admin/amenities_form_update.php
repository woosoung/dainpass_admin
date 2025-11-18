<?php
$sub_menu = '920150';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

$amenity_id = isset($_REQUEST['amenity_id']) ? (int)$_REQUEST['amenity_id'] : 0;

if( ! $amenity_id && $w == 'u' ){
    alert('편의시설 ID가 없습니다.', './amenities_list.php');
}

if( $amenity_id && $w == 'u' ){
    $sql = " SELECT * FROM {$g5['amenities_table']} WHERE amenity_id = '$amenity_id' ";
    $am = sql_fetch_pg($sql);

    if ($am && function_exists('get_admin_captcha_by') && get_admin_captcha_by()){
        include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');

        if (!chk_captcha()) {
            alert('자동등록방지 숫자가 틀렸습니다.');
        }
    }
}

if ($w == "u" || $w == "d")
    check_demo();

auth_check_menu($auth, $sub_menu, "d");

check_admin_token();

$amenity_name = isset($_POST['amenity_name']) ? trim($_POST['amenity_name']) : '';
$description = isset($_POST['description']) ? conv_unescape_nl(stripslashes($_POST['description'])) : '';

if (!$amenity_name) {
    alert('편의시설명을 입력해 주세요.');
}

if($w != 'd'){
    $sql_common = " amenity_name = '".addslashes($amenity_name)."',
                    description = '".addslashes($description)."'
    ";
    $sql_common_columns = "amenity_name, description";
    $sql_common_values = "'".addslashes($amenity_name)."', '".addslashes($description)."'";
}

if ($w == "") {
    $sql_common_columns = "( ".$sql_common_columns.", created_at, updated_at )";
    $sql_common_values = "( ".$sql_common_values.", '".G5_TIME_YMDHIS."', '".G5_TIME_YMDHIS."' )";

    $sql = " INSERT INTO {$g5['amenities_table']}
            {$sql_common_columns}
              VALUES
            {$sql_common_values} ";

    // echo $sql."<br>";exit;
    sql_query_pg($sql);
    $amenity_id = sql_insert_id_pg('amenities');
} else if ($w == "u") {
    $sql = " UPDATE {$g5['amenities_table']}
                SET $sql_common
                    , updated_at = '".G5_TIME_YMDHIS."'
              WHERE amenity_id = '$amenity_id' ";
    sql_query_pg($sql);
}
else if ($w == "d")
{
    // 해당 편의시설이 가맹점에 사용되고 있는지 확인
    $sql = " SELECT COUNT(*) AS cnt FROM {$g5['shop_amenities_table']} WHERE amenity_id = '$amenity_id' ";
    $row = sql_fetch_pg($sql);
    if ($row['cnt'] > 0)
        alert("이 편의시설이 가맹점에 사용되고 있으므로 삭제 할 수 없습니다.\\n\\n먼저 가맹점에서 해당 편의시설을 제거하여 주십시오.");
    
    // 먼저 해당 편의시설 관련 이미지를 전부 삭제처리
    $fsql = " SELECT string_agg(fle_idx::text, ',' ORDER BY fle_reg_dt DESC) AS fle_idxs
                FROM {$g5['dain_file_table']}
                WHERE fle_db_tbl = 'amenities'
                    AND fle_type IN ('amnt_enabled', 'amnt_disabled')
                    AND fle_dir = 'shop/amenity_img'
                    AND fle_db_idx = '{$amenity_id}' ";

    $frow = sql_fetch_pg($fsql);
    if(isset($frow['fle_idxs'])){
        $del_arr = explode(',', $frow['fle_idxs']);
        if(count($del_arr)) delete_idx_s3_file($del_arr);
    }
    
    // 해당 편의시설 삭제
    $sql = " DELETE FROM {$g5['amenities_table']} WHERE amenity_id = '$amenity_id' ";
    sql_query_pg($sql);
}

// exit;
if($w == '' || $w == 'u'){
    //파일 삭제처리
    $merge_del = array();
    $del_arr = array();

    $amnt_enabled_del = isset($_POST['amnt_enabled_'.$amenity_id.'_del']) && is_array($_POST['amnt_enabled_'.$amenity_id.'_del']) ? $_POST['amnt_enabled_'.$amenity_id.'_del'] : array();
    $amnt_disabled_del  = isset($_POST['amnt_disabled_'.$amenity_id.'_del']) && is_array($_POST['amnt_disabled_'.$amenity_id.'_del'])  ? $_POST['amnt_disabled_'.$amenity_id.'_del']  : array();

    if(!empty($amnt_enabled_del)){
        foreach($amnt_enabled_del as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }

    if(!empty($amnt_disabled_del)){
        foreach($amnt_disabled_del as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }

    if(!empty($merge_del)){
        foreach($merge_del as $k=>$v) {
            array_push($del_arr,$k);
        }
    }
    // print_r2($del_arr);exit;
    if(!empty($del_arr)) delete_idx_s3_file($del_arr);
    
    //file의 name, fle_db_idx, fle_idx, fle_dir, fle_type
    upload_multi_file($_FILES['amenityicon_enabled'],'amenities',$amenity_id,'shop/amenity_img','amnt_enabled');
    upload_multi_file($_FILES['amenityicon_disabled'],'amenities',$amenity_id,'shop/amenity_img','amnt_disabled');

    $has_enabled_upload = isset($_FILES['amenityicon_enabled']['name']) && is_array($_FILES['amenityicon_enabled']['name']) && array_filter($_FILES['amenityicon_enabled']['name']);
    $has_disabled_upload  = isset($_FILES['amenityicon_disabled']['name'])  && is_array($_FILES['amenityicon_disabled']['name'])  && array_filter($_FILES['amenityicon_disabled']['name']);

    if ($has_enabled_upload || $has_disabled_upload) {
        $latest_paths = array();

        if ($has_enabled_upload) {
            $enabled_row = sql_fetch_pg(" SELECT fle_path FROM {$g5['dain_file_table']} 
                                        WHERE fle_db_tbl = 'amenities'
                                            AND fle_db_idx = '{$amenity_id}'
                                            AND fle_type = 'amnt_enabled'
                                            AND fle_dir = 'shop/amenity_img'
                                        ORDER BY fle_reg_dt DESC LIMIT 1 ");
            if (!empty($enabled_row['fle_path'])) {
                $latest_paths['icon_url_enabled'] = $enabled_row['fle_path'];
            }
        }

        if ($has_disabled_upload) {
            $disabled_row = sql_fetch_pg(" SELECT fle_path FROM {$g5['dain_file_table']}
                                        WHERE fle_db_tbl = 'amenities'
                                            AND fle_db_idx = '{$amenity_id}'
                                            AND fle_type = 'amnt_disabled'
                                            AND fle_dir = 'shop/amenity_img'
                                        ORDER BY fle_reg_dt DESC LIMIT 1 ");
            if (!empty($disabled_row['fle_path'])) {
                $latest_paths['icon_url_disabled'] = $disabled_row['fle_path'];
            }
        }

        if (!empty($latest_paths)) {
            $set_clauses = array();
            foreach ($latest_paths as $column => $path) {
                $escaped_path = pg_escape_string($g5['connect_pg'], $path);
                $set_clauses[] = "{$column} = '{$escaped_path}'";
            }

            if (!empty($set_clauses)) {
                $set_sql = implode(', ', $set_clauses);
                $update_sql = " UPDATE {$g5['amenities_table']} SET {$set_sql} WHERE amenity_id = '{$amenity_id}' ";
                sql_query_pg($update_sql);
            }
        }
    }
}

if(function_exists('get_admin_captcha_by'))
    get_admin_captcha_by('remove');

if ($w == "" || $w == "u")
{
    goto_url("./amenities_form.php?w=u&amp;amenity_id=$amenity_id&amp;$qstr");
} else {
    goto_url("./amenities_list.php?$qstr");
}
