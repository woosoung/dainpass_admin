<?php
$sub_menu = "960200";
include_once("./_common.php");
include_once(G5_LIB_PATH."/register.lib.php");

if ($w == 'u')
    check_demo();

auth_check($auth[$sub_menu], 'w');
//check_admin_token();

if(!trim($_POST['com_name'])) alert('업체명을 입력해 주세요.');
if(!trim($_POST['com_email'])) alert('이메일을 입력해 주세요.');
if(!trim($_POST['com_president'])) alert('대표자명을 입력해 주세요.');
if(!trim($_POST['com_tel'])) alert('업체전화번호를 입력해 주세요.');

$com_name = trim($_POST['com_name']);
$com_name_eng = trim($_POST['com_name_eng']);
$com_branch = trim($_POST['com_branch']);
$com_email = trim($_POST['com_email']);
$com_url = trim($_POST['com_url']);
$com_president = trim($_POST['com_president']);
$com_tel = trim($_POST['com_tel']);
$com_tel = preg_replace('/[^0-9]/', '', $com_tel); // 전화번호 숫자만 추출
$com_biz_no = trim($_POST['com_biz_no']);
$com_biz_no = preg_replace('/[^0-9]/', '', $com_biz_no); // 사업자번호 숫자만 추출
$com_fax = trim($_POST['com_fax']);
$com_fax = preg_replace('/[^0-9]/', '', $com_fax); // 팩스번호 숫자만 추출
$com_biz_type = trim($_POST['com_biz_type']);
$com_biz_type2 = trim($_POST['com_biz_type2']);
$com_zip = trim($_POST['com_zip']);
$com_addr = trim($_POST['com_addr']);
$com_addr2 = trim($_POST['com_addr2']);
$com_addr3 = trim($_POST['com_addr3']);
$com_addr_jibeon = trim($_POST['com_addr_jibeon']);
$com_longitude = trim($_POST['com_longitude']);
$com_addr_jibeon = trim($_POST['com_addr_jibeon']);
$com_memo = conv_unescape_nl(stripslashes($_POST['com_memo']));

if($com_idx == $com_idx_parent){
    alert('현재업체를 본사업체로 등록할 수 없습니다.');
}

//위도형식에 맞지 않으면 경고창 띄우기
if($com_latitude){
    if(!preg_match('/^(\+|-)?(?:90(?:(?:\.0{1,})?)|(?:[1-8]?\d(?:\.\d{1,})?))$/', $com_latitude)){
        alert('위도의 형식이 올바르지 않습니다.');
    }
}
//경도형식에 맞지 않으면 경고창 띄우기
if($com_longitude){
    if(!preg_match('/^(\+|-)?(?:180(?:(?:\.0{1,})?)|(?:1[0-7]?\d(?:\.\d{1,})?)|(?:\d?\d(?:\.\d{1,})?))$/', $com_longitude)){
        alert('경도의 형식이 올바르지 않습니다.');
    }
}

// 업체정보 추출
if ($w=='u')
	$com = sql_fetch(" SELECT * FROM {$g5['company_table']} WHERE com_idx = '$com_idx' ");


// 업체명 히스토리
if($com['com_name'] != $com_name) {
	$com_names = $com['com_names'].', '.$com_name.'('.substr(G5_TIME_YMD,2).'~)';
}
else {
	$com_names = $_POST['com_names'];
}


$sql_common = "	com_name = '".addslashes($com_name)."'
                , com_branch = '".addslashes($com_branch)."'
                , com_name_eng = '".addslashes($com_name_eng)."'
                , com_names = '".addslashes($com_names)."'
                , com_url = '{$com_url}'
                , com_type = '{$_POST['com_type']}'
                , com_tel = '{$com_tel}'
                , com_fax = '{$com_fax}'
                , com_email = '{$com_email}'
                , com_president = '{$com_president}'
                , com_biz_no = '{$com_biz_no}'
                , com_biz_type = '{$com_biz_type}'
                , com_biz_type2 = '{$com_biz_type2}'
                , com_zip = '{$com_zip}'
                , com_addr = '{$com_addr}'
                , com_addr2 = '{$com_addr2}'
                , com_addr3 = '{$com_addr3}'
                , com_addr_jibeon = '{$com_addr_jibeon}'
                , com_latitude = '{$com_latitude}'
                , com_longitude = '{$com_longitude}'
                , com_memo = '{$com_memo}'
                , com_status = '{$_POST['com_status']}'
";

// API key 생성
// tms_get_random_string('09azAZ',40);
if($key_renewal){
    $com_api_key = tms_get_random_string('09azAZ',40);
    $sql_common .= " , com_api_key = '{$com_api_key}' ";
}
else if($key_clear){
    $sql_common .= " , com_api_key = '' ";
}

$sql_common .= ($head_clear) ? " , com_idx_parent = '' " : " , com_idx_parent = '{$_POST['com_idx_parent']}' ";

// 생성
if ($w == '') {
    // 업체 정보 생성
	$sql = " INSERT into {$g5['company_table']} SET
				{$sql_common}
                , com_reg_dt = '".G5_TIME_YMDHIS."'
                , com_update_dt = '".G5_TIME_YMDHIS."'
	";
    sql_query($sql,1);
	$com_idx = sql_insert_id();

}
// 수정
else if ($w == 'u') {

	if (!$com['com_idx'])
		alert('존재하지 않는 업체자료입니다.');
 
    $sql = "	UPDATE {$g5['company_table']} SET 
					{$sql_common}
					, com_update_dt = '".G5_TIME_YMDHIS."'
				WHERE com_idx = '{$com_idx}' 
	";
    sql_query($sql,1);
    //echo $sql.'<br>';
}
else if ($w=="d") {

	if (!$com['com_idx']) {
		alert('존재하지 않는 업체자료입니다.');
	} else {
		// 자료 삭제
        if(!$set_conf['set_del_yn']){
            $sql = " UPDATE {$g5['company_table']} SET com_status = 'trash' WHERE com_idx = $com_idx ";
        }
        else{
            $sql = " DELETE FROM {$g5['company_table']} WHERE com_idx = $com_idx ";
        }
		sql_query($sql,1);
	}
}


if($w == '' || $w == 'u'){
    //파일 삭제처리
    $merge_del = array();
    $del_arr = array();
    if(@count($com_del)){
        foreach($com_del as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }
    
    if(count($merge_del)){
        foreach($merge_del as $k=>$v) {
            array_push($del_arr,$k);
        }
    }
    if(count($del_arr)) delete_idx_file($del_arr);
    
    //멀티파일처리
    upload_multi_file($_FILES['com_datas'],'company',$com_idx,'com');
}


foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
        }
    }
}

// exit;
if($w == 'u') {
	//alert('업체 정보를 수정하였습니다.','./company_form.php?'.$qstr.'&amp;w=u&amp;com_idx='.$com_idx, false);
	// alert('업체 정보를 수정하였습니다.','./company_list.php?'.$qstr, false);
    goto_url('./company_list.php?'.$qstr, false);
}
else if($w == 'd') {
    goto_url('./company_list.php?'.$qstr, false);
}
else {
	// alert('업체 정보를 등록하였습니다.','./company_list.php', false);
    goto_url('./company_list.php?'.$qstr, false);
}