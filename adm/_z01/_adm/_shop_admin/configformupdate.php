<?php
$sub_menu = '920050';
include_once('./_common.php');

check_demo();

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

// 대표전화번호 유효성 체크
if(! (isset($_POST['de_admin_company_tel']) && check_vaild_callback($_POST['de_admin_company_tel'])) )
    alert('대표전화번호를 올바르게 입력해 주세요.');

// 로그인을 바로 이 주소로 하는 경우 쇼핑몰설정값이 사라지는 현상을 방지
if (!$_POST['de_admin_company_owner']) goto_url("./configform.php");


$de_kcp_mid = isset($_POST['de_kcp_mid']) ? substr($_POST['de_kcp_mid'], 0, 3) : '';



// 현금영수증 발급수단
$de_taxsave_types = 'account';	// 무통장

if(isset($_POST['de_taxsave_types_vbank']) && $_POST['de_taxsave_types_vbank']){	//가상계좌
	$de_taxsave_types .= ',vbank';
}
if(isset($_POST['de_taxsave_types_transfer']) && $_POST['de_taxsave_types_transfer']){		//실시간계좌이체
	$de_taxsave_types .= ',transfer';
}

// NHN_KCP 간편결제 체크
$de_easy_pay_services = '';
if(isset($_POST['de_easy_pays'])){
    $tmps = array();
    foreach( (array) $_POST['de_easy_pays'] as $v ){
        $tmps[] = preg_replace('/[^0-9a-z_\-]/i', '', $v);
    }
    $de_easy_pay_services = implode(",", $tmps);
}


$check_sanitize_keys = sql_field_names_pg($g5['dain_default_table']);
// print_r2($fields); // 디버깅용 출력
/*
//KVE-2019-0689, KVE-2019-0691, KVE-2019-0694
$check_sanitize_keys = array(
'de_admin_company_name',        //회사명
'de_admin_company_saupja_no',   //사업자등록번호
'de_admin_company_owner',       //대표자명
'de_admin_company_tel',         //대표전화번호
'de_admin_company_fax',         //팩스번호
'de_admin_tongsin_no',          //통신판매업 신고번호
'de_admin_buga_no',             //부가통신 사업자번호
'de_admin_company_zip',         //사업자우편번호
'de_admin_company_addr',        //사업장주소
'de_admin_info_name',           //정보관리책임자명
'de_admin_info_email',          //정보책임자e-mail
'de_card_noint_use',            //신용카드 무이자할부사용
'de_settle_min_point',          //결제 최소포인트
'de_settle_max_point',          //최대 결제포인트
'de_settle_point_unit',         //결제 포인트단위
'de_card_point',                //포인트부여
'de_point_days',                //주문완료 포인트
'de_pg_service',                //결제대행사
'de_lg_mid',                    //토스페이먼트 상점아이디
'de_lg_mert_key',               //토스페이먼트 MERT KEY
'de_escrow_use',                //에스크로 사용
'de_card_test',                 //결제 테스트
'de_tax_flag_use',              //복합과세 결제
'de_cart_keep_term',            //장바구니 보관기간
'de_guest_cart_use',            //비회원 장바구니
'de_sms_use',                   //SMS 사용
'de_sms_hp',                    //관리자 휴대폰번호
'de_aligo_user_id',             //알리고 회원아이디
'de_aligo_key',                 //알리고 비밀번호
'de_sms_max_bytes',             //문자 최대입력 사이즈
'de_sms_use1',                  //SMS 회원가입시 고객님께 발송
'de_sms_use2',                  //SMS 주문시 고객님께 발송
'de_sms_use3',                  //SMS 주문시 주문시 관리자에게 발송
'de_sms_use4',                  //SMS 입금확인시 고객님께 발송
'de_sms_use5',                  //SMS 상품배송시 고객님께 발송
'de_sms_use6',                  //SMS 상품배송시 고객님께 발송
'de_sms_use7',                  //SMS 상품배송시 고객님께 발송
'de_sms_use8',                  //SMS 상품배송시 고객님께 발송
'de_sms_use9',                  //SMS 상품배송시 고객님께 발송
'de_sms_use10',                 //SMS 상품배송시 고객님께 발송
'de_sms_cont1',                  //SMS 회원가입시 고객님께 발송
'de_sms_cont2',                  //SMS 주문시 고객님께 발송
'de_sms_cont3',                  //SMS 주문시 주문시 관리자에게 발송
'de_sms_cont4',                  //SMS 입금확인시 고객님께 발송
'de_sms_cont5',                  //SMS 상품배송시 고객님께 발송
'de_sms_cont6',                  //SMS 상품배송시 고객님께 발송
'de_sms_cont7',                  //SMS 상품배송시 고객님께 발송
'de_sms_cont8',                  //SMS 상품배송시 고객님께 발송
'de_sms_cont9',                  //SMS 상품배송시 고객님께 발송
'de_sms_cont10',                 //SMS 상품배송시 고객님께 발송
);
*/
foreach( $check_sanitize_keys as $key ){
    if( in_array($key, array('de_bank_account')) ){
        $$key = isset($_POST[$key]) ? clean_xss_tags($_POST[$key], 1, 1, 0, 0) : '';
    } else {
        $$key = isset($_POST[$key]) ? clean_xss_tags($_POST[$key], 1, 1) : '';
    }
}

