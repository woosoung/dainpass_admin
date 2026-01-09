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

// 쿠폰 통계 요약 정보
if (!function_exists('get_coupon_summary')) {
function get_coupon_summary($shop_id, $range_start, $range_end)
{
    // 총 쿠폰 발급 수
    $sql_issued = "
        SELECT COUNT(*) AS total_issued
        FROM customer_coupons cc
        INNER JOIN shop_coupons sc ON cc.coupon_id = sc.coupon_id
        WHERE sc.shop_id = {$shop_id}
          AND cc.issued_at >= '{$range_start} 00:00:00'
          AND cc.issued_at <= '{$range_end} 23:59:59'
    ";
    $row_issued = sql_fetch_pg($sql_issued);
    $total_issued = (int)($row_issued['total_issued'] ?? 0);

    // 총 쿠폰 사용 수 (기간 내 발급된 쿠폰 중에서 사용된 쿠폰 수)
    // 쿠폰 사용률 계산을 위해 기간 내 발급된 쿠폰 중 사용된 쿠폰을 계산
    $sql_used = "
        SELECT COUNT(*) AS total_used
        FROM customer_coupons cc
        INNER JOIN shop_coupons sc ON cc.coupon_id = sc.coupon_id
        WHERE sc.shop_id = {$shop_id}
          AND cc.issued_at >= '{$range_start} 00:00:00'
          AND cc.issued_at <= '{$range_end} 23:59:59'
          AND (cc.status = 'USED' OR cc.used_at IS NOT NULL)
    ";
    $row_used = sql_fetch_pg($sql_used);
    $total_used = (int)($row_used['total_used'] ?? 0);

    // 총 쿠폰 할인 금액
    $sql_discount = "
        SELECT COALESCE(SUM(coupon_amount), 0) AS total_discount_amount
        FROM appointment_shop_detail
        WHERE shop_id = {$shop_id}
          AND coupon_amount IS NOT NULL
          AND coupon_amount > 0
          AND appointment_datetime >= '{$range_start} 00:00:00'
          AND appointment_datetime <= '{$range_end} 23:59:59'
          AND status != 'BOOKED'
    ";
    $row_discount = sql_fetch_pg($sql_discount);
    $total_discount = (int)($row_discount['total_discount_amount'] ?? 0);

    return [
        'total_coupon_issued' => $total_issued,
        'total_coupon_used' => $total_used,
        'total_coupon_discount' => $total_discount,
    ];
}
}

// 기간별 쿠폰 발급/사용 추이
if (!function_exists('get_coupon_issue_use_trend')) {
function get_coupon_issue_use_trend($shop_id, $range_start, $range_end, $period_type = 'daily')
{
    // period_type에 따라 날짜 그룹화 방식 결정
    if ($period_type == 'weekly') {
        // 주별: 해당 주의 첫 번째 날짜로 그룹화 (월요일 기준)
        $date_group_expr = "DATE_TRUNC('week', CAST(cc.issued_at AS DATE))::DATE";
        $date_group_expr_used = "DATE_TRUNC('week', CAST(cc.used_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('week', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DOW FROM date_series.date) = 1"; // 월요일만
    } elseif ($period_type == 'monthly') {
        // 월별: 해당 월의 첫 번째 날짜로 그룹화
        $date_group_expr = "DATE_TRUNC('month', CAST(cc.issued_at AS DATE))::DATE";
        $date_group_expr_used = "DATE_TRUNC('month', CAST(cc.used_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('month', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DAY FROM date_series.date) = 1"; // 매월 1일만
    } else {
        // 일별
        $date_group_expr = "CAST(cc.issued_at AS DATE)";
        $date_group_expr_used = "CAST(cc.used_at AS DATE)";
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
        issued_data AS (
            SELECT 
                {$date_group_expr} AS period_date,
                COUNT(*) AS issued_count
            FROM customer_coupons cc
            INNER JOIN shop_coupons sc ON cc.coupon_id = sc.coupon_id
            WHERE sc.shop_id = {$shop_id}
              AND cc.issued_at >= '{$range_start} 00:00:00'
              AND cc.issued_at <= '{$range_end} 23:59:59'
            GROUP BY {$date_group_expr}
        ),
        used_data AS (
            SELECT 
                {$date_group_expr_used} AS period_date,
                COUNT(*) AS used_count
            FROM customer_coupons cc
            INNER JOIN shop_coupons sc ON cc.coupon_id = sc.coupon_id
            WHERE sc.shop_id = {$shop_id}
              AND cc.used_at >= '{$range_start} 00:00:00'
              AND cc.used_at <= '{$range_end} 23:59:59'
              AND (cc.status = 'USED' OR cc.used_at IS NOT NULL)
            GROUP BY {$date_group_expr_used}
        )
        SELECT 
            dp.period_date AS date,
            COALESCE(i.issued_count, 0) AS issued_count,
            COALESCE(u.used_count, 0) AS used_count
        FROM date_periods dp
        LEFT JOIN issued_data i ON dp.period_date = i.period_date
        LEFT JOIN used_data u ON dp.period_date = u.period_date
        ORDER BY dp.period_date ASC
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'date' => $row['date'],
                'issued_count' => (int)$row['issued_count'],
                'used_count' => (int)$row['used_count'],
            ];
        }
    }

    return $rows;
}
}

