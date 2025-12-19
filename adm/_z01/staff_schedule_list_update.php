<?php
$sub_menu = "930500";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

// 이 파일은 목록에서의 일괄 업데이트를 처리하는 파일입니다.
// 현재는 개별 수정/삭제만 있으므로 기본 구조만 제공합니다.

alert('잘못된 접근입니다.', './staff_schedule_list.php');
?>

