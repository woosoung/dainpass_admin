<?php
// 출력 버퍼링 시작 - 예상치 못한 출력 방지
ob_start();

include_once('./_common.php');

ob_clean();
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

// =========================
// Helper functions
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
            // 기본: 한 달 전부터 오늘까지
            $end = clone $today;
            $start = (clone $today)->modify('-1 month');
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
    // 총 서비스 수 (활성화된 서비스)
    $sql_total = "
        SELECT COUNT(*) AS total_services
        FROM shop_services
        WHERE shop_id = {$shop_id}
          AND status = 'active'
    ";
    $row_total = sql_fetch_pg($sql_total);
    $total_services = (int)$row_total['total_services'];

    // 서비스별 평균 가격
    $sql_avg_price = "
        SELECT AVG(price) AS avg_service_price
        FROM shop_services
        WHERE shop_id = {$shop_id}
          AND status = 'active'
    ";
    $row_avg_price = sql_fetch_pg($sql_avg_price);
    $avg_service_price = (float)$row_avg_price['avg_service_price'];

    // 총 서비스 매출 (기간 내)
    $sql_sales = "
        SELECT COALESCE(SUM(sad.price * sad.quantity), 0) AS total_service_sales
        FROM shop_appointment_details sad
        INNER JOIN appointment_shop_detail asd ON sad.shopdetail_id = asd.shopdetail_id
        WHERE asd.shop_id = {$shop_id}
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
          AND asd.status != 'BOOKED'
    ";
    $row_sales = sql_fetch_pg($sql_sales);
    $total_service_sales = (float)$row_sales['total_service_sales'];

    // 평균 평점
    $sql_rating = "
        SELECT AVG(sr_score) AS avg_rating
        FROM shop_review
        WHERE shop_id = {$shop_id}
          AND sr_deleted = 'N'
          AND sr_created_at >= '{$range_start} 00:00:00'
          AND sr_created_at <= '{$range_end} 23:59:59'
    ";
    $row_rating = sql_fetch_pg($sql_rating);
    $avg_rating = (float)$row_rating['avg_rating'];

    // 리뷰 건수
    $sql_review = "
        SELECT COUNT(*) AS review_count
        FROM shop_review
        WHERE shop_id = {$shop_id}
          AND sr_deleted = 'N'
          AND sr_created_at >= '{$range_start} 00:00:00'
          AND sr_created_at <= '{$range_end} 23:59:59'
    ";
    $row_review = sql_fetch_pg($sql_review);
    $review_count = (int)$row_review['review_count'];

    // 서비스별 평균 예약 건수
    $sql_avg_appt = "
        SELECT 
            COUNT(DISTINCT sad.service_id) AS service_count,
            COUNT(*) AS total_appointments
        FROM shop_appointment_details sad
        INNER JOIN appointment_shop_detail asd ON sad.shopdetail_id = asd.shopdetail_id
        WHERE asd.shop_id = {$shop_id}
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
          AND asd.status != 'BOOKED'
    ";
    $row_avg_appt = sql_fetch_pg($sql_avg_appt);
    $service_count = (int)$row_avg_appt['service_count'];
    $total_appointments = (int)$row_avg_appt['total_appointments'];
    $avg_appointment_per_service = 0.0;
    if ($service_count > 0) {
        $avg_appointment_per_service = round($total_appointments / $service_count, 2);
    }

    return [
        'total_services' => $total_services,
        'avg_service_price' => $avg_service_price,
        'total_service_sales' => $total_service_sales,
        'avg_rating' => $avg_rating,
        'review_count' => $review_count,
        'avg_appointment_per_service' => $avg_appointment_per_service,
    ];
}
}

// 서비스별 예약 건수 (상위 10개)
if (!function_exists('get_service_popularity')) {
function get_service_popularity($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            sad.service_id,
            ss.service_name,
            COUNT(*) AS appointment_count
        FROM shop_appointment_details sad
        INNER JOIN appointment_shop_detail asd ON sad.shopdetail_id = asd.shopdetail_id
        LEFT JOIN shop_services ss ON sad.service_id = ss.service_id
        WHERE asd.shop_id = {$shop_id}
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
          AND asd.status != 'BOOKED'
        GROUP BY sad.service_id, ss.service_name
        ORDER BY appointment_count DESC
        LIMIT 10
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'service_id' => (int)$row['service_id'],
                'service_name' => $row['service_name'],
                'appointment_count' => (int)$row['appointment_count'],
            ];
        }
    }

    return $rows;
}
}

// 서비스별 매출 (상위 10개)
if (!function_exists('get_service_sales')) {
function get_service_sales($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            sad.service_id,
            ss.service_name,
            COALESCE(SUM(sad.price * sad.quantity), 0) AS total_sales
        FROM shop_appointment_details sad
        INNER JOIN appointment_shop_detail asd ON sad.shopdetail_id = asd.shopdetail_id
        LEFT JOIN shop_services ss ON sad.service_id = ss.service_id
        WHERE asd.shop_id = {$shop_id}
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
          AND asd.status != 'BOOKED'
        GROUP BY sad.service_id, ss.service_name
        ORDER BY total_sales DESC
        LIMIT 10
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'service_id' => (int)$row['service_id'],
                'service_name' => $row['service_name'],
                'total_sales' => (float)$row['total_sales'],
            ];
        }
    }

    return $rows;
}
}

