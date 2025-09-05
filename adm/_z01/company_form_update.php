<?php
$sub_menu = "920200";
include_once("./_common.php");
include_once(G5_LIB_PATH."/register.lib.php");

// 입력 기본값 안전 초기화
$w = isset($_POST['w']) ? trim($_POST['w']) : (isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '');
$shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : (isset($_REQUEST['shop_id']) ? (int)$_REQUEST['shop_id'] : 0);
// 본사 선택값: shop_parent_id만 사용
$shop_parent_id = isset($_POST['shop_parent_id']) ? (int)$_POST['shop_parent_id'] : 0;
$head_clear = isset($_POST['head_clear']) ? (int)$_POST['head_clear'] : 0;

if ($w == 'u')
    check_demo();

@auth_check($auth[$sub_menu], 'w');
//check_admin_token();

if(!trim($_POST['category_ids'])) alert('업종(분류)을 반드시 선택해 주세요.');
if(!trim($_POST['name'])) alert('업체명을 입력해 주세요.');
if(!trim($_POST['contact_email'])) alert('이메일을 입력해 주세요.');
if(!trim($_POST['owner_name'])) alert('대표자명을 입력해 주세요.');
if(!trim($_POST['contact_phone'])) alert('업체전화번호를 입력해 주세요.');

$name = trim($_POST['name']);
$shop_name = trim($_POST['shop_name']);
$business_no = trim($_POST['business_no']);
$business_no = preg_replace('/[^0-9]/', '', $business_no); // 사업자번호 숫자만 추출
$owner_name = trim($_POST['owner_name']);
$contact_email = trim($_POST['contact_email']);
$contact_phone = trim($_POST['contact_phone']);
$contact_phone = preg_replace('/[^0-9]/', '', $contact_phone); // 전화번호 숫자만 추출
$zipcode = trim($_POST['zipcode']);
$addr1 = trim($_POST['addr1']);
$addr2 = trim($_POST['addr2']);
$addr3 = trim($_POST['addr3']);
$latitude = trim($_POST['latitude']);
$longitude = trim($_POST['longitude']);
$url = trim($_POST['url']);
$max_capacity = (int)$_POST['max_capacity'];
$reservelink_yn = (isset($_POST['reservelink_yn']) && $_POST['reservelink_yn'] == '') ? $_POST['reservelink_yn'] : '';
$reservelink = trim($_POST['reservelink']);
$reserve_tel = trim($_POST['reserve_tel']);
$shop_description = conv_unescape_nl(stripslashes($_POST['shop_description']));
$bank_account = trim($_POST['bank_account']);
$bank_account = preg_replace('/[^0-9]/', '', $bank_account); // 계좌번호 숫자만 추출
$bank_name = trim($_POST['bank_name']); //은행명
$bank_holder = trim($_POST['bank_holder']); //예금주
$settlement_type = trim($_POST['settlement_type']); //정산타입(수동/자동)
$settlement_cycle = trim($_POST['settlement_cycle']); //정산주기(monthly, weekly, 2monthly)
$settlement_day = (int)$_POST['settlement_day']; //정산일(25 | 01 ...)
$tax_type = trim($_POST['tax_type']); //과세유형
$settlement_memo = conv_unescape_nl(stripslashes($_POST['settlement_memo'])); //정산메모
$is_active = (isset($_POST['is_active']) && $_POST['is_active'] != '') ? $_POST['is_active'] : 'N'; //활성화여부
$settlement_memo = conv_unescape_nl(stripslashes($_POST['settlement_memo']));
$cancel_policy = conv_unescape_nl(stripslashes($_POST['cancel_policy']));
// point_rate는 소수점 2자리까지만 
$point_rate = (float)$_POST['point_rate'];
$point_rate = number_format($point_rate,2,'.','');
// names 업체명 히스토리
$branch = trim($_POST['branch']);
// shop_parent_id 본사가맹점 id
// shop_names = 가맹점명 히스토리


if($shop_id == $shop_parent_id){
    alert('현재의 가맹점을 본사로 등록할 수 없습니다.');
}

// 이메일 형식 체크
if(!preg_match("/^[a-z0-9_+.-]+@([a-z0-9-]+\.)+[a-z0-9]{2,4}$/",$contact_email)) {
    alert('이메일 형식이 올바르지 않습니다.');
}

