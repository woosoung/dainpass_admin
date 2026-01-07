<?php
$sub_menu = "960300";
include_once('./_common.php');

check_demo();

// w 파라미터 화이트리스트 검증
$allowed_w = array('r', 'u', 'd');
$w = isset($_POST['w']) ? clean_xss_tags($_POST['w']) : '';
$w = in_array($w, $allowed_w) ? $w : '';

if (!$w) {
    alert('잘못된 접근입니다.', './shop_customer_qa_list.php');
    exit;
}

// 가맹점 접근 권한 체크 (세션에서 shop_id 가져옴)
$result = check_shop_access();
$shop_id = $result['shop_id'];

auth_check_menu($auth, $sub_menu, "w");
check_admin_token();

// qna_id 검증
$qna_id = isset($_POST['qna_id']) ? (int)$_POST['qna_id'] : 0;
if ($qna_id <= 0 || $qna_id > 2147483647) {
    alert("문의번호가 없습니다.", './shop_customer_qa_list.php');
    exit;
}

// 최초 질문 조회 및 검증 (해당 가맹점의 문의만)
$sql = " SELECT * FROM shop_qna WHERE qna_id = {$qna_id} AND shop_id = {$shop_id} AND qna_parent_id IS NULL ";
$qna = sql_fetch_pg($sql);

if (!isset($qna['qna_id']) || !$qna['qna_id']) {
    alert("문의자료가 없거나 해당 가맹점의 문의가 아닙니다.", './shop_customer_qa_list.php');
    exit;
}

