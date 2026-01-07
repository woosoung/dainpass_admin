<?php
$sub_menu = '100000';
require_once './_common.php';

@require_once G5_ADMIN_PATH.'/safe_check.php';

// 플랫폼 관리자 여부 확인 (mb_level >= 6 또는 mb_1이 0 또는 빈 문자열)
$is_platform_admin = false;
if ($is_member && isset($member['mb_level']) && $member['mb_level'] >= 6) {
    $mb_1_value = isset($member['mb_1']) ? trim($member['mb_1']) : '';
    if ($mb_1_value === '0' || $mb_1_value === '') {
        $is_platform_admin = true;
    }
}

if ($is_platform_admin) {
    // 플랫폼 관리자
    $g5['title'] = '플랫폼대시보드';
    require_once G5_ADMIN_PATH.'/admin.head.php';
    include_once G5_ZADM_PATH.'/index_plt_mng.php';
    require_once G5_ADMIN_PATH.'/admin.tail.php';
} else {
    // 가맹점 관리자 - 가맹점 접근 권한 체크
    $result = check_shop_access();
    $shop_id = $result['shop_id'];
    $shop_info = $result['shop_info'];
    
    if($shop_id > 0) {
        $g5['title'] = '가맹점대시보드';
        require_once G5_ADMIN_PATH.'/admin.head.php';
        include_once G5_ZADM_PATH.'/index_shop_mng.php';
    } else {
        $g5['title'] = '플랫폼대시보드';
        require_once G5_ADMIN_PATH.'/admin.head.php';
        // shop_id가 없으면 플랫폼 관리자 페이지로
        include_once G5_ZADM_PATH.'/index_plt_mng.php';
    }
    require_once G5_ADMIN_PATH.'/admin.tail.php';
}



