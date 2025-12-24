<?php
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

// 에러 발생 시 JSON으로 응답하도록 설정
error_reporting(E_ALL);
ini_set('display_errors', 0);
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => '서버 오류가 발생했습니다: ' . $error['message']], JSON_UNESCAPED_UNICODE);
        exit;
    }
});

// 참조 파일들의 함수를 사용하기 위해 include
// 라이브러리 모드 상수 정의 후 include 해야 각 파일의 ajax 응답 블록이 실행되지 않는다.
if (!defined('SHOP_STAT_LIB_MODE')) {
    define('SHOP_STAT_LIB_MODE', true);
}
require_once('./shop_statistics_sales_data.php');
require_once('./shop_statistics_reservation_data.php');
require_once('./shop_statistics_customer_data.php');
require_once('./shop_statistics_service_review_data.php');
require_once('./shop_statistics_coupon_data.php');

// 공통: 가맹점 접근 권한 및 shop_id 확인
$has_access = false;
$shop_id = 0;

if ($is_member && $member['mb_id']) {
    $mb_sql = " SELECT mb_id, mb_level, mb_1, mb_2, mb_leave_date, mb_intercept_date ".
              " FROM {$g5['member_table']} ".
              " WHERE mb_id = '{$member['mb_id']}' ".
              " AND mb_level >= 4 ".
              " AND ( ".
              "     mb_level >= 6 ".
              "     OR (mb_level < 6 AND mb_2 = 'Y') ".
              " ) ".
              " AND (mb_leave_date = '' OR mb_leave_date IS NULL) ".
              " AND (mb_intercept_date = '' OR mb_intercept_date IS NULL) ";
    $mb_row = sql_fetch($mb_sql, 1);

    if ($mb_row && $mb_row['mb_id']) {
        $mb_1_value = trim($mb_row['mb_1']);

        if ($mb_1_value !== '0' && $mb_1_value !== '') {
            $shop_id_check = (int)$mb_1_value;
            $shop_sql = " SELECT shop_id, status FROM {$g5['shop_table']} WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);

            if ($shop_row && $shop_row['shop_id']) {
                if ($shop_row['status'] == 'pending') {
                    echo json_encode(['success' => false, 'message' => '아직 승인이 되지 않았습니다.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                if ($shop_row['status'] == 'closed') {
                    echo json_encode(['success' => false, 'message' => '폐업된 업체입니다.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                if ($shop_row['status'] == 'shutdown') {
                    echo json_encode(['success' => false, 'message' => '접근이 제한된 업체입니다. 플랫폼 관리자에게 문의하세요.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
            }
        }
    }
}

if (!$has_access || !$shop_id) {
    echo json_encode(['success' => false, 'message' => '접속할 수 없는 페이지 입니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 입력값
$period_type = isset($_POST['period_type']) ? trim($_POST['period_type']) : 'daily';
$start_date  = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
$end_date    = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';

try {
    // 기간 계산
    list($range_start, $range_end) = calculate_date_range($period_type, $start_date, $end_date);

    // 각 통계 페이지의 ajax 파일에서 사용하는 함수를 직접 호출하여 동일한 데이터 사용
    // 매출/정산 통계 데이터 (shop_statistics_sales_data.php의 함수 사용)
    // 참고: get_sales_summary 함수는 날짜 범위와 무관하게 항상 오늘과 이번 달 기준으로 계산합니다
    $sales_summary = get_sales_summary($shop_id, $range_start, $range_end);
    
    // 데이터 검증 및 디버깅
    if (!isset($sales_summary['month_sales_amount'])) {
        error_log("Warning: month_sales_amount is not set in sales_summary. shop_id: {$shop_id}");
        $sales_summary['month_sales_amount'] = 0;
    }
    // 디버깅: 실제 데이터 확인
    error_log("sales_summary month_sales_amount: " . (isset($sales_summary['month_sales_amount']) ? $sales_summary['month_sales_amount'] : 'NOT SET'));
    $daily_sales = get_daily_sales($shop_id, $range_start, $range_end, $period_type);
    $settlement_logs = get_settlement_logs($shop_id, $range_start, $range_end);
    $settlement_deduction = get_settlement_deduction_statistics($shop_id, $range_start, $range_end);
    
    // 정산 처리 내역 최근 10건만
    $settlement_logs = array_slice($settlement_logs, 0, 10);

    // 예약/운영 통계 데이터 (shop_statistics_reservation_data.php의 함수 사용)
    $reservation_summary = get_reservation_summary($shop_id, $range_start, $range_end);
    $daily_appointments = get_daily_appointments($shop_id, $range_start, $range_end, $period_type);
    $hourly_appointments = get_hourly_appointments($shop_id, $range_start, $range_end);
    $weekly_appointments = get_weekly_appointments($shop_id, $range_start, $range_end);

    // 고객 통계 데이터 (shop_statistics_customer_data.php의 함수 사용)
    $customer_summary = get_customer_summary($shop_id, $range_start, $range_end);
    $customer_type_distribution = get_customer_type_distribution($shop_id, $range_start, $range_end);
    $top_customers = get_top_customers($shop_id, $range_start, $range_end);

    // 서비스/리뷰 통계 데이터 (shop_statistics_service_review_data.php의 함수 사용)
    $service_summary = get_service_summary($shop_id, $range_start, $range_end);
    $service_popularity = get_service_popularity($shop_id, $range_start, $range_end);
    $service_sales = get_service_sales($shop_id, $range_start, $range_end);
    $review_summary = get_review_summary($shop_id, $range_start, $range_end);
    
    // 서비스 통계에 리뷰 통계 포함 (shop_statistics_service_review_data.php와 동일하게)
    $service_summary['avg_rating'] = $review_summary['avg_rating'];
    $service_summary['review_count'] = $review_summary['review_count'];

    // 쿠폰 통계 데이터 (shop_statistics_coupon_data.php의 함수 사용)
    $coupon_summary = get_coupon_summary($shop_id, $range_start, $range_end);
    $coupon_issue_use_trend = get_coupon_issue_use_trend($shop_id, $range_start, $range_end, $period_type);
    $discount_amount_trend = get_discount_amount_trend($shop_id, $range_start, $range_end, $period_type);
    
    // 쿠폰 사용률 계산 (shop_statistics_coupon_data.php와 동일하게)
    $coupon_usage_rate_percent = 0;
    if ($coupon_summary['total_coupon_issued'] > 0) {
        $coupon_usage_rate_percent = ($coupon_summary['total_coupon_used'] / $coupon_summary['total_coupon_issued']) * 100;
    }
    $coupon_summary['coupon_usage_rate'] = $coupon_usage_rate_percent;

    // 통합 응답 데이터 구성
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
    echo json_encode([
        'success' => false,
        'message' => '데이터 조회 중 오류가 발생했습니다: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
exit;

