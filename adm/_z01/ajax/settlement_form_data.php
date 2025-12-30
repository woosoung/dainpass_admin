<?php
// 오류 처리 설정 - 모든 오류를 캐치
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// 오류 핸들러 설정
function handleError($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'PHP 오류가 발생했습니다.',
        'error' => [
            'type' => 'PHP Error',
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'code' => $errno
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 예외 핸들러 설정
function handleException($exception) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '예외가 발생했습니다.',
        'error' => [
            'type' => 'Exception',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

set_error_handler('handleError');
set_exception_handler('handleException');

include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

$action = isset($_POST['action']) ? trim($_POST['action']) : '';

if ($action === 'get_payment') {
    $payment_id = isset($_POST['payment_id']) ? (int)$_POST['payment_id'] : 0;
    
    if (!$payment_id) {
        echo json_encode(['success' => false, 'message' => '결제 ID가 없습니다.']);
        exit;
    }

    // payments 테이블에서 결제 정보 조회
    $sql = " SELECT 
                p.payment_id,
                p.pay_flag,
                p.amount,
                p.personal_id,
                p.appointment_id,
                p.paid_at,
                p.updated_at
            FROM payments p
            WHERE p.payment_id = '{$payment_id}' 
            LIMIT 1 ";
    $payment = sql_fetch_pg($sql);

    if (!$payment || !$payment['payment_id']) {
        echo json_encode(['success' => false, 'message' => '결제 정보를 찾을 수 없습니다.']);
        exit;
    }

    $result = array(
        'payment_id' => $payment['payment_id'],
        'pay_flag' => $payment['pay_flag'] ?? null,
        'amount' => $payment['amount'] ?? 0,
        'personal_id' => $payment['personal_id'] ?? null,
        'appointment_id' => $payment['appointment_id'] ?? null
    );

    // appointment_shop_detail 정보 조회 (GENERAL 결제인 경우)
    if ($payment['pay_flag'] == 'GENERAL' || !$payment['pay_flag']) {
        if ($payment['appointment_id']) {
            $asd_sql = " SELECT 
                            asd.shopdetail_id,
                            asd.shop_id,
                            asd.appointment_datetime,
                            asd.org_balance_amount,
                            asd.balance_amount
                        FROM appointment_shop_detail asd
                        WHERE asd.appointment_id = '{$payment['appointment_id']}'
                        LIMIT 1 ";
            $asd = sql_fetch_pg($asd_sql);
            
            if ($asd) {
                $result['shopdetail_id'] = $asd['shopdetail_id'] ?? null;
                $result['shop_id'] = $asd['shop_id'] ?? null;
                $result['appointment_datetime'] = $asd['appointment_datetime'] ?? null;
                $result['total_payment_amount'] = $asd['org_balance_amount'] ?? $payment['amount'];
                $result['cancel_amount'] = ($asd['org_balance_amount'] ?? 0) - ($asd['balance_amount'] ?? 0);
            }
        }
    }

    // personal_payment 정보 조회 (PERSONAL 결제인 경우)
    if ($payment['pay_flag'] == 'PERSONAL' && $payment['personal_id']) {
        $pp_sql = " SELECT 
                        pp.personal_id,
                        pp.shop_id,
                        pp.shopdetail_id,
                        pp.status,
                        pp.created_at,
                        pp.updated_at
                    FROM personal_payment pp
                    WHERE pp.personal_id = '{$payment['personal_id']}'
                    LIMIT 1 ";
        $pp = sql_fetch_pg($pp_sql);
        
        if ($pp) {
            $result['shop_id'] = $pp['shop_id'] ?? null;
            $result['shopdetail_id'] = $pp['shopdetail_id'] ?? null;
            $result['appointment_datetime'] = $pp['updated_at'] ?? $pp['created_at'] ?? null;
        }
    }

    // 취소 내역 확인
    if ($payment['appointment_id']) {
        $cancel_sql = " SELECT COALESCE(SUM(cancel_amount), 0) AS total_cancel
                        FROM payments_shop_cancel
                        WHERE appointment_id = '{$payment['appointment_id']}'
                          AND approval_yn = 'Y' ";
        $cancel = sql_fetch_pg($cancel_sql);
        if ($cancel && isset($cancel['total_cancel'])) {
            $result['cancel_amount'] = (float)$cancel['total_cancel'];
        }
    }

    echo json_encode(['success' => true, 'data' => $result]);
    exit;
}

if ($action === 'get_payments_by_shop') {
    $shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : 0;
    
    if (!$shop_id) {
        echo json_encode(['success' => false, 'message' => '가맹점 ID가 없습니다.']);
        exit;
    }

    // current_work.md의 쿼리 기반으로 해당 가맹점의 결제 내역 조회
    // PostgreSQL에서 UNION ALL 후 ORDER BY는 서브쿼리로 감싸야 함
    $sql = "SELECT * FROM (
                SELECT sh.shop_id AS shopId
                    , sh.name AS shopName
                    , py.payment_id AS paymentId
                    , py.pay_flag AS payType
                    , sa.appointment_id AS appointmentId
                    , asd.shopdetail_id AS shopdetailId
                    , py.personal_id AS personalId
                    , ct.customer_id AS customerId
                    , ct.name AS customerName
                    , asd.appointment_datetime AS appointmentDatetime
                    , py.amount AS amount
                    , py.paid_at AS paidAt
                FROM payments py
                LEFT JOIN shop_appointments sa ON py.appointment_id = sa.appointment_id
                LEFT JOIN appointment_shop_detail asd ON sa.appointment_id = asd.appointment_id
                LEFT JOIN shop sh ON asd.shop_id = sh.shop_id
                LEFT JOIN customers ct ON sa.customer_id = ct.customer_id
                WHERE sh.shop_id = {$shop_id}
                    AND py.status = 'DONE'

                UNION ALL

                SELECT pp.shop_id AS shopId
                    , sh.name AS shopName
                    , py.payment_id AS paymentId
                    , py.pay_flag AS payType
                    , asd.appointment_id AS appointmentId
                    , pp.shopdetail_id AS shopdetailId
                    , py.personal_id AS personalId
                    , ct.customer_id AS customerId
                    , ct.name AS customerName
                    , asd.appointment_datetime AS appointmentDatetime
                    , py.amount AS amount
                    , py.paid_at AS paidAt
                FROM payments py
                LEFT JOIN personal_payment pp ON py.personal_id = pp.personal_id 
                LEFT JOIN shop sh ON pp.shop_id = sh.shop_id
                LEFT JOIN appointment_shop_detail asd ON pp.shopdetail_id = asd.shopdetail_id 
                LEFT JOIN customers ct ON pp.user_id = ct.user_id 
                WHERE sh.shop_id = {$shop_id}
                    AND pp.status = 'PAID'
            ) AS combined_result
            ORDER BY paidAt DESC
            LIMIT 20 ";
    
    // 쿼리 실행 (에러 표시 비활성화)
    $result = null;
    $query_error = null;
    
    try {
        $result = sql_query_pg($sql, false);
    } catch (Exception $e) {
        $query_error = [
            'type' => 'Exception',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
    } catch (Error $e) {
        $query_error = [
            'type' => 'Error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
    }
    
    $payments = array();
    
    // 쿼리 실행 결과 확인
    if ($result === false || $result === null || $query_error !== null) {
        // 쿼리 실행 실패
        global $g5;
        $error_details = [];
        
        if ($query_error) {
            $error_details = $query_error;
        } else {
            $error_details['type'] = 'Database Query Error';
            $error_details['message'] = '쿼리 실행 실패';
            
            if (isset($g5['connect_pg']) && $g5['connect_pg']) {
                $pg_error = @pg_last_error($g5['connect_pg']);
                if ($pg_error !== false && !empty($pg_error)) {
                    $error_details['pg_error'] = $pg_error;
                }
            }
            
            // 쿼리 정보 추가 (디버깅용)
            $error_details['sql'] = $sql;
            $error_details['shop_id'] = $shop_id;
        }
        
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => '결제 정보 조회 중 오류가 발생했습니다.',
            'error' => $error_details
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 결과가 객체이고 result 속성이 있는 경우
    if (is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            if ($row) {
                // PostgreSQL은 기본적으로 소문자로 반환하지만, AS로 지정한 별칭은 그대로 반환될 수 있음
                $payments[] = array(
                    'payment_id' => $row['paymentid'] ?? $row['paymentId'] ?? $row['payment_id'] ?? null,
                    'pay_flag' => $row['paytype'] ?? $row['payType'] ?? $row['pay_flag'] ?? null,
                    'appointment_id' => $row['appointmentid'] ?? $row['appointmentId'] ?? $row['appointment_id'] ?? null,
                    'shopdetail_id' => $row['shopdetailid'] ?? $row['shopdetailId'] ?? $row['shopdetail_id'] ?? null,
                    'personal_id' => $row['personalid'] ?? $row['personalId'] ?? $row['personal_id'] ?? null,
                    'customer_id' => $row['customerid'] ?? $row['customerId'] ?? $row['customer_id'] ?? null,
                    'customer_name' => $row['customername'] ?? $row['customerName'] ?? $row['customer_name'] ?? $row['name'] ?? null,
                    'appointment_datetime' => $row['appointmentdatetime'] ?? $row['appointmentDatetime'] ?? $row['appointment_datetime'] ?? null,
                    'amount' => $row['amount'] ?? $row['Amount'] ?? $row['amount'] ?? null,
                    'paid_at' => $row['paidat'] ?? $row['paidAt'] ?? $row['paid_at'] ?? null,
                    'shop_name' => $row['shopname'] ?? $row['shopName'] ?? $row['shop_name'] ?? null
                );
            }
        }
    }

    echo json_encode(['success' => true, 'data' => $payments], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['success' => false, 'message' => '알 수 없는 요청입니다.']);

