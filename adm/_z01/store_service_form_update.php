<?php
$sub_menu = "930200";
include_once("./_common.php");
include_once(G5_LIB_PATH."/register.lib.php");

// 가맹점측 관리자 접근 권한 체크
$has_access = false;
$shop_id = 0;

if ($is_member && $member['mb_id']) {
    // MySQL에서 회원 정보 확인
    // 플랫폼 관리자(mb_level >= 6)는 mb_2 = 'N'일 수 있으므로 mb_2 조건을 다르게 적용
    $mb_sql = " SELECT mb_id, mb_level, mb_1, mb_2, mb_leave_date, mb_intercept_date 
                FROM {$g5['member_table']} 
                WHERE mb_id = '{$member['mb_id']}' 
                AND mb_level >= 4 
                AND (
                    mb_level >= 6 
                    OR (mb_level < 6 AND mb_2 = 'Y')
                )
                AND (mb_leave_date = '' OR mb_leave_date IS NULL)
                AND (mb_intercept_date = '' OR mb_intercept_date IS NULL) ";
    
    $mb_row = sql_fetch($mb_sql, 1);
    
    if ($mb_row && $mb_row['mb_id']) {
        $mb_1_value = trim($mb_row['mb_1']);
        
        // mb_1 = '0'인 경우: 플랫폼 관리자
        if ($mb_1_value === '0' || $mb_1_value === '') {
            // 플랫폼 관리자는 shop_id = 0에 해당하는 레코드가 없으므로 '업체 데이터가 없습니다.' 표시
            alert('업체 데이터가 없습니다.');
        }
        
        // mb_1에 shop_id 값이 있는 경우: 해당 shop_id로 shop 테이블 조회
        if (!empty($mb_1_value)) {
            // PostgreSQL에서 shop_id 확인 (shop_id는 bigint이므로 정수로 비교)
            $shop_id_check = (int)$mb_1_value;
            $shop_sql = " SELECT shop_id FROM {$g5['shop_table']} WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);
            
            if ($shop_row && $shop_row['shop_id']) {
                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
            } else {
                // shop_id에 해당하는 레코드가 없는 경우
                alert('업체 데이터가 없습니다.');
            }
        }
    }
}

// 접근 권한이 없으면 메시지 표시
if (!$has_access) {
    alert('접속할 수 없는 페이지 입니다.');
}

// 입력 기본값 안전 초기화
$w = isset($_POST['w']) ? trim($_POST['w']) : (isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '');
$post_shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : (isset($_REQUEST['shop_id']) ? (int)$_REQUEST['shop_id'] : 0);
$service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : (isset($_REQUEST['service_id']) ? (int)$_REQUEST['service_id'] : 0);

// 가맹점측 관리자는 자신의 가맹점만 수정 가능
if ($post_shop_id != $shop_id) {
    alert('접속할 수 없는 페이지 입니다.');
}

if ($w == 'u')
    check_demo();

//check_admin_token();
if(!trim($_POST['service_name'])) alert('서비스명을 입력해 주세요.');
if(!trim($_POST['service_time']) || (int)$_POST['service_time'] < 0) alert('소요시간을 입력해 주세요.');

$service_name = trim($_POST['service_name']);
$description = isset($_POST['description']) ? conv_unescape_nl(stripslashes($_POST['description'])) : '';
$price = isset($_POST['price']) ? (int)$_POST['price'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : 'active';
$link_yn = isset($_POST['link_yn']) ? trim($_POST['link_yn']) : 'N';
$option_yn = isset($_POST['option_yn']) ? trim($_POST['option_yn']) : 'N';
$main_yn = isset($_POST['main_yn']) ? trim($_POST['main_yn']) : 'N';
$signature_yn = isset($_POST['signature_yn']) ? trim($_POST['signature_yn']) : 'N';
$service_time = isset($_POST['service_time']) ? (int)$_POST['service_time'] : 0;

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

