<?php
$sub_menu = '920100';
include_once('./_common.php');

check_demo();

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$post_ca_id_count = (isset($_POST['category_id']) && is_array($_POST['category_id'])) ? count($_POST['category_id']) : 0;

// print_r2($_POST);exit;

for ($i=0; $i<$post_ca_id_count; $i++)
{
    $p_ca_name = is_array($_POST['name']) ? strip_tags(clean_xss_attributes($_POST['name'][$i])) : '';
    $p_ca_desc = is_array($_POST['description']) ? strip_tags(clean_xss_attributes($_POST['description'][$i])) : '';

    $_POST['cert_use_yn'][$i] = isset($_POST['cert_use_yn'][$i]) && $_POST['cert_use_yn'][$i] ? 'Y' : 'N';
    $_POST['adult_use_yn'][$i] = isset($_POST['adult_use_yn'][$i]) && $_POST['adult_use_yn'][$i] ? 'Y' : 'N';
    $_POST['use_yn'][$i] = isset($_POST['use_yn'][$i]) && $_POST['use_yn'][$i] ? 'Y' : 'N';
    
    $posts = array();

    $check_keys = array('category_id', 'use_yn', 'cert_use_yn', 'adult_use_yn');

    foreach($check_keys as $key){
        $posts[$key] = (isset($_POST[$key]) && isset($_POST[$key][$i])) ? $_POST[$key][$i] : '';
    }
    
    $sql = " UPDATE {$g5['shop_categories_table']}
                set name             = '".$p_ca_name."',
                    description      = '".$p_ca_desc."',
                    use_yn           = '".sql_real_escape_string(strip_tags($_POST['use_yn'][$i]))."',
                    cert_use_yn      = '".sql_real_escape_string(strip_tags($_POST['cert_use_yn'][$i]))."',
                    adult_use_yn     = '".sql_real_escape_string(strip_tags($_POST['adult_use_yn'][$i]))."'
              where category_id = '".sql_real_escape_string($posts['category_id'])."' ";
    // echo $sql . "<br>";continue;
    sql_query_pg($sql);

}
// exit;
goto_url("./categorylist.php?$qstr");