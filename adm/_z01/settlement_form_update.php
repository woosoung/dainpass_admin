<?php
$sub_menu = "920450";
include_once("./_common.php");

$w = isset($_POST['w']) ? trim($_POST['w']) : (isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '');
$ssl_id = isset($_POST['ssl_id']) ? (int)$_POST['ssl_id'] : (isset($_REQUEST['ssl_id']) ? (int)$_REQUEST['ssl_id'] : 0);
$settlement_id = isset($_POST['settlement_id']) ? (int)$_POST['settlement_id'] : 0;

if ($w == 'u')
    check_demo();

@auth_check($auth[$sub_menu], 'w');

// 입력값 검증
if(!trim($_POST['shop_id'])) alert('가맹점을 선택해 주세요.');
if(!trim($_POST['pay_flag'])) alert('결제 유형을 선택해 주세요.');
if(!trim($_POST['total_payment_amount']) || (float)$_POST['total_payment_amount'] <= 0) alert('결제금액을 입력해 주세요.');

$shop_id = (int)trim($_POST['shop_id']);
$pay_flag = trim($_POST['pay_flag']);
$shopdetail_id = !empty($_POST['shopdetail_id']) ? (int)$_POST['shopdetail_id'] : null;
$personal_id = !empty($_POST['personal_id']) ? (int)$_POST['personal_id'] : null;
$appointment_datetime = !empty($_POST['appointment_datetime']) ? trim($_POST['appointment_datetime']) : null;
$payment_id = !empty($_POST['payment_id']) ? (int)$_POST['payment_id'] : null;
$total_payment_amount = (float)trim($_POST['total_payment_amount']);
$cancel_amount = (float)(trim($_POST['cancel_amount']) ?? 0);
$net_settlement_amount = (float)trim($_POST['net_settlement_amount']);
$settlement_type = trim($_POST['settlement_type']);

// 정산금액 검증
if ($net_settlement_amount < 0) {
    alert('정산금액이 올바르지 않습니다.');
}

// 정산 기간 설정 (appointment_datetime이 있으면 해당 날짜 기준, 없으면 현재 날짜 기준)
if ($appointment_datetime) {
    $settlement_date = date('Y-m-d', strtotime($appointment_datetime));
    $settlement_start_at = $settlement_date . ' 00:00:00';
    $settlement_end_at = $settlement_date . ' 23:59:59';
} else {
    $settlement_date = date('Y-m-d');
    $settlement_start_at = $settlement_date . ' 00:00:00';
    $settlement_end_at = $settlement_date . ' 23:59:59';
}

// SQL용 NULL 처리
$shopdetail_id_sql = ($shopdetail_id !== null) ? $shopdetail_id : 'NULL';
$personal_id_sql = ($personal_id !== null) ? $personal_id : 'NULL';
$payment_id_sql = ($payment_id !== null) ? $payment_id : 'NULL';
$appointment_datetime_sql = ($appointment_datetime !== null) ? "'{$appointment_datetime}'" : 'NULL';

