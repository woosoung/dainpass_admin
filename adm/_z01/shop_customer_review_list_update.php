<?php
$sub_menu = "960400";
include_once('./_common.php');

check_demo();

// CSRF 토큰 검증
check_admin_token();

if (!isset($_POST['chk']) || !is_array($_POST['chk']) || !count($_POST['chk'])) {
    alert("하실 항목을 하나 이상 체크하세요.");
}

auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = (int)$result['shop_id'];

// 작업 타입 검증
$w = isset($_POST['w']) ? $_POST['w'] : '';
$allowed_w = array('d'); // 'd' = delete
if (!in_array($w, $allowed_w)) {
    alert("잘못된 요청입니다.");
}

// 삭제 작업은 개발자(mb_level 8 이상)만 가능
if ($w == 'd') {
    if (!isset($member['mb_level']) || $member['mb_level'] < 8) {
        alert("삭제 권한이 없습니다.");
    }
}

// 삭제할 때
if($w == 'd') {
    // review_id 배열 검증
    if (!isset($_POST['review_id']) || !is_array($_POST['review_id'])) {
        alert("잘못된 요청입니다.");
    }

    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        // 실제 번호를 넘김
        $k = (int)$_POST['chk'][$i];

        // review_id 검증 및 정수 변환
        if (!isset($_POST['review_id'][$k])) {
            continue;
        }

        $review_id = (int)$_POST['review_id'][$k];
        if ($review_id <= 0) {
            $msg .= "잘못된 리뷰 ID입니다.\\n";
            continue;
        }

        // SQL Injection 방지: 정수로 변환된 값 사용
        $review = sql_fetch_pg(" SELECT * FROM shop_review WHERE review_id = {$review_id} ");

        if (!$review['review_id']) {
            $msg .= $review_id.' : 리뷰자료가 존재하지 않습니다.\\n';
        } else {
            // 가맹점 리뷰인지 확인 (shop_id = 해당 가맹점)
            if ((int)$review['shop_id'] != $shop_id) {
                $msg .= $review_id.' : 해당 가맹점의 리뷰가 아닙니다.\\n';
                continue;
            }

            // 삭제 처리 (논리 삭제)
            $sql = " UPDATE shop_review
                     SET sr_deleted = 'Y',
                         sr_deleted_at = NOW()
                     WHERE review_id = {$review_id} AND shop_id = {$shop_id} ";
            sql_query_pg($sql,1);
        }
    }
}

if ($msg)
    alert($msg);

// qstr 생성 - 입력값 검증
$qstr = '';

// 정렬 필드 검증
if (isset($_POST['sst'])) {
    $sst = clean_xss_tags($_POST['sst']);
    $allowed_sst = array('review_id', 'sr_score', 'sr_created_at', 'sr_updated_at');
    if (in_array($sst, $allowed_sst)) {
        $qstr .= '&sst='.urlencode($sst);
    }
}

// 정렬 방향 검증
if (isset($_POST['sod'])) {
    $sod = strtolower(clean_xss_tags($_POST['sod']));
    if (in_array($sod, array('asc', 'desc'))) {
        $qstr .= '&sod='.urlencode($sod);
    }
}

// 검색 필드 검증
if (isset($_POST['sfl'])) {
    $sfl = clean_xss_tags($_POST['sfl']);
    $allowed_sfl = array('nickname', 'sr_content');
    if (in_array($sfl, $allowed_sfl)) {
        $qstr .= '&sfl='.urlencode($sfl);
    }
}

// 검색어
if (isset($_POST['stx'])) {
    $qstr .= '&stx='.urlencode(clean_xss_tags($_POST['stx']));
}

// 평점 필터 검증
if (isset($_POST['sfl2']) && $_POST['sfl2'] !== '') {
    $sfl2 = (int)$_POST['sfl2'];
    if ($sfl2 >= 1 && $sfl2 <= 5) {
        $qstr .= '&sfl2='.$sfl2;
    }
}

// 페이지 번호 검증
if (isset($_POST['page'])) {
    $page = (int)$_POST['page'];
    if ($page > 0) {
        $qstr .= '&page='.$page;
    }
}

goto_url('./shop_customer_review_list.php?'.$qstr, false);
