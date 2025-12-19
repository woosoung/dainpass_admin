<?php
$sub_menu = "960100";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

// 토큰 체크
check_admin_token();

$action = isset($_POST['act']) ? clean_xss_tags($_POST['act']) : '';

// qstr 생성
$qstr = '';
if (isset($_POST['page']) && $_POST['page']) {
    $qstr .= '&page=' . (int)$_POST['page'];
}
if (isset($_POST['sst']) && $_POST['sst']) {
    $qstr .= '&sst=' . urlencode($_POST['sst']);
}
if (isset($_POST['sod']) && $_POST['sod']) {
    $qstr .= '&sod=' . urlencode($_POST['sod']);
}
if (isset($_POST['sfl']) && $_POST['sfl']) {
    $qstr .= '&sfl=' . urlencode($_POST['sfl']);
}
if (isset($_POST['stx']) && $_POST['stx']) {
    $qstr .= '&stx=' . urlencode($_POST['stx']);
}
if (isset($_POST['sfl2']) && $_POST['sfl2']) {
    $qstr .= '&sfl2=' . urlencode($_POST['sfl2']);
}

if ($action == 'delete') {
    $chk = isset($_POST['chk']) ? $_POST['chk'] : array();
    
    if (empty($chk) || !is_array($chk)) {
        alert('선택된 항목이 없습니다.', './shop_notice_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    $chk_count = count($chk);
    $chk_ids = array();
    
    foreach ($chk as $id) {
        $id = (int)$id;
        if ($id > 0) {
            $chk_ids[] = $id;
        }
    }
    
    if (empty($chk_ids)) {
        alert('선택된 항목이 없습니다.', './shop_notice_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }
    
    $ids_str = implode(',', $chk_ids);
    
    // 삭제 전에 각 게시물의 S3 이미지 삭제
    if (function_exists('delete_shop_notice_all_s3_images')) {
        foreach ($chk_ids as $shopnotice_id) {
            delete_shop_notice_all_s3_images($shopnotice_id);
        }
    }
    
    // shop_id 검증 후 삭제
    $delete_sql = " DELETE FROM shop_notice 
                    WHERE shopnotice_id IN ({$ids_str}) 
                    AND shop_id = {$shop_id} ";
    
    sql_query_pg($delete_sql);
    
    alert('선택한 ' . $chk_count . '개의 공지사항이 삭제되었습니다.', './shop_notice_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
    
} else {
    alert('잘못된 요청입니다.', './shop_notice_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
}

exit;
?>

