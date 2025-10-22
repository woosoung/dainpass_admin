<?php
include_once('./_common.php');
/*
$arr = [
    '920400',
    '920400,920450',
    '920400,920450,920500',
    '920400,920450,920500,920550',
    '920400,920450,920500,920550,920600',
    '920400,920450,920500,920550,920600,920650'
];
$sql = " SELECT shop_id,owner_name,contact_phone,contact_email,mng_menus FROM {$g5['shop_table']} ";
$result = sql_query_pg($sql);
if($result->num_rows){
    $sql = " INSERT INTO {$g5['member_table']} (mb_name,mb_hp,mb_email,mb_id,mb_nick,mb_level,mb_password,mb_datetime,mb_ip,mb_email_certify,mb_1,mb_2,mb_10) VALUES "; 
    for($i=0;$row=sql_fetch_array_pg($result->result);$i++){
        $mb_name = html_purifier($row['owner_name']);

        // 만약 기존 가맹점id를 가지고 있으면서 동일한 이름의 회원이 있을 경우 건너뜀
        $exist_sql = " SELECT mb_id FROM {$g5['member_table']} WHERE mb_name = '{$mb_name}' AND mb_1 = '{$row['shop_id']}' AND mb_2 = 'Y' LIMIT 1 ";
        $exist_row = sql_fetch_pg($exist_sql);
        if($exist_row['mb_id']) {
            continue;
        }

        $mb_hp = preg_replace("/\-/","",$row['contact_phone']);
        $mb_email = html_purifier($row['contact_email']);
        $mb_id = generateUserId($mb_name);

        $mb_id_set = explode('_',$mb_id);
        $mb_time = (int) $mb_id_set[1] + $i;

        $mb_id = $mb_id_set[0].'_'.$mb_time;

        $mb_nick = $mb_id;
        if($i>0) $sql .= ",";
        $sql .= " ('{$mb_name}','{$mb_hp}','{$mb_email}','{$mb_id}','{$mb_id}','5','".get_encrypt_string($mb_id)."','".G5_TIME_YMDHIS."','{$_SERVER['REMOTE_ADDR']}','".G5_TIME_YMDHIS."','{$row['shop_id']}','Y','ok') ";

        // 직함을 전부  사장(16)으로 설정
        gmeta_update(array("mta_db_table"=>"member","mta_db_id"=>$mb_id,"mta_key"=>'mb_rank',"mta_value"=>'16'));
        // 관리메뉴 설정
        if(isset($row['mng_menus']) && $row['mng_menus']) {
            $mng_menus = $row['mng_menus'];
        } else {
            $mng_menus = $arr[rand(0, 5)];
            $ssql = " UPDATE {$g5['shop_table']} SET mng_menus = '{$mng_menus}' WHERE shop_id = '{$row['shop_id']}' ";
            sql_query_pg($ssql,1);
        }
        // $mb_id에 해당하는 권한 부여
        $menus = explode(',',$mng_menus);
        $auth_sql = " INSERT INTO {$g5['auth_table']} (mb_id,au_menu,au_auth) VALUES
                            ('{$mb_id}','100000','r') ";
        foreach($menus as $menu){
            $auth_sql .= " ,('{$mb_id}','{$menu}','r,w,d') ";
        }
        sql_query($auth_sql,1);
        
    }

    // echo $sql;
    sql_query($sql,1);
}

//#########################################################################################
$val = '60';
$len = strlen($val);
$csql = " SELECT sct.category_id,name FROM {$g5['shop_category_relation_table']} scr
                LEFT JOIN {$g5['shop_categories_table']} sct ON scr.category_id = sct.category_id
            WHERE shop_id = '353' 
            AND sct.category_id ~ '^.{".($len+2)."}$' 
            AND sct.category_id LIKE '{$val}%' ";
// echo $csql;exit;
$result = sql_query_pg($csql, 1);
// echo $result->num_rows;exit;
// print_r2($result);exit;
$arr = array("error"=>"none");
if($result->num_rows){
    $arr = array();
    for($i=0;$row=sql_fetch_array_pg($result->result);$i++){
        $arr[$row['category_id']] = $row['name'];
    }
}

echo json_encode($arr);
*/