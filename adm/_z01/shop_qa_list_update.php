<?php
$sub_menu = "920700";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

auth_check($auth[$sub_menu], 'w');

// 삭제할 때
if($w == 'd') {
    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];
        $inq = sql_fetch_pg(" SELECT * FROM {$g5['shop_admin_inquiry_table']} WHERE inq_id = '".$_POST['inq_id'][$k]."' ");

        if (!$inq['inq_id']) {
            $msg .= $_POST['inq_id'][$k].' : 문의자료가 존재하지 않습니다.\\n';
        } else {
            // 최초 질문인지 확인 (inq_parent_id IS NULL 또는 0)
            if ($inq['inq_parent_id'] !== null && $inq['inq_parent_id'] != 0) {
                $msg .= $_POST['inq_id'][$k].' : 최초 질문이 아닙니다.\\n';
                continue;
            }
            
            // 해당 문의와 관련된 모든 답변도 삭제
            $sql = " DELETE FROM {$g5['shop_admin_inquiry_table']} WHERE inq_id = '{$_POST['inq_id'][$k]}' OR inq_parent_id = '{$_POST['inq_id'][$k]}' ";
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

goto_url('./shop_qa_list.php?'.$qstr, false);

