<?php
$sub_menu = '960200';
include_once('./_common.php');

if ($w === 'u' || $w === 'd') {
    check_demo();
}

if ($w === 'd') {
    @auth_check($auth[$sub_menu], 'd');
} else {
    @auth_check($auth[$sub_menu], 'w');
}

check_admin_token();

// 현재 로그인한 가맹점 정보 확인
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

// 파라미터
$fm_id = isset($_REQUEST['fm_id']) ? (int) $_REQUEST['fm_id'] : 0;
$fa_id = isset($_REQUEST['fa_id']) ? (int) $_REQUEST['fa_id'] : 0;
$fa_order = isset($_POST['fa_order']) ? (int) $_POST['fa_order'] : 0;

// 에디터 값 (질문/답변)은 HTML 그대로 받아와서,
// 저장 전 convert_shop_faq_content_images_to_s3()로 S3에 업로드 및 URL 변환
$fa_question = isset($_POST['fa_question']) ? $_POST['fa_question'] : '';
$fa_answer   = isset($_POST['fa_answer']) ? $_POST['fa_answer'] : '';

// 필수값 검사 (질문/답변 모두 비어있으면 에러)
if (trim(strip_tags($fa_question)) === '' || trim(strip_tags($fa_answer)) === '') {
    alert('질문과 답변을 모두 입력해 주세요.', './shop_faqform.php?w='.$w.'&fm_id='.$fm_id.($fa_id ? '&fa_id='.$fa_id : ''));
    exit;
}

// 해당 fm_id가 현재 가맹점의 것인지 검사
$fm_sql = " SELECT fm_id, shop_id
            FROM faq_master
            WHERE fm_id = {$fm_id}
              AND shop_id = {$shop_id} ";
$fm = sql_fetch_pg($fm_sql);

if (!$fm || !$fm['fm_id']) {
    alert('등록된 FAQ 마스터가 없거나, 다른 가맹점의 데이터입니다.', './shop_faqmasterlist.php');
    exit;
}
// --- S3 업로드 & URL 변환을 위해 content 사전/사후 처리 ---