// 등록 처리
if ($w == '') {
    try {
        sql_query_pg("BEGIN");

        // 1단계: shop_settlement_log 레코드 생성
        $sql_log = " INSERT INTO shop_settlement_log (
                        shop_id,
                        settlement_type,
                        settlement_amount,
                        settlement_start_at,
                        settlement_end_at,
                        status,
                        settlement_at
                    ) VALUES (
                        {$shop_id},
                        '{$settlement_type}',
                        {$net_settlement_amount},
                        '{$settlement_start_at}',
                        '{$settlement_end_at}',
                        'done',
                        NOW()
                    ) RETURNING ssl_id ";
        $result_log = sql_query_pg($sql_log);
        
        // RETURNING으로 ssl_id 가져오기
        if ($result_log && is_object($result_log) && isset($result_log->result)) {
            $new_log = sql_fetch_array_pg($result_log->result);
            $new_ssl_id = $new_log['ssl_id'] ?? null;
        } else {
            // RETURNING이 실패한 경우 대체 방법
            $new_log = sql_fetch_pg("SELECT ssl_id FROM shop_settlement_log ORDER BY ssl_id DESC LIMIT 1");
            $new_ssl_id = $new_log['ssl_id'] ?? null;
        }

        if (!$new_ssl_id) {
            throw new Exception('정산 로그 생성에 실패했습니다.');
        }

        // 2단계: shop_settlements 레코드 생성
        // payment_id와 appointment_datetime은 NOT NULL이므로 기본값 설정
        $final_payment_id = ($payment_id !== null) ? $payment_id : 0;
        $final_appointment_datetime = ($appointment_datetime !== null) ? $appointment_datetime : date('Y-m-d H:i:s');
        
        $sql_settlement = " INSERT INTO shop_settlements (
                                ssl_id,
                                shop_id,
                                pay_flag,
                                shopdetail_id,
                                personal_id,
                                payment_id,
                                appointment_datetime,
                                total_payment_amount,
                                cancel_amount,
                                net_settlement_amount,
                                settlement_status,
                                created_at,
                                updated_at
                            ) VALUES (
                                {$new_ssl_id},
                                {$shop_id},
                                '{$pay_flag}',
                                {$shopdetail_id_sql},
                                {$personal_id_sql},
                                {$final_payment_id},
                                '{$final_appointment_datetime}',
                                {$total_payment_amount},
                                {$cancel_amount},
                                {$net_settlement_amount},
                                'COMPLETED',
                                NOW(),
                                NOW()
                            ) ";
        
        if (!sql_query_pg($sql_settlement)) {
            throw new Exception('정산 내역 생성에 실패했습니다.');
        }

        sql_query_pg("COMMIT");
        $msg = '정산 정보를 등록하였습니다.';

    } catch (Exception $e) {
        sql_query_pg("ROLLBACK");
        alert('등록 중 오류가 발생했습니다: ' . $e->getMessage());
    }
}
// 수정 처리
else if ($w == 'u') {
    if (!$ssl_id) {
        alert('정산 로그 ID가 없습니다.');
    }
    
    // 기존 정산 로그 확인
    $log_check = sql_fetch_pg(" SELECT settlement_type FROM shop_settlement_log WHERE ssl_id = '{$ssl_id}' LIMIT 1 ");
    if (!$log_check) {
        alert('존재하지 않는 정산 자료입니다.');
    }
    
    // 자동 정산은 수정 불가
    if ($log_check['settlement_type'] == 'AUTO') {
        alert('자동 정산 데이터는 수정할 수 없습니다.');
    }
    
    // settlement_id가 없으면 첫 번째 레코드 조회
    $existing_settlement = null;
    if (!$settlement_id) {
        $first_settlement = sql_fetch_pg(" SELECT * FROM shop_settlements WHERE ssl_id = '{$ssl_id}' ORDER BY settlement_id LIMIT 1 ");
        if ($first_settlement) {
            $settlement_id = $first_settlement['settlement_id'];
            $existing_settlement = $first_settlement;
        }
    } else {
        // settlement_id가 있으면 기존 레코드 조회
        $existing_settlement = sql_fetch_pg(" SELECT * FROM shop_settlements WHERE settlement_id = '{$settlement_id}' AND ssl_id = '{$ssl_id}' LIMIT 1 ");
    }

    if (!$settlement_id) {
        alert('정산 내역 ID가 없습니다.');
    }

    if (!$existing_settlement || !$existing_settlement['settlement_id']) {
        alert('존재하지 않는 정산 내역입니다.');
    }

    try {
        sql_query_pg("BEGIN");

        // shop_settlement_log 업데이트 (정산금액도 업데이트)
        // 주의: shop_settlement_log 테이블에는 updated_at 컬럼이 없음
        $sql_log_update = " UPDATE shop_settlement_log
                            SET settlement_amount = {$net_settlement_amount}
                            WHERE ssl_id = '{$ssl_id}'
                              AND settlement_type = 'MANUAL' ";
        
        $log_update_result = sql_query_pg($sql_log_update, false);
        
        if ($log_update_result === false || $log_update_result === null) {
            global $g5;
            $error_msg = '';
            if (isset($g5['connect_pg']) && $g5['connect_pg']) {
                $pg_error = @pg_last_error($g5['connect_pg']);
                if ($pg_error !== false && !empty($pg_error)) {
                    $error_msg = $pg_error;
                }
            }
            throw new Exception('정산 로그 수정에 실패했습니다.' . ($error_msg ? ' (' . $error_msg . ')' : ''));
        }

        // shop_settlements 업데이트
        // 수정 모드에서는 결제금액, 취소금액, 정산금액만 수정
        $sql_settlement_update = " UPDATE shop_settlements
                                    SET 
                                        total_payment_amount = {$total_payment_amount},
                                        cancel_amount = {$cancel_amount},
                                        net_settlement_amount = {$net_settlement_amount},
                                        updated_at = NOW()
                                    WHERE settlement_id = '{$settlement_id}'
                                      AND ssl_id = '{$ssl_id}' ";
        
        $update_result = sql_query_pg($sql_settlement_update, false);
        
        if ($update_result === false || $update_result === null) {
            global $g5;
            $error_msg = '';
            if (isset($g5['connect_pg']) && $g5['connect_pg']) {
                $pg_error = @pg_last_error($g5['connect_pg']);
                if ($pg_error !== false && !empty($pg_error)) {
                    $error_msg = $pg_error;
                }
            }
            throw new Exception('정산 내역 수정에 실패했습니다.' . ($error_msg ? ' (' . $error_msg . ')' : ''));
        }

        sql_query_pg("COMMIT");
        $msg = '정산 정보를 수정하였습니다.';

    } catch (Exception $e) {
        sql_query_pg("ROLLBACK");
        alert('수정 중 오류가 발생했습니다: ' . $e->getMessage());
    }
}

if ($msg) {
    alert($msg, './settlement_list.php');
} else {
    alert('처리할 내용이 없습니다.', './settlement_list.php');
}
?>

