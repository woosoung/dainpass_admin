<?php
$sub_menu = "920600";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

if(!$is_super && !$is_manager)
    alert('관리권한이 없습니다.');

// print_r2($_POST);exit;
$msg = '';

if ($_POST['act_button'] == "선택수정") {
    for ($i=0; $i<count($_POST['chk']); $i++){
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        $sql_mb_leave_date = '';
        if($mb_leave_date[$k]){
            $mb_level[$k] = 1;
            $mb_leave_date[$k] = preg_replace('/[^0-9]/', '', $mb_leave_date[$k]); // 숫자만 추출해서 재대입
            $sql_mb_leave_date = " , mb_leave_date = '{$mb_leave_date[$k]}' ";
        }

        $mb_datetime[$k] = $mb_datetime[$k].' '.date('H:i:s');

        $sql = " UPDATE {$g5['member_table']} SET
                    mb_level = '{$mb_level[$k]}'
                    , mb_datetime = '{$mb_datetime[$k]}'
                    {$sql_mb_leave_date}
                WHERE mb_id = '{$mb_id[$k]}' ";
        sql_query($sql,1);
        
        // 메타테이블에 저장
        meta_update(array("mta_db_tbl"=>"member","mta_db_idx"=>$mb_id[$k],"mta_key"=>'mb_department',"mta_value"=>$mb_department[$k]));
        meta_update(array("mta_db_tbl"=>"member","mta_db_idx"=>$mb_id[$k],"mta_key"=>'mb_rank',"mta_value"=>$mb_rank[$k]));
        meta_update(array("mta_db_tbl"=>"member","mta_db_idx"=>$mb_id[$k],"mta_key"=>'mb_role',"mta_value"=>$mb_role[$k]));
    }
}
else if($_POST['act_button'] == "선택퇴사"){
    $leave_date = preg_replace('/[^0-9]/', '', G5_TIME_YMD); // 숫자만 추출해서 대입
    for ($i=0; $i<count($_POST['chk']); $i++){
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        $sql = " UPDATE {$g5['member_table']} SET
                    mb_level = 1,
                    mb_leave_date = '{$leave_date}'
                WHERE mb_id = '{$mb_id[$k]}' ";
        sql_query($sql,1);
    }
}

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

goto_url('./employee_list.php?'.$qstr);