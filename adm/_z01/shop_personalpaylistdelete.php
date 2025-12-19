<?php
$sub_menu = "950200";
include_once('./_common.php');

check_demo();

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

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

