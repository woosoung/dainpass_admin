<?php
$sub_menu = "960300";
include_once('./_common.php');

check_demo();

// w 파라미터 화이트리스트 검증
$allowed_w = array('d');
$w = isset($_POST['w']) ? clean_xss_tags($_POST['w']) : '';
$w = in_array($w, $allowed_w) ? $w : '';

if (!$w) {
    alert('잘못된 접근입니다.', './shop_customer_qa_list.php');
    exit;
}

if (!isset($_POST['chk']) || !is_array($_POST['chk']) || !count($_POST['chk'])) {
    alert((isset($_POST['act_button']) ? $_POST['act_button'] : '선택삭제')." 하실 항목을 하나 이상 체크하세요.", './shop_customer_qa_list.php');
    exit;
}

auth_check($auth[$sub_menu], 'w');
check_admin_token();

// 개발 권한 체크 (mb_level 8 이상만 삭제 가능)
if ($member['mb_level'] < 8) {
    alert('삭제 권한이 없습니다. 개발 관리자만 삭제할 수 있습니다.', './shop_customer_qa_list.php');
    exit;
}

// 가맹점 접근 권한 체크 (세션에서 shop_id 가져옴)
$result = check_shop_access();
$shop_id = $result['shop_id'];

// 삭제할 때
$msg = '';
if($w == 'd') {
    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        // 실제 번호를 넘김
        $k = (int)$_POST['chk'][$i];

        // qna_id 검증
        $qna_id = isset($_POST['qna_id'][$k]) ? (int)$_POST['qna_id'][$k] : 0;
        if ($qna_id <= 0 || $qna_id > 2147483647) {
            $msg .= $qna_id.' : 잘못된 문의 ID입니다.\\n';
            continue;
        }

        $qna = sql_fetch_pg(" SELECT qna_id, shop_id, qna_parent_id FROM shop_qna WHERE qna_id = {$qna_id} ");

        if (!$qna || !isset($qna['qna_id'])) {
            $msg .= $qna_id.' : 문의자료가 존재하지 않습니다.\\n';
        } else {
            // 가맹점 문의인지 확인 (shop_id = 해당 가맹점)
            if ((int)$qna['shop_id'] !== $shop_id) {
                $msg .= $qna_id.' : 해당 가맹점의 문의가 아닙니다.\\n';
                continue;
            }

            // 최초 질문인지 확인 (qna_parent_id IS NULL)
            if ($qna['qna_parent_id'] !== null && $qna['qna_parent_id'] !== '') {
                $msg .= $qna_id.' : 최초 질문이 아닙니다.\\n';
                continue;
            }

            // 해당 문의와 관련된 모든 답변도 삭제
            $sql = " DELETE FROM shop_qna WHERE qna_id = {$qna_id} OR qna_parent_id = {$qna_id} ";
            sql_query_pg($sql, 1);
        }
    }
}

if ($msg)
    alert($msg);

// 추가적인 검색조건
foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
        }
    }
}

goto_url('./shop_customer_qa_list.php?'.$qstr, false);
