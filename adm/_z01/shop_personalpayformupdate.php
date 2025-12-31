<?php
$sub_menu = "950200";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

check_admin_token();

$allowed_w = array('', 'u', 'd');
$w = isset($_REQUEST['w']) ? clean_xss_tags($_REQUEST['w']) : '';
$w = in_array($w, $allowed_w) ? $w : '';

$personal_id = isset($_REQUEST['personal_id']) ? (int)$_REQUEST['personal_id'] : 0;
$personal_id = ($personal_id > 0 && $personal_id <= 2147483647) ? $personal_id : 0;

if($w == 'd') {
    @auth_check($auth[$sub_menu], 'd');

    $sql = " SELECT personal_id, shop_id, status FROM personal_payment WHERE personal_id = " . (int)$personal_id . " AND shop_id = " . (int)$shop_id . " ";
    $row = sql_fetch_pg($sql);
    
    if(!$row || !$row['personal_id'])
        alert('삭제하시려는 자료가 존재하지 않습니다.');
    
    // 결제완료된 건은 삭제 불가
    if ($row['status'] == 'PAID') {
        alert('결제완료된 건은 삭제할 수 없습니다.');
    }
    
    // payments 테이블에 레코드가 있으면 삭제 불가
    $payment_check_sql = " SELECT payment_id FROM payments WHERE personal_id = " . (int)$personal_id . " AND pay_flag = 'PERSONAL' ";
    $payment_check_row = sql_fetch_pg($payment_check_sql);
    
    if ($payment_check_row && $payment_check_row['payment_id']) {
        alert('결제 정보가 있는 건은 삭제할 수 없습니다.');
    }

    sql_query_pg(" DELETE FROM personal_payment WHERE personal_id = " . (int)$personal_id . " AND shop_id = " . (int)$shop_id . " ");

    // qstr 생성 - 화이트리스트 검증
    // 개인정보보호법 준수: 닉네임만 검색 가능
    $allowed_sst = array('personal_id', 'order_id', 'created_at', 'status', 'amount');
    $allowed_sod = array('asc', 'desc');
    $allowed_sfl = array('', 'personal_id', 'order_id', 'nickname');
    $allowed_sfl2 = array('', 'CHARGE', 'PAID');

    $qstr = '';
    if (isset($_GET['page']) && $_GET['page']) {
        $page = (int)$_GET['page'];
        $page = ($page > 0 && $page <= 10000) ? $page : 1;
        $qstr .= '&page=' . $page;
    }
    if (isset($_GET['sst']) && $_GET['sst']) {
        $sst = clean_xss_tags($_GET['sst']);
        if (in_array($sst, $allowed_sst)) {
            $qstr .= '&sst=' . urlencode($sst);
        }
    }
    if (isset($_GET['sod']) && $_GET['sod']) {
        $sod = clean_xss_tags($_GET['sod']);
        if (in_array($sod, $allowed_sod)) {
            $qstr .= '&sod=' . urlencode($sod);
        }
    }
    if (isset($_GET['sfl']) && $_GET['sfl']) {
        $sfl = clean_xss_tags($_GET['sfl']);
        if (in_array($sfl, $allowed_sfl)) {
            $qstr .= '&sfl=' . urlencode($sfl);
        }
    }
    if (isset($_GET['stx']) && $_GET['stx']) {
        $stx = clean_xss_tags($_GET['stx']);
        $stx = substr($stx, 0, 100);
        $qstr .= '&stx=' . urlencode($stx);
    }
    if (isset($_GET['sfl2']) && $_GET['sfl2']) {
        $sfl2 = clean_xss_tags($_GET['sfl2']);
        if (in_array($sfl2, $allowed_sfl2)) {
            $qstr .= '&sfl2=' . urlencode($sfl2);
        }
    }
    
    goto_url('./shop_personalpaylist.php?'.ltrim($qstr, '&'));
} else {
    @auth_check($auth[$sub_menu], 'w');

    $order_id = isset($_POST['order_id']) ? clean_xss_tags($_POST['order_id'], 1, 1) : '';
    $order_id = substr($order_id, 0, 50); // 길이 제한

    $user_id = isset($_POST['user_id']) ? clean_xss_tags($_POST['user_id'], 1, 1) : '';
    $user_id = substr($user_id, 0, 50); // 길이 제한

    // 개인정보보호법 준수: name, phone, email은 POST로 받지 않고 user_id로부터 자동 조회
    $name = '';
    $phone = '';
    $email = '';

    $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
    $reason = substr($reason, 0, 1000); // 길이 제한

    $amount = isset($_POST['amount']) ? (int)$_POST['amount'] : 0;
    // amount 상한선 검증
    if ($amount > 100000000) { // 1억 원 제한
        alert('청구금액은 최대 1억 원까지 입력할 수 있습니다.');
    }

    $shopdetail_id = isset($_POST['shopdetail_id']) && $_POST['shopdetail_id'] !== '' ? (int)$_POST['shopdetail_id'] : null;

    // 기본 필수 입력값 검증
    if(!$user_id)
        alert('회원ID를 입력해 주십시오.');
    if(!$reason)
        alert('청구사유를 입력해 주십시오.');
    if(!$amount || $amount <= 0)
        alert('청구금액을 올바르게 입력해 주십시오.');
    // 주문번호는 신규 등록 시 DB에서 자동 생성되므로 검증하지 않음
    // 수정 모드일 때만 order_id가 필요
    if ($w == 'u' && !$order_id) {
        alert('주문번호가 필요합니다.');
    }

    // user_id로부터 customers 테이블에서 개인정보 자동 조회 (개인정보보호법 준수)
    $user_id_escaped = sql_escape_string($user_id);
    $customer_sql = " SELECT customer_id, user_id, name, phone, email
                     FROM customers
                     WHERE user_id = '{$user_id_escaped}' ";
    $customer_row = sql_fetch_pg($customer_sql);

    if (!$customer_row || !$customer_row['customer_id']) {
        alert('존재하지 않는 회원ID입니다.');
    }

    // 고객 정보 자동 설정
    $name = $customer_row['name'] ? $customer_row['name'] : '';
    $phone = $customer_row['phone'] ? $customer_row['phone'] : '';
    $email = $customer_row['email'] ? $customer_row['email'] : '';

    if (!$name) {
        alert('해당 회원의 이름 정보가 없습니다.');
    }

    // shopdetail_id가 있으면 해당 shopdetail_id가 해당 shop_id에 속하는지 확인
    if ($shopdetail_id) {
        $shopdetail_check_sql = " SELECT shopdetail_id, shop_id
                                  FROM appointment_shop_detail
                                  WHERE shopdetail_id = " . (int)$shopdetail_id . "
                                  AND shop_id = " . (int)$shop_id . " ";
        $shopdetail_check_row = sql_fetch_pg($shopdetail_check_sql);
        
        if (!$shopdetail_check_row || !$shopdetail_check_row['shopdetail_id']) {
            alert('해당 세부예약가맹점 ID가 존재하지 않거나 해당 가맹점의 것이 아닙니다.');
        }
    }

    // order_id 중복 체크 (수정 시에만)
    if ($w == 'u' && $order_id) {
        $order_id_escaped = sql_escape_string($order_id);
        $order_check_sql = " SELECT personal_id FROM personal_payment WHERE order_id = '{$order_id_escaped}' AND personal_id != " . (int)$personal_id . " ";
        $order_check_row = sql_fetch_pg($order_check_sql);
        
        if ($order_check_row && $order_check_row['personal_id']) {
            alert('이미 사용 중인 주문번호입니다.');
        }
    }

    // PostgreSQL의 경우 문자열 이스케이프 처리
    $reason_escaped = sql_escape_string($reason);
    $name_escaped = sql_escape_string($name);
    $phone_escaped = sql_escape_string($phone);
    $email_escaped = sql_escape_string($email);
    $order_id_escaped = sql_escape_string($order_id);
    $user_id_escaped = $user_id ? sql_escape_string($user_id) : 'NULL';
}

