<?php
include_once('./_common.php');

$json = file_get_contents('php://input');

$d = json_decode($json, true);
$mb_hp = trim($d['mb_hp']);
$mb_hp = preg_replace("/[^0-9]/", "", $mb_hp); // 숫자만 추출
$mb_id = trim($d['mb_id']);
$w = $d['w'];

$mb_id_where = ($w == 'u') ? " AND mb_id <> '{$mb_id}' " : '';
$mb_no = 0;

if($mb_hp){
    $sql = " SELECT mb_no FROM {$g5['member_table']} WHERE mb_hp = '{$mb_hp}' {$mb_id_where} ";
    $res = sql_fetch($sql);
    $mb_no = $res['mb_no'];
}

$status = 0;

if(!$mb_no){
    $status = 1;
}

echo $status;