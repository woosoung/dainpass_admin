<?php
$sub_menu = "930500";
include_once('./_common.php');

check_demo();

@auth_check($auth[$sub_menu],"w");

//-- 필드명 추출 & mb_ 와 같은 앞자리 3자 추출 --//
$r = getPrefixFields($g5['member_table']);//arr['prefix'], arr['fields']를 반환

// $_REQUEST로 넘어온 데이터중에 추출한 접두어를 가진 필드중에 테이블에 존재하지 않는 데이터만 추출해서 배열로 반환
// 이 배열은 meta테이블에 따로 저장하기 위해 사용됨
$exArr = getExTableData($r['prefix'],$r['fields'],$_REQUEST);

$mb_id = trim($_POST['mb_id']);
$sql_password = '';
if($_POST['mb_password']){
    $mb_password = trim($_POST['mb_password']);
    $mb_password = get_encrypt_string($mb_password);
    $sql_password = " , mb_password = '{$mb_password}' ";
}
$mb_name = trim($_POST['mb_name']);
$mb_nick = trim($_POST['mb_nick']);
$mb_email = trim($_POST['mb_email']);
$mb_hp = trim($_POST['mb_hp']);
$mb_hp = preg_replace('/[^0-9]/', '', $mb_hp); // 숫자만 추출
$sql_certify = ($w == '') ? " , mb_email_certify = '".G5_TIME_YMDHIS."' " : '';
$sql_open = ($w == '') ? " , mb_open = '1' " : '';
$sql_open_date = ($w == '') ? " , mb_open_date = '".G5_TIME_YMD."' " : '';
$mb_zip1 = substr(trim($_POST['mb_zip']), 0, 3);
$mb_zip2 = substr(trim($_POST['mb_zip']), 3);
$mb_addr1 = trim($_POST['mb_addr1']);
$mb_addr2 = trim($_POST['mb_addr2']);
$mb_addr3 = trim($_POST['mb_addr3']);
$mb_addr_jibeon = trim($_POST['mb_addr_jibeon']);
$mb_memo = conv_unescape_nl(stripslashes($_POST['mb_memo']));
$mb_datetime = $_POST['mb_datetime'].' '.date('H:i:s');
if($mb_leave_date){
    $mb_level = 1;
    $mb_leave_date = preg_replace('/[^0-9]/', '', $mb_leave_date); // 숫자만 추출
}

$sql_common = " mb_id = '{$mb_id}'
                {$sql_password}
                , mb_name = '{$mb_name}'
                , mb_nick = '{$mb_nick}'
                , mb_email = '{$mb_email}'
                , mb_hp = '{$mb_hp}'
                , mb_level = '{$mb_level}'
                , mb_zip1 = '{$mb_zip1}'
                , mb_zip2 = '{$mb_zip2}'
                , mb_addr1 = '{$mb_addr1}'
                , mb_addr2 = '{$mb_addr2}'
                , mb_addr3 = '{$mb_addr3}'
                , mb_addr_jibeon = '{$mb_addr_jibeon}'
                , mb_memo = '{$mb_memo}'
                , mb_datetime = '{$mb_datetime}'
                , mb_leave_date = '{$mb_leave_date}'
                {$sql_open}
                {$sql_open_date}
                {$sql_certify}
";


if($w == ''){
    $sql = " INSERT INTO {$g5['member_table']} SET {$sql_common} ";
}
else if($w == 'u'){
    $sql = " UPDATE {$g5['member_table']} SET {$sql_common} WHERE mb_id = '{$mb_id}' ";
}
sql_query($sql,1);


$skip_arr = array('mb_zip');
if(count($exArr)){
    foreach($exArr as $k => $v){
        if(in_array($k,$skip_arr)) continue;
        meta_update(array("mta_db_tbl"=>"member","mta_db_idx"=>$mb_id,"mta_key"=>$k,"mta_value"=>$v));
    }
}

$auth_renewal = $auth_renewal ?? true; // 권한갱신 여부
if($auth_renewal){
    $auth_del_sql = " DELETE FROM {$g5['auth_table']} WHERE mb_id = '{$mb_id}' ";
    sql_query($auth_del_sql,1);
    $auth_arr = ($auths) ? explode(',',$auths) : array();
    if(count($auth_arr)){
        foreach($auth_arr as $v){
            $arr = explode('_', $v);
            $code = $arr[0];
            $auth_str = $arr[1];
            $auth_str .= ($arr[2]) ? ','.$arr[2] : '';
            $auth_str .= ($arr[3]) ? ','.$arr[3] : '';
            $auth_sql = " INSERT INTO {$g5['auth_table']} SET mb_id = '{$mb_id}', au_menu = '{$code}', au_auth = '{$auth_str}' ";
            sql_query($auth_sql,1);
        }
    }
}

// 퇴사처리시 모든권한 삭제
if($mb_leave_date){
    $auth_del_sql = " DELETE FROM {$g5['auth_table']} WHERE mb_id = '{$mb_id}' ";
    sql_query($auth_del_sql,1);
}


if($w == '' || $w == 'u'){
    //파일 삭제처리
    $merge_del = array();
    $del_arr = array();
    if(@count($emp_del)){
        foreach($emp_del as $k=>$v) {
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
    upload_multi_file($_FILES['emp_datas'],'member',$mb_id,'emp');
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

$msg = ($w == '') ? '등록' : '수정';
alert('사원정보가 '.$msg.'되었습니다.','./employee_list.php?'.$qstr, false);