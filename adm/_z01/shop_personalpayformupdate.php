<?php
// PHP 오류 표시 (디버깅용 - 운영 환경에서는 제거)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$sub_menu = "950200";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

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
    $row_result = sql_query_pg($sql);
    if ($row_result && is_object($row_result) && isset($row_result->result)) {
        $row = sql_fetch_array_pg($row_result->result);
    } else {
        $row = false;
    }
    
    if(!$row || !$row['personal_id'])
        alert('삭제하시려는 자료가 존재하지 않습니다.');
    
    // 결제완료된 건은 삭제 불가
    if ($row['status'] == 'PAID') {
        alert('결제완료된 건은 삭제할 수 없습니다.');
    }
    
    // payments 테이블에 레코드가 있으면 삭제 불가 (1:0 또는 1:1 관계이므로 단일 레코드만 조회)
    $payment_check_sql = " SELECT payment_id FROM payments WHERE personal_id = {$personal_id} AND pay_flag = 'PERSONAL' LIMIT 1 ";
    $payment_check_result = sql_query_pg($payment_check_sql);
    if ($payment_check_result && is_object($payment_check_result) && isset($payment_check_result->result)) {
        $payment_check_row = sql_fetch_array_pg($payment_check_result->result);
    } else {
        $payment_check_row = false;
    }
    
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
    $status = isset($_POST['status']) ? clean_xss_tags($_POST['status'], 1, 1) : 'CHARGE';
    $shopdetail_id = isset($_POST['shopdetail_id']) && $_POST['shopdetail_id'] !== '' ? (int)$_POST['shopdetail_id'] : null;

    if(!$name)
        alert('이름을 입력해 주십시오.');
    if(!$reason)
        alert('청구사유를 입력해 주십시오.');
    if(!$amount || $amount <= 0)
        alert('청구금액을 올바르게 입력해 주십시오.');
    
    // 상태 검증
    if (!in_array($status, array('CHARGE', 'PAID'))) {
        alert('올바른 상태 값을 선택해 주십시오.');
    }
    
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
        $shopdetail_check_result = sql_query_pg($shopdetail_check_sql);
        if ($shopdetail_check_result && is_object($shopdetail_check_result) && isset($shopdetail_check_result->result)) {
            $shopdetail_check_row = sql_fetch_array_pg($shopdetail_check_result->result);
        } else {
            $shopdetail_check_row = false;
        }
        
        if (!$shopdetail_check_row || !$shopdetail_check_row['shopdetail_id']) {
            alert('해당 세부예약가맹점 ID가 존재하지 않거나 해당 가맹점의 것이 아닙니다.');
        }
    }

    // order_id 중복 체크 (수정 시에만)
    if ($w == 'u' && $order_id) {
        $order_check_sql = " SELECT personal_id FROM personal_payment WHERE order_id = '{$order_id}' AND personal_id != {$personal_id} ";
        $order_check_result = sql_query_pg($order_check_sql);
        if ($order_check_result && is_object($order_check_result) && isset($order_check_result->result)) {
            $order_check_row = sql_fetch_array_pg($order_check_result->result);
        } else {
            $order_check_row = false;
        }
        
        if ($order_check_row && $order_check_row['personal_id']) {
            alert('이미 사용 중인 주문번호입니다.');
        }
    }

    // PostgreSQL의 경우 문자열 이스케이프 처리
    $pg = isset($g5['connect_pg']) && (is_object($g5['connect_pg']) || is_resource($g5['connect_pg'])) ? $g5['connect_pg'] : null;
    $esc = function($v) use ($pg) {
        $s = (string)$v;
        return $pg ? pg_escape_string($pg, $s) : $s;
    };
    
    $reason_escaped = $esc($reason);
    $name_escaped = $esc($name);
    $phone_escaped = $phone ? $esc($phone) : 'NULL';
    $email_escaped = $email ? $esc($email) : 'NULL';
    $order_id_escaped = $order_id ? $esc($order_id) : '';
    $user_id_escaped = $user_id ? $esc($user_id) : 'NULL';
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
                '{$status}',
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
        $new_result = sql_query_pg($new_sql);
        if ($new_result && is_object($new_result) && isset($new_result->result)) {
            $new_row = sql_fetch_array_pg($new_result->result);
            $personal_id = $new_row['personal_id'];
        } else {
            alert('개인결제 정보를 등록하는 중 오류가 발생했습니다.');
        }
    }
    
    // 신규 등록 시 상태가 'PAID'이면 payments 테이블에 레코드 생성
    // order_id는 DB에서 자동 생성되므로 다시 조회
    if ($status == 'PAID') {
        $order_sql = " SELECT order_id FROM personal_payment WHERE personal_id = {$personal_id} ";
        $order_result = sql_query_pg($order_sql);
        if ($order_result && is_object($order_result) && isset($order_result->result)) {
            $order_row = sql_fetch_array_pg($order_result->result);
            $order_id_for_payment = ($order_row && $order_row['order_id']) ? $esc($order_row['order_id']) : '';
        } else {
            $order_id_for_payment = '';
        }
        
        // payment_key 생성 (NOT NULL 제약조건)
        $payment_key = 'PERSONAL_PAYMENT_'.time().'_'.$personal_id;
        // transaction_key 생성 (NOT NULL 제약조건)
        $transaction_key = 'PERSONAL_PAYMENT_'.time().'_'.$personal_id;
        
        $payment_insert_sql = " INSERT INTO payments (
                                    pay_flag,
                                    personal_id,
                                    order_id,
                                    amount,
                                    status,
                                    payment_method,
                                    payment_key,
                                    response,
                                    transaction_key,
                                    paid_at,
                                    updated_at
                                ) VALUES (
                                    'PERSONAL',
                                    {$personal_id},
                                    '{$order_id_for_payment}',
                                    {$amount},
                                    'DONE',
                                    'MANUAL',
                                    '{$payment_key}',
                                    '{}'::jsonb,
                                    '{$transaction_key}',
                                    NOW(),
                                    NOW()
                                ) ";
        $payment_result = sql_query_pg($payment_insert_sql, false);
        
        // INSERT 실패 시 오류 확인
        if ($payment_result === false || $payment_result === null) {
            $pg_error = pg_last_error($g5['connect_pg']);
            if ($pg_error) {
                alert('payments 테이블에 데이터를 저장하는 중 오류가 발생했습니다: ' . $pg_error);
            } else {
                alert('payments 테이블에 데이터를 저장하는 중 오류가 발생했습니다.');
            }
        }
    }
    
} else if($w == 'u') {
    // 수정
    $sql = " SELECT personal_id, shop_id, status FROM personal_payment WHERE personal_id = {$personal_id} AND shop_id = {$shop_id} ";
    $row_result = sql_query_pg($sql);
    if ($row_result && is_object($row_result) && isset($row_result->result)) {
        $row = sql_fetch_array_pg($row_result->result);
    } else {
        $row = false;
    }
    
    if(!$row || !$row['personal_id'])
        alert('수정하시려는 자료가 존재하지 않습니다.');
    
    // 결제완료된 건은 수정 불가
    if ($row['status'] == 'PAID') {
        alert('결제완료된 건은 수정할 수 없습니다.');
    }
    
    // payments 테이블에 레코드가 있는지 확인 (1:0 또는 1:1 관계이므로 단일 레코드만 조회)
    $payment_check_sql = " SELECT payment_id, paid_at FROM payments WHERE personal_id = {$personal_id} AND pay_flag = 'PERSONAL' LIMIT 1 ";
    $payment_check_result = sql_query_pg($payment_check_sql);
    if ($payment_check_result && is_object($payment_check_result) && isset($payment_check_result->result)) {
        $payment_check_row = sql_fetch_array_pg($payment_check_result->result);
    } else {
        $payment_check_row = false;
    }

    $sql = " UPDATE personal_payment 
             SET shopdetail_id = " . ($shopdetail_id ? $shopdetail_id : 'NULL') . ",
                 user_id = " . ($user_id ? "'{$user_id_escaped}'" : 'NULL') . ",
                 name = '{$name_escaped}',
                 reason = '{$reason_escaped}',
                 amount = {$amount},
                 status = '{$status}',
                 phone = " . ($phone ? "'{$phone_escaped}'" : 'NULL') . ",
                 email = " . ($email ? "'{$email_escaped}'" : 'NULL') . ",
                 updated_at = NOW()";
    
    // order_id는 수정 시에만 업데이트 (변경된 경우에만)
    if ($order_id) {
        $sql .= ", order_id = '{$order_id_escaped}'";
    }
    
    $sql .= " WHERE personal_id = {$personal_id} 
             AND shop_id = {$shop_id} ";
    $update_result = sql_query_pg($sql, false);
    
    // personal_payment 업데이트 실패 확인
    // sql_query_pg는 성공 시 PGSQLResultWrapper 객체를 반환하고, 실패 시 false/null을 반환
    if ($update_result === false || $update_result === null) {
        $pg_error = pg_last_error($g5['connect_pg']);
        if ($pg_error) {
            alert('개인결제 정보를 업데이트하는 중 오류가 발생했습니다: ' . $pg_error);
        } else {
            alert('개인결제 정보를 업데이트하는 중 오류가 발생했습니다.');
        }
    }
    
    // 상태가 'PAID'일 때 payments 테이블 처리
    if ($status == 'PAID') {
        // order_id가 비어있으면 personal_payment에서 다시 조회
        if (empty($order_id_escaped)) {
            $order_sql = " SELECT order_id FROM personal_payment WHERE personal_id = {$personal_id} ";
            $order_result = sql_query_pg($order_sql);
            if ($order_result && is_object($order_result) && isset($order_result->result)) {
                $order_row = sql_fetch_array_pg($order_result->result);
                $order_id_escaped = ($order_row && $order_row['order_id']) ? $esc($order_row['order_id']) : '';
            } else {
                $order_id_escaped = '';
            }
        }
        // payments 테이블에 레코드가 있는지 다시 확인 (업데이트 후, 1:0 또는 1:1 관계이므로 단일 레코드만 조회)
        $payment_check_sql2 = " SELECT payment_id, paid_at FROM payments WHERE personal_id = {$personal_id} AND pay_flag = 'PERSONAL' LIMIT 1 ";
        $payment_check_result2 = sql_query_pg($payment_check_sql2);
        if ($payment_check_result2 && is_object($payment_check_result2) && isset($payment_check_result2->result)) {
            $payment_check_row2 = sql_fetch_array_pg($payment_check_result2->result);
        } else {
            $payment_check_row2 = false;
        }
        
        if (!$payment_check_row2 || !$payment_check_row2['payment_id']) {
            // payments 테이블에 레코드가 없으면 생성
            if (empty($order_id_escaped)) {
                // order_id가 없으면 다시 조회 시도
                $order_sql2 = " SELECT order_id FROM personal_payment WHERE personal_id = {$personal_id} LIMIT 1 ";
                $order_result2 = sql_query_pg($order_sql2);
                if ($order_result2 && is_object($order_result2) && isset($order_result2->result)) {
                    $order_row2 = sql_fetch_array_pg($order_result2->result);
                    if ($order_row2 && $order_row2['order_id']) {
                        $order_id_escaped = $esc($order_row2['order_id']);
                    }
                }
                
                if (empty($order_id_escaped)) {
                    alert('주문번호가 없어 payments 테이블에 데이터를 저장할 수 없습니다.');
                }
            }
            
            // payment_key 생성 (NOT NULL 제약조건)
            $payment_key = 'PERSONAL_PAYMENT_'.time().'_'.$personal_id;
            $payment_key_escaped = $esc($payment_key);
            // transaction_key 생성 (NOT NULL 제약조건)
            $transaction_key = 'PERSONAL_PAYMENT_'.time().'_'.$personal_id;
            $transaction_key_escaped = $esc($transaction_key);
            
            $payment_insert_sql = " INSERT INTO payments (
                                        pay_flag,
                                        personal_id,
                                        order_id,
                                        amount,
                                        status,
                                        payment_method,
                                        payment_key,
                                        response,
                                        transaction_key,
                                        paid_at,
                                        updated_at
                                    ) VALUES (
                                        'PERSONAL',
                                        {$personal_id},
                                        '{$order_id_escaped}',
                                        {$amount},
                                        'DONE',
                                        'MANUAL',
                                        '{$payment_key_escaped}',
                                        '{}'::jsonb,
                                        '{$transaction_key_escaped}',
                                        NOW(),
                                        NOW()
                                    ) ";
            $payment_result = sql_query_pg($payment_insert_sql, false);
            
            // INSERT 실패 시 오류 확인
            if ($payment_result === false || $payment_result === null) {
                $pg_error = pg_last_error($g5['connect_pg']);
                if ($pg_error) {
                    alert('payments 테이블에 데이터를 저장하는 중 오류가 발생했습니다: ' . $pg_error . '<br>SQL: ' . htmlspecialchars($payment_insert_sql));
                } else {
                    alert('payments 테이블에 데이터를 저장하는 중 오류가 발생했습니다.<br>SQL: ' . htmlspecialchars($payment_insert_sql));
                }
            }
        } else {
            // payments 테이블에 레코드가 있으면 업데이트
            if (empty($order_id_escaped)) {
                // order_id가 없으면 다시 조회 시도
                $order_sql3 = " SELECT order_id FROM personal_payment WHERE personal_id = {$personal_id} LIMIT 1 ";
                $order_result3 = sql_query_pg($order_sql3);
                if ($order_result3 && is_object($order_result3) && isset($order_result3->result)) {
                    $order_row3 = sql_fetch_array_pg($order_result3->result);
                    if ($order_row3 && $order_row3['order_id']) {
                        $order_id_escaped = $esc($order_row3['order_id']);
                    }
                }
                
                if (empty($order_id_escaped)) {
                    alert('주문번호가 없어 payments 테이블 데이터를 업데이트할 수 없습니다.');
                }
            }
            
            $payment_update_sql = " UPDATE payments 
                                     SET order_id = '{$order_id_escaped}',
                                         amount = {$amount},
                                         status = 'DONE',
                                         updated_at = NOW()";
            
            // paid_at이 없으면 현재 시간으로 설정
            if (!$payment_check_row2['paid_at']) {
                $payment_update_sql .= ", paid_at = NOW()";
            }
            
            $payment_update_sql .= " WHERE payment_id = {$payment_check_row2['payment_id']} 
                                     AND personal_id = {$personal_id} 
                                     AND pay_flag = 'PERSONAL' ";
            $payment_result = sql_query_pg($payment_update_sql, false);
            
            // UPDATE 실패 시 오류 확인
            if ($payment_result === false || $payment_result === null) {
                $pg_error = pg_last_error($g5['connect_pg']);
                if ($pg_error) {
                    alert('payments 테이블 데이터를 업데이트하는 중 오류가 발생했습니다: ' . $pg_error . '<br>SQL: ' . htmlspecialchars($payment_update_sql));
                } else {
                    alert('payments 테이블 데이터를 업데이트하는 중 오류가 발생했습니다.<br>SQL: ' . htmlspecialchars($payment_update_sql));
                }
            }
        }
    }
}

// personal_id가 설정되지 않았으면 오류
if (!isset($personal_id) || !$personal_id) {
    alert('개인결제 ID가 설정되지 않았습니다.');
}

$qstr = '';
if (isset($_POST['sst'])) $qstr .= '&sst='.clean_xss_tags($_POST['sst']);
if (isset($_POST['sod'])) $qstr .= '&sod='.clean_xss_tags($_POST['sod']);
if (isset($_POST['sfl'])) $qstr .= '&sfl='.clean_xss_tags($_POST['sfl']);
if (isset($_POST['stx'])) $qstr .= '&stx='.urlencode($_POST['stx']);
if (isset($_POST['sfl2'])) $qstr .= '&sfl2='.clean_xss_tags($_POST['sfl2']);
if (isset($_POST['page'])) $qstr .= '&page='.(int)$_POST['page'];

$redirect_url = './shop_personalpayform.php?w=u&amp;personal_id='.$personal_id.'&amp;'.ltrim($qstr, '&');
goto_url($redirect_url);