//위도형식에 맞지 않으면 경고창 띄우기
if($latitude){
    if(!preg_match('/^(\+|-)?(?:90(?:(?:\.0{1,})?)|(?:[1-8]?\d(?:\.\d{1,})?))$/', $latitude)){
        alert('위도의 형식이 올바르지 않습니다.');
    }
}
//경도형식에 맞지 않으면 경고창 띄우기
if($longitude){
    if(!preg_match('/^(\+|-)?(?:180(?:(?:\.0{1,})?)|(?:1[0-7]?\d(?:\.\d{1,})?)|(?:\d?\d(?:\.\d{1,})?))$/', $longitude)){
        alert('경도의 형식이 올바르지 않습니다.');
    }
}

// 먼저 shop 해당 업체(shop_id)와 관계되는 category_id들을 전부 삭제
$cdsql = " DELETE FROM {$g5['shop_category_relation_table']} WHERE shop_id = '{$shop_id}' ";
sql_query_pg($cdsql);

// $category_ids 라는 (,)로 구분된 문자열을 (,)구분자로 배열에 담는다.
$category_ids_arr = (isset($_POST['category_ids']) && !empty(trim($_POST['category_ids'] ?? ''))) ? explode(',', $_POST['category_ids']) : array();
if(count($category_ids_arr)){
    $cisql = " INSERT INTO {$g5['shop_category_relation_table']} (shop_id, category_id, sort) VALUES ";
    $values = array();
    $n = 1;
    foreach($category_ids_arr as $category_id){
        $values[] = "('{$shop_id}', '{$category_id}', '{$n}')";
        $n++;
    }
    $cisql .= implode(',', $values);
    sql_query_pg($cisql);
}
// print_r2($category_ids_arr);exit;



// 업체정보 추출
if ($w=='u')
	$com = sql_fetch_pg(" SELECT * FROM {$g5['shop_table']} WHERE shop_id = '$shop_id' ");


// 업체명 히스토리
if($com['name'] != $name) {
	$names = $com['names'].', '.$name.'('.substr(G5_TIME_YMD,2).'~)';
    if($w == 'u')
        change_com_names($shop_id, $com['name']);
}
else {
	$names = $_POST['names'];
}
// 여기까지 작업 ##########################################################################################################################

$sql_common = "	name = '".addslashes($name)."'
                , shop_name = '".addslashes($shop_name)."'
                , business_no = '{$business_no}'
                , owner_name = '{$owner_name}'
                , contact_email = '{$contact_email}'
                , contact_phone = '{$contact_phone}'
                , zipcode = '{$zipcode}'
                , addr1 = '{$addr1}'
                , addr2 = '{$addr2}'
                , addr3 = '{$addr3}'
                , latitude = '{$latitude}'
                , longitude = '{$longitude}'
                , url = '{$url}'
                , max_capacity = '{$max_capacity}'
                , status = '{$_POST['status']}'
                , reservelink_yn = '{$reservelink_yn}'
                , reservelink = '{$reservelink}'
                , reserve_tel = '{$reserve_tel}'
                , shop_description = '{$shop_description}'
                , names = '".addslashes($names)."'
                , tax_type = '{$_POST['tax_type']}'
                , branch = '".addslashes($branch)."'
                , memo = '{$memo}'
";

// API key 생성
// tms_get_random_string('09azAZ',40);
if($key_renewal){
    $com_api_key = tms_get_random_string('09azAZ',40);
    $sql_common .= " , api_key = '{$com_api_key}' ";
}
else if($key_clear){
    $sql_common .= " , com_api_key = '' ";
}

$sql_common .= ($head_clear) ? " , shop_parent_id = 0 " : " , shop_parent_id = '".$shop_parent_id."' ";

// 생성
if ($w == '') {
    // 업체 정보 생성
	$sql = " INSERT into {$g5['shop_table']} SET
				{$sql_common}
                , created_at = '".G5_TIME_YMDHIS."'
                , updated_at = '".G5_TIME_YMDHIS."'
	";
    sql_query($sql,1);
	$com_idx = sql_insert_id();

}
// 수정
else if ($w == 'u') {

	if (!$com['com_idx'])
		alert('존재하지 않는 업체자료입니다.');
 
    $sql = "	UPDATE {$g5['shop_table']} SET 
					{$sql_common}
					, updated_at = '".G5_TIME_YMDHIS."'
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
    upload_multi_file($_FILES['comf_datas'],'shop',$shop_id,'shop/shop_file','comf');
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