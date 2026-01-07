<?php
$sub_menu = "920400";
include_once('./_common.php');

check_demo();

$w = isset($_POST['w']) ? $_POST['w'] : '';
$act = isset($_POST['act']) ? $_POST['act'] : '';

// 포인트 수동 부여 처리
if ($act == 'grant') {
    @auth_check($auth[$sub_menu], 'w');
    
    $customer_id = isset($_POST['selected_customer_id']) ? (int)$_POST['selected_customer_id'] : 0;
    $amount = isset($_POST['grant_point_amount']) ? (int)$_POST['grant_point_amount'] : 0;
    $memo = isset($_POST['grant_point_memo']) ? clean_xss_tags($_POST['grant_point_memo']) : '관리자 수동 부여';
    
    if (!$customer_id) {
        alert('회원을 선택해주세요.');
        exit;
    }
    
    if ($amount <= 0) {
        alert('포인트 금액을 올바르게 입력해주세요.');
        exit;
    }
    
    // payment_id 처리 - 수동 부여를 위한 더미 payment 생성 또는 기존 payment 재사용
    $dummy_payment = sql_fetch_pg(" SELECT payment_id FROM payments WHERE payment_key LIKE 'MANUAL_GRANT%' LIMIT 1 ");
    if ($dummy_payment && isset($dummy_payment['payment_id'])) {
        $payment_id = $dummy_payment['payment_id'];
    } else {
        // 기존 payment_id 중 최신 것 사용
        $check_payment = sql_fetch_pg(" SELECT payment_id FROM payments ORDER BY payment_id DESC LIMIT 1 ");
        if ($check_payment && isset($check_payment['payment_id']) && $check_payment['payment_id'] > 0) {
            $payment_id = $check_payment['payment_id'];
        } else {
            // payments 테이블이 비어있으면 더미 payment 생성
            $payment_key = 'MANUAL_GRANT_'.time();
            $dummy_sql = " INSERT INTO payments (payment_key, order_id, payment_method, amount, status, response, transaction_key, paid_at, updated_at) 
                          VALUES ('{$payment_key}', 'MANUAL', 'MANUAL', 0, 'DONE', '{}'::jsonb, 'MANUAL_".time()."', NOW(), NOW()) 
                          RETURNING payment_id ";
            $dummy_result = @pg_query($g5['connect_pg'], $dummy_sql);
            if ($dummy_result) {
                $dummy_row = pg_fetch_assoc($dummy_result);
                $payment_id = $dummy_row['payment_id'];
            } else {
                $error = pg_last_error($g5['connect_pg']);
                alert('더미 payment 생성 실패: '.$error);
                exit;
            }
        }
    }
    
    // 포인트 부여 (Boot Spring 로직 참고: earned_at = NOW(), expired_at = NOW() + 6 MONTH)
    $memo = addslashes($memo);
    $sql = " INSERT INTO point_transactions (
                customer_id,
                type,
                amount,
                earned_at,
                expired_at,
                payment_id,
                memo,
                created_at,
                updated_at
            ) VALUES (
                {$customer_id},
                '적립',
                {$amount},
                NOW(),
                NOW() + INTERVAL '6 MONTH',
                {$payment_id},
                '{$memo}',
                NOW(),
                NOW()
            ) ";
    
    $link = $g5['connect_pg'];
    $result = @pg_query($link, $sql);
    if ($result) {
        alert('포인트가 성공적으로 부여되었습니다.', './pointlist.php');
    } else {
        $error = pg_last_error($link);
        alert('포인트 부여에 실패했습니다.\n오류: '.$error);
    }
    exit;
}

// 선택 삭제 처리
if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