if($w == '') {
    // 신규 등록 - 주문번호는 DB에서 자동 생성 (DEFAULT 값 사용)
    // order_id는 DB의 DEFAULT 값으로 자동 생성되므로 INSERT 시 제외
    $sql = " INSERT INTO personal_payment (
                shop_id,
                shopdetail_id,
                user_id,
                name,
                reason,
                amount,
                status,
                phone,
                email,
                is_settlement_target,
                created_at,
                updated_at
            ) VALUES (
                " . (int)$shop_id . ",
                " . ($shopdetail_id ? (int)$shopdetail_id : 'NULL') . ",
                " . ($user_id ? "'{$user_id_escaped}'" : 'NULL') . ",
                '{$name_escaped}',
                '{$reason_escaped}',
                " . (int)$amount . ",
                'CHARGE',
                " . ($phone ? "'{$phone_escaped}'" : 'NULL') . ",
                " . ($email ? "'{$email_escaped}'" : 'NULL') . ",
                true,
                NOW(),
                NOW()
            ) RETURNING personal_id ";
    
    $result = sql_query_pg($sql);
    // PostgreSQL의 RETURNING 절 사용
    if ($result && is_object($result) && isset($result->result)) {
        $new_row = sql_fetch_array_pg($result->result);
        $personal_id = $new_row['personal_id'];
    } else {
        // RETURNING이 작동하지 않는 경우 최근 생성된 레코드 조회
        $new_sql = " SELECT personal_id FROM personal_payment WHERE shop_id = " . (int)$shop_id . " ORDER BY personal_id DESC LIMIT 1 ";
        $new_row = sql_fetch_pg($new_sql);
        $personal_id = $new_row['personal_id'];
    }
    
} else if($w == 'u') {
    // 수정
    $sql = " SELECT personal_id, shop_id, status FROM personal_payment WHERE personal_id = " . (int)$personal_id . " AND shop_id = " . (int)$shop_id . " ";
    $row = sql_fetch_pg($sql);
    
    if(!$row || !$row['personal_id'])
        alert('수정하시려는 자료가 존재하지 않습니다.');
    
    // 결제완료된 건은 수정 불가
    if ($row['status'] == 'PAID') {
        alert('결제완료된 건은 수정할 수 없습니다.');
    }
    
    // payments 테이블에 레코드가 있으면 수정 불가
    $payment_check_sql = " SELECT payment_id FROM payments WHERE personal_id = " . (int)$personal_id . " AND pay_flag = 'PERSONAL' ";
    $payment_check_row = sql_fetch_pg($payment_check_sql);
    
    if ($payment_check_row && $payment_check_row['payment_id']) {
        alert('결제 정보가 있는 건은 수정할 수 없습니다.');
    }

    $sql = " UPDATE personal_payment
             SET shopdetail_id = " . ($shopdetail_id ? (int)$shopdetail_id : 'NULL') . ",
                 user_id = " . ($user_id ? "'{$user_id_escaped}'" : 'NULL') . ",
                 name = '{$name_escaped}',
                 reason = '{$reason_escaped}',
                 amount = " . (int)$amount . ",
                 phone = " . ($phone ? "'{$phone_escaped}'" : 'NULL') . ",
                 email = " . ($email ? "'{$email_escaped}'" : 'NULL') . ",
                 updated_at = NOW()";
    
    // order_id는 수정 시에만 업데이트 (변경된 경우에만)
    if ($order_id) {
        $sql .= ", order_id = '{$order_id_escaped}'";
    }
    
    $sql .= " WHERE personal_id = " . (int)$personal_id . "
             AND shop_id = " . (int)$shop_id . " ";
    sql_query_pg($sql);
}

// qstr 생성 - 화이트리스트 검증
// 개인정보보호법 준수: 닉네임만 검색 가능
$allowed_sst = array('personal_id', 'order_id', 'created_at', 'status', 'amount');
$allowed_sod = array('asc', 'desc');
$allowed_sfl = array('', 'personal_id', 'order_id', 'nickname');
$allowed_sfl2 = array('', 'CHARGE', 'PAID');

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

goto_url('./shop_personalpayform.php?w=u&amp;personal_id='.$personal_id.'&amp;'.ltrim($qstr, '&'));

