<?php
include_once('./_common.php');

// echo $aj;
// exit;
if($member['mb_level']<6){
	echo "접근권한이 없습니다.";
	exit;
}
if ($aj == "c1") {

	$sql = " UPDATE {$g5['member_table']} SET mb_1 = '".$shop_id."' WHERE mb_id = '".$mb_id."' ";
	sql_query($sql,1);

    set_session('ss_shop_id', $shop_id);

	$msg = "가맹점을 변경하였습니다.";

}
else {
	$msg = "잘못된 접근입니다. \n정상적인 방법으로 이용해 주세요.";
}

echo $msg;
exit;
?>