// 쿠폰별 사용률 (상위 10개)
if (!function_exists('get_coupon_usage_rate')) {
function get_coupon_usage_rate($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            sc.coupon_id,
            sc.coupon_name,
            COUNT(cc.customer_coupon_id) AS total_issued,
            COUNT(CASE WHEN cc.used_at IS NOT NULL OR cc.status = 'USED' THEN 1 END) AS total_used,
            CASE 
                WHEN COUNT(cc.customer_coupon_id) > 0 THEN
                    (COUNT(CASE WHEN cc.used_at IS NOT NULL OR cc.status = 'USED' THEN 1 END)::numeric / COUNT(cc.customer_coupon_id)) * 100
                ELSE 0
            END AS usage_rate
        FROM shop_coupons sc
        LEFT JOIN customer_coupons cc ON sc.coupon_id = cc.coupon_id
            AND cc.issued_at >= '{$range_start} 00:00:00'
            AND cc.issued_at <= '{$range_end} 23:59:59'
        WHERE sc.shop_id = {$shop_id}
        GROUP BY sc.coupon_id, sc.coupon_name
        HAVING COUNT(cc.customer_coupon_id) > 0
        ORDER BY usage_rate DESC, total_issued DESC
        LIMIT 10
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'coupon_id' => (int)$row['coupon_id'],
                'coupon_name' => $row['coupon_name'] ?? '',
                'total_issued' => (int)$row['total_issued'],
                'total_used' => (int)$row['total_used'],
                'usage_rate' => (float)$row['usage_rate'],
            ];
        }
    }

    return $rows;
}
}

// 기간별 할인 금액 추이
if (!function_exists('get_discount_amount_trend')) {
function get_discount_amount_trend($shop_id, $range_start, $range_end, $period_type = 'daily')
{
    // period_type에 따라 날짜 그룹화 방식 결정
    if ($period_type == 'weekly') {
        // 주별: 해당 주의 첫 번째 날짜로 그룹화 (월요일 기준)
        $date_group_expr = "DATE_TRUNC('week', CAST(appointment_datetime AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('week', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DOW FROM date_series.date) = 1"; // 월요일만
    } elseif ($period_type == 'monthly') {
        // 월별: 해당 월의 첫 번째 날짜로 그룹화
        $date_group_expr = "DATE_TRUNC('month', CAST(appointment_datetime AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('month', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DAY FROM date_series.date) = 1"; // 매월 1일만
    } else {
        // 일별
        $date_group_expr = "CAST(appointment_datetime AS DATE)";
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
        discount_data AS (
            SELECT 
                {$date_group_expr} AS period_date,
                COALESCE(SUM(coupon_amount), 0) AS discount_amount
            FROM appointment_shop_detail
            WHERE shop_id = {$shop_id}
              AND coupon_amount IS NOT NULL
              AND coupon_amount > 0
              AND appointment_datetime >= '{$range_start} 00:00:00'
              AND appointment_datetime <= '{$range_end} 23:59:59'
              AND status != 'BOOKED'
            GROUP BY {$date_group_expr}
        )
        SELECT 
            dp.period_date AS date,
            COALESCE(d.discount_amount, 0) AS discount_amount
        FROM date_periods dp
        LEFT JOIN discount_data d ON dp.period_date = d.period_date
        ORDER BY dp.period_date ASC
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'date' => $row['date'],
                'discount_amount' => (int)$row['discount_amount'],
            ];
        }
    }

    return $rows;
}
}

