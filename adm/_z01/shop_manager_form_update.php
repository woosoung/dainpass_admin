<?php
$sub_menu = "920200";
include_once("./_common.php");
include_once(G5_ZSQL_PATH."/set_conf.php");

// mb_level에 따라 $sub_menu 재정의
if ($is_member && $member['mb_id']) {
    $mb_sql = " SELECT mb_level, mb_1 
                FROM {$g5['member_table']} 
                WHERE mb_id = '{$member['mb_id']}' ";
    $mb_row = sql_fetch($mb_sql, 1);

    if ($mb_row && $mb_row['mb_level'] >= 4 && $mb_row['mb_level'] <= 5) {
        // 가맹점 오너/관리자는 930100 권한으로 체크
        $sub_menu = "930100";

        // shop_id 소유권 검증 (필수!)
        $user_shop_id = (int)trim($mb_row['mb_1']);
        $requested_shop_id = (int)$shop_id;

        if ($requested_shop_id > 0 && $user_shop_id > 0 && $requested_shop_id != $user_shop_id) {
            alert_close('자신의 가맹점만 관리할 수 있습니다.');
        }
    }
}

@auth_check($auth[$sub_menu], 'w');

//check_admin_token();
$shop = get_table_meta_pg('shop','shop_id',$shop_id);
//print_r2($shop);
//exit;
if(!$shop['shop_id'])
    alert('업체가 존재하지 않습니다.');

if($w == '' || $w == 'u'){
    $mb_hp = preg_replace("/\-/","",$_POST['mb_hp']);
    if($w == ''){
        $mb_id = generateUserId($mb_name);
        $mb_nick = $mb_id;
    } else {
        $mb_id = $_POST['mb_id'];
        $mb_nick = $_POST['mb_nick'];
        $mb_password = html_purifier(trim($_POST['mb_password']));
    }
    $mb_name = html_purifier($_POST['mb_name']);
    $mb_memo = html_purifier($_POST['mb_memo']);
    $mb_email = html_purifier($_POST['mb_email']);
    
    
    
    
    
    
    // 회원정보(mb_1: 가맹점id, mb_2: 가맹점관리자여부(Y/N))
    $sql_common1 = " mb_name = '{$mb_name}'
                    , mb_hp = '{$mb_hp}'
                    , mb_email = '{$mb_email}'
                    , mb_memo = '{$mb_memo}'
    ";
}

