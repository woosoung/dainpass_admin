<?php
$sub_menu = '920100';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

$ca_id = isset($_REQUEST['category_id']) ? preg_replace('/[^0-9a-z]/i', '', $_REQUEST['category_id']) : '';

if( ! $ca_id ){
    alert('', G5_SHOP_URL);
}



if( $ca_id ){
    $sql = " select * from {$g5['shop_categories_table']} where category_id = '$ca_id' ";
    $ca = sql_fetch_pg($sql);

    if ($ca && function_exists('get_admin_captcha_by') && get_admin_captcha_by()){
        include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');

        if (!chk_captcha()) {
            alert('자동등록방지 숫자가 틀렸습니다.');
        }
    }
}



// $check_str_keys = array(
//     'name'=>'str',
//     'sort_order'=>'int',
//     'img_url'=>'str',
//     'use_yn'=>'str',
//     'sort_order'=>'int',
//     'img2_url'=>'str',
//     'cert_use_yn'=>'str',
//     'adult_use_yn'=>'str'
// );


// foreach( $check_str_keys as $key=>$val ){
//     if( $val === 'int' ){
//         $value = isset($_POST[$key]) ? (int) $_POST[$key] : 0;
//     } else {
//         $value = isset($_POST[$key]) ? clean_xss_tags($_POST[$key], 1, 1) : '';
//     }
//     $$key = $_POST[$key] = $value;
// }


 
$use_yn = isset($_POST['use_yn']) ? 'Y' : 'N';




if ($w == "u" || $w == "d")
    check_demo();

auth_check_menu($auth, $sub_menu, "d");

check_admin_token();

if ((!$is_dev_manager && !$ca_id) || ($member['mb_level'] < 8 && !$ca_id))
    alert("최고관리자만 분류를 삭제할 수 있습니다.");


/*
category_id varchar(10)
name varchar(100)
description text
created_at timestamp
updated_at timestamp
img_url varchar(255)
use_yn varchar(1) DEFAULT 'Y'
sort_order integer DEFAULT 0
img2_url varchar(255)
cert_use_yn varchar(1) DEFAULT 'N'
adult_use_yn varchar(1) DEFAULT 'N'
*/
if($w != 'd'){
    $sql_common = " description             = '$description',
                    use_yn                  = '$use_yn',
                    sort_order              = '$sort_order',
                    cert_use_yn             = '$cert_use_yn',
                    adult_use_yn            = '$adult_use_yn'
    ";
    $sql_common_columns = "description, use_yn, sort_order, cert_use_yn, adult_use_yn";
    $sql_common_values = "'$description', '$use_yn', '$sort_order', '$cert_use_yn', '$adult_use_yn'";
}
// exit;
// echo $sql_common;exit;
if ($w == "") {
    if (!trim($ca_id))
        alert("분류 코드가 없으므로 분류를 추가하실 수 없습니다.");

    // 소문자로 변환
    $ca_id = strtolower($ca_id);

    $sql_common_columns = "( category_id, name, ".$sql_common_columns.", created_at, updated_at )";
    $sql_common_values = "( '$ca_id', '$name', ".$sql_common_values.", '".G5_TIME_YMDHIS."', '".G5_TIME_YMDHIS."' )";

    $sql = " INSERT INTO {$g5['shop_categories_table']}
            {$sql_common_columns}
              VALUES
            {$sql_common_values} ";

    // echo $sql."<br>";exit;
    sql_query_pg($sql);
} else if ($w == "u") {
    $sql = " UPDATE {$g5['shop_categories_table']}
                SET name = '$name',
                    $sql_common
                    , updated_at = '".G5_TIME_YMDHIS."'
              WHERE category_id = '$ca_id' ";
    sql_query_pg($sql);

    // 하위분류를 똑같은 설정으로 반영
    if (isset($_POST['sub_category']) && $_POST['sub_category']) {
        $len = strlen($ca_id);
        $sql = " UPDATE {$g5['shop_categories_table']}
                    SET $sql_common
                  WHERE SUBSTR(category_id,1,$len) = '$ca_id' ";


        // echo $sql."<br>";exit;
        sql_query_pg($sql);
    }
}
else if ($w == "d")
{
    // 분류의 길이
    $len = strlen($ca_id);

    


    $sql = " SELECT COUNT(*) AS cnt FROM {$g5['shop_categories_table']}
              WHERE SUBSTR(category_id,1,$len) = '$ca_id'
                AND category_id <> '$ca_id' ";
    $row = sql_fetch_pg($sql);
    if ($row['cnt'] > 0)
        alert("이 분류에 속한 하위 분류가 있으므로 삭제 할 수 없습니다.\\n\\n하위분류를 우선 삭제하여 주십시오.");
    
    $str = $comma = "";
    $sql = " SELECT DISTINCT shop_id FROM {$g5['shop_category_relation_table']} WHERE category_id = '$ca_id' ";
    
    $result = sql_query_pg($sql);

    for ($i=0; $row=sql_fetch_array_pg($result->result); $i++)
    {
        // if ($i % 10 == 0) $str .= "\\n";
        $str .= "$comma{$row['shop_id']}";
        $comma = " , ";
    }
    
    if ($str)
        alert("이 업종(분류)과 관련된 가맹점이 총 {$i} 건 존재하므로 가맹점데이터을 삭제한 후 업종(분류)을 삭제하여 주십시오.\\n\\n$str");
    
    // 먼저 해당 업종(분류) 관련 이미지를 전부 삭제처리
    $fsql = " SELECT string_agg(fle_idx::text, ',' ORDER BY fle_reg_dt DESC) AS fle_idxs
                FROM {$g5['dain_file_table']}
                WHERE fle_db_tbl = 'shop_categories'
                    AND fle_type IN ('cat_on', 'cat_off')
                    AND fle_dir = 'admin/category'
                    AND fle_db_idx = '{$ca_id}' ";

    $frow = sql_fetch_pg($fsql);
    if(isset($frow['fle_idxs'])){
        $del_arr = explode(',', $frow['fle_idxs']);
        if(count($del_arr)) delete_idx_s3_file($del_arr);
    }
    
    // 해당 업종(분류) 삭제
    $sql = " DELETE FROM {$g5['shop_categories_table']} WHERE category_id = '$ca_id' ";
    sql_query_pg($sql);
}

