<?php
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

// 권한 체크
if (!$is_member || !$member['mb_id']) {
    echo json_encode(array('success' => false, 'message' => '로그인이 필요합니다.'));
    exit;
}

// POST 데이터 읽기
$json_data = file_get_contents('php://input');
$request_data = json_decode($json_data, true);

if (!$request_data) {
    echo json_encode(array('success' => false, 'message' => '잘못된 요청 데이터입니다.'));
    exit;
}

$appointment_id = isset($request_data['appointmentId']) ? (int)$request_data['appointmentId'] : 0;

if ($appointment_id <= 0) {
    echo json_encode(array('success' => false, 'message' => '예약 ID가 올바르지 않습니다.'));
    exit;
}

// 가맹점 접근 권한 체크
$has_access = false;
$shop_id = 0;
$cancellation_period = 1;

$mb_sql = " SELECT mb_id, mb_level, mb_1, mb_2 
            FROM {$g5['member_table']} 
            WHERE mb_id = '{$member['mb_id']}' 
            AND mb_level >= 4 
            AND (
                mb_level >= 6 
                OR (mb_level < 6 AND mb_2 = 'Y')
            ) ";
$mb_row = sql_fetch($mb_sql, 1);

if ($mb_row && $mb_row['mb_id']) {
    $mb_1_value = trim($mb_row['mb_1']);
    if (!empty($mb_1_value) && $mb_1_value !== '0') {
        $shop_id_check = (int)$mb_1_value;
        $shop_sql = " SELECT shop_id, cancellation_period FROM {$g5['shop_table']} WHERE shop_id = {$shop_id_check} ";
        $shop_row = sql_fetch_pg($shop_sql);
        if ($shop_row && $shop_row['shop_id']) {
            $has_access = true;
            $shop_id = (int)$shop_row['shop_id'];
            $cancellation_period = isset($shop_row['cancellation_period']) ? (int)$shop_row['cancellation_period'] : 1;
        }
    }
}

if (!$has_access) {
    echo json_encode(array('success' => false, 'message' => '접근 권한이 없습니다.'));
    exit;
}

// 예약이 해당 가맹점의 예약인지 확인 (BOOKED 상태 제외) 및 order_id 조회
$check_sql = " SELECT sa.appointment_id, sa.order_id, sa.status, 
                      MIN(asd.appointment_datetime) as first_datetime
               FROM shop_appointments sa
               INNER JOIN appointment_shop_detail asd ON sa.appointment_id = asd.appointment_id
               WHERE sa.appointment_id = {$appointment_id} 
               AND sa.status != 'BOOKED'
               AND asd.shop_id = {$shop_id}
               AND asd.status != 'BOOKED'
               GROUP BY sa.appointment_id, sa.order_id, sa.status ";
$check_row = sql_fetch_pg($check_sql);

if (!$check_row || !$check_row['appointment_id']) {
    echo json_encode(array('success' => false, 'message' => '해당 가맹점의 예약이 아닙니다.'));
    exit;
}

// 취소 가능 시간 체크
$first_datetime = $check_row['first_datetime'];
$cancel_deadline = date('Y-m-d H:i:s', strtotime($first_datetime . ' -' . $cancellation_period . ' hours'));
if (strtotime('now') >= strtotime($cancel_deadline)) {
    echo json_encode(array('success' => false, 'message' => '취소 가능 시간이 지났습니다. (취소 가능 시간: 예약 시간 ' . $cancellation_period . '시간 전까지)'));
    exit;
}

// 상태 체크 (COMPLETED 상태만 취소 가능)
if ($check_row['status'] != 'COMPLETED') {
    echo json_encode(array('success' => false, 'message' => '결제 완료된 예약만 취소할 수 있습니다.'));
    exit;
}

// secretKey 생성
// setting 테이블에서 set_api_hidden_code 값 가져오기
$keyword_sql = " SELECT set_value FROM setting WHERE set_name = 'set_api_hidden_code' LIMIT 1 ";
$keyword_row = sql_fetch_pg($keyword_sql);
$keyword = null;
if ($keyword_row && isset($keyword_row['set_value']) && !empty($keyword_row['set_value'])) {
    $keyword = trim($keyword_row['set_value']);
    // keyword는 6자리여야 함
    if (strlen($keyword) !== 6) {
        $keyword = null;
    }
}