// 업데이트인 경우, 기존 S3 이미지 목록 확보 (미사용 이미지 정리용)
$old_question = '';
$old_answer   = '';
if ($w === 'u' && $fa_id) {
    $old_row = sql_fetch_pg(" SELECT fa_question, fa_answer
                              FROM faq
                              WHERE fa_id = {$fa_id}
                                AND fm_id = {$fm_id} ");
    if ($old_row) {
        $old_question = (string) $old_row['fa_question'];
        $old_answer   = (string) $old_row['fa_answer'];
    }
}

// 수정 모드에서 로컬 이미지가 있으면 S3로 업로드 (에디터에서 이미 S3로 올라간 이미지는 그대로 유지)
if (function_exists('convert_shop_faq_content_images_to_s3') && $w === 'u' && $fa_id) {
    $fa_question = convert_shop_faq_content_images_to_s3($fa_question, $shop_id, $fm_id, $fa_id, 'fa_question');
    $fa_answer   = convert_shop_faq_content_images_to_s3($fa_answer,   $shop_id, $fm_id, $fa_id, 'fa_answer');
}

// PostgreSQL 이스케이프
$fa_question_pg = pg_escape_string($g5['connect_pg'], $fa_question);
$fa_answer_pg   = pg_escape_string($g5['connect_pg'], $fa_answer);

if ($w === '') {
    // INSERT
    $sql = " INSERT INTO faq (fm_id, fa_question, fa_answer, fa_order)
             VALUES ({$fm_id}, '{$fa_question_pg}', '{$fa_answer_pg}', {$fa_order}) ";
    $result = sql_query_pg($sql);

    if ($result === false) {
        $error_msg = pg_last_error($g5['connect_pg']);
        alert('FAQ 항목 등록 중 오류가 발생했습니다.\\n'.$error_msg, './shop_faqlist.php?fm_id='.$fm_id);
        exit;
    }

    $fa_id = (int) sql_insert_id_pg('faq');

    // 신규 등록 후 fa_id를 얻었으므로, 임시 경로에 저장된 이미지를 확정 경로로 이동
    if ($fa_id && function_exists('move_shop_faq_temp_images_to_fa_id')) {
        $moved_q = move_shop_faq_temp_images_to_fa_id($fa_question, $shop_id, $fm_id, $fa_id, 'fa_question');
        $moved_a = move_shop_faq_temp_images_to_fa_id($fa_answer,   $shop_id, $fm_id, $fa_id, 'fa_answer');

        // 이동된 경우 content 업데이트
        if ($moved_q !== $fa_question || $moved_a !== $fa_answer) {
            $fa_question = $moved_q;
            $fa_answer   = $moved_a;
            $fa_question_pg = pg_escape_string($g5['connect_pg'], $fa_question);
            $fa_answer_pg   = pg_escape_string($g5['connect_pg'], $fa_answer);

            $up_sql = " UPDATE faq
                        SET fa_question = '{$fa_question_pg}',
                            fa_answer   = '{$fa_answer_pg}'
                        WHERE fa_id = {$fa_id}
                          AND fm_id = {$fm_id} ";
            sql_query_pg($up_sql);
        }
    }

} elseif ($w === 'u') {
    if (!$fa_id) {
        alert('잘못된 접근입니다.', './shop_faqlist.php?fm_id='.$fm_id);
        exit;
    }

    // 해당 항목이 현재 마스터에 속하는지 확인
    $exist_sql = " SELECT f.fa_id
                   FROM faq AS f
                   INNER JOIN faq_master AS m ON f.fm_id = m.fm_id
                   WHERE f.fa_id = {$fa_id}
                     AND f.fm_id = {$fm_id}
                     AND m.shop_id = {$shop_id} ";
    $exist = sql_fetch_pg($exist_sql);
    if (!$exist || !$exist['fa_id']) {
        alert('등록된 FAQ 항목이 없거나, 다른 가맹점의 데이터입니다.', './shop_faqlist.php?fm_id='.$fm_id);
        exit;
    }

    $sql = " UPDATE faq
             SET fa_question = '{$fa_question_pg}',
                 fa_answer   = '{$fa_answer_pg}',
                 fa_order    = {$fa_order}
             WHERE fa_id = {$fa_id}
               AND fm_id = {$fm_id} ";
    $result = sql_query_pg($sql);

    if ($result === false) {
        $error_msg = pg_last_error($g5['connect_pg']);
        alert('FAQ 항목 수정 중 오류가 발생했습니다.\\n'.$error_msg, './shop_faqform.php?w=u&fm_id='.$fm_id.'&fa_id='.$fa_id);
        exit;
    }

    // 수정 후, 사용되지 않는 S3 FAQ 이미지 정리
    // 방법 1: content 비교 방식 (기존 방식)
    if (function_exists('extract_shop_faq_s3_images') && function_exists('delete_shop_faq_s3_images')) {
        $new_q_imgs = extract_shop_faq_s3_images($fa_question);
        $new_a_imgs = extract_shop_faq_s3_images($fa_answer);
        $old_q_imgs = extract_shop_faq_s3_images($old_question);
        $old_a_imgs = extract_shop_faq_s3_images($old_answer);

        $new_all = array_unique(array_merge((array)$new_q_imgs, (array)$new_a_imgs));
        $old_all = array_unique(array_merge((array)$old_q_imgs, (array)$old_a_imgs));

        $unused = array_diff($old_all, $new_all);
        if (!empty($unused)) {
            delete_shop_faq_s3_images($unused);
        }
    }
    
    // 방법 2: S3 디렉토리 스캔 방식 (content에서 이미지를 삭제했지만 S3에는 남아있는 경우 처리)
    // 공지사항과 동일한 방식으로, content에 없는 이미지를 모두 삭제
    if (function_exists('delete_unused_shop_faq_s3_images')) {
        delete_unused_shop_faq_s3_images($shop_id, $fm_id, $fa_id, $fa_question, $fa_answer);
    }

} elseif ($w === 'd') {
    if (!$fa_id) {
        alert('잘못된 접근입니다.', './shop_faqlist.php?fm_id='.$fm_id);
        exit;
    }

    // 해당 항목이 현재 마스터/가맹점 소유인지 확인
    $exist_sql = " SELECT f.fa_id
                   FROM faq AS f
                   INNER JOIN faq_master AS m ON f.fm_id = m.fm_id
                   WHERE f.fa_id = {$fa_id}
                     AND f.fm_id = {$fm_id}
                     AND m.shop_id = {$shop_id} ";
    $exist = sql_fetch_pg($exist_sql);
    if (!$exist || !$exist['fa_id']) {
        alert('등록된 FAQ 항목이 없거나, 다른 가맹점의 데이터입니다.', './shop_faqlist.php?fm_id='.$fm_id);
        exit;
    }

    // FAQ 항목 삭제 전, 관련 S3 이미지 전체 삭제
    if (function_exists('delete_shop_faq_all_s3_images')) {
        delete_shop_faq_all_s3_images($shop_id, $fm_id, $fa_id);
    }

    // FAQ 항목 삭제
    $del_sql = " DELETE FROM faq
                 WHERE fa_id = {$fa_id}
                   AND fm_id = {$fm_id} ";
    sql_query_pg($del_sql);

    alert('FAQ 항목이 삭제되었습니다.', './shop_faqlist.php?fm_id='.$fm_id);
    exit;
}

// 등록/수정 후에는 수정 폼으로 이동
alert('FAQ 항목이 저장되었습니다.', './shop_faqform.php?w=u&fm_id='.$fm_id.'&fa_id='.$fa_id);

exit;