// exit;
if($w == '' || $w == 'u'){
    //파일 삭제처리
    $merge_del = array();
    $del_arr = array();

    $cat_off_del = isset($_POST['cat_off_'.$ca_id.'_del']) && is_array($_POST['cat_off_'.$ca_id.'_del']) ? $_POST['cat_off_'.$ca_id.'_del'] : array();
    $cat_on_del  = isset($_POST['cat_on_'.$ca_id.'_del']) && is_array($_POST['cat_on_'.$ca_id.'_del'])  ? $_POST['cat_on_'.$ca_id.'_del']  : array();

    if(!empty($cat_off_del)){
        foreach($cat_off_del as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }

    if(!empty($cat_on_del)){
        foreach($cat_on_del as $k=>$v) {
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
    upload_multi_file($_FILES['caticon_off'],'shop_categories',$ca_id,'admin/category','cat_off');
    upload_multi_file($_FILES['caticon_on'],'shop_categories',$ca_id,'admin/category','cat_on');

    $has_off_upload = isset($_FILES['caticon_off']['name']) && is_array($_FILES['caticon_off']['name']) && array_filter($_FILES['caticon_off']['name']);
    $has_on_upload  = isset($_FILES['caticon_on']['name'])  && is_array($_FILES['caticon_on']['name'])  && array_filter($_FILES['caticon_on']['name']);

    if ($has_off_upload || $has_on_upload) {
        $latest_paths = array();

        if ($has_off_upload) {
            $off_row = sql_fetch_pg(" SELECT fle_path FROM {$g5['dain_file_table']} 
                                        WHERE fle_db_tbl = 'shop_categories'
                                            AND fle_db_idx = '{$ca_id}'
                                            AND fle_type = 'cat_off'
                                            AND fle_dir = 'admin/category'
                                        ORDER BY fle_reg_dt DESC LIMIT 1 ");
            if (!empty($off_row['fle_path'])) {
                $latest_paths['img_url'] = $off_row['fle_path'];
            }
        }

        if ($has_on_upload) {
            $on_row = sql_fetch_pg(" SELECT fle_path FROM {$g5['dain_file_table']}
                                        WHERE fle_db_tbl = 'shop_categories'
                                            AND fle_db_idx = '{$ca_id}'
                                            AND fle_type = 'cat_on'
                                            AND fle_dir = 'admin/category'
                                        ORDER BY fle_reg_dt DESC LIMIT 1 ");
            if (!empty($on_row['fle_path'])) {
                $latest_paths['img2_url'] = $on_row['fle_path'];
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
                $update_sql = " UPDATE {$g5['shop_categories_table']} SET {$set_sql} WHERE category_id = '{$ca_id}' ";
                sql_query_pg($update_sql);
            }
        }
    }
}



if(function_exists('get_admin_captcha_by'))
    get_admin_captcha_by('remove');

if ($w == "" || $w == "u")
{
    goto_url("./categoryform.php?w=u&amp;category_id=$ca_id&amp;$qstr");
} else {
    goto_url("./categorylist.php?$qstr");
}