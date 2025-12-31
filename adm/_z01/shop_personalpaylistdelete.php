<?php
$sub_menu = "950200";
include_once('./_common.php');

check_demo();

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

@auth_check($auth[$sub_menu], 'd');

check_admin_token();

// 화이트리스트로 허용값 정의
// 개인정보보호법 준수: 닉네임만 검색 가능
$allowed_sst = array('personal_id', 'order_id', 'created_at', 'status', 'amount');
$allowed_sod = array('asc', 'desc');
$allowed_sfl = array('', 'personal_id', 'order_id', 'nickname');
$allowed_sfl2 = array('', 'CHARGE', 'PAID');

// qstr 생성 - 입력값 검증 강화
$qstr = '';
if (isset($_POST['page']) && $_POST['page']) {
    $page = (int)$_POST['page'];
    $page = ($page > 0 && $page <= 10000) ? $page : 1;
    $qstr .= '&page=' . $page;
}
if (isset($_POST['sst']) && $_POST['sst']) {
    $sst = clean_xss_tags($_POST['sst']);
    if (in_array($sst, $allowed_sst)) {
        $qstr .= '&sst=' . urlencode($sst);
    }
}
if (isset($_POST['sod']) && $_POST['sod']) {
    $sod = clean_xss_tags($_POST['sod']);
    if (in_array($sod, $allowed_sod)) {
        $qstr .= '&sod=' . urlencode($sod);
    }
}
if (isset($_POST['sfl']) && $_POST['sfl']) {
    $sfl = clean_xss_tags($_POST['sfl']);
    if (in_array($sfl, $allowed_sfl)) {
        $qstr .= '&sfl=' . urlencode($sfl);
    }
}
if (isset($_POST['stx']) && $_POST['stx']) {
    $stx = clean_xss_tags($_POST['stx']);
    $stx = substr($stx, 0, 100);
    $qstr .= '&stx=' . urlencode($stx);
}
if (isset($_POST['sfl2']) && $_POST['sfl2']) {
    $sfl2 = clean_xss_tags($_POST['sfl2']);
    if (in_array($sfl2, $allowed_sfl2)) {
        $qstr .= '&sfl2=' . urlencode($sfl2);
    }
}

$count = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;
if(!$count)
    alert('선택삭제 하실 항목을 하나이상 선택해 주세요.', './shop_personalpaylist.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));

// 배열 크기 제한 (최대 100개)
if ($count > 100) {
    alert('한 번에 최대 100개까지만 삭제할 수 있습니다.', './shop_personalpaylist.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));
}

for ($i=0; $i<$count; $i++)
{
    $personal_id = isset($_POST['chk'][$i]) ? (int)$_POST['chk'][$i] : 0;

    if ($personal_id <= 0 || $personal_id > 2147483647) {
        continue;
    }
    
    // 해당 가맹점의 개인결제인지 확인
    $check_sql = " SELECT personal_id, shop_id, status
                   FROM personal_payment
                   WHERE personal_id = " . (int)$personal_id . "
                   AND shop_id = " . (int)$shop_id . " ";
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
                           WHERE personal_id = " . (int)$personal_id . "
                           AND pay_flag = 'PERSONAL' ";
    $payment_check_row = sql_fetch_pg($payment_check_sql);
    
    if ($payment_check_row && $payment_check_row['payment_id']) {
        continue; // 결제 레코드가 있으면 삭제하지 않음
    }
    
    // 개인결제 삭제
    $delete_sql = " DELETE FROM personal_payment
                    WHERE personal_id = " . (int)$personal_id . "
                    AND shop_id = " . (int)$shop_id . " ";
    sql_query_pg($delete_sql);
}

goto_url('./shop_personalpaylist.php' . ($qstr ? '?' . ltrim($qstr, '&') : ''));

