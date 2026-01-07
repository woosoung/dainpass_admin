<?php
$sub_menu = "930300";
include_once("./_common.php");
include_once(G5_LIB_PATH."/register.lib.php");

@auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

// print_r2($_POST);exit;
// 입력 기본값 안전 초기화
$w = isset($_POST['w']) ? trim($_POST['w']) : (isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '');
$staff_id = isset($_POST['staff_id']) ? (int)$_POST['staff_id'] : (isset($_REQUEST['staff_id']) ? (int)$_REQUEST['staff_id'] : 0);

if ($w == 'u')
    check_demo();

//check_admin_token();
if(!trim($_POST['name'])) alert('이름을 입력해 주세요.');
if(!trim($_POST['max_customers_per_slot']) || (int)$_POST['max_customers_per_slot'] < 1) alert('슬롯당 최대고객수를 입력해 주세요.');

$name = trim($_POST['name']);
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$phone = preg_replace('/[^0-9]/', '', $phone); // 숫자만 추출
$specialty = isset($_POST['specialty']) ? trim($_POST['specialty']) : '';
$max_customers_per_slot = isset($_POST['max_customers_per_slot']) ? (int)$_POST['max_customers_per_slot'] : 1;
$title = isset($_POST['title']) ? trim($_POST['title']) : '';

// 입력값 길이 및 범위 검증
if (mb_strlen($name, 'UTF-8') > 10) {
    alert('이름은 최대 10자까지 입력 가능합니다.');
}

if (mb_strlen($phone, 'UTF-8') > 20) {
    alert('전화번호는 최대 20자까지 입력 가능합니다.');
}

if (mb_strlen($title, 'UTF-8') > 30) {
    alert('직책은 최대 30자까지 입력 가능합니다.');
}

if (mb_strlen($specialty, 'UTF-8') > 100) {
    alert('전문분야는 최대 100자까지 입력 가능합니다.');
}

if ($max_customers_per_slot < 1 || $max_customers_per_slot > 100) {
    alert('슬롯당 최대고객수는 1명 이상 100명 이하로 입력해 주세요.');
}

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
            ) ";
    // echo $sql;exit;
    sql_query_pg($sql);
    $staff_id = sql_insert_id_pg('staff');
} else if ($w == 'u') {
    // 수정
    // 해당 직원이 해당 가맹점의 것인지 확인
    $check_sql = " SELECT staff_id FROM staff WHERE staff_id = {$staff_id} AND store_id = {$shop_id} ";
    $check_row = sql_fetch_pg($check_sql);
    
    if (!$check_row || !$check_row['staff_id']) {
        alert('존재하지 않는 직원자료입니다.');
    }
    
    $sql = " UPDATE staff SET 
                name = '".addslashes($name)."',
                phone = ".($phone ? "'".addslashes($phone)."'" : "NULL").",
                specialty = ".($specialty ? "'".addslashes($specialty)."'" : "NULL").",
                max_customers_per_slot = {$max_customers_per_slot},
                title = ".($title ? "'".addslashes($title)."'" : "NULL").",
                updated_at = '".G5_TIME_YMDHIS."'
            WHERE staff_id = {$staff_id} AND store_id = {$shop_id} ";
    
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
    
    //$staff_id가 반드시 있어야 파일업로드가 가능
    if($staff_id){
        //멀티파일처리
        upload_multi_file($_FILES['stfi_datas'],'staff',$staff_id,'shop/staff_img','stfi');
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
    goto_url('./staff_form.php?'.$qstr.'&w=u&staff_id='.$staff_id, false);
}
else {
    goto_url('./staff_form.php?'.$qstr.'&w=u&staff_id='.$staff_id, false);
}

?>

