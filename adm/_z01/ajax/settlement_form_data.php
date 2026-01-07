<?php
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

// 에러 핸들러 설정
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

try {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';
    
    if ($action == 'get_payments_by_shop') {
        $shop_id = isset($_POST['shop_id']) ? (int)$_POST['shop_id'] : 0;
        
        if ($shop_id <= 0) {
            echo json_encode(['success' => false, 'message' => '가맹점 ID가 올바르지 않습니다.']);
            exit;
        }
        
        // 결제 목록 조회 (GENERAL과 PERSONAL 결제 모두 포함)
        $sql = "
            SELECT * FROM (
                SELECT 
                    sh.shop_id AS shopId,
                    sh.name AS shopName,
                    py.payment_id AS paymentId,
                    py.pay_flag AS payType,
                    sa.appointment_id AS appointmentId,
                    asd.shopdetail_id AS shopdetailId,
                    py.personal_id AS personalId,
                    ct.customer_id AS customerId,
                    ct.name AS customerName,
                    asd.appointment_datetime AS appointmentDatetime,
                    py.amount AS amount,
                    py.paid_at AS paidAt,
                    '일반 결제 - ' || COALESCE(ct.name, '') || ' / ' || COALESCE(asd.appointment_datetime::text, '') || ' / ' || py.amount::text || '원' AS displayText
                FROM payments py
                LEFT JOIN shop_appointments sa ON py.appointment_id = sa.appointment_id
                LEFT JOIN appointment_shop_detail asd ON sa.appointment_id = asd.appointment_id
                LEFT JOIN shop sh ON asd.shop_id = sh.shop_id
                LEFT JOIN customers ct ON sa.customer_id = ct.customer_id
                WHERE sh.shop_id = {$shop_id}
                  AND py.status = 'DONE'
                UNION ALL
                SELECT 
                    pp.shop_id AS shopId,
                    sh.name AS shopName,
                    py.payment_id AS paymentId,
                    py.pay_flag AS payType,
                    asd.appointment_id AS appointmentId,
                    pp.shopdetail_id AS shopdetailId,
                    py.personal_id AS personalId,
                    ct.customer_id AS customerId,
                    ct.name AS customerName,
                    asd.appointment_datetime AS appointmentDatetime,
                    py.amount AS amount,
                    py.paid_at AS paidAt,
                    '개인 결제 - ' || COALESCE(ct.name, '') || ' / ' || COALESCE(asd.appointment_datetime::text, '') || ' / ' || py.amount::text || '원' AS displayText
                FROM payments py
                LEFT JOIN personal_payment pp ON py.personal_id = pp.personal_id
                LEFT JOIN shop sh ON pp.shop_id = sh.shop_id
                LEFT JOIN appointment_shop_detail asd ON pp.shopdetail_id = asd.shopdetail_id
                LEFT JOIN customers ct ON pp.user_id = ct.user_id
                WHERE sh.shop_id = {$shop_id}
                  AND pp.status = 'PAID'
            ) AS combined_result
            ORDER BY paidAt DESC
            LIMIT 20
        ";
        
        $result = sql_query_pg($sql);
        $payments = array();
        
        if ($result && is_object($result) && isset($result->result)) {
            for($i=0; $row = sql_fetch_array_pg($result->result); $i++) {
                $payments[] = array(
                    'shopId' => $row['shopid'],
                    'shopName' => $row['shopname'],
                    'payFlag' => $row['paytype'] ?? '',
                    'paymentId' => $row['paymentid'],
                    'appointmentId' => $row['appointmentid'],
                    'shopdetailId' => $row['shopdetailid'],
                    'personalId' => $row['personalid'],
                    'customerId' => $row['customerid'],
                    'customerName' => $row['customername'],
                    'appointmentDatetime' => $row['appointmentdatetime'] ? date('Y-m-d H:i:s', strtotime($row['appointmentdatetime'])) : '',
                    'amount' => $row['amount'],
                    'paidAt' => $row['paidat'] ? date('Y-m-d H:i:s', strtotime($row['paidat'])) : '',
                    'displayText' => $row['displaytext']
                );
            }
        }
        
        echo json_encode(['success' => true, 'payments' => $payments]);
        exit;
        
    } else if ($action == 'get_payment') {
        $payment_id = isset($_POST['payment_id']) ? (int)$_POST['payment_id'] : 0;
        
        if ($payment_id <= 0) {
            echo json_encode(['success' => false, 'message' => '결제 ID가 올바르지 않습니다.']);
            exit;
        }
        
        // 결제 상세 정보 조회
        $sql = " SELECT 
                    p.payment_id,
                    p.pay_flag,
                    p.amount,
                    p.paid_at,
                    p.updated_at,
                    p.personal_id,
                    asd.shopdetail_id,
                    asd.appointment_datetime,
                    pp.personal_id AS pp_personal_id
                 FROM payments p
                 LEFT JOIN appointment_shop_detail asd ON p.appointment_id = asd.appointment_id
                 LEFT JOIN personal_payment pp ON p.personal_id = pp.personal_id
                 WHERE p.payment_id = {$payment_id}
                 LIMIT 1 ";
        
        $payment = sql_fetch_pg($sql);
        
        if (!$payment) {
            echo json_encode(['success' => false, 'message' => '결제 정보를 찾을 수 없습니다.']);
            exit;
        }
        
        $payment_data = array(
            'payFlag' => $payment['pay_flag'] ?? '',
            'paymentId' => $payment['payment_id'],
            'shopdetailId' => $payment['shopdetail_id'] ?? null,
            'personalId' => $payment['personal_id'] ?? $payment['pp_personal_id'] ?? null,
            'appointmentDatetime' => $payment['appointment_datetime'] ? date('Y-m-d H:i:s', strtotime($payment['appointment_datetime'])) : '',
            'amount' => $payment['amount'] ?? 0
        );
        
        echo json_encode(['success' => true, 'payment' => $payment_data]);
        exit;
        
    } else {
        echo json_encode(['success' => false, 'message' => '알 수 없는 액션입니다.']);
        exit;
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '오류가 발생했습니다: ' . $e->getMessage()]);
    exit;
}
?>