// 리뷰 통계 요약 정보
if (!function_exists('get_review_summary')) {
function get_review_summary($shop_id, $range_start, $range_end)
{
    // 평균 평점
    $sql_rating = "
        SELECT AVG(sr_score) AS avg_rating
        FROM shop_review
        WHERE shop_id = {$shop_id}
          AND sr_deleted = 'N'
          AND sr_created_at >= '{$range_start} 00:00:00'
          AND sr_created_at <= '{$range_end} 23:59:59'
    ";
    $row_rating = sql_fetch_pg($sql_rating);
    $avg_rating = $row_rating && isset($row_rating['avg_rating']) ? (float)$row_rating['avg_rating'] : 0.0;

    // 리뷰 건수
    $sql_review = "
        SELECT COUNT(*) AS review_count
        FROM shop_review
        WHERE shop_id = {$shop_id}
          AND sr_deleted = 'N'
          AND sr_created_at >= '{$range_start} 00:00:00'
          AND sr_created_at <= '{$range_end} 23:59:59'
    ";
    $row_review = sql_fetch_pg($sql_review);
    $review_count = $row_review && isset($row_review['review_count']) ? (int)$row_review['review_count'] : 0;

    return [
        'avg_rating' => $avg_rating,
        'review_count' => $review_count,
    ];
}
}

// 기간별 리뷰 추이
if (!function_exists('get_review_trend')) {
function get_review_trend($shop_id, $range_start, $range_end, $period_type = 'daily')
{
    // period_type에 따라 날짜 그룹화 방식 결정
    if ($period_type == 'weekly') {
        $date_group_expr = "DATE_TRUNC('week', CAST(sr_created_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('week', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DOW FROM date_series.date) = 1"; // 월요일만
    } elseif ($period_type == 'monthly') {
        $date_group_expr = "DATE_TRUNC('month', CAST(sr_created_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('month', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DAY FROM date_series.date) = 1"; // 매월 1일만
    } else {
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
        ORDER BY sr_score ASC
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

// 서비스별 상세 통계 (상위 20개)
if (!function_exists('get_service_details')) {
function get_service_details($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            sad.service_id,
            ss.service_name,
            ss.price AS service_price,
            COUNT(*) AS appointment_count,
            COALESCE(SUM(sad.price * sad.quantity), 0) AS total_sales,
            CASE 
                WHEN COUNT(*) > 0 THEN COALESCE(SUM(sad.price * sad.quantity), 0) / COUNT(*)
                ELSE 0
            END AS avg_sales_per_appointment
        FROM shop_appointment_details sad
        INNER JOIN appointment_shop_detail asd ON sad.shopdetail_id = asd.shopdetail_id
        LEFT JOIN shop_services ss ON sad.service_id = ss.service_id
        WHERE asd.shop_id = {$shop_id}
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
          AND asd.status != 'BOOKED'
        GROUP BY sad.service_id, ss.service_name, ss.price
        ORDER BY total_sales DESC
        LIMIT 20
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'service_id' => (int)$row['service_id'],
                'service_name' => $row['service_name'],
                'service_price' => (float)$row['service_price'],
                'appointment_count' => (int)$row['appointment_count'],
                'total_sales' => (float)$row['total_sales'],
                'avg_sales_per_appointment' => (float)$row['avg_sales_per_appointment'],
            ];
        }
    }

    return $rows;
}
}

// =========================
// Main Logic
// =========================
// 공통: 가맹점 접근 권한 및 shop_id 확인
// 이 블록은 단독 ajax 호출일 때만 실행되고,
// 다른 파일에서 라이브러리처럼 include 할 때는 실행되지 않도록 가드한다.
if (!defined('SHOP_STAT_LIB_MODE')) {
    // POST 요청만 허용
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_clean();
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => '허용되지 않은 요청 방식입니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 입력값 검증 - 필수 파라미터 확인
    if (!isset($_POST['period_type']) || !isset($_POST['start_date']) || !isset($_POST['end_date'])) {
        ob_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '필수 파라미터가 누락되었습니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 가맹점 접근 권한 체크
    $result = check_shop_access();
    $shop_id = $result['shop_id'];

    if (!$shop_id) {
        ob_clean();
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => '접속할 수 없는 페이지 입니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 입력값
    $period_type = trim($_POST['period_type']);
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);

    // 날짜 및 period_type 검증 및 보정
    list($period_type, $start_date, $end_date) = validate_and_sanitize_statistics_params($period_type, $start_date, $end_date);

    try {

        // 날짜 범위 계산
        list($range_start, $range_end) = calculate_date_range($period_type, $start_date, $end_date);

        // 데이터 조회
        $summary = get_service_summary($shop_id, $range_start, $range_end);
        $service_popularity = get_service_popularity($shop_id, $range_start, $range_end);
        $service_sales = get_service_sales($shop_id, $range_start, $range_end);
        $review_trend = get_review_trend($shop_id, $range_start, $range_end, $period_type);
        $rating_distribution = get_rating_distribution($shop_id, $range_start, $range_end);
        $service_details = get_service_details($shop_id, $range_start, $range_end);

        // summary에 리뷰 통계도 포함
        $review_summary = get_review_summary($shop_id, $range_start, $range_end);
        $summary['avg_rating'] = $review_summary['avg_rating'];
        $summary['review_count'] = $review_summary['review_count'];

        // 응답
        // 출력 버퍼 정리 후 JSON 출력
        ob_clean();
        echo json_encode([
            'success' => true,
            'period_type' => $period_type,
            'range_start' => $range_start,
            'range_end' => $range_end,
            'summary' => $summary,
            'service_popularity' => $service_popularity,
            'service_sales' => $service_sales,
            'review_trend' => $review_trend,
            'rating_distribution' => $rating_distribution,
            'service_details' => $service_details,
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
}