$secretKey = null;
try {
    $secretKey = generateSecretKey($keyword);
} catch (InvalidArgumentException $e) {
    echo json_encode(array('success' => false, 'message' => 'secretKey 생성 중 오류가 발생했습니다: ' . $e->getMessage()));
    exit;
} catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => 'secretKey 생성 중 오류가 발생했습니다: ' . $e->getMessage()));
    exit;
}

// API 호출 준비 - 규격서에 맞게 데이터 구조 재구성
// 규격서: orderId (String), secretKey (String), appointmentShops (Array)
$order_id = isset($check_row['order_id']) ? $check_row['order_id'] : '';
if (empty($order_id)) {
    echo json_encode(array('success' => false, 'message' => '주문번호(order_id)를 찾을 수 없습니다.'));
    exit;
}

// JavaScript에서 전송한 shopdetailId 추출 (현재 가맹점의 shopdetailId)
$requested_shopdetail_id = 0;
if (isset($request_data['appointmentShops']) && is_array($request_data['appointmentShops']) && count($request_data['appointmentShops']) > 0) {
    $requested_shopdetail_id = isset($request_data['appointmentShops'][0]['shopdetailId']) ? (int)$request_data['appointmentShops'][0]['shopdetailId'] : 0;
}

if ($requested_shopdetail_id <= 0) {
    echo json_encode(array('success' => false, 'message' => 'shopdetailId가 올바르지 않습니다.'));
    exit;
}

// 해당 shopdetailId가 현재 가맹점의 예약인지 확인
$shopdetail_check_sql = " SELECT asd.shopdetail_id, asd.shop_id
                          FROM appointment_shop_detail asd
                          WHERE asd.shopdetail_id = {$requested_shopdetail_id}
                          AND asd.shop_id = {$shop_id}
                          AND asd.appointment_id = {$appointment_id}
                          AND asd.status != 'BOOKED' ";
$shopdetail_check_row = sql_fetch_pg($shopdetail_check_sql);

if (!$shopdetail_check_row || !$shopdetail_check_row['shopdetail_id']) {
    echo json_encode(array('success' => false, 'message' => '해당 가맹점의 shopdetailId가 아닙니다.'));
    exit;
}

// API 서버가 요구하는 대로: 예약에 속한 모든 shopdetailId 조회
$all_shopdetails_sql = " SELECT asd.shopdetail_id, asd.shop_id
                         FROM appointment_shop_detail asd
                         WHERE asd.appointment_id = {$appointment_id}
                         AND asd.status != 'BOOKED'
                         ORDER BY asd.shopdetail_id ";
$all_shopdetails_result = sql_query_pg($all_shopdetails_sql);

if (!$all_shopdetails_result || !is_object($all_shopdetails_result) || !isset($all_shopdetails_result->result)) {
    echo json_encode(array('success' => false, 'message' => '예약에 속한 가맹점 정보를 조회할 수 없습니다.'));
    exit;
}

// JavaScript에서 전송한 취소 정보를 맵으로 저장 (shopdetailId별로 그룹화)
$cancel_info_map = array(); // [shopdetailId][detailId] = quantity
if (isset($request_data['appointmentShops']) && is_array($request_data['appointmentShops'])) {
    foreach ($request_data['appointmentShops'] as $shop) {
        $shopdetail_id = isset($shop['shopdetailId']) ? (int)$shop['shopdetailId'] : 0;
        if ($shopdetail_id > 0 && isset($shop['shopAppointmentDetails']) && is_array($shop['shopAppointmentDetails'])) {
            $cancel_info_map[$shopdetail_id] = array();
            foreach ($shop['shopAppointmentDetails'] as $detail) {
                $detail_id = isset($detail['detailId']) ? (int)$detail['detailId'] : 0;
                if ($detail_id > 0) {
                    $cancel_info_map[$shopdetail_id][$detail_id] = array(
                        'detailId' => $detail_id,
                        'serviceId' => isset($detail['serviceId']) ? (int)$detail['serviceId'] : 0,
                        'quantity' => isset($detail['quantity']) ? (int)$detail['quantity'] : 0  // 취소 후 남을 수량
                    );
                }
            }
        }
    }
}

// API 요청 데이터 구조 재구성
// API 서버가 요구하는 대로: 예약에 속한 모든 shopdetailId를 포함
$api_request_data = array(
    'orderId' => (string)$order_id,  // 규격서: String 타입
    'secretKey' => $secretKey,      // 규격서: String 타입
    'appointmentShops' => array()
);

