<?php
$sub_menu = "920700";
include_once('./_common.php');

check_demo();

auth_check_menu($auth, $sub_menu, "w");

$inq_id = isset($_POST['inq_id']) ? (int)$_POST['inq_id'] : 0;
$w = isset($_POST['w']) ? $_POST['w'] : '';

if ($inq_id <= 0) {
    alert("문의번호가 없습니다.");
}

// 최초 질문 조회 및 검증
$sql = " SELECT * FROM {$g5['shop_admin_inquiry_table']} WHERE inq_id = '$inq_id' AND (inq_parent_id IS NULL OR inq_parent_id = 0) ";
$inq = sql_fetch_pg($sql);

if (!isset($inq['inq_id']) || !$inq['inq_id']) {
    alert("문의자료가 없습니다.");
}

if ($w == 'r') {
    // 답변 등록
    $inq_reply_content = isset($_POST['inq_reply_content']) ? trim($_POST['inq_reply_content']) : '';
    $inq_reply_secret_yn = isset($_POST['inq_reply_secret_yn']) ? trim($_POST['inq_reply_secret_yn']) : 'N';
    $original_secret_yn = isset($_POST['original_secret_yn']) ? trim($_POST['original_secret_yn']) : 'N';
    
    if (empty($inq_reply_content)) {
        alert("답변 내용을 입력해주세요.");
    }
    
    // 내용 처리 (XSS 방지)
    $inq_reply_content = clean_xss_tags($inq_reply_content, 1, 1);
    
    // 비밀글 처리: 댓글에 비밀글 설정을 하면 본 문의글도 비밀글로 전환
    if ($original_secret_yn == 'N' && $inq_reply_secret_yn == 'Y') {
        // 본 문의글을 비밀글로 전환
        $sql_update = " UPDATE {$g5['shop_admin_inquiry_table']} SET inq_secret_yn = 'Y', inq_updated_at = CURRENT_TIMESTAMP WHERE inq_id = '{$inq_id}' ";
        sql_query_pg($sql_update);
        $inq['inq_secret_yn'] = 'Y';
    }
    
    // IP 주소 가져오기
    $inq_ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $inq_ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    }
    
    // 관리자 ID 가져오기
    $reply_mb_id = $member['mb_id'];
    
    // 답변 등록
    $sql = " INSERT INTO {$g5['shop_admin_inquiry_table']} 
             (inq_parent_id, shop_id, shop_mb_id, inq_subject, inq_content, inq_ip, inq_secret_yn, reply_mb_id, inq_status, inq_created_at, inq_updated_at)
             VALUES 
             ('{$inq_id}', '{$inq['shop_id']}', NULL, '', '".addslashes($inq_reply_content)."', '".addslashes($inq_ip)."', '{$inq_reply_secret_yn}', '".addslashes($reply_mb_id)."', 'pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP) ";
    
    sql_query_pg($sql);
    
    alert("답변이 등록되었습니다.", "./shop_qa_form.php?inq_id={$inq_id}");
}
else if ($w == 'u') {
    // 답변 수정
    $reply_id = isset($_POST['reply_id']) ? (int)$_POST['reply_id'] : 0;
    $inq_reply_content = isset($_POST['inq_reply_content']) ? trim($_POST['inq_reply_content']) : '';
    
    if ($reply_id <= 0) {
        // AJAX 요청인 경우 JSON 응답
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(array('error' => true, 'message' => '답변번호가 없습니다.'));
            exit;
        }
        alert("답변번호가 없습니다.");
    }
    
    if (empty($inq_reply_content)) {
        // AJAX 요청인 경우 JSON 응답
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(array('error' => true, 'message' => '답변 내용을 입력해주세요.'));
            exit;
        }
        alert("답변 내용을 입력해주세요.");
    }
    
    // 답변 조회 및 검증
    $sql_reply = " SELECT * FROM {$g5['shop_admin_inquiry_table']} WHERE inq_id = '$reply_id' AND inq_parent_id = '$inq_id' AND reply_mb_id IS NOT NULL ";
    $reply = sql_fetch_pg($sql_reply);
    
    if (!isset($reply['inq_id']) || !$reply['inq_id']) {
        // AJAX 요청인 경우 JSON 응답
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(array('error' => true, 'message' => '답변자료가 없습니다.'));
            exit;
        }
        alert("답변자료가 없습니다.");
    }
    
    // 가맹점 관리자가 확인했는지 확인 (inq_status가 'ok'이면 수정 불가)
    if (!empty($reply['inq_status']) && $reply['inq_status'] == 'ok') {
        // AJAX 요청인 경우 JSON 응답
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(array('error' => true, 'message' => '가맹점 관리자가 이미 확인한 답변은 수정할 수 없습니다.'));
            exit;
        }
        alert("가맹점 관리자가 이미 확인한 답변은 수정할 수 없습니다.");
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
    $inq_reply_content = clean_xss_tags($inq_reply_content, 1, 1);
    
    // 답변 수정
    $sql = " UPDATE {$g5['shop_admin_inquiry_table']} 
             SET inq_content = '".addslashes($inq_reply_content)."', 
                 inq_updated_at = CURRENT_TIMESTAMP 
             WHERE inq_id = '$reply_id' ";
    
    sql_query_pg($sql);
    
    // AJAX 요청인 경우 JSON 응답
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(array('error' => false, 'message' => '답변이 수정되었습니다.'));
        exit;
    }
    
    alert("답변이 수정되었습니다.", "./shop_qa_form.php?inq_id={$inq_id}");
}
else if ($w == 'd') {
    // 답변 삭제
    $reply_id = isset($_POST['reply_id']) ? (int)$_POST['reply_id'] : 0;
    
    if ($reply_id <= 0) {
        // AJAX 요청인 경우 JSON 응답
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(array('error' => true, 'message' => '답변번호가 없습니다.'));
            exit;
        }
        alert("답변번호가 없습니다.");
    }
    
    // 답변 조회 및 검증
    $sql_reply = " SELECT * FROM {$g5['shop_admin_inquiry_table']} WHERE inq_id = '$reply_id' AND inq_parent_id = '$inq_id' AND reply_mb_id IS NOT NULL ";
    $reply = sql_fetch_pg($sql_reply);
    
    if (!isset($reply['inq_id']) || !$reply['inq_id']) {
        // AJAX 요청인 경우 JSON 응답
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(array('error' => true, 'message' => '답변자료가 없습니다.'));
            exit;
        }
        alert("답변자료가 없습니다.");
    }
    
    // 가맹점 관리자가 확인했는지 확인 (inq_status가 'ok'이면 삭제 불가)
    if (!empty($reply['inq_status']) && $reply['inq_status'] == 'ok') {
        // AJAX 요청인 경우 JSON 응답
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(array('error' => true, 'message' => '가맹점 관리자가 이미 확인한 답변은 삭제할 수 없습니다.'));
            exit;
        }
        alert("가맹점 관리자가 이미 확인한 답변은 삭제할 수 없습니다.");
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
    $sql = " DELETE FROM {$g5['shop_admin_inquiry_table']} WHERE inq_id = '$reply_id' ";
    sql_query_pg($sql);
    
    // AJAX 요청인 경우 JSON 응답
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(array('error' => false, 'message' => '답변이 삭제되었습니다.'));
        exit;
    }
    
    alert("답변이 삭제되었습니다.", "./shop_qa_form.php?inq_id={$inq_id}");
}

goto_url('./shop_qa_list.php', false);

