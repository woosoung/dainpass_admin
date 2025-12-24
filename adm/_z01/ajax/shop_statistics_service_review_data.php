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

// =========================
// Helper functions (함수 정의를 먼저)
// =========================

if (!function_exists('calculate_date_range')) {
function calculate_date_range($period_type, $start_date, $end_date)
{
    $today = new DateTime('today');

    // start_date와 end_date가 모두 제공되면 custom으로 처리
    if ($start_date && $end_date) {
        $start = DateTime::createFromFormat('Y-m-d', $start_date);
        $end   = DateTime::createFromFormat('Y-m-d', $end_date);
        
        if ($start && $end) {
            // 날짜 유효성 검사
            if ($start > $end) {
                $tmp = $start;
                $start = $end;
                $end = $tmp;
            }
            return [$start->format('Y-m-d'), $end->format('Y-m-d')];
        }
    }

    // start_date만 제공되면 해당 날짜 기준으로 처리
    if ($start_date && !$end_date) {
        $start = DateTime::createFromFormat('Y-m-d', $start_date);
        if ($start) {
            $end = clone $start;
            switch ($period_type) {
                case 'weekly':
                    $end->modify('+6 days');
                    break;
                case 'monthly':
                    $end->modify('last day of this month');
                    break;
                case 'daily':
                default:
                    $end = clone $today;
                    break;
            }
            return [$start->format('Y-m-d'), $end->format('Y-m-d')];
        }
    }

    // period_type에 따라 기본 기간 계산
    switch ($period_type) {
        case 'weekly':
            // 최근 7일
            $end = clone $today;
            $start = (clone $today)->modify('-6 days');
            break;
        case 'monthly':
            // 이번 달 1일부터 오늘까지
            $end = clone $today;
            $start = new DateTime($today->format('Y-m-01'));
            break;
        case 'daily':
        default:
            // 기본: 최근 30일
            $end = clone $today;
            $start = (clone $today)->modify('-29 days');
            break;
    }

    if ($start > $end) {
        $tmp = $start;
        $start = $end;
        $end = $tmp;
    }

    return [$start->format('Y-m-d'), $end->format('Y-m-d')];
}
}

// 서비스 통계 요약 정보
if (!function_exists('get_service_summary')) {
function get_service_summary($shop_id, $range_start, $range_end)
{
    // 총 서비스 수 (활성화된 서비스만)
    $sql_total = "
        SELECT COUNT(*) AS total_services
        FROM shop_services
        WHERE shop_id = {$shop_id}
          AND status = 'active'
    ";
    $row_total = sql_fetch_pg($sql_total);
    $total_services = (int)($row_total['total_services'] ?? 0);

    // 평균 서비스 가격
    $sql_avg_price = "
        SELECT 
            AVG(price) AS avg_service_price,
            COUNT(*) AS total_services
        FROM shop_services
        WHERE shop_id = {$shop_id}
          AND status = 'active'
          AND price IS NOT NULL
    ";
    $row_avg_price = sql_fetch_pg($sql_avg_price);
    $avg_service_price = (float)($row_avg_price['avg_service_price'] ?? 0);

    // 총 서비스 매출
    $sql_total_sales = "
        SELECT 
            COALESCE(SUM(asd.balance_amount), 0) AS total_service_sales
        FROM shop_services ss
        INNER JOIN shop_appointment_details sad ON ss.service_id = sad.service_id
        INNER JOIN appointment_shop_detail asd ON sad.shopdetail_id = asd.shopdetail_id
        WHERE ss.shop_id = {$shop_id}
          AND ss.status = 'active'
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
          AND asd.status != 'BOOKED'
    ";
    $row_total_sales = sql_fetch_pg($sql_total_sales);
    $total_service_sales = (int)($row_total_sales['total_service_sales'] ?? 0);

    // 서비스별 평균 예약 건수
    $sql_avg_appointment = "
        SELECT 
            CASE 
                WHEN COUNT(DISTINCT ss.service_id) > 0 THEN
                    COUNT(DISTINCT sad.shopdetail_id)::numeric / COUNT(DISTINCT ss.service_id)
                ELSE 0
            END AS avg_appointment_per_service
        FROM shop_services ss
        LEFT JOIN shop_appointment_details sad ON ss.service_id = sad.service_id
        LEFT JOIN appointment_shop_detail asd ON sad.shopdetail_id = asd.shopdetail_id
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
          AND (asd.status != 'BOOKED' OR asd.status IS NULL)
        WHERE ss.shop_id = {$shop_id}
          AND ss.status = 'active'
    ";
    $row_avg_appointment = sql_fetch_pg($sql_avg_appointment);
    $avg_appointment_per_service = (float)($row_avg_appointment['avg_appointment_per_service'] ?? 0);

    return [
        'total_services' => $total_services,
        'avg_service_price' => $avg_service_price,
        'total_service_sales' => $total_service_sales,
        'avg_appointment_per_service' => $avg_appointment_per_service,
    ];
}
}