if ($w == 'r') {
    // 답변 등록
    $qna_reply_content = isset($_POST['qna_reply_content']) ? trim($_POST['qna_reply_content']) : '';

    // 비밀글 화이트리스트 검증
    $allowed_secret_yn = array('Y', 'N');
    $qna_reply_secret_yn = isset($_POST['qna_reply_secret_yn']) ? trim(clean_xss_tags($_POST['qna_reply_secret_yn'])) : 'N';
    $qna_reply_secret_yn = in_array($qna_reply_secret_yn, $allowed_secret_yn) ? $qna_reply_secret_yn : 'N';

    $original_secret_yn = isset($_POST['original_secret_yn']) ? trim(clean_xss_tags($_POST['original_secret_yn'])) : 'N';
    $original_secret_yn = in_array($original_secret_yn, $allowed_secret_yn) ? $original_secret_yn : 'N';

    if (empty($qna_reply_content)) {
        alert("답변 내용을 입력해주세요.", './shop_customer_qa_form.php?qna_id='.$qna_id);
        exit;
    }

    // 답변 내용 길이 제한 (10MB)
    if (strlen($qna_reply_content) > 10485760) {
        alert('답변 내용이 너무 깁니다. (최대 10MB)', './shop_customer_qa_form.php?qna_id='.$qna_id);
        exit;
    }

    // 내용 처리 (XSS 방지)
    $qna_reply_content = clean_xss_tags($qna_reply_content, 1, 1);

    // 비밀글 처리: 댓글에 비밀글 설정을 하면 본 문의글도 비밀글로 전환
    if ($original_secret_yn == 'N' && $qna_reply_secret_yn == 'Y') {
        // 본 문의글을 비밀글로 전환
        $sql_update = " UPDATE shop_qna SET qna_secret_yn = 'Y', qna_updated_at = NOW() WHERE qna_id = {$qna_id} ";
        sql_query_pg($sql_update);
        $qna['qna_secret_yn'] = 'Y';
    }

    // IP 주소 가져오기
    $qna_ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $qna_ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    }

    // 관리자 ID 가져오기
    $reply_mb_id = $member['mb_id'];

    // PostgreSQL 이스케이프
    $qna_reply_content_pg = pg_escape_string($g5['connect_pg'], $qna_reply_content);
    $qna_ip_pg = pg_escape_string($g5['connect_pg'], $qna_ip);
    $reply_mb_id_pg = pg_escape_string($g5['connect_pg'], $reply_mb_id);

    // 답변 등록 (shop_id 자동 세팅)
    $sql = " INSERT INTO shop_qna
             (qna_parent_id, shop_id, customer_id, qna_subject, qna_content, qna_ip, qna_secret_yn, reply_mb_id, qna_status, qna_created_at, qna_updated_at)
             VALUES
             ({$qna_id}, {$shop_id}, NULL, '', '{$qna_reply_content_pg}', '{$qna_ip_pg}', '{$qna_reply_secret_yn}', '{$reply_mb_id_pg}', 'pending', NOW(), NOW()) ";

    sql_query_pg($sql);

    alert("답변이 등록되었습니다.", "./shop_customer_qa_form.php?qna_id={$qna_id}");
    exit;
}
else if ($w == 'u') {
    // 답변 수정
    $reply_id = isset($_POST['reply_id']) ? (int)$_POST['reply_id'] : 0;
    $qna_reply_content = isset($_POST['qna_reply_content']) ? trim($_POST['qna_reply_content']) : '';

    // AJAX 요청 확인
    $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

    if ($reply_id <= 0 || $reply_id > 2147483647) {
        if ($is_ajax) {
            echo json_encode(array('error' => true, 'message' => '답변번호가 없습니다.'));
            exit;
        }
        alert("답변번호가 없습니다.", './shop_customer_qa_form.php?qna_id='.$qna_id);
        exit;
    }

    if (empty($qna_reply_content)) {
        if ($is_ajax) {
            echo json_encode(array('error' => true, 'message' => '답변 내용을 입력해주세요.'));
            exit;
        }
        alert("답변 내용을 입력해주세요.", './shop_customer_qa_form.php?qna_id='.$qna_id);
        exit;
    }

    // 답변 내용 길이 제한 (10MB)
    if (strlen($qna_reply_content) > 10485760) {
        if ($is_ajax) {
            echo json_encode(array('error' => true, 'message' => '답변 내용이 너무 깁니다. (최대 10MB)'));
            exit;
        }
        alert('답변 내용이 너무 깁니다. (최대 10MB)', './shop_customer_qa_form.php?qna_id='.$qna_id);
        exit;
    }

    // 답변 조회 및 검증 (해당 가맹점의 답변만)
    $sql_reply = " SELECT * FROM shop_qna WHERE qna_id = {$reply_id} AND qna_parent_id = {$qna_id} AND shop_id = {$shop_id} AND reply_mb_id IS NOT NULL ";
    $reply = sql_fetch_pg($sql_reply);
    
    if (!isset($reply['qna_id']) || !$reply['qna_id']) {
        // AJAX 요청인 경우 JSON 응답
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(array('error' => true, 'message' => '답변자료가 없습니다.'));
            exit;
        }
        alert("답변자료가 없습니다.");
    }
    
    // 고객이 확인했는지 확인 (qna_status가 'ok'이면 수정 불가)
    if (!empty($reply['qna_status']) && $reply['qna_status'] == 'ok') {
        // AJAX 요청인 경우 JSON 응답
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(array('error' => true, 'message' => '고객이 이미 확인한 답변은 수정할 수 없습니다.'));
            exit;
        }
        alert("고객이 이미 확인한 답변은 수정할 수 없습니다.");
    }
    
    // 작성자 확인 (본인이 작성한 답변만 수정 가능)
    if ($reply['reply_mb_id'] != $member['mb_id']) {
        // AJAX 요청인 경우 JSON 응답
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(array('error' => true, 'message' => '본인이 작성한 답변만 수정할 수 있습니다.'));
            exit;
        }
        alert("본인이 작성한 답변만 수정할 수 있습니다.");
    }
    
    // 내용 처리 (XSS 방지)
    $qna_reply_content = clean_xss_tags($qna_reply_content, 1, 1);

    // PostgreSQL 이스케이프
    $qna_reply_content_pg = pg_escape_string($g5['connect_pg'], $qna_reply_content);

    // 답변 수정
    $sql = " UPDATE shop_qna
             SET qna_content = '{$qna_reply_content_pg}',
                 qna_updated_at = NOW()
             WHERE qna_id = {$reply_id} ";

    sql_query_pg($sql);
    
    // AJAX 요청인 경우 JSON 응답
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(array('error' => false, 'message' => '답변이 수정되었습니다.'));
        exit;
    }
    
    alert("답변이 수정되었습니다.", "./shop_customer_qa_form.php?qna_id={$qna_id}");
}
else if ($w == 'd') {
    // 답변 삭제
    $reply_id = isset($_POST['reply_id']) ? (int)$_POST['reply_id'] : 0;

    // AJAX 요청 확인
    $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

    if ($reply_id <= 0 || $reply_id > 2147483647) {
        if ($is_ajax) {
            echo json_encode(array('error' => true, 'message' => '답변번호가 없습니다.'));
            exit;
        }
        alert("답변번호가 없습니다.", './shop_customer_qa_form.php?qna_id='.$qna_id);
        exit;
    }

    // 답변 조회 및 검증 (해당 가맹점의 답변만)
    $sql_reply = " SELECT * FROM shop_qna WHERE qna_id = {$reply_id} AND qna_parent_id = {$qna_id} AND shop_id = {$shop_id} AND reply_mb_id IS NOT NULL ";
    $reply = sql_fetch_pg($sql_reply);
    
    if (!isset($reply['qna_id']) || !$reply['qna_id']) {
        // AJAX 요청인 경우 JSON 응답
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(array('error' => true, 'message' => '답변자료가 없습니다.'));
            exit;
        }
        alert("답변자료가 없습니다.");
    }
    
    // 고객이 확인했는지 확인 (qna_status가 'ok'이면 삭제 불가)
    if (!empty($reply['qna_status']) && $reply['qna_status'] == 'ok') {
        // AJAX 요청인 경우 JSON 응답
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(array('error' => true, 'message' => '고객이 이미 확인한 답변은 삭제할 수 없습니다.'));
            exit;
        }
        alert("고객이 이미 확인한 답변은 삭제할 수 없습니다.");
    }
    
    // 작성자 확인 (본인이 작성한 답변만 삭제 가능)
    if ($reply['reply_mb_id'] != $member['mb_id']) {
        // AJAX 요청인 경우 JSON 응답
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(array('error' => true, 'message' => '본인이 작성한 답변만 삭제할 수 있습니다.'));
            exit;
        }
        alert("본인이 작성한 답변만 삭제할 수 있습니다.");
    }
    
    // 답변 삭제
    $sql = " DELETE FROM shop_qna WHERE qna_id = {$reply_id} ";
    sql_query_pg($sql);
    
    // AJAX 요청인 경우 JSON 응답
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(array('error' => false, 'message' => '답변이 삭제되었습니다.'));
        exit;
    }
    
    alert("답변이 삭제되었습니다.", "./shop_customer_qa_form.php?qna_id={$qna_id}");
}

goto_url('./shop_customer_qa_list.php', false);