if ($w == '') {
    // 해당 가맹점에 휴대폰 번호로 중복회원 체크 (중복회원이 있으면 회원정보 생성 안함)
    // $msql = " SELECT mb_id FROM {$g5['member_table']} WHERE REGEXP_REPLACE(mb_hp,'-','') = '".preg_replace("/-/","",$_POST['mb_hp'])."' OR mb_email = '{$_POST['mb_email']}' ";
    // $mb1 = sql_fetch(" SELECT mb_id FROM {$g5['member_table']} WHERE REGEXP_REPLACE(mb_hp,'-'ㅌ,'') = '".$m_hp."' AND mb_leave_date = '' AND mb_intercept_date = '' LIMIT 1 ");
    $msql = " SELECT mb_id FROM {$g5['member_table']} WHERE REGEXP_REPLACE(mb_hp,'-','') = '".$mb_hp."' AND mb_leave_date = '' AND mb_intercept_date = '' AND mb_1 = '".$shop_id."' LIMIT 1 ";   
    // echo $msql;exit;
    $mb1 = sql_fetch($msql);
    if(isset($mb1['mb_id']) && $mb1['mb_id']) {
        // $mb_id = $mb1['mb_id'];
        alert('이미 해당 휴대폰번호으로 등록된 담당자가 존재합니다.\n관리자에게 문의하시기 바랍니다.');
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
                        , mb_1 = '{$shop_id}'
                        , mb_2 = 'Y'
        ";
        // echo $sql;exit;
        sql_query($sql,1);
        // $c_sql = " SELECT mb_id FROM {$g5['member_table']} WHERE mb_id = '{$mb_id}' AND mb_nick = '{$mb_id}' AND mb_level = '4' AND mb_1 = '{$shop_id}' AND mb_2 = 'Y' ";
        // $mb_no = sql_insert_id();
    }
}
else if ($w == 'u') {
    $sql_password = (isset($mb_password) && $mb_password) ? ", mb_password = '".get_encrypt_string($mb_password)."'" : '';
    $sql = "UPDATE {$g5['member_table']} SET
                {$sql_common1}
                {$sql_password}
            WHERE mb_id = '{$mb_id}' ";
    sql_query($sql,1);
}
else if ($w == 'd') {
    // 담당자 메타정보 삭제
    $sql = " DELETE FROM {$g5['gmeta_table']}
            WHERE mta_db_table = 'member' AND mta_db_id = '{$mb_id}' ";
    sql_query($sql,1);
    // 담당자 권한정보 삭제
    $sql = " DELETE FROM {$g5['auth_table']}
            WHERE mb_id = '{$mb_id}' ";
    sql_query($sql,1);
    // 담당자 회원정보 삭제
    // $sql = "UPDATE {$g5['member_table']} SET
    //             mb_leave_date = '".G5_TIME_YMDHIS."'
    //         WHERE mb_id = '{$mb_id}' ";
    $sql = " DELETE FROM {$g5['member_table']}
            WHERE mb_id = '{$mb_id}' ";
    sql_query($sql,1);
    goto_url('./shop_manager_list.php?shop_id='.$shop_id, false);
    
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

// 추가적인 필드에 대해서 meta테이블에 저장
$r = getPrefixFields($g5['member_table']);//$r['prefix']=>'mb_', $r['fields']=>['mb_id','mb_password',...,mb_9,mb_10']
// $_REQUEST로 넘어온 데이터중에 추출한 접두어를 가진 필드중에 테이블에 존재하지 않는 데이터만 추출해서 배열로 반환
// 이 배열은 meta테이블에 따로 저장하기 위해 사용됨
$exArr = getExTableData($r['prefix'],$r['fields'],$_REQUEST);
if (!is_array($exArr)) { $exArr = []; }
// print_r2($exArr);exit;
//-- 메타 입력 (디비에 있는 설정된 값은 입력하지 않는다.) --//
// $db_fields = ["mb_2_old"];	// 건너뛸 변수명은 배열로 추가해 준다.
// foreach($exArr as $key => $value ) {
// 	//-- 해당 테이블에 있는 필드 제외하고 테이블 prefix 로 시작하는 변수들만 업데이트 --//
// 	if(!in_array($key,$db_fields) && substr($key,0,3)==$db_prefix) {
// 		echo $key."=".$value."<br>";
// 		// gmeta_update(array("mta_db_table"=>"member","mta_db_id"=>$mb_id,"mta_key"=>$key,"mta_value"=>$value));
// 	}
// }
foreach($exArr as $key => $value ) {
	//-- 해당 테이블에 있는 필드 제외하고 테이블 prefix 로 시작하는 변수들만 업데이트 --//
    // echo $key."=".$value."<br>";
    gmeta_update(array("mta_db_table"=>"member","mta_db_id"=>$mb_id,"mta_key"=>$key,"mta_value"=>$value));
}
// exit;

//업체 담당자권한
if($w == '' || $w == 'u'){
    $shop_auth_sql = " SELECT mng_menus FROM {$g5['shop_table']} WHERE shop_id = '{$shop_id}' ";
    $shop_auth_res = sql_fetch_pg($shop_auth_sql);
    $shop_auths = isset($shop_auth_res['mng_menus']) ? explode(',',$shop_auth_res['mng_menus']) : array();
    // print_r2($shop_auths);exit;


    // 메뉴 접근 권한 설정
    if(count($shop_auths) > 0){
        //필수메뉴 배열
        $essential_menus = $set_conf['set_shopmanager_basic_menu_arr'];
        // 기존 정보 삭제(초기화)
        $sql = " DELETE FROM {$g5['auth_table']} WHERE mb_id = '".$mb_id."' ";
        sql_query($sql,1);
        $sql = " INSERT INTO {$g5['auth_table']} (mb_id, au_menu, au_auth) VALUES ";
        // 필수메뉴 권한 추가
        $en = 0;
        foreach ($essential_menus as $essential_code => $essential_arr) {
            $sql .= ($en == 0) ? "" : ",";
            $sql .= "('".$mb_id."','".$essential_code."','".$essential_arr['auth']."')";
            $en++;
        }
        sql_query($sql,1);
        // 선택메뉴 권한 추가
        // $shop_auths 배열에 요소가 있으면 foreach문으로 반복하면서 권한정보를 추가
        if(count($shop_auths) > 0) {
            $mmn = 0;
            foreach ($shop_auths as $shop_auth) {
                $sql .= ",('".$mb_id."','".$shop_auth."','r,w,d')";
                $mmn++;
            }
        }
        $sql .= " ON DUPLICATE KEY UPDATE au_auth = VALUES(au_auth) ";
        sql_query($sql,1);
    } else {
        // 기존 권한정보 삭제(초기화)
        $sql = " DELETE FROM {$g5['auth_table']} WHERE mb_id = '".$mb_id."' ";
        sql_query($sql,1);
        // $sql = "INSERT INTO {$g5['auth_table']} SET
        //                     mb_id = '".$mb_id."'
        //                     , au_menu = '100000'
        //                     , au_auth = 'r' ";
        // sql_query($sql,1);
    }
}

//exit;
goto_url('./shop_manager_list.php?'.$qstr.'&amp;w=u&shop_id='.$shop_id, false);