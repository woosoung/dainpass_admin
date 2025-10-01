<?php
include_once('./_common.php');
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