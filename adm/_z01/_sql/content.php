<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$cont['cont_karr'] = array();
$cont['cont_varr'] = array();
$cont['cont_option'] = '';
if(sql_query(" DESCRIBE {$g5['content_table']} ", false)){
	$consql = " SELECT co_id, co_subject FROM {$g5['content_table']} ORDER BY co_subject ";
	$conres = sql_query($consql,1);
	for($i=0;$conrow=sql_fetch_array($conres);$i++){
		$cont['cont_karr'][$conrow['co_id']] = $conrow['co_subject'];
		$cont['cont_varr'][$conrow['co_subject']] = $conrow['co_id'];
		$cont['cont_option'] .= '<option value="'.$conrow['co_id'].'">'.$conrow['co_subject'].'</option>';
	}
	unset($consql);
	unset($conres);
	unset($i);
	unset($conrow);
}