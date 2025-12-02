<?php
$sub_menu = "930300";
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
$steps_id = isset($_POST['steps_id']) ? (int)$_POST['steps_id'] : (isset($_REQUEST['steps_id']) ? (int)$_REQUEST['steps_id'] : 0);

// 가맹점측 관리자는 자신의 가맹점만 수정 가능
if ($post_shop_id != $shop_id) {
    alert('접속할 수 없는 페이지 입니다.');
}

if ($w == 'u')
    check_demo();

//check_admin_token();
if(!trim($_POST['name'])) alert('이름을 입력해 주세요.');
if(!trim($_POST['max_customers_per_slot']) || (int)$_POST['max_customers_per_slot'] < 1) alert('슬롯당 최대고객수를 입력해 주세요.');

$name = trim($_POST['name']);
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$specialty = isset($_POST['specialty']) ? trim($_POST['specialty']) : '';
$max_customers_per_slot = isset($_POST['max_customers_per_slot']) ? (int)$_POST['max_customers_per_slot'] : 1;
$title = isset($_POST['title']) ? trim($_POST['title']) : '';

if ($max_customers_per_slot < 1) $max_customers_per_slot = 1;

if ($w == '') {
    // 추가
    $sql = " INSERT INTO staff (
                store_id,
                name,
                phone,
                specialty,
                max_customers_per_slot,
                title,
                created_at,
                updated_at
            ) VALUES (
                {$shop_id},
                '".addslashes($name)."',
                ".($phone ? "'".addslashes($phone)."'" : "NULL").",
                ".($specialty ? "'".addslashes($specialty)."'" : "NULL").",
                {$max_customers_per_slot},
                ".($title ? "'".addslashes($title)."'" : "NULL").",
                '".G5_TIME_YMDHIS."',
                '".G5_TIME_YMDHIS."'
            ) RETURNING steps_id ";
    
    $result = sql_query_pg($sql);
    if ($result && is_object($result) && isset($result->result)) {
        $row = sql_fetch_array_pg($result->result);
        $steps_id = (int)$row['steps_id'];
    } else {
        alert('직원 추가 중 오류가 발생했습니다.');
    }
} else if ($w == 'u') {
    // 수정
    // 해당 직원이 해당 가맹점의 것인지 확인
    $check_sql = " SELECT steps_id FROM staff WHERE steps_id = {$steps_id} AND store_id = {$shop_id} ";
    $check_row = sql_fetch_pg($check_sql);
    
    if (!$check_row || !$check_row['steps_id']) {
        alert('존재하지 않는 직원자료입니다.');
    }
    
    $sql = " UPDATE staff SET 
                name = '".addslashes($name)."',
                phone = ".($phone ? "'".addslashes($phone)."'" : "NULL").",
                specialty = ".($specialty ? "'".addslashes($specialty)."'" : "NULL").",
                max_customers_per_slot = {$max_customers_per_slot},
                title = ".($title ? "'".addslashes($title)."'" : "NULL").",
                updated_at = '".G5_TIME_YMDHIS."'
            WHERE steps_id = {$steps_id} AND store_id = {$shop_id} ";
    
    sql_query_pg($sql);
}

if($w == '' || $w == 'u'){
    
    //파일 삭제처리
    $merge_del = array();
    $del_arr = array();
    if(isset($stfi_del) && @count($stfi_del)){
        foreach($stfi_del as $k=>$v) {
            $merge_del[$k] = $v;
        }
    }
    
    if(is_array($merge_del) && @count($merge_del)){
        foreach($merge_del as $k=>$v) {
            array_push($del_arr,$k);
        }
    }
    if(is_array($del_arr) && @count($del_arr)) delete_idx_s3_file($del_arr);
    
    //$steps_id가 반드시 있어야 파일업로드가 가능
    if($steps_id){
        //멀티파일처리
        upload_multi_file($_FILES['stfi_datas'],'staff',$steps_id,'shop/staff_img','stfi');
    }
}

$qstr = '';
$sfl = isset($_POST['sfl']) ? trim($_POST['sfl']) : '';
$stx = isset($_POST['stx']) ? trim($_POST['stx']) : '';
$sst = isset($_POST['sst']) ? trim($_POST['sst']) : '';
$sod = isset($_POST['sod']) ? trim($_POST['sod']) : '';
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;

if ($sfl) $qstr .= '&sfl='.urlencode($sfl);
if ($stx) $qstr .= '&stx='.urlencode($stx);
if ($sst) $qstr .= '&sst='.urlencode($sst);
if ($sod) $qstr .= '&sod='.urlencode($sod);
if ($page > 1) $qstr .= '&page='.$page;

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
    goto_url('./staff_form.php?'.$qstr.'&w=u&steps_id='.$steps_id, false);
}
else {
    goto_url('./staff_form.php?'.$qstr.'&w=u&steps_id='.$steps_id, false);
}

?>

