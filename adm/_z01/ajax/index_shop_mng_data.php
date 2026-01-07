<?php
// 출력 버퍼링 시작 - 예상치 못한 출력 방지
ob_start();

include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

// 에러 발생 시 JSON으로 응답하도록 설정
error_reporting(E_ALL);
ini_set('display_errors', 0);
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => '서버 오류가 발생했습니다: ' . $error['message']], JSON_UNESCAPED_UNICODE);
        exit;
    }
});

// 라이브러리 모드로 각 통계 AJAX 파일의 함수들을 로드
if (!defined('SHOP_STAT_LIB_MODE')) {
    define('SHOP_STAT_LIB_MODE', true);
}

// 출력 버퍼 정리 후 함수들 로드
ob_clean();
require_once('./shop_statistics_sales_data.php');
require_once('./shop_statistics_reservation_data.php');
require_once('./shop_statistics_customer_data.php');
require_once('./shop_statistics_service_review_data.php');
require_once('./shop_statistics_coupon_data.php');

// 공통: 가맹점 접근 권한 및 shop_id 확인
$result = check_shop_access();
$shop_id = $result['shop_id'];

if (!$shop_id) {
    echo json_encode(['success' => false, 'message' => '접속할 수 없는 페이지 입니다.']);
    exit;
}

// 입력값
$period_type = isset($_POST['period_type']) ? trim($_POST['period_type']) : 'daily';
$start_date  = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
$end_date    = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';

try {
    // 기간 계산
    list($range_start, $range_end) = calculate_date_range($period_type, $start_date, $end_date);

    // 매출/정산 통계 데이터
    $sales_summary = get_sales_summary($shop_id, $range_start, $range_end);
    $daily_sales = get_daily_sales($shop_id, $range_start, $range_end, $period_type);
    $settlement_logs = get_settlement_logs($shop_id, $range_start, $range_end);
    $settlement_deduction = get_settlement_deduction_statistics($shop_id, $range_start, $range_end);

    // 예약/운영 통계 데이터
    $reservation_summary = get_reservation_summary($shop_id, $range_start, $range_end);
    $daily_appointments = get_daily_appointments($shop_id, $range_start, $range_end, $period_type);
    $hourly_appointments = get_hourly_appointments($shop_id, $range_start, $range_end);
    $weekly_appointments = get_weekly_appointments($shop_id, $range_start, $range_end);

    // 고객 통계 데이터
    $customer_summary = get_customer_summary($shop_id, $range_start, $range_end);
    $customer_type_distribution = get_customer_type_distribution($shop_id, $range_start, $range_end);
    $top_customers = get_top_customers($shop_id, $range_start, $range_end);

    // 서비스/리뷰 통계 데이터
    $service_summary = get_service_summary($shop_id, $range_start, $range_end);
    $service_popularity = get_service_popularity($shop_id, $range_start, $range_end);
    $service_sales = get_service_sales($shop_id, $range_start, $range_end);
    $review_summary = get_review_summary($shop_id, $range_start, $range_end);
    
    // summary에 리뷰 통계도 포함
    $service_summary['avg_rating'] = $review_summary['avg_rating'];
    $service_summary['review_count'] = $review_summary['review_count'];

    // 쿠폰 통계 데이터
    $coupon_summary = get_coupon_summary($shop_id, $range_start, $range_end);
    $coupon_issue_use_trend = get_coupon_issue_use_trend($shop_id, $range_start, $range_end, $period_type);
    $discount_amount_trend = get_discount_amount_trend($shop_id, $range_start, $range_end, $period_type);

    // 쿠폰 사용률 계산
    $coupon_usage_rate_percent = 0;
    if ($coupon_summary['total_coupon_issued'] > 0) {
        $coupon_usage_rate_percent = ($coupon_summary['total_coupon_used'] / $coupon_summary['total_coupon_issued']) * 100;
    }
    $coupon_summary['coupon_usage_rate'] = $coupon_usage_rate_percent;

    // 통합 응답 데이터 구성
    // 출력 버퍼 정리 후 JSON 출력
    ob_clean();
    echo json_encode([
        'success' => true,
        'period_type' => $period_type,
        'range_start' => $range_start,
        'range_end' => $range_end,
        
        // 매출/정산 통계
        'sales_summary' => $sales_summary,
        'daily_sales' => $daily_sales,
        'settlement_logs' => $settlement_logs,
        'settlement_deduction' => $settlement_deduction,
        
        // 예약/운영 통계
        'reservation_summary' => $reservation_summary,
        'daily_appointments' => $daily_appointments,
        'hourly_appointments' => $hourly_appointments,
        'weekly_appointments' => $weekly_appointments,
        
        // 고객 통계
        'customer_summary' => $customer_summary,
        'customer_type_distribution' => $customer_type_distribution,
        'top_customers' => $top_customers,
        
        // 서비스/리뷰 통계
        'service_summary' => $service_summary,
        'service_popularity' => $service_popularity,
        'service_sales' => $service_sales,
        
        // 쿠폰 통계
        'coupon_summary' => $coupon_summary,
        'coupon_issue_use_trend' => $coupon_issue_use_trend,
        'discount_amount_trend' => $discount_amount_trend,
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => '데이터 조회 중 오류가 발생했습니다: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
ob_end_flush();
exit;

