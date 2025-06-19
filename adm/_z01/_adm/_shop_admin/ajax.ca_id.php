<?php
include_once('./_common.php');

$ca_id = isset($_POST['category_id']) ? trim($_POST['category_id']) : '';
if (preg_match("/[^0-9a-z]/i", $ca_id)) {
    die("{\"error\":\"업종코드는 영문자 숫자 만 입력 가능합니다.\"}");
}

$sql = " SELECT name FROM {$g5['shop_categories_table']} WHERE category_id = '{$ca_id}' ";
$row = sql_fetch_pg($sql);
if (isset($row['name']) && $row['name']) {
    $ca_name = addslashes($row['name']);
    die("{\"error\":\"이미 등록된 업종코드 입니다.\\n\\n업종명 : {$ca_name}\"}");
}

die("{\"error\":\"\"}"); // 정상;