// 각 shopdetailId에 대해 처리
while ($shopdetail_row = sql_fetch_array_pg($all_shopdetails_result->result)) {
    $shopdetail_id = (int)$shopdetail_row['shopdetail_id'];
    
    // 해당 shopdetailId에 속한 모든 서비스 조회
    $services_sql = " SELECT sad.detail_id,
                             sad.service_id,
                             sad.quantity as current_quantity
                      FROM shop_appointment_details sad
                      WHERE sad.shopdetail_id = {$shopdetail_id}
                      ORDER BY sad.detail_id ";
    $services_result = sql_query_pg($services_sql);
    
    $shop_item = array(
        'shopdetailId' => (string)$shopdetail_id,  // 규격서: String 타입
        'shopAppointmentDetails' => array()
    );
    
    // 모든 서비스를 포함 (취소 정보가 있으면 적용, 없으면 현재 수량 유지)
    // 서비스가 없어도 shopdetailId는 포함해야 함 (API 서버가 모든 shopdetailId를 요구)
    if ($services_result && is_object($services_result) && isset($services_result->result)) {
        while ($service_row = sql_fetch_array_pg($services_result->result)) {
            $detail_id = (int)$service_row['detail_id'];
            $service_id = (int)$service_row['service_id'];
            $current_quantity = (int)$service_row['current_quantity'];
            
            $final_quantity = $current_quantity;  // 기본값: 현재 수량
            
            // 현재 가맹점의 shopdetailId이고 취소 정보가 있으면 취소 후 남을 수량 사용
            if ($shopdetail_id == $requested_shopdetail_id && 
                isset($cancel_info_map[$shopdetail_id][$detail_id])) {
                $final_quantity = $cancel_info_map[$shopdetail_id][$detail_id]['quantity'];
            }
            
            $shop_item['shopAppointmentDetails'][] = array(
                'detailId' => (string)$detail_id,      // 규격서: String 타입
                'serviceId' => (string)$service_id,     // 규격서: String 타입
                'quantity' => (string)$final_quantity   // 규격서: String 타입 (취소 후 남을 수량)
            );
        }
    }
    // 서비스가 없어도 shopdetailId는 포함 (빈 shopAppointmentDetails 배열)
    
    $api_request_data['appointmentShops'][] = $shop_item;
}

$api_url = 'https://api.dainpass.com/api8000/order-service/shop-manager/cancelServices';
$api_data = json_encode($api_request_data);

// ============================================
// 개발 모드: API 요청 전 데이터 확인
// ============================================
$debug_mode = false; // 개발 환경에서만 true로 설정, 운영 환경에서는 false로 변경
if ($debug_mode) {
    // API 호출 전에 디버그 정보를 반환하고 종료
    echo json_encode(array(
        'success' => false,
        'debug' => true,
        'message' => '디버그 모드: API 요청 데이터 확인',
        'api_url' => $api_url,
        'original_request_data' => $request_data,
        'api_request_data' => $api_request_data,
        'request_json' => $api_data,
        'request_json_pretty' => json_encode($api_request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        'keyword' => $keyword,
        'secretKey' => $secretKey,
        'shop_id' => $shop_id,
        'appointment_id' => $appointment_id,
        'order_id' => $order_id
    ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// curl로 API 호출
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $api_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($api_data)
));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// 응답 처리
if ($curl_error) {
    echo json_encode(array('success' => false, 'message' => 'API 호출 중 오류가 발생했습니다: ' . $curl_error));
    exit;
}

if ($http_code == 200) {
    // 성공 응답이 "success" 문자열인 경우
    if (trim($response) === 'success') {
        echo json_encode(array('success' => true, 'message' => '부분 취소가 완료되었습니다.'));
    } else {
        // JSON 응답인 경우 파싱
        $response_data = json_decode($response, true);
        if ($response_data) {
            echo json_encode(array('success' => true, 'message' => '부분 취소가 완료되었습니다.', 'data' => $response_data));
        } else {
            echo json_encode(array('success' => true, 'message' => '부분 취소가 완료되었습니다.'));
        }
    }
} else {
    // 에러 응답 파싱 시도
    $error_data = json_decode($response, true);
    $error_message = '부분 취소 처리 중 오류가 발생했습니다. (HTTP ' . $http_code . ')';
    if ($error_data && isset($error_data['message'])) {
        $error_message = $error_data['message'];
    } else if ($response) {
        $error_message .= ': ' . $response;
    }
    echo json_encode(array('success' => false, 'message' => $error_message));
}

exit;
?>