if($w == 'd') {
    @auth_check($auth[$sub_menu], 'd');
    
    $failed_points = array();
    $success_count = 0;
    
    for ($i=0; $i<count($_POST['chk']); $i++) {
        $k = $_POST['chk'][$i];
        $point_id = isset($_POST['point_id'][$k]) ? (int)$_POST['point_id'][$k] : 0;
        
        if (!$point_id) {
            $failed_points[] = '포인트 ID가 없습니다.';
            continue;
        }
        
        // 포인트 거래 정보 확인
        $point = sql_fetch_pg(" SELECT * FROM point_transactions WHERE point_id = {$point_id} ");
        if (!$point) {
            $failed_points[] = '포인트 거래 정보를 찾을 수 없습니다. (ID: '.$point_id.')';
            continue;
        }
        
        // 옵션 1: 적립 포인트만 삭제 가능하고, 관련 거래가 없을 때만 삭제 가능
        $can_delete = true;
        $delete_reason = '';
        
        // 1. 적립 포인트 타입인지 확인
        if ($point['type'] != '적립') {
            $can_delete = false;
            $delete_reason = '적립 포인트만 삭제할 수 있습니다. (현재 타입: '.$point['type'].')';
        }
        // 2. 관련 거래가 있는지 확인
        else {
            $related_sql = " SELECT COUNT(*) AS cnt 
                             FROM point_transactions 
                             WHERE related_id = {$point_id} ";
            $related_count = sql_fetch_pg($related_sql);
            
            if ($related_count && $related_count['cnt'] > 0) {
                $can_delete = false;
                $delete_reason = '관련된 포인트 거래가 있어 삭제할 수 없습니다. (관련 거래: '.$related_count['cnt'].'건)';
            }
        }
        
        if (!$can_delete) {
            $failed_points[] = $delete_reason.' (ID: '.$point_id.')';
            continue;
        }
        
        // 삭제 실행
        $delete_sql = " DELETE FROM point_transactions WHERE point_id = {$point_id} ";
        if (sql_query_pg($delete_sql)) {
            $success_count++;
        } else {
            $failed_points[] = '삭제에 실패했습니다. (ID: '.$point_id.')';
        }
    }
    
    // 결과 메시지 생성
    if ($success_count > 0 && count($failed_points) == 0) {
        $msg = '삭제가 완료되었습니다. ('.$success_count.'건)';
    } else if ($success_count > 0 && count($failed_points) > 0) {
        $msg = '삭제 완료: '.$success_count.'건'."\n\n".'실패: '.count($failed_points).'건'."\n".implode("\n", $failed_points);
    } else if (count($failed_points) > 0) {
        $msg = '삭제 실패: '.count($failed_points).'건'."\n".implode("\n", $failed_points);
    }
    
    if (isset($msg)) {
        $msg = str_replace(["\\", "'", '"'], ["\\\\", "\\'", '\\"'], $msg);
        $msg = str_replace(["\r\n", "\r", "\n"], "\\n", $msg);
        alert($msg);
    }
}

// 검색 조건 유지
$qstr = '';
if (isset($_POST['sst']) && $_POST['sst']) $qstr .= '&sst='.urlencode($_POST['sst']);
if (isset($_POST['sod']) && $_POST['sod']) $qstr .= '&sod='.urlencode($_POST['sod']);
if (isset($_POST['sfl']) && $_POST['sfl']) $qstr .= '&sfl='.urlencode($_POST['sfl']);
if (isset($_POST['stx']) && $_POST['stx']) $qstr .= '&stx='.urlencode($_POST['stx']);
if (isset($_POST['ser_type']) && $_POST['ser_type']) $qstr .= '&ser_type='.urlencode($_POST['ser_type']);
if (isset($_POST['ser_date_field']) && $_POST['ser_date_field']) $qstr .= '&ser_date_field='.urlencode($_POST['ser_date_field']);
if (isset($_POST['fr_date']) && $_POST['fr_date']) $qstr .= '&fr_date='.urlencode($_POST['fr_date']);
if (isset($_POST['to_date']) && $_POST['to_date']) $qstr .= '&to_date='.urlencode($_POST['to_date']);
if (isset($_POST['page']) && $_POST['page']) $qstr .= '&page='.urlencode($_POST['page']);

goto_url('./pointlist.php?'.$qstr);