// 쿠폰별 상세 통계
if (!function_exists('get_coupon_detail_statistics')) {
function get_coupon_detail_statistics($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            sc.coupon_id,
            sc.coupon_name,
            sc.coupon_code,
            COUNT(cc.customer_coupon_id) AS issued_count,
            COUNT(CASE WHEN cc.used_at IS NOT NULL OR cc.status = 'USED' THEN 1 END) AS used_count,
            CASE 
                WHEN COUNT(cc.customer_coupon_id) > 0 THEN
                    (COUNT(CASE WHEN cc.used_at IS NOT NULL OR cc.status = 'USED' THEN 1 END)::numeric / COUNT(cc.customer_coupon_id)) * 100
                ELSE 0
            END AS usage_rate,
            COALESCE(SUM(CASE WHEN asd.coupon_amount IS NOT NULL THEN asd.coupon_amount ELSE 0 END), 0) AS total_discount_amount
        FROM shop_coupons sc
        LEFT JOIN customer_coupons cc ON sc.coupon_id = cc.coupon_id
            AND cc.issued_at >= '{$range_start} 00:00:00'
            AND cc.issued_at <= '{$range_end} 23:59:59'
        LEFT JOIN appointment_shop_detail asd ON asd.customer_coupon_id = cc.customer_coupon_id
            AND asd.appointment_datetime >= '{$range_start} 00:00:00'
            AND asd.appointment_datetime <= '{$range_end} 23:59:59'
        WHERE sc.shop_id = {$shop_id}
        GROUP BY sc.coupon_id, sc.coupon_name, sc.coupon_code
        ORDER BY issued_count DESC, used_count DESC
        LIMIT 20
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'coupon_id' => (int)$row['coupon_id'],
                'coupon_name' => $row['coupon_name'] ?? '',
                'coupon_code' => $row['coupon_code'] ?? '',
                'issued_count' => (int)$row['issued_count'],
                'used_count' => (int)$row['used_count'],
                'usage_rate' => (float)$row['usage_rate'],
                'total_discount_amount' => (int)$row['total_discount_amount'],
            ];
        }
    }

    return $rows;
}
}

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
    $start_date  = trim($_POST['start_date']);
    $end_date    = trim($_POST['end_date']);

    // 날짜 및 period_type 검증 및 보정
    list($period_type, $start_date, $end_date) = validate_and_sanitize_statistics_params($period_type, $start_date, $end_date);

    try {
        // 기간 계산
        list($range_start, $range_end) = calculate_date_range($period_type, $start_date, $end_date);

        // 통계 데이터 조회
        $coupon_summary = get_coupon_summary($shop_id, $range_start, $range_end);
        $coupon_issue_use_trend = get_coupon_issue_use_trend($shop_id, $range_start, $range_end, $period_type);
        $coupon_usage_rate = get_coupon_usage_rate($shop_id, $range_start, $range_end);
        $discount_amount_trend = get_discount_amount_trend($shop_id, $range_start, $range_end, $period_type);
        $coupon_detail_statistics = get_coupon_detail_statistics($shop_id, $range_start, $range_end);

        // 쿠폰 사용률 계산
        $coupon_usage_rate_percent = 0;
        if ($coupon_summary['total_coupon_issued'] > 0) {
            $coupon_usage_rate_percent = ($coupon_summary['total_coupon_used'] / $coupon_summary['total_coupon_issued']) * 100;
        }
        $coupon_summary['coupon_usage_rate'] = $coupon_usage_rate_percent;

        // summary
        $summary = $coupon_summary;

        // 출력 버퍼 정리 후 JSON 출력
        ob_clean();
        echo json_encode([
            'success' => true,
            'period_type' => $period_type,
            'range_start' => $range_start,
            'range_end' => $range_end,
            'summary' => $summary,
            'coupon_issue_use_trend' => $coupon_issue_use_trend,
            'coupon_usage_rate' => $coupon_usage_rate,
            'discount_amount_trend' => $discount_amount_trend,
            'coupon_detail_statistics' => $coupon_detail_statistics,
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

