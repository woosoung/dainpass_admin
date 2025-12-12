<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//----------------------------------------------------------
// gmeta_table에 회원 메타 정보 저장
//----------------------------------------------------------
if ($w == '') {
    gmeta_update([
        'mta_country'   => 'ko_KR',
        'mta_db_table'  => 'member',
        'mta_db_id'     => $mb_id,
        'mta_key'       => 'mb_rank',
        'mta_value'     => '16'
    ]);

    //----------------------------------------------------------
    // 가맹점 오너 권한 설정
    //----------------------------------------------------------
    // 신규 가입한 회원의 정보 조회

    $mb = get_member($mb_id);

    // 정상적으로 가입체크, 가맹점 관리자 여부 체크
    if ($mb && !empty($mb['mb_1']) && $mb['mb_2'] == 'Y') {
        // 필수 메뉴 배열
        $essential_menus = $set_conf['set_shopmanager_basic_menu_arr'];

        $sql = " INSERT INTO {$g5['auth_table']} (mb_id, au_menu, au_auth) VALUES ";

        // 필수 메뉴 권한 추가
        $en = 0;
        foreach ($essential_menus as $essential_code => $essential_arr) {
            $sql .= ($en == 0) ? "" : ",";
            $sql .= "('{$mb_id}', '{$essential_code}', '{$essential_arr['auth']}')";
            $en++;
        }
        $result = sql_query($sql, 1);
    }
}

exit;

//----------------------------------------------------------
// SMS 문자전송 시작
//----------------------------------------------------------

$sms_contents = $default['de_sms_cont1'];
$sms_contents = str_replace("{이름}", $mb_name, $sms_contents);
$sms_contents = str_replace("{회원아이디}", $mb_id, $sms_contents);
$sms_contents = str_replace("{회사명}", $default['de_admin_company_name'], $sms_contents);

// 핸드폰번호에서 숫자만 취한다
$receive_number = preg_replace("/[^0-9]/", "", $mb_hp);  // 수신자번호 (회원님의 핸드폰번호)
$send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']); // 발신자번호

if ($w == "" && $default['de_sms_use1'] && $receive_number) {
    if ($config['cf_sms_use'] == 'icode') {
        if ($config['cf_sms_type'] == 'LMS') {
            include_once(G5_LIB_PATH . '/icode.lms.lib.php');

            $port_setting = get_icode_port_type($config['cf_icode_id'], $config['cf_icode_pw']);

            // SMS 모듈 클래스 생성
            if ($port_setting !== false) {
                $SMS = new LMS;
                $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $port_setting);

                $strDest     = array();
                $strDest[]   = $receive_number;
                $strCallBack = $send_number;
                $strCaller   = iconv_euckr(trim($default['de_admin_company_name']));
                $strSubject  = '';
                $strURL      = '';
                $strData     = iconv_euckr($sms_contents);
                $strDate     = '';
                $nCount      = count($strDest);

                $res = $SMS->Add($strDest, $strCallBack, $strCaller, $strSubject, $strURL, $strData, $strDate, $nCount);

                $SMS->Send();
                $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
            }
        } else {
            include_once(G5_LIB_PATH . '/icode.sms.lib.php');

            $SMS = new SMS; // SMS 연결
            $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);
            $SMS->Add($receive_number, $send_number, $config['cf_icode_id'], iconv_euckr(stripslashes($sms_contents)), "");
            $SMS->Send();
            $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
        }
    }
}
//----------------------------------------------------------
// SMS 문자전송 끝
//----------------------------------------------------------;