<?php
$sub_menu = "960300";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

auth_check($auth[$sub_menu], 'w');

// 가맹점측 관리자 접근 권한 체크
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
        
        if (!empty($mb_1_value) && $mb_1_value !== '0') {
            $shop_id_check = (int)$mb_1_value;
            $shop_sql = " SELECT shop_id, status 
                         FROM {$g5['shop_table']} 
                         WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);
            
            if ($shop_row && $shop_row['shop_id']) {
                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
            }
        }
    }
}

if (!$has_access) {
    alert("접속할 수 없는 페이지 입니다.");
}

// POST로 받은 shop_id와 세션의 shop_id 일치 확인
$post_shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : 0;
if ($post_shop_id !== $shop_id) {
    alert("잘못된 접근입니다.");
}

// 삭제할 때
if($w == 'd') {
    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];
        $qna = sql_fetch_pg(" SELECT * FROM shop_qna WHERE qna_id = '".$_POST['qna_id'][$k]."' ");

        if (!$qna['qna_id']) {
            $msg .= $_POST['qna_id'][$k].' : 문의자료가 존재하지 않습니다.\\n';
        } else {
            // 가맹점 문의인지 확인 (shop_id = 해당 가맹점)
            if ($qna['shop_id'] != $shop_id) {
                $msg .= $_POST['qna_id'][$k].' : 해당 가맹점의 문의가 아닙니다.\\n';
                continue;
            }
            
            // 최초 질문인지 확인 (qna_parent_id IS NULL)
            if ($qna['qna_parent_id'] !== null) {
                $msg .= $_POST['qna_id'][$k].' : 최초 질문이 아닙니다.\\n';
                continue;
            }
            
            // 해당 문의와 관련된 모든 답변도 삭제
            $sql = " DELETE FROM shop_qna WHERE qna_id = '{$_POST['qna_id'][$k]}' OR qna_parent_id = '{$_POST['qna_id'][$k]}' ";
            sql_query_pg($sql,1);
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