$warning_msg = '';



//
// 영카트 default
//sms_use 데이터처리
$de_sms_use1 = isset($_POST['de_sms_use1']) ? 'Y' : 'N';
$de_sms_use2 = isset($_POST['de_sms_use2']) ? 'Y' : 'N';
$de_sms_use3 = isset($_POST['de_sms_use3']) ? 'Y' : 'N';
$de_sms_use4 = isset($_POST['de_sms_use4']) ? 'Y' : 'N';
$de_sms_use5 = isset($_POST['de_sms_use5']) ? 'Y' : 'N';
$de_sms_use6 = isset($_POST['de_sms_use6']) ? 'Y' : 'N';
$de_sms_use7 = isset($_POST['de_sms_use7']) ? 'Y' : 'N';
$de_sms_use8 = isset($_POST['de_sms_use8']) ? 'Y' : 'N';
$de_sms_use9 = isset($_POST['de_sms_use9']) ? 'Y' : 'N';
$de_sms_use10 = isset($_POST['de_sms_use10']) ? 'Y' : 'N';
// POST 값은 반드시 escape 처리
$de_sms_cont1 = pg_escape_string($g5['connect_pg'], $_POST['de_sms_cont1']);
$de_sms_cont2 = pg_escape_string($g5['connect_pg'], $_POST['de_sms_cont2']);
$de_sms_cont3 = pg_escape_string($g5['connect_pg'], $_POST['de_sms_cont3']);
$de_sms_cont4 = pg_escape_string($g5['connect_pg'], $_POST['de_sms_cont4']);
$de_sms_cont5 = pg_escape_string($g5['connect_pg'], $_POST['de_sms_cont5']);
$de_sms_cont6 = pg_escape_string($g5['connect_pg'], $_POST['de_sms_cont6']);
$de_sms_cont7 = pg_escape_string($g5['connect_pg'], $_POST['de_sms_cont7']);
$de_sms_cont8 = pg_escape_string($g5['connect_pg'], $_POST['de_sms_cont8']);
$de_sms_cont9 = pg_escape_string($g5['connect_pg'], $_POST['de_sms_cont9']);
$de_sms_cont10 = pg_escape_string($g5['connect_pg'], $_POST['de_sms_cont10']);

$de_sms_max_bytes = trim($de_sms_max_bytes) === '' ? 0 : (int)$de_sms_max_bytes;
$de_settle_min_point = trim($de_settle_min_point) === '' ? 0 : (int)$de_settle_min_point;
$de_settle_max_point = trim($de_settle_max_point) === '' ? 0  : (int)$de_settle_max_point;
$de_settle_point_unit = trim($de_settle_point_unit) === '' ? 0 : (int)$de_settle_point_unit;
$de_point_days = trim($de_point_days) === '' ? 0 : (int)$de_point_days;
$de_cart_keep_term = trim($de_cart_keep_term) === '' ? 0 : (int)$de_cart_keep_term;
// 복합과세 결제 사용여부
$de_tax_flag_use = isset($_POST['de_tax_flag_use']) ? 'Y' : 'N';
// 비회원 장바구니 체크박스
$de_guest_cart_use = isset($_POST['de_guest_cart_use']) ? 'Y' : 'N';




