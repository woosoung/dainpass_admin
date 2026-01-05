<?php
$sub_menu = "960100";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

// 토큰 체크
check_admin_token();

// 액션 화이트리스트 검증
$allowed_actions = array('delete');
$action = isset($_POST['act']) ? clean_xss_tags($_POST['act']) : '';
$action = in_array($action, $allowed_actions) ? $action : '';

// qstr 생성 - 화이트리스트 검증
$allowed_sst = array('shopnotice_id', 'subject', 'status', 'create_at');
$allowed_sod = array('asc', 'desc');
$allowed_sfl = array('', 'subject', 'content', 'mb_id');
$allowed_sfl2 = array('', 'ok', 'pending');

$qstr = '';
if (isset($_POST['page']) && $_POST['page']) {
    $page = (int)$_POST['page'];
    $page = ($page > 0 && $page <= 10000) ? $page : 1;
    $qstr .= '&page=' . $page;
}
if (isset($_POST['sst']) && $_POST['sst']) {
    $sst = clean_xss_tags($_POST['sst']);
    if (in_array($sst, $allowed_sst)) {
        $qstr .= '&sst=' . urlencode($sst);
    }
}
if (isset($_POST['sod']) && $_POST['sod']) {
    $sod = clean_xss_tags($_POST['sod']);
    if (in_array($sod, $allowed_sod)) {
        $qstr .= '&sod=' . urlencode($sod);
    }
}
if (isset($_POST['sfl']) && $_POST['sfl']) {
    $sfl = clean_xss_tags($_POST['sfl']);
    if (in_array($sfl, $allowed_sfl)) {
        $qstr .= '&sfl=' . urlencode($sfl);
    }
}
if (isset($_POST['stx']) && $_POST['stx']) {
    $stx = clean_xss_tags($_POST['stx']);
    $stx = substr($stx, 0, 100); // 최대 길이 제한
    $qstr .= '&stx=' . urlencode($stx);
}
if (isset($_POST['sfl2']) && $_POST['sfl2']) {
    $sfl2 = clean_xss_tags($_POST['sfl2']);
    if (in_array($sfl2, $allowed_sfl2)) {
        $qstr .= '&sfl2=' . urlencode($sfl2);
    }
}

if ($action == 'delete') {
    $chk = isset($_POST['chk']) ? $_POST['chk'] : array();

    if (empty($chk) || !is_array($chk)) {
        alert('선택된 항목이 없습니다.', './shop_notice_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }

    // 최대 선택 개수 제한 (DoS 공격 방지)
    if (count($chk) > 100) {
        alert('한 번에 최대 100개까지만 삭제할 수 있습니다.', './shop_notice_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
        exit;
    }

    $chk_count = count($chk);
    $chk_ids = array();

    foreach ($chk as $id) {
        $id = (int)$id;
        // ID 범위 검증 (양수이며 2147483647 이하)
        if ($id > 0 && $id <= 2147483647) {
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
                    AND shop_id = " . (int)$shop_id . " ";

    sql_query_pg($delete_sql);

    alert('선택한 ' . $chk_count . '개의 공지사항이 삭제되었습니다.', './shop_notice_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));

} else {
    // 유효하지 않은 요청이거나 요청이 없는 경우
    if ($action) {
        alert('유효하지 않은 요청입니다.', './shop_notice_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
    } else {
        alert('잘못된 요청입니다.', './shop_notice_list.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
    }
}

exit;
?>