// 서비스별 인기도 (예약 건수 기준)
if (!function_exists('get_service_popularity')) {
function get_service_popularity($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            ss.service_id,
            ss.service_name,
            COUNT(DISTINCT sad.shopdetail_id) AS appointment_count
        FROM shop_services ss
        LEFT JOIN shop_appointment_details sad ON ss.service_id = sad.service_id
        LEFT JOIN appointment_shop_detail asd ON sad.shopdetail_id = asd.shopdetail_id
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
          AND (asd.status != 'BOOKED' OR asd.status IS NULL)
        WHERE ss.shop_id = {$shop_id}
          AND ss.status = 'active'
        GROUP BY ss.service_id, ss.service_name
        ORDER BY appointment_count DESC
        LIMIT 10
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'service_id' => (int)$row['service_id'],
                'service_name' => $row['service_name'] ?? '',
                'appointment_count' => (int)$row['appointment_count'],
            ];
        }
    }

    return $rows;
}
}

// 서비스별 매출
if (!function_exists('get_service_sales')) {
function get_service_sales($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            ss.service_id,
            ss.service_name,
            COALESCE(SUM(asd.balance_amount), 0) AS total_sales,
            COUNT(DISTINCT sad.shopdetail_id) AS appointment_count
        FROM shop_services ss
        LEFT JOIN shop_appointment_details sad ON ss.service_id = sad.service_id
        LEFT JOIN appointment_shop_detail asd ON sad.shopdetail_id = asd.shopdetail_id
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
          AND asd.status != 'BOOKED'
        WHERE ss.shop_id = {$shop_id}
          AND ss.status = 'active'
        GROUP BY ss.service_id, ss.service_name
        ORDER BY total_sales DESC
        LIMIT 10
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'service_id' => (int)$row['service_id'],
                'service_name' => $row['service_name'] ?? '',
                'total_sales' => (int)$row['total_sales'],
                'appointment_count' => (int)$row['appointment_count'],
            ];
        }
    }

    return $rows;
}
}

// 리뷰 통계 요약
if (!function_exists('get_review_summary')) {
function get_review_summary($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            AVG(sr_score) AS avg_rating,
            COUNT(*) AS review_count
        FROM shop_review
        WHERE shop_id = {$shop_id}
          AND sr_deleted = 'N'
          AND sr_created_at >= '{$range_start} 00:00:00'
          AND sr_created_at <= '{$range_end} 23:59:59'
    ";
    $row = sql_fetch_pg($sql);

    return [
        'avg_rating' => (float)($row['avg_rating'] ?? 0),
        'review_count' => (int)($row['review_count'] ?? 0),
    ];
}
}

// 기간별 리뷰 추이
if (!function_exists('get_review_trend')) {
function get_review_trend($shop_id, $range_start, $range_end, $period_type = 'daily')
{
    // period_type에 따라 날짜 그룹화 방식 결정
    if ($period_type == 'weekly') {
        // 주별: 해당 주의 첫 번째 날짜로 그룹화 (월요일 기준)
        $date_group_expr = "DATE_TRUNC('week', CAST(sr_created_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('week', date_series.date)::DATE";
        // 각 주의 월요일만 추출 (DOW: 0=일요일, 1=월요일, ..., 6=토요일)
        $date_series_filter = "AND EXTRACT(DOW FROM date_series.date) = 1"; // 월요일만
    } elseif ($period_type == 'monthly') {
        // 월별: 해당 월의 첫 번째 날짜로 그룹화
        $date_group_expr = "DATE_TRUNC('month', CAST(sr_created_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('month', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DAY FROM date_series.date) = 1"; // 매월 1일만
    } else {
        // 일별
        $date_group_expr = "CAST(sr_created_at AS DATE)";
        $date_series_expr = "date_series.date";
        $date_series_filter = "";
    }
    
    $sql = "
        WITH date_series AS (
            SELECT generate_series(
                '{$range_start}'::DATE,
                '{$range_end}'::DATE,
                '1 day'::INTERVAL
            )::DATE AS date
        ),
        date_periods AS (
            SELECT DISTINCT {$date_series_expr} AS period_date
            FROM date_series
            WHERE 1=1 {$date_series_filter}
            ORDER BY period_date
        ),
        review_data AS (
            SELECT 
                {$date_group_expr} AS period_date,
                COUNT(*) AS review_count,
                AVG(sr_score) AS avg_rating
            FROM shop_review
            WHERE shop_id = {$shop_id}
              AND sr_deleted = 'N'
              AND sr_created_at >= '{$range_start} 00:00:00'
              AND sr_created_at <= '{$range_end} 23:59:59'
            GROUP BY {$date_group_expr}
        )
        SELECT 
            dp.period_date AS date,
            COALESCE(r.review_count, 0) AS review_count,
            COALESCE(r.avg_rating, 0) AS avg_rating
        FROM date_periods dp
        LEFT JOIN review_data r ON dp.period_date = r.period_date
        ORDER BY dp.period_date ASC
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'date' => $row['date'],
                'review_count' => (int)$row['review_count'],
                'avg_rating' => (float)$row['avg_rating'],
            ];
        }
    }

    return $rows;
}
}

