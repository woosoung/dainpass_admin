<?php
$sub_menu = "950200";
include_once('./_common.php');

check_demo();

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
        
        if ($mb_1_value !== '0' && !empty($mb_1_value)) {
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
    alert('접속할 수 없는 페이지 입니다.');
}

@auth_check($auth[$sub_menu], 'd');

check_admin_token();

$count = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;
if(!$count)
    alert('선택삭제 하실 항목을 하나이상 선택해 주세요.');

for ($i=0; $i<$count; $i++)
{
    $personal_id = isset($_POST['chk'][$i]) ? (int)$_POST['chk'][$i] : 0;
    
    if (!$personal_id) {
        continue;
    }
    
    // 해당 가맹점의 개인결제인지 확인
    $check_sql = " SELECT personal_id, shop_id, status 
                   FROM personal_payment 
                   WHERE personal_id = {$personal_id} 
                   AND shop_id = {$shop_id} ";
    $check_row = sql_fetch_pg($check_sql);
    
    if (!$check_row || !$check_row['personal_id']) {
        continue; // 해당 가맹점의 개인결제가 아니면 스킵
    }
    
    // 결제완료된 건은 삭제 불가
    if ($check_row['status'] == 'PAID') {
        continue; // 결제완료된 건은 삭제하지 않음
    }
    
    // payments 테이블에 레코드가 있으면 삭제하지 않음
    $payment_check_sql = " SELECT payment_id 
                           FROM payments 
                           WHERE personal_id = {$personal_id} 
                           AND pay_flag = 'PERSONAL' ";
    $payment_check_row = sql_fetch_pg($payment_check_sql);
    
    if ($payment_check_row && $payment_check_row['payment_id']) {
        continue; // 결제 레코드가 있으면 삭제하지 않음
    }
    
    // 개인결제 삭제
    $delete_sql = " DELETE FROM personal_payment 
                    WHERE personal_id = {$personal_id} 
                    AND shop_id = {$shop_id} ";
    sql_query_pg($delete_sql);
}

goto_url('./shop_personalpaylist.php');

