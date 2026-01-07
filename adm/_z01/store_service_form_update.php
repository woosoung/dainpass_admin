<?php
$sub_menu = "930200";
include_once("./_common.php");
include_once(G5_LIB_PATH."/register.lib.php");

@auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

// 입력 기본값 안전 초기화
$w = isset($_POST['w']) ? trim($_POST['w']) : (isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '');
$service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : (isset($_REQUEST['service_id']) ? (int)$_REQUEST['service_id'] : 0);

if ($w == 'u')
    check_demo();

//check_admin_token();
if(!trim($_POST['service_name'])) alert('서비스명을 입력해 주세요.');
if(!isset($_POST['service_time']) || $_POST['service_time'] === '') alert('소요시간을 입력해 주세요.');

$service_name = trim($_POST['service_name']);
$description = isset($_POST['description']) ? conv_unescape_nl(stripslashes($_POST['description'])) : '';
$price = isset($_POST['price']) ? (int)$_POST['price'] : 0;
$service_time = isset($_POST['service_time']) ? (int)$_POST['service_time'] : 0;

// 입력값 길이 및 범위 검증
if (mb_strlen($service_name, 'UTF-8') > 50) {
    alert('서비스명은 최대 50자까지 입력 가능합니다.');
}

if (mb_strlen($description, 'UTF-8') > 500) {
    alert('서비스 설명은 최대 500자까지 입력 가능합니다.');
}

if ($price < 0 || $price > 100000000) {
    alert('가격은 0원 이상 1억원 이하로 입력해 주세요.');
}

if ($service_time < 0 || $service_time > 1440) {
    alert('소요시간은 0분 이상 1440분(24시간) 이하로 입력해 주세요.');
}

// select 값들의 화이트리스트 검증 및 기본값 처리
$status_whitelist = ['active', 'inactive'];
$status = isset($_POST['status']) ? trim($_POST['status']) : 'active';
$status = in_array($status, $status_whitelist) ? $status : 'active';

$yn_whitelist = ['Y', 'N'];
$link_yn = isset($_POST['link_yn']) ? trim($_POST['link_yn']) : 'N';
$link_yn = in_array($link_yn, $yn_whitelist) ? $link_yn : 'N';

$option_yn = isset($_POST['option_yn']) ? trim($_POST['option_yn']) : 'N';
$option_yn = in_array($option_yn, $yn_whitelist) ? $option_yn : 'N';

$main_yn = isset($_POST['main_yn']) ? trim($_POST['main_yn']) : 'N';
$main_yn = in_array($main_yn, $yn_whitelist) ? $main_yn : 'N';

$signature_yn = isset($_POST['signature_yn']) ? trim($_POST['signature_yn']) : 'N';
$signature_yn = in_array($signature_yn, $yn_whitelist) ? $signature_yn : 'N';

$onsite_payment_yn = isset($_POST['onsite_payment_yn']) ? trim($_POST['onsite_payment_yn']) : 'N';
$onsite_payment_yn = in_array($onsite_payment_yn, $yn_whitelist) ? $onsite_payment_yn : 'N';

if ($w == '') {
    // 추가
    $sql = " INSERT INTO shop_services (
                shop_id,
                service_name,
                description,
                price,
                status,
                link_yn,
                option_yn,
                main_yn,
                signature_yn,
                service_time,
                onsite_payment_yn,
                created_at,
                updated_at
            ) VALUES (
                {$shop_id},
                '".addslashes($service_name)."',
                ".($description ? "'".addslashes($description)."'" : "NULL").",
                {$price},
                '{$status}',
                '{$link_yn}',
                '{$option_yn}',
                '{$main_yn}',
                '{$signature_yn}',
                {$service_time},
                '{$onsite_payment_yn}',
                '".G5_TIME_YMDHIS."',
                '".G5_TIME_YMDHIS."'
            ) RETURNING service_id ";
    
    $result = sql_query_pg($sql);
    if ($result && is_object($result) && isset($result->result)) {
        $row = sql_fetch_array_pg($result->result);
        $service_id = (int)$row['service_id'];
        
        // 시그니처 서비스는 1개만 가능
        if ($signature_yn == 'Y') {
            // 같은 가맹점의 다른 서비스들의 시그니처를 N으로 변경
            $update_sql = " UPDATE shop_services 
                            SET signature_yn = 'N', updated_at = '".G5_TIME_YMDHIS."'
                            WHERE shop_id = {$shop_id} 
                              AND service_id != {$service_id} 
                              AND signature_yn = 'Y' ";
            sql_query_pg($update_sql);
        }
    } else {
        alert('서비스 추가 중 오류가 발생했습니다.');
    }
} else if ($w == 'u') {
    // 수정
    // 해당 서비스가 해당 가맹점의 것인지 확인
    $check_sql = " SELECT service_id FROM shop_services WHERE service_id = {$service_id} AND shop_id = {$shop_id} ";
    $check_row = sql_fetch_pg($check_sql);
    
    if (!$check_row || !$check_row['service_id']) {
        alert('존재하지 않는 서비스자료입니다.');
    }
    
    // 시그니처 서비스는 1개만 가능
    if ($signature_yn == 'Y') {
        // 같은 가맹점의 다른 서비스들의 시그니처를 N으로 변경
        $update_sql = " UPDATE shop_services 
                        SET signature_yn = 'N', updated_at = '".G5_TIME_YMDHIS."'
                        WHERE shop_id = {$shop_id} 
                          AND service_id != {$service_id} 
                          AND signature_yn = 'Y' ";
        sql_query_pg($update_sql);
    }
    
    $sql = " UPDATE shop_services SET 
                service_name = '".addslashes($service_name)."',
                description = ".($description ? "'".addslashes($description)."'" : "NULL").",
                price = {$price},
                status = '{$status}',
                link_yn = '{$link_yn}',
                option_yn = '{$option_yn}',
                main_yn = '{$main_yn}',
                signature_yn = '{$signature_yn}',
                onsite_payment_yn = '{$onsite_payment_yn}',
                service_time = {$service_time},
                updated_at = '".G5_TIME_YMDHIS."'
            WHERE service_id = {$service_id} AND shop_id = {$shop_id} ";
    
    sql_query_pg($sql);
}

if($w == '' || $w == 'u'){
    
    //파일 삭제처리
    $merge_del = array();
    $del_arr = array();
    if(isset($svci_del) && @count($svci_del)){
        foreach($svci_del as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }
    
    if(is_array($merge_del) && @count($merge_del)){
        foreach($merge_del as $k=>$v) {
            array_push($del_arr,$k);
        }
    }
    if(is_array($del_arr) && @count($del_arr)) delete_idx_s3_file($del_arr);
    
    //$service_id가 반드시 있어야 파일업로드가 가능
    if($service_id){
        //멀티파일처리
        upload_multi_file($_FILES['svci_datas'],'shop_services',$service_id,'shop/service_img','svci');
    }
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

if($w == 'u') {
    goto_url('./store_service_form.php?'.$qstr.'&w=u&service_id='.$service_id, false);
}
else {
    goto_url('./store_service_form.php?'.$qstr.'&w=u&service_id='.$service_id, false);
}

?>

