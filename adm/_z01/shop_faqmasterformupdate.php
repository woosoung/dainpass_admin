<?php
$sub_menu = '960200';
include_once('./_common.php');

// 입력 검증 - 화이트리스트 방식
$allowed_w = array('', 'u', 'd');
$w = isset($_REQUEST['w']) ? clean_xss_tags($_REQUEST['w']) : '';
$w = in_array($w, $allowed_w) ? $w : '';

// 등록/수정/삭제 모두에 대해 데모 체크
if ($w === 'u' || $w === 'd') {
    check_demo();
}

if ($w === 'd') {
    @auth_check($auth[$sub_menu], 'd');
} else {
    @auth_check($auth[$sub_menu], 'w');
}

check_admin_token();

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

// 입력값 검증
$fm_id = isset($_REQUEST['fm_id']) ? (int)$_REQUEST['fm_id'] : 0;
$fm_id = ($fm_id >= 0 && $fm_id <= 2147483647) ? $fm_id : 0;

// form에서 넘어온 shop_id는 신뢰하지 않고, 현재 로그인 정보 기준으로 강제
$form_shop_id = $shop_id;

// 삭제가 아닐 때만 제목/순서 검증
if ($w !== 'd') {
    $fm_subject = isset($_POST['fm_subject']) ? strip_tags(clean_xss_attributes($_POST['fm_subject'])) : '';
    $fm_subject = trim($fm_subject);
    $fm_subject = substr($fm_subject, 0, 100); // 최대 길이 제한

    $fm_order = isset($_POST['fm_order']) ? (int)$_POST['fm_order'] : 0;
    // 출력순서 범위 검증 (-1000~1000)
    if ($fm_order < -1000 || $fm_order > 1000) {
        $fm_order = 0;
    }

    if ($fm_subject === '') {
        alert('제목을 입력해 주세요.', './shop_faqmasterform.php?w='.$w.($fm_id ? '&fm_id='.$fm_id : ''));
        exit;
    }

    // 공통 SET 절 (PostgreSQL, faq_master)
    // 문자열은 pg_escape_string으로 이스케이프
    $fm_subject_pg = pg_escape_string($g5['connect_pg'], $fm_subject);
}

if ($w === '') {
    // INSERT
    $sql = " INSERT INTO faq_master (shop_id, fm_subject, fm_order)
             VALUES ({$form_shop_id}, '{$fm_subject_pg}', {$fm_order}) ";
    $result = sql_query_pg($sql);

    if ($result === false) {
        $error_msg = pg_last_error($g5['connect_pg']);
        alert('FAQ 마스터 등록 중 오류가 발생했습니다.\\n'.$error_msg, './shop_faqmasterlist.php');
        exit;
    }

    $fm_id = (int) sql_insert_id_pg('faq_master');

} elseif ($w === 'u') {
    if (!$fm_id || $fm_id <= 0) {
        alert('잘못된 접근입니다.', './shop_faqmasterlist.php');
        exit;
    }

    // 해당 가맹점의 마스터인지 확인
    $exist_sql = " SELECT fm_id, shop_id
                   FROM faq_master
                   WHERE fm_id = {$fm_id}
                     AND shop_id = {$form_shop_id} ";
    $exist = sql_fetch_pg($exist_sql);
    if (!$exist || !$exist['fm_id']) {
        alert('등록된 자료가 없습니다.', './shop_faqmasterlist.php');
        exit;
    }

    $sql = " UPDATE faq_master
             SET fm_subject = '{$fm_subject_pg}',
                 fm_order   = {$fm_order}
             WHERE fm_id = {$fm_id}
               AND shop_id = {$form_shop_id} ";
    $result = sql_query_pg($sql);

    if ($result === false) {
        $error_msg = pg_last_error($g5['connect_pg']);
        alert('FAQ 마스터 수정 중 오류가 발생했습니다.\\n'.$error_msg, './shop_faqmasterform.php?w=u&fm_id='.$fm_id);
        exit;
    }

} elseif ($w === 'd') {
    if (!$fm_id || $fm_id <= 0) {
        alert('잘못된 접근입니다.', './shop_faqmasterlist.php');
        exit;
    }

    // 해당 가맹점의 마스터인지 확인
    $exist_sql = " SELECT fm_id, shop_id
                   FROM faq_master
                   WHERE fm_id = {$fm_id}
                     AND shop_id = {$form_shop_id} ";
    $exist = sql_fetch_pg($exist_sql);
    if (!$exist || !$exist['fm_id']) {
        alert('등록된 자료가 없습니다.', './shop_faqmasterlist.php');
        exit;
    }

    // 마스터에 속한 FAQ 항목 개수 확인
    $faq_cnt_sql = " SELECT COUNT(*) AS cnt
                     FROM faq
                     WHERE fm_id = {$fm_id} ";
    $faq_cnt_row = sql_fetch_pg($faq_cnt_sql);
    $faq_cnt = isset($faq_cnt_row['cnt']) ? (int)$faq_cnt_row['cnt'] : 0;

    // FAQ 항목이 존재하면 삭제 불가
    if ($faq_cnt > 0) {
        alert('해당 FAQ 마스터에 속한 FAQ 항목이 '.number_format($faq_cnt).'개 존재합니다.\\n\\nFAQ 항목을 먼저 삭제한 후 마스터를 삭제해주세요.', './shop_faqmasterlist.php');
        exit;
    }

    // 마스터 삭제
    $del_master_sql = " DELETE FROM faq_master
                        WHERE fm_id = {$fm_id}
                          AND shop_id = {$form_shop_id} ";
    sql_query_pg($del_master_sql);

    alert('FAQ 마스터가 삭제되었습니다.', './shop_faqmasterlist.php');
    exit;

} else {
    // 허용되지 않는 w 값
    alert('잘못된 접근입니다.', './shop_faqmasterlist.php');
    exit;
}

// 등록/수정 후에는 수정 폼으로 이동
alert('FAQ 마스터가 저장되었습니다.', './shop_faqmasterlist.php');

exit;
