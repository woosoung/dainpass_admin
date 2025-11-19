<?php
$sub_menu = "920500";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

auth_check($auth[$sub_menu], 'w');

if($w == 'u') {
    for ($i=0; $i<count($_POST['chk']); $i++) {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];
        $cu = sql_fetch_pg(" SELECT * FROM {$g5['customers_table']} WHERE customer_id = '".$_POST['customer_id'][$k]."' ");

        if (!$cu['customer_id']) {
            $msg .= $cu['customer_id'].' : 고객자료가 존재하지 않습니다.\\n';
        } else {
            // 상태 업데이트
            $status = isset($_POST['status'][$k]) ? trim($_POST['status'][$k]) : '';
            $withdraw = isset($_POST['withdraw'][$k]) ? trim($_POST['withdraw'][$k]) : 'N';
            
            $sql = " UPDATE {$g5['customers_table']} SET
                        status = ".($status ? "'".addslashes($status)."'" : "NULL").",
                        withdraw = '".addslashes($withdraw)."',
                        updated_at = '".G5_TIME_YMDHIS."'
                    WHERE customer_id = '{$_POST['customer_id'][$k]}' ";
            sql_query_pg($sql);
        }
    }
}
// 삭제할 때
else if($w == 'd') {
    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];
        $cu = sql_fetch_pg(" SELECT * FROM {$g5['customers_table']} WHERE customer_id = '".$_POST['customer_id'][$k]."' ");

        if (!$cu['customer_id']) {
            $msg .= $cu['customer_id'].' : 고객자료가 존재하지 않습니다.\\n';
        } else {
            // 해당 customer_id 관련 모든 파일 삭제(완전히 삭제)
            delete_db_s3_file('customers', $_POST['customer_id'][$k], 'profile_img');

            if($set_conf['set_del_yn']){
                // 레코드 삭제
                $sql = " DELETE FROM {$g5['customers_table']} WHERE customer_id = '{$_POST['customer_id'][$k]}' ";
            }
            else{
                // 레코드 삭제상태로 변경
                $sql = " UPDATE {$g5['customers_table']} SET withdraw = 'Y', updated_at = '".G5_TIME_YMDHIS."' WHERE customer_id = '{$_POST['customer_id'][$k]}' ";
            }
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

goto_url('./customer_list.php?'.$qstr, false);