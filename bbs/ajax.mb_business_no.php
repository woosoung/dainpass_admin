<?php
include_once('./_common.php');
include_once(G5_LIB_PATH . '/register_sub.lib.php');

$business_no = isset($_POST['reg_business_no']) ? trim($_POST['reg_business_no']) : '';

set_session('ss_check_business_no', '');

// 빈 값 체크
if (empty($business_no)) {
    die("사업자등록번호를 입력해 주십시오.");
}

// 형식 체크 (000-00-00000 또는 0000000000)
$business_no_clean = preg_replace("/[^0-9]/", "", $business_no);
if (strlen($business_no_clean) != 10) {
    die("사업자등록번호는 10자리 숫자여야 합니다.");
}

// 유효성 검증
if (!valid_business_no($business_no)) {
    die("유효하지 않은 사업자등록번호입니다.");
}

// 중복 체크
$sql = "SELECT business_no FROM {$g5['shop_table']} WHERE business_no = '{$business_no_clean}'";
$row = sql_fetch_pg($sql);
if ($row) {
    die("이미 등록된 사업자등록번호입니다.");
}

set_session('ss_check_business_no', $business_no_clean);