// 평점별 리뷰 건수 분포
if (!function_exists('get_rating_distribution')) {
function get_rating_distribution($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            sr_score AS rating,
            COUNT(*) AS review_count
        FROM shop_review
        WHERE shop_id = {$shop_id}
          AND sr_deleted = 'N'
          AND sr_created_at >= '{$range_start} 00:00:00'
          AND sr_created_at <= '{$range_end} 23:59:59'
        GROUP BY sr_score
        ORDER BY sr_score DESC
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'rating' => (int)$row['rating'],
                'review_count' => (int)$row['review_count'],
            ];
        }
    }

    return $rows;
}
}

// 서비스별 상세 통계
if (!function_exists('get_service_details')) {
function get_service_details($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            ss.service_id,
            ss.service_name,
            ss.price AS service_price,
            COUNT(DISTINCT sad.shopdetail_id) AS appointment_count,
            COALESCE(SUM(asd.balance_amount), 0) AS total_sales,
            CASE 
                WHEN COUNT(DISTINCT sad.shopdetail_id) > 0 THEN
                    COALESCE(SUM(asd.balance_amount), 0)::numeric / COUNT(DISTINCT sad.shopdetail_id)
                ELSE 0
            END AS avg_sales_per_appointment
        FROM shop_services ss
        LEFT JOIN shop_appointment_details sad ON ss.service_id = sad.service_id
        LEFT JOIN appointment_shop_detail asd ON sad.shopdetail_id = asd.shopdetail_id
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
          AND (asd.status != 'BOOKED' OR asd.status IS NULL)
        WHERE ss.shop_id = {$shop_id}
          AND ss.status = 'active'
        GROUP BY ss.service_id, ss.service_name, ss.price
        ORDER BY appointment_count DESC, total_sales DESC
        LIMIT 20
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'service_id' => (int)$row['service_id'],
                'service_name' => $row['service_name'] ?? '',
                'service_price' => (int)$row['service_price'],
                'appointment_count' => (int)$row['appointment_count'],
                'total_sales' => (int)$row['total_sales'],
                'avg_sales_per_appointment' => (float)$row['avg_sales_per_appointment'],
            ];
        }
    }

    return $rows;
}
}

// 공통: 가맹점 접근 권한 및 shop_id 확인 (페이지와 동일 로직이지만 JSON으로 응답)
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

    // 통계 데이터 조회
    $summary = get_service_summary($shop_id, $range_start, $range_end);
    $service_popularity = get_service_popularity($shop_id, $range_start, $range_end);
    $service_sales = get_service_sales($shop_id, $range_start, $range_end);
    $review_summary = get_review_summary($shop_id, $range_start, $range_end);
    $review_trend = get_review_trend($shop_id, $range_start, $range_end, $period_type);
    $rating_distribution = get_rating_distribution($shop_id, $range_start, $range_end);
    $service_details = get_service_details($shop_id, $range_start, $range_end);

    // summary에 리뷰 통계도 포함
    $summary['avg_rating'] = $review_summary['avg_rating'];
    $summary['review_count'] = $review_summary['review_count'];

    echo json_encode([
        'success' => true,
        'period_type' => $period_type,
        'range_start' => $range_start,
        'range_end' => $range_end,
        'summary' => $summary,
        'service_popularity' => $service_popularity,
        'service_sales' => $service_sales,
        'review_summary' => $review_summary,
        'review_trend' => $review_trend,
        'rating_distribution' => $rating_distribution,
        'service_details' => $service_details,
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '데이터 조회 중 오류가 발생했습니다: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
exit;

