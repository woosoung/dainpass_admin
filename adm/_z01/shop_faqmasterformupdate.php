<?php
$sub_menu = '960200';
include_once('./_common.php');

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

// 현재 로그인한 가맹점 정보 확인 (shop_faqmasterform과 동일 패턴)
$has_access = false;
$shop_id = 0;

if ($is_member && $member['mb_id']) {
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
        if ($mb_1_value !== '' && $mb_1_value !== '0') {
            $shop_id = (int) $mb_1_value;
            $has_access = true;
        }
    }
}

if (!$has_access || !$shop_id) {
    alert('접속할 수 없는 페이지 입니다.', './shop_faqmasterlist.php');
    exit;
}

// 입력값
$fm_id    = isset($_REQUEST['fm_id']) ? (int) $_REQUEST['fm_id'] : 0;
// form에서 넘어온 shop_id는 신뢰하지 않고, 현재 로그인 정보 기준으로 강제
$form_shop_id = $shop_id;
$fm_subject = isset($_POST['fm_subject']) ? strip_tags(clean_xss_attributes($_POST['fm_subject'])) : '';
$fm_order   = isset($_POST['fm_order']) ? (int) $_POST['fm_order'] : 0;

if ($fm_subject === '') {
    alert('제목을 입력해 주세요.', './shop_faqmasterform.php?w='.$w.($fm_id ? '&fm_id='.$fm_id : ''));
    exit;
}

// 공통 SET 절 (PostgreSQL, faq_master)
// 문자열은 pg_escape_string으로 이스케이프
$fm_subject_pg = pg_escape_string($g5['connect_pg'], $fm_subject);

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
    if (!$fm_id) {
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
        alert('등록된 자료가 없거나, 다른 가맹점의 FAQ 마스터입니다.', './shop_faqmasterlist.php');
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
    if (!$fm_id) {
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
        alert('등록된 자료가 없거나, 다른 가맹점의 FAQ 마스터입니다.', './shop_faqmasterlist.php');
        exit;
    }

    // 마스터에 속한 FAQ 항목 먼저 삭제
    $del_faq_sql = " DELETE FROM faq WHERE fm_id = {$fm_id} ";
    sql_query_pg($del_faq_sql);

    // 마스터 삭제
    $del_master_sql = " DELETE FROM faq_master
                        WHERE fm_id = {$fm_id}
                          AND shop_id = {$form_shop_id} ";
    sql_query_pg($del_master_sql);

    alert('FAQ 마스터가 삭제되었습니다.', './shop_faqmasterlist.php');
    exit;
}

// 등록/수정 후에는 수정 폼으로 이동
alert('FAQ 마스터가 저장되었습니다.', './shop_faqmasterform.php?w=u&fm_id='.$fm_id);

exit;