$sql = " UPDATE {$g5['dain_default_table']}
   SET de_admin_company_owner        = '{$de_admin_company_owner}',
       de_admin_company_name         = '{$de_admin_company_name}',
       de_admin_company_saupja_no    = '{$de_admin_company_saupja_no}',
       de_admin_company_tel          = '{$de_admin_company_tel}',
       de_admin_company_fax          = '{$de_admin_company_fax}',
       de_admin_tongsin_no           = '{$de_admin_tongsin_no}',
       de_admin_company_zip          = '{$de_admin_company_zip}',
       de_admin_company_addr         = '{$de_admin_company_addr}',
       de_admin_info_name            = '{$de_admin_info_name}',
       de_admin_info_email           = '{$de_admin_info_email}',
       de_card_test                  = '{$de_card_test}',
       de_lg_mid                     = '{$de_lg_mid}',
       de_lg_mert_key                = '{$de_lg_mert_key}',
       de_card_point                 = '{$de_card_point}',
       de_settle_min_point           = {$de_settle_min_point},
       de_settle_max_point           = {$de_settle_max_point},
       de_settle_point_unit          = {$de_settle_point_unit},
       de_point_days                 = {$de_point_days},
       de_pg_service                 = '{$de_pg_service}',
       de_sms_max_bytes              = '{$de_sms_max_bytes}',
       de_sms_cont1                  = '{$de_sms_cont1}',
       de_sms_cont2                  = '{$de_sms_cont2}',
       de_sms_cont3                  = '{$de_sms_cont3}',
       de_sms_cont4                  = '{$de_sms_cont4}',
       de_sms_cont5                  = '{$de_sms_cont5}',
       de_sms_cont6                  = '{$de_sms_cont6}',
       de_sms_cont7                  = '{$de_sms_cont7}',
       de_sms_cont8                  = '{$de_sms_cont8}',
       de_sms_cont9                  = '{$de_sms_cont9}',
       de_sms_cont10                 = '{$de_sms_cont10}',
       de_sms_use1                   = '{$de_sms_use1}',
       de_sms_use2                   = '{$de_sms_use2}',
       de_sms_use3                   = '{$de_sms_use3}',
       de_sms_use4                   = '{$de_sms_use4}',
       de_sms_use5                   = '{$de_sms_use5}',
       de_sms_use6                   = '{$de_sms_use5}',
       de_sms_use7                   = '{$de_sms_use7}',
       de_sms_use8                   = '{$de_sms_use8}',
       de_sms_use9                   = '{$de_sms_use9}',
       de_sms_use10                  = '{$de_sms_use10}',
       de_sms_hp                     = '{$de_sms_hp}',
       de_sms_use                    = '{$de_sms_use}',
       de_aligo_user_id              = '{$de_aligo_user_id}',
       de_aligo_key                  = '{$de_aligo_key}',
       de_cart_keep_term             =  {$de_cart_keep_term},
       de_guest_cart_use             = '{$de_guest_cart_use}',
       de_admin_buga_no              = '{$de_admin_buga_no}',
       de_escrow_use                 = '{$de_escrow_use}',
       de_tax_flag_use               = '{$de_tax_flag_use}',
       de_schedule_max_days          = '{$de_schedule_max_days}',
       de_schedule_days_colors       = '{$de_schedule_days_colors}',
       de_schedule_com_counts        = '{$de_schedule_com_counts}',
       de_schedule_com_colors        = '{$de_schedule_com_colors}'
";

// WHERE 절 추가 (없으면 전체 레코드 수정됨)
// $sql .= " WHERE de_id = 1";


// echo $sql;exit;
sql_query_pg($sql);



// run_event('shop_admin_configformupdate');

if( $warning_msg ){
    alert($warning_msg, "./configform.php");
} else {
    goto_url("./configform.php");
}