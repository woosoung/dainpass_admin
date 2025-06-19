<?php
include_once('./_common.php');

$mb_id = trim($mb_id);
$mb_no = 0;
if($mb_id){
    $res = sql_fetch(" SELECT mb_no FROM {$g5['member_table']} WHERE mb_id = '{$mb_id}' ");
    $mb_no = $res['mb_no'];
}

$status = 0;

if(!$mb_no){
    $status = 1;
}

echo $status;