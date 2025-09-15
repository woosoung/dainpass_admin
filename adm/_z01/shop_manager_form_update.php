<?php
$sub_menu = "920200";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

//check_admin_token();
$shop = get_table_meta_pg('shop','shop_id',$shop_id);
//print_r2($shop);
//exit;
if(!$shop['shop_id'])
    alert('업체가 존재하지 않습니다.');

$m_hp = preg_replace("/\-/","",$_POST['mb_hp']);

// if ($w == '') {
//     $chksql = " SELECT COUNT(mb_id) AS cnt, mb_id, mb_6 
//                     FROM {$g5['member_table']}
//                     WHERE REGEXP_REPLACE(mb_hp,'-','') = '{$m_hp}' 
//                         AND mb_6 = '{$_POST['cst_idx']}'
//                         AND mb_leave_date = ''
//                         AND mb_intercept_date = ''
//     ";
//     $chkres = sql_fetch($chksql);
//     if($chkres['cnt']){
//         alert('이미 해당 휴대폰번호으로 등록된 담당자가 존재합니다.\n관리자에게 문의하시기 바랍니다. ');
//     }
// }


// 회원정보
$sql_common1 = " mb_name = '{$_POST['mb_name']}'
                , mb_hp = '{$_POST['mb_hp']}'
                , mb_email = '{$_POST['mb_email']}'
                , mb_memo = '{$_POST['mb_memo']}'
                , mb_4 = '".$_SESSION['ss_com_idx']."'
                , mb_6 = '".$_POST['cst_idx']."'
";

// 업체담당자 테이블 정보
$sql_common2 = " cst_idx = '{$_POST['cst_idx']}'
                , ctm_title = '{$_POST['ctm_title']}'
                , ctm_memo = '{$_POST['ctm_memo']}'
";

if ($w == '') {
    
    // 휴대폰 번호 or 이메일로 중복회원 체크 (중복회원이 있으면 회원정보 생성 안함)
    $mb1 = sql_fetch(" SELECT mb_id FROM {$g5['member_table']} WHERE REGEXP_REPLACE(mb_hp,'-','') = '".$m_hp."' AND mb_leave_date = '' AND mb_intercept_date = '' ");
    // $msql = " SELECT mb_id FROM {$g5['member_table']} WHERE REGEXP_REPLACE(mb_hp,'-','') = '".preg_replace("/-/","",$_POST['mb_hp'])."' OR mb_email = '{$_POST['mb_email']}' ";
    
    //echo $msql;exit;

    $mb1 = sql_fetch($msql);
    if($mb1['mb_id']) {
        $mb_id = $mb1['mb_id'];
    }
    else {
        $sql = " INSERT INTO {$g5['member_table']} SET
                        {$sql_common1}
                        , mb_id = '{$mb_id}'
                        , mb_nick = '{$mb_id}'
                        , mb_level = '4'
                        , mb_password = '".get_encrypt_string($mb_id)."'
                        , mb_datetime = '".G5_TIME_YMDHIS."'
                        , mb_ip = '{$_SERVER['REMOTE_ADDR']}'
                        , mb_email_certify = '".G5_TIME_YMDHIS."'
                        , mb_mailling = '{$_POST['mb_mailling']}'
                        , mb_sms = '{$_POST['mb_sms']}'
                        , mb_open = '{$_POST['mb_open']}'
        ";
        sql_query($sql,1);
        $mb_no = sql_insert_id();
    }
    //echo $mb_id;exit;
    $cmrslt = sql_fetch(" SELECT COUNT(*) AS same_cnt FROM {$g5['customer_member_table']} WHERE mb_id = '{$mb_id}' AND cst_idx = '{$_POST['cst_idx']}' AND ctm_status = 'ok' ");
    if($cmrslt['same_cnt']){
        alert('해당 업체에 동일한 연락처정보를 가진 담당자가 이미 존재합니다.');
    }else{

        $sql = " INSERT INTO {$g5['customer_member_table']} SET
                        {$sql_common2}
                        , mb_id = '{$mb_id}'
                        , ctm_status = 'ok'
                        , ctm_reg_dt = '".G5_TIME_YMDHIS."'
                        , ctm_update_dt = '".G5_TIME_YMDHIS."'
        ";
        //echo $sql;exit;
        sql_query($sql,1);
        $ctm_idx = sql_insert_id();
    }
    
}
else if ($w == 'u') {

    $sql = "UPDATE {$g5['member_table']} SET
                {$sql_common1}
            WHERE mb_id = '{$mb_id}' ";
    sql_query($sql,1);
    

    $sql = "UPDATE {$g5['customer_member_table']} SET
                {$sql_common2}
                , ctm_update_dt = '".G5_TIME_YMDHIS."'
            WHERE ctm_idx = '{$ctm_idx}' ";
    sql_query($sql,1);
    
}
else if ($w == 'd') {

    $sql = "UPDATE {$g5['customer_member_table']} SET
                ctm_status = 'trash'
            WHERE ctm_idx = '{$ctm_idx}' ";
    sql_query($sql,1);
    goto_url('./customer_member_list.php?cst_idx='.$cst_idx, false);
    
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

//업체 담당자권한
if($w == '' || $w == 'u'){
    $auth_type = '';
    // 공급업체 && 직함"기사"아니면 : 915900=r=대시보드, 922150=r,w=발주관리
    if($cst_type == 'provider' && $ctm_title != '13') $auth_type = 'provider';
    // 직함"기사"이면 : 915900=r=대시보드
    else if($ctm_title == '13') $auth_type = 'driver';

    // 메뉴 접근 권한 설정
    if($auth_type != ''){
        // 기존 정보 삭제(초기화)
        $sql = "DELETE FROM {$g5['auth_table']} WHERE mb_id = '".$mb_id."' ";
        sql_query($sql,1);

        $set_values = explode("\n", $g5['setting']['set_'.$auth_type.'_auth']);
        foreach ($set_values as $set_value) {
            list($key, $value) = explode('=', trim($set_value));
            if($key&&$value) {
                $sql = "INSERT INTO {$g5['auth_table']} SET
                            mb_id = '".$mb_id."'
                            , au_menu = '".$key."'
                            , au_auth = '".$value."'
                ";
                //echo $sql.'<br>';
                sql_query($sql,1);
            }
        }
    }
}
else if($w == 'd'){

}

//exit;
goto_url('./shop_manager_list.php?'.$qstr.'&amp;w=u&shop_id='.$shop_id, false);