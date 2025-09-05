<?php
$sub_menu = "920200";
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
		$com = sql_fetch_pg(" SELECT * FROM {$g5['shop_table']} WHERE shop_id = '".$_POST['shop_id'][$k]."' ");
		// $mb = get_member($com['mb_id']);

        
        $sql = " UPDATE {$g5['shop_table']} SET
                    status = '{$_POST['status'][$k]}'
                WHERE shop_id = '{$_POST['shop_id'][$k]}' ";
        sql_query_pg($sql);
    }
}
// 삭제할 때
else if($w == 'd') {
    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];
		$com = sql_fetch(" SELECT * FROM {$g5['shop_table']} WHERE shop_id = '".$_POST['shop_id'][$k]."' ");

        if (!$com['shop_id']) {
            $msg .= $com['shop_id'].' : 업체자료가 존재하지 않습니다.\\n';
        } else {
            // 해당 com_idx관련 모든 파일 삭제(완전히 삭제)
            delete_db_s3_file('shop', $_POST['shop_id'][$k],'shop');

            if($set_conf['set_del_yn']){
                // 레코드 삭제
                $sql = " DELETE FROM {$g5['shop_table']} WHERE shop_id = '{$_POST['shop_id'][$k]}' ";
                // company_member 삭제
                // $sql2 = " DELETE FROM {$g5['company_member_table']} WHERE cmm_com_idx = '{$_POST['com_idx'][$k]}' ";
            }
            else{
                // 레코드 삭제상태로 변경
                $sql = " UPDATE {$g5['shop_table']} SET status = 'trash' WHERE shop_id = '{$_POST['shop_id'][$k]}' ";
                // company_member 삭제상태로 변경
                // $sql2 = " UPDATE {$g5['company_member_table']} SET cmm_status = 'trash' WHERE cmm_com_idx = '{$_POST['com_idx'][$k]}' ";
            }
			sql_query_pg($sql,1);
            // sql_query($sql2,1);
        }
    }
}

if ($msg)
    alert($msg);
    //echo '<script> alert("'.$msg.'"); </script>';

    
// 추가적인 검색조건
foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
    //    print_r3($key.'='.$value);
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
//                print_r3($key.$k2.'='.$v2);
                $qstr .= '&'.$key.'[]='.$v2;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
        }
    }
}

goto_url('./company_list.php?'.$qstr, false);