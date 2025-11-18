<?php
$sub_menu = '920150';
include_once('./_common.php');

check_demo();

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$post_amenity_id_count = (isset($_POST['amenity_id']) && is_array($_POST['amenity_id'])) ? count($_POST['amenity_id']) : 0;

// print_r2($_POST);exit;

for ($i=0; $i<$post_amenity_id_count; $i++)
{
    $p_amenity_name = is_array($_POST['amenity_name']) ? strip_tags(clean_xss_attributes($_POST['amenity_name'][$i])) : '';
    $p_amenity_desc = is_array($_POST['description']) ? strip_tags(clean_xss_attributes($_POST['description'][$i])) : '';

    $posts = array();

    $check_keys = array('amenity_id');

    foreach($check_keys as $key){
        $posts[$key] = (isset($_POST[$key]) && isset($_POST[$key][$i])) ? $_POST[$key][$i] : '';
    }
    
    if (!$p_amenity_name) {
        continue; // 편의시설명이 없으면 건너뛰기
    }
    
    $sql = " UPDATE {$g5['amenities_table']}
                set amenity_name    = '".addslashes($p_amenity_name)."',
                    description      = '".addslashes($p_amenity_desc)."',
                    updated_at       = '".G5_TIME_YMDHIS."'
              where amenity_id = '".sql_real_escape_string($posts['amenity_id'])."' ";
    // echo $sql . "<br>";continue;
    sql_query_pg($sql);

}
// exit;
goto_url("./amenities_list.php?$qstr");
