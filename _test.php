<?php
include_once('./_common.php');
$sql = " SELECT shop_id, name, branch, zipcode, addr1, addr2, latitude, longitude FROM {$g5['shop_table']} ";
$result = sql_query_pg($sql);
for($i=0; $row=sql_fetch_array_pg($result->result); $i++) {
    print_r2($row);
}