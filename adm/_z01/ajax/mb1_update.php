<?php
include_once('./_common.php');

// 권한 체크
if(!$is_manager) {
    echo json_encode(array('success' => false, 'message' => '관리자만 접근할 수 있습니다.'));
    exit;
}

// POST 데이터 받기
$shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : 0;
$mb_id = $member['mb_id'];

// shop_id 유효성 검사 (0 이상의 정수만 허용)
if($shop_id < 0) {
    echo json_encode(array('success' => false, 'message' => '잘못된 가맹점 ID입니다.'));
    exit;
}

// shop_id가 0이 아닌 경우, 해당 shop_id가 존재하는지 확인
if($shop_id > 0) {
    $shop_check_sql = " SELECT shop_id FROM {$g5['shop_table']} WHERE shop_id = '{$shop_id}' AND status NOT IN ('trash','closed','shutdown') ";
    $shop_check = sql_fetch_pg($shop_check_sql);
    
    if(!$shop_check || !is_array($shop_check)) {
        echo json_encode(array('success' => false, 'message' => '존재하지 않는 가맹점입니다.'));
        exit;
    }
}

// mb_1 업데이트
$sql = " UPDATE {$g5['member_table']} SET mb_1 = '{$shop_id}' WHERE mb_id = '{$mb_id}' ";
sql_query($sql, 1);

// 세션 업데이트 (필요한 경우)
if($shop_id > 0) {
    set_session('ss_shop_id', $shop_id);
} else {
    // 플랫폼 모드로 되돌릴 때는 세션 삭제
    unset($_SESSION['ss_shop_id']);
}

$message = ($shop_id == 0) ? '플랫폼 모드로 변경되었습니다.' : '가맹점이 변경되었습니다.';

echo json_encode(array('success' => true, 'message' => $message, 'shop_id' => $shop_id));
exit;
?>

