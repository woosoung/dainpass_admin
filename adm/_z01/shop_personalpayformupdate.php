<?php
$sub_menu = "950200";
include_once('./_common.php');

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

check_admin_token();

$w = isset($_REQUEST['w']) ? clean_xss_tags($_REQUEST['w']) : '';
$personal_id = isset($_REQUEST['personal_id']) ? (int)$_REQUEST['personal_id'] : 0;
$post_shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : 0;

// shop_id 검증
if ($post_shop_id != $shop_id) {
    alert('잘못된 접근입니다.');
}

if($w == 'd') {
    @auth_check($auth[$sub_menu], 'd');

    $sql = " SELECT personal_id, shop_id, status FROM personal_payment WHERE personal_id = {$personal_id} AND shop_id = {$shop_id} ";
    $row = sql_fetch_pg($sql);
    
    if(!$row || !$row['personal_id'])
        alert('삭제하시려는 자료가 존재하지 않습니다.');
    
    // 결제완료된 건은 삭제 불가
    if ($row['status'] == 'PAID') {
        alert('결제완료된 건은 삭제할 수 없습니다.');
    }
    
    // payments 테이블에 레코드가 있으면 삭제 불가
    $payment_check_sql = " SELECT payment_id FROM payments WHERE personal_id = {$personal_id} AND pay_flag = 'PERSONAL' ";
    $payment_check_row = sql_fetch_pg($payment_check_sql);
    
    if ($payment_check_row && $payment_check_row['payment_id']) {
        alert('결제 정보가 있는 건은 삭제할 수 없습니다.');
    }

    sql_query_pg(" DELETE FROM personal_payment WHERE personal_id = {$personal_id} AND shop_id = {$shop_id} ");

    $qstr = '';
    if (isset($_GET['sst'])) $qstr .= '&sst='.clean_xss_tags($_GET['sst']);
    if (isset($_GET['sod'])) $qstr .= '&sod='.clean_xss_tags($_GET['sod']);
    if (isset($_GET['sfl'])) $qstr .= '&sfl='.clean_xss_tags($_GET['sfl']);
    if (isset($_GET['stx'])) $qstr .= '&stx='.urlencode($_GET['stx']);
    if (isset($_GET['sfl2'])) $qstr .= '&sfl2='.clean_xss_tags($_GET['sfl2']);
    if (isset($_GET['page'])) $qstr .= '&page='.(int)$_GET['page'];
    
    goto_url('./shop_personalpaylist.php?'.ltrim($qstr, '&'));
} else {
    @auth_check($auth[$sub_menu], 'w');

    $order_id = isset($_POST['order_id']) ? clean_xss_tags($_POST['order_id'], 1, 1) : '';
    $user_id = isset($_POST['user_id']) ? clean_xss_tags($_POST['user_id'], 1, 1) : '';
    $name = isset($_POST['name']) ? strip_tags(clean_xss_attributes($_POST['name'])) : '';
    $phone = isset($_POST['phone']) ? clean_xss_tags($_POST['phone'], 1, 1) : '';
    $email = isset($_POST['email']) ? clean_xss_tags($_POST['email'], 1, 1) : '';
    $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
    $amount = isset($_POST['amount']) ? (int)$_POST['amount'] : 0;
    $shopdetail_id = isset($_POST['shopdetail_id']) && $_POST['shopdetail_id'] !== '' ? (int)$_POST['shopdetail_id'] : null;

    if(!$name)
        alert('이름을 입력해 주십시오.');
    if(!$reason)
        alert('청구사유를 입력해 주십시오.');
    if(!$amount || $amount <= 0)
        alert('청구금액을 올바르게 입력해 주십시오.');
    // 주문번호는 신규 등록 시 DB에서 자동 생성되므로 검증하지 않음
    // 수정 모드일 때만 order_id가 필요
    if ($w == 'u' && !$order_id) {
        alert('주문번호가 필요합니다.');
    }

    // user_id가 있으면 customers 테이블에서 정보 가져오기
    if ($user_id) {
        $customer_sql = " SELECT customer_id, user_id, name, phone, email 
                         FROM customers 
                         WHERE user_id = '{$user_id}' ";
        $customer_row = sql_fetch_pg($customer_sql);
        
        if ($customer_row && $customer_row['customer_id']) {
            // 고객 정보가 있으면 자동으로 채우기
            if (!$name && $customer_row['name']) {
                $name = $customer_row['name'];
            }
            if (!$phone && $customer_row['phone']) {
                $phone = $customer_row['phone'];
            }
            if (!$email && $customer_row['email']) {
                $email = $customer_row['email'];
            }
        }
    }

    // shopdetail_id가 있으면 해당 shopdetail_id가 해당 shop_id에 속하는지 확인
    if ($shopdetail_id) {
        $shopdetail_check_sql = " SELECT shopdetail_id, shop_id 
                                  FROM appointment_shop_detail 
                                  WHERE shopdetail_id = {$shopdetail_id} 
                                  AND shop_id = {$shop_id} ";
        $shopdetail_check_row = sql_fetch_pg($shopdetail_check_sql);
        
        if (!$shopdetail_check_row || !$shopdetail_check_row['shopdetail_id']) {
            alert('해당 세부예약가맹점 ID가 존재하지 않거나 해당 가맹점의 것이 아닙니다.');
        }
    }

    // order_id 중복 체크 (수정 시에만)
    if ($w == 'u' && $order_id) {
        $order_check_sql = " SELECT personal_id FROM personal_payment WHERE order_id = '{$order_id}' AND personal_id != {$personal_id} ";
        $order_check_row = sql_fetch_pg($order_check_sql);
        
        if ($order_check_row && $order_check_row['personal_id']) {
            alert('이미 사용 중인 주문번호입니다.');
        }
    }

    // PostgreSQL의 경우 문자열 이스케이프 처리
    $reason_escaped = pg_escape_string($reason);
    $name_escaped = pg_escape_string($name);
    $phone_escaped = pg_escape_string($phone);
    $email_escaped = pg_escape_string($email);
    $order_id_escaped = pg_escape_string($order_id);
    $user_id_escaped = $user_id ? pg_escape_string($user_id) : 'NULL';
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
                {$shop_id},
                " . ($shopdetail_id ? $shopdetail_id : 'NULL') . ",
                " . ($user_id ? "'{$user_id_escaped}'" : 'NULL') . ",
                '{$name_escaped}',
                '{$reason_escaped}',
                {$amount},
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
        $new_sql = " SELECT personal_id FROM personal_payment WHERE shop_id = {$shop_id} ORDER BY personal_id DESC LIMIT 1 ";
        $new_row = sql_fetch_pg($new_sql);
        $personal_id = $new_row['personal_id'];
    }
    
} else if($w == 'u') {
    // 수정
    $sql = " SELECT personal_id, shop_id, status FROM personal_payment WHERE personal_id = {$personal_id} AND shop_id = {$shop_id} ";
    $row = sql_fetch_pg($sql);
    
    if(!$row || !$row['personal_id'])
        alert('수정하시려는 자료가 존재하지 않습니다.');
    
    // 결제완료된 건은 수정 불가
    if ($row['status'] == 'PAID') {
        alert('결제완료된 건은 수정할 수 없습니다.');
    }
    
    // payments 테이블에 레코드가 있으면 수정 불가
    $payment_check_sql = " SELECT payment_id FROM payments WHERE personal_id = {$personal_id} AND pay_flag = 'PERSONAL' ";
    $payment_check_row = sql_fetch_pg($payment_check_sql);
    
    if ($payment_check_row && $payment_check_row['payment_id']) {
        alert('결제 정보가 있는 건은 수정할 수 없습니다.');
    }

    $sql = " UPDATE personal_payment 
             SET shopdetail_id = " . ($shopdetail_id ? $shopdetail_id : 'NULL') . ",
                 user_id = " . ($user_id ? "'{$user_id_escaped}'" : 'NULL') . ",
                 name = '{$name_escaped}',
                 reason = '{$reason_escaped}',
                 amount = {$amount},
                 phone = " . ($phone ? "'{$phone_escaped}'" : 'NULL') . ",
                 email = " . ($email ? "'{$email_escaped}'" : 'NULL') . ",
                 updated_at = NOW()";
    
    // order_id는 수정 시에만 업데이트 (변경된 경우에만)
    if ($order_id) {
        $sql .= ", order_id = '{$order_id_escaped}'";
    }
    
    $sql .= " WHERE personal_id = {$personal_id} 
             AND shop_id = {$shop_id} ";
    sql_query_pg($sql);
}

$qstr = '';
if (isset($_POST['sst'])) $qstr .= '&sst='.clean_xss_tags($_POST['sst']);
if (isset($_POST['sod'])) $qstr .= '&sod='.clean_xss_tags($_POST['sod']);
if (isset($_POST['sfl'])) $qstr .= '&sfl='.clean_xss_tags($_POST['sfl']);
if (isset($_POST['stx'])) $qstr .= '&stx='.urlencode($_POST['stx']);
if (isset($_POST['sfl2'])) $qstr .= '&sfl2='.clean_xss_tags($_POST['sfl2']);
if (isset($_POST['page'])) $qstr .= '&page='.(int)$_POST['page'];

goto_url('./shop_personalpayform.php?w=u&amp;personal_id='.$personal_id.'&amp;'.ltrim($qstr, '&'));

