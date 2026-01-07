<?php
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

// 플랫폼 관리자만 접근 가능 ($is_manager == true)
if (!$is_manager) {
    echo json_encode(['success' => false, 'message' => '플랫폼 관리자만 접근할 수 있습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

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
                default:
                    $end = clone $today;
            }
            return [$start->format('Y-m-d'), $end->format('Y-m-d')];
        }
    }

    // period_type에 따라 기본 기간 설정
    $start = clone $today;
    $end = clone $today;

    switch ($period_type) {
        case 'weekly':
            // 이번 주 월요일부터 일요일까지
            $start->modify('monday this week');
            $end->modify('sunday this week');
            break;
        case 'monthly':
            // 이번 달 1일부터 마지막 날까지
            $start->modify('first day of this month');
            $end->modify('last day of this month');
            break;
        case 'custom':
            // custom은 start_date와 end_date가 필요하므로 오늘부터 오늘까지
            break;
        default: // daily
            // 오늘부터 오늘까지
            break;
    }

    return [$start->format('Y-m-d'), $end->format('Y-m-d')];
}
}

// 요청 파라미터 받기
$period_type = isset($_POST['period_type']) ? $_POST['period_type'] : 'daily';
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
$category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? trim($_POST['category_id']) : '';
$region = isset($_POST['region']) && $_POST['region'] !== '' ? trim($_POST['region']) : '';

// 기간 계산
list($range_start, $range_end) = calculate_date_range($period_type, $start_date, $end_date);

// PostgreSQL 이스케이프 헬퍼
global $g5;
$pg_link = isset($g5['connect_pg']) ? $g5['connect_pg'] : null;
$esc = function($v) use ($pg_link) {
    $s = (string)$v;
    return $pg_link && function_exists('pg_escape_string') ? pg_escape_string($pg_link, $s) : addslashes($s);
};

// 업종 필터링 조건 (shop 테이블 기준)
$category_filter_sql = '';
if ($category_id !== '' && $category_id !== '0') {
    $category_id_escaped = $esc($category_id);
    $category_id_length = strlen($category_id);
    if ($category_id_length == 2) {
        // 1차 업종(2자리): 해당 카테고리로 시작하는 모든 하위 분류 포함
        $category_filter_sql = " AND EXISTS (
            SELECT 1 FROM {$g5['shop_category_relation_table']} scr
            WHERE scr.shop_id = s.shop_id AND LEFT(scr.category_id, 2) = '{$category_id_escaped}'
        )";
    } else {
        // 2차 업종(4자리) 이상: 정확히 일치하는 것만
        $category_filter_sql = " AND EXISTS (
            SELECT 1 FROM {$g5['shop_category_relation_table']} scr
            WHERE scr.shop_id = s.shop_id AND scr.category_id = '{$category_id_escaped}'
        )";
    }
}

// 지역 필터링 조건 (shop 테이블 기준)
$region_filter_sql = '';
if ($region !== '') {
    $region_escaped = $esc($region);
    $region_filter_sql = " AND (
        CASE
            WHEN s.addr1 IS NOT NULL AND s.addr1 != '' THEN
                CASE
                    WHEN s.addr1 LIKE '서울%' THEN '서울특별시'
                    WHEN s.addr1 LIKE '부산%' THEN '부산광역시'
                    WHEN s.addr1 LIKE '대구%' THEN '대구광역시'
                    WHEN s.addr1 LIKE '인천%' THEN '인천광역시'
                    WHEN s.addr1 LIKE '광주%' THEN '광주광역시'
                    WHEN s.addr1 LIKE '대전%' THEN '대전광역시'
                    WHEN s.addr1 LIKE '울산%' THEN '울산광역시'
                    WHEN s.addr1 LIKE '세종%' THEN '세종특별자치시'
                    WHEN s.addr1 LIKE '경기%' THEN '경기도'
                    WHEN s.addr1 LIKE '강원%' THEN '강원도'
                    WHEN s.addr1 LIKE '충북%' OR s.addr1 LIKE '충청북도%' THEN '충청북도'
                    WHEN s.addr1 LIKE '충남%' OR s.addr1 LIKE '충청남도%' THEN '충청남도'
                    WHEN s.addr1 LIKE '전북%' OR s.addr1 LIKE '전라북도%' THEN '전라북도'
                    WHEN s.addr1 LIKE '전남%' OR s.addr1 LIKE '전라남도%' THEN '전라남도'
                    WHEN s.addr1 LIKE '경북%' OR s.addr1 LIKE '경상북도%' THEN '경상북도'
                    WHEN s.addr1 LIKE '경남%' OR s.addr1 LIKE '경상남도%' THEN '경상남도'
                    WHEN s.addr1 LIKE '제주%' THEN '제주특별자치도'
                    ELSE NULL
                END = '{$region_escaped}'
            ELSE FALSE
        END
    )";
}

// 기간별 집계 표현식 결정
$date_group_expr = '';
$date_group_expr_used = '';
switch ($period_type) {
    case 'weekly':
        $date_group_expr = "DATE_TRUNC('week', CAST(cc.issued_at AS DATE))";
        $date_group_expr_used = "DATE_TRUNC('week', CAST(cc.used_at AS DATE))";
        break;
    case 'monthly':
        $date_group_expr = "DATE_TRUNC('month', CAST(cc.issued_at AS DATE))";
        $date_group_expr_used = "DATE_TRUNC('month', CAST(cc.used_at AS DATE))";
        break;
    default: // daily
        $date_group_expr = "CAST(cc.issued_at AS DATE)";
        $date_group_expr_used = "CAST(cc.used_at AS DATE)";
        break;
}

try {
    // 1. 주요 지표 카드 데이터
    $range_start_escaped = $esc($range_start);
    $range_end_escaped = $esc($range_end);

    // 총 쿠폰 발급 건수
    $sql = " SELECT COUNT(*) as total_issued_count
             FROM customer_coupons cc
             INNER JOIN shop_coupons sc ON cc.coupon_id = sc.coupon_id
             INNER JOIN shop s ON sc.shop_id = s.shop_id
             WHERE cc.issued_at::date BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}'
             {$category_filter_sql} {$region_filter_sql} ";
    $row = sql_fetch_pg($sql);
    $total_issued_count = (int)($row['total_issued_count'] ?? 0);

    // 총 쿠폰 사용 건수
    $sql = " SELECT COUNT(*) as total_used_count
             FROM customer_coupons cc
             INNER JOIN shop_coupons sc ON cc.coupon_id = sc.coupon_id
             INNER JOIN shop s ON sc.shop_id = s.shop_id
             WHERE cc.used_at::date BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}'
               AND (cc.status = 'USED' OR cc.used_at IS NOT NULL)
             {$category_filter_sql} {$region_filter_sql} ";
    $row = sql_fetch_pg($sql);
    $total_used_count = (int)($row['total_used_count'] ?? 0);

    // 쿠폰 사용률
    $usage_rate = 0.0;
    if ($total_issued_count > 0) {
        $usage_rate = round(($total_used_count / $total_issued_count) * 100, 1);
    }

    // 총 할인 금액
    $sql = " SELECT COALESCE(SUM(asd.coupon_amount), 0) as total_discount_amount
             FROM appointment_shop_detail asd
             INNER JOIN shop s ON asd.shop_id = s.shop_id
             WHERE asd.coupon_amount IS NOT NULL
               AND asd.coupon_amount > 0
               AND asd.appointment_datetime >= '{$range_start_escaped} 00:00:00'
               AND asd.appointment_datetime <= '{$range_end_escaped} 23:59:59'
               AND asd.status != 'BOOKED'
             {$category_filter_sql} {$region_filter_sql} ";
    $row = sql_fetch_pg($sql);
    $total_discount_amount = (float)($row['total_discount_amount'] ?? 0);
    
    // 디버깅: 총 할인 금액 쿼리 결과 확인
    // if (!$row || !isset($row['total_discount_amount'])) {
    //     error_log("총 할인 금액 쿼리 결과 오류 - SQL: " . $sql);
    // }

    // 쿠폰 사용 시 평균 주문 금액
    $sql = " SELECT COALESCE(AVG(asd.balance_amount), 0) as avg_order_amount
             FROM appointment_shop_detail asd
             INNER JOIN customer_coupons cc ON asd.customer_coupon_id = cc.customer_coupon_id
             INNER JOIN shop s ON asd.shop_id = s.shop_id
             WHERE asd.coupon_amount IS NOT NULL
               AND asd.coupon_amount > 0
               AND asd.appointment_datetime >= '{$range_start_escaped} 00:00:00'
               AND asd.appointment_datetime <= '{$range_end_escaped} 23:59:59'
               AND asd.status != 'BOOKED'
             {$category_filter_sql} {$region_filter_sql} ";
    $row = sql_fetch_pg($sql);
    $avg_order_amount_with_coupon = (float)($row['avg_order_amount'] ?? 0);

    // 쿠폰 사용으로 인한 예상 매출 증가액
    $sql = " SELECT COALESCE(SUM(asd.balance_amount - asd.coupon_amount), 0) as estimated_sales_increase
             FROM appointment_shop_detail asd
             INNER JOIN shop s ON asd.shop_id = s.shop_id
             WHERE asd.coupon_amount IS NOT NULL
               AND asd.coupon_amount > 0
               AND asd.appointment_datetime >= '{$range_start_escaped} 00:00:00'
               AND asd.appointment_datetime <= '{$range_end_escaped} 23:59:59'
               AND asd.status != 'BOOKED'
             {$category_filter_sql} {$region_filter_sql} ";
    $row = sql_fetch_pg($sql);
    $estimated_sales_increase = (float)($row['estimated_sales_increase'] ?? 0);
    
    // 디버깅: 예상 매출 증가액 쿼리 결과 확인
    // if (!$row || !isset($row['estimated_sales_increase'])) {
    //     error_log("예상 매출 증가액 쿼리 결과 오류 - SQL: " . $sql);
    // }

    // 2. 기간별 쿠폰 발급/사용 추이
    $date_series_expr_issue_use = '';
    switch ($period_type) {
        case 'weekly':
            $date_series_expr_issue_use = "DATE_TRUNC('week', generate_series('{$range_start_escaped}'::DATE, '{$range_end_escaped}'::DATE, '1 day'::INTERVAL)::DATE)";
            break;
        case 'monthly':
            $date_series_expr_issue_use = "DATE_TRUNC('month', generate_series('{$range_start_escaped}'::DATE, '{$range_end_escaped}'::DATE, '1 day'::INTERVAL)::DATE)";
            break;
        default: // daily
            $date_series_expr_issue_use = "generate_series('{$range_start_escaped}'::DATE, '{$range_end_escaped}'::DATE, '1 day'::INTERVAL)::DATE";
            break;
    }

    $sql = " WITH date_series AS (
                SELECT DISTINCT {$date_series_expr_issue_use} AS date
                ORDER BY date
             ),
             issued_data AS (
                 SELECT 
                     {$date_group_expr} AS period_date,
                     COUNT(*) AS issued_count
                 FROM customer_coupons cc
                 INNER JOIN shop_coupons sc ON cc.coupon_id = sc.coupon_id
                 INNER JOIN shop s ON sc.shop_id = s.shop_id
                 WHERE cc.issued_at::date BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}'
                 {$category_filter_sql} {$region_filter_sql}
                 GROUP BY {$date_group_expr}
             ),
             used_data AS (
                 SELECT 
                     {$date_group_expr_used} AS period_date,
                     COUNT(*) AS used_count
                 FROM customer_coupons cc
                 INNER JOIN shop_coupons sc ON cc.coupon_id = sc.coupon_id
                 INNER JOIN shop s ON sc.shop_id = s.shop_id
                 WHERE cc.used_at::date BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}'
                   AND (cc.status = 'USED' OR cc.used_at IS NOT NULL)
                 {$category_filter_sql} {$region_filter_sql}
                 GROUP BY {$date_group_expr_used}
             )
             SELECT 
                 ds.date,
                 COALESCE(i.issued_count, 0) AS issued_count,
                 COALESCE(u.used_count, 0) AS used_count
             FROM date_series ds
             LEFT JOIN issued_data i ON ds.date = i.period_date
             LEFT JOIN used_data u ON ds.date = u.period_date
             ORDER BY ds.date ASC ";
    
    $result = sql_query_pg($sql);
    $coupon_issue_use_trend = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $coupon_issue_use_trend[] = [
                'date' => $row['date'] ?? '',
                'issued_count' => (int)($row['issued_count'] ?? 0),
                'used_count' => (int)($row['used_count'] ?? 0)
            ];
        }
    }

    // 3. 기간별 할인 금액 추이
    if ($period_type == 'weekly') {
        $date_group_expr_discount = "DATE_TRUNC('week', CAST(appointment_datetime AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('week', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DOW FROM date_series.date) = 1";
    } elseif ($period_type == 'monthly') {
        $date_group_expr_discount = "DATE_TRUNC('month', CAST(appointment_datetime AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('month', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DAY FROM date_series.date) = 1";
    } else {
        $date_group_expr_discount = "CAST(appointment_datetime AS DATE)";
        $date_series_expr = "date_series.date";
        $date_series_filter = "";
    }

    $sql = " WITH date_series AS (
                SELECT generate_series(
                    '{$range_start_escaped}'::DATE,
                    '{$range_end_escaped}'::DATE,
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
                     {$date_group_expr_discount} AS period_date,
                     COALESCE(SUM(coupon_amount), 0) AS discount_amount
                 FROM appointment_shop_detail asd
                 INNER JOIN shop s ON asd.shop_id = s.shop_id
                 WHERE coupon_amount IS NOT NULL
                   AND coupon_amount > 0
                   AND appointment_datetime >= '{$range_start_escaped} 00:00:00'
                   AND appointment_datetime <= '{$range_end_escaped} 23:59:59'
                   AND asd.status != 'BOOKED'
                 {$category_filter_sql} {$region_filter_sql}
                 GROUP BY {$date_group_expr_discount}
             )
             SELECT 
                 dp.period_date AS date,
                 COALESCE(d.discount_amount, 0) AS discount_amount
             FROM date_periods dp
             LEFT JOIN discount_data d ON dp.period_date = d.period_date
             ORDER BY dp.period_date ASC ";
    
    $result = sql_query_pg($sql);
    $discount_amount_trend = [];
    
    // SQL 에러 확인
    // if (!$result) {
    //     error_log("할인 금액 추이 쿼리 실패: " . $sql);
    // } elseif (is_object($result) && isset($result->result)) {
    if (is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $date_value = $row['date'] ?? '';
            // 날짜가 NULL이거나 빈 문자열이 아닌 경우만 추가
            if ($date_value !== '' && $date_value !== null) {
                $discount_amount_trend[] = [
                    'date' => is_string($date_value) ? substr($date_value, 0, 10) : $date_value,
                    'discount_amount' => (float)($row['discount_amount'] ?? 0)
                ];
            }
        }
    }
    // } else {
    //     error_log("할인 금액 추이 쿼리 결과 형식 오류");
    // }
    
    // 디버깅: 데이터가 없을 때 로그
    // if (empty($discount_amount_trend)) {
    //     error_log("할인 금액 추이 데이터 없음 - 기간: {$range_start} ~ {$range_end}, 타입: {$period_type}");
    // }

    // 4. 가맹점별 쿠폰 발급/사용 현황 (상위 20개)
    $sql = " SELECT 
                 s.shop_id,
                 s.name as shop_name,
                 COUNT(DISTINCT cc.customer_coupon_id) FILTER (WHERE cc.issued_at::date BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}') as issued_count,
                 COUNT(DISTINCT cc.customer_coupon_id) FILTER (WHERE cc.used_at::date BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}' AND (cc.status = 'USED' OR cc.used_at IS NOT NULL)) as used_count
             FROM shop s
             INNER JOIN shop_coupons sc ON s.shop_id = sc.shop_id
             LEFT JOIN customer_coupons cc ON sc.coupon_id = cc.coupon_id
               AND (cc.issued_at::date BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}' OR cc.used_at::date BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}')
             WHERE 1=1 {$category_filter_sql} {$region_filter_sql}
             GROUP BY s.shop_id, s.name
             HAVING COUNT(DISTINCT cc.customer_coupon_id) > 0
             ORDER BY issued_count DESC, used_count DESC
             LIMIT 20 ";
    
    $result = sql_query_pg($sql);
    $shop_coupon_issue_use = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $shop_coupon_issue_use[] = [
                'shop_id' => $row['shop_id'] ?? '',
                'shop_name' => $row['shop_name'] ?? '',
                'issued_count' => (int)($row['issued_count'] ?? 0),
                'used_count' => (int)($row['used_count'] ?? 0)
            ];
        }
    }

    // 5. 쿠폰 타입별 분포
    $sql = " SELECT 
                 CASE 
                     WHEN sc.discount_type = 'AMOUNT' THEN '금액할인'
                     WHEN sc.discount_type = 'PERCENT' THEN '비율할인'
                     ELSE '기타'
                 END as coupon_type,
                 COUNT(cc.customer_coupon_id) as coupon_count
             FROM shop_coupons sc
             INNER JOIN customer_coupons cc ON sc.coupon_id = cc.coupon_id
             INNER JOIN shop s ON sc.shop_id = s.shop_id
             WHERE cc.issued_at::date BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}'
             {$category_filter_sql} {$region_filter_sql}
             GROUP BY sc.discount_type
             ORDER BY coupon_count DESC ";
    
    $result = sql_query_pg($sql);
    $coupon_type_distribution = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $coupon_type_distribution[] = [
                'coupon_type' => $row['coupon_type'] ?? '',
                'coupon_count' => (int)($row['coupon_count'] ?? 0)
            ];
        }
    }

    // 6. 쿠폰 사용률 추이
    $date_series_expr_usage_rate = '';
    switch ($period_type) {
        case 'weekly':
            $date_series_expr_usage_rate = "DATE_TRUNC('week', generate_series('{$range_start_escaped}'::DATE, '{$range_end_escaped}'::DATE, '1 day'::INTERVAL)::DATE)";
            break;
        case 'monthly':
            $date_series_expr_usage_rate = "DATE_TRUNC('month', generate_series('{$range_start_escaped}'::DATE, '{$range_end_escaped}'::DATE, '1 day'::INTERVAL)::DATE)";
            break;
        default: // daily
            $date_series_expr_usage_rate = "generate_series('{$range_start_escaped}'::DATE, '{$range_end_escaped}'::DATE, '1 day'::INTERVAL)::DATE";
            break;
    }

    $sql = " WITH date_series AS (
                SELECT DISTINCT {$date_series_expr_usage_rate} AS date
                ORDER BY date
             ),
             issued_daily AS (
                 SELECT {$date_group_expr} as date, COUNT(*) as issued_count
                 FROM customer_coupons cc
                 INNER JOIN shop_coupons sc ON cc.coupon_id = sc.coupon_id
                 INNER JOIN shop s ON sc.shop_id = s.shop_id
                 WHERE cc.issued_at::date BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}'
                 {$category_filter_sql} {$region_filter_sql}
                 GROUP BY {$date_group_expr}
             ),
             used_daily AS (
                 SELECT {$date_group_expr_used} as date, COUNT(*) as used_count
                 FROM customer_coupons cc
                 INNER JOIN shop_coupons sc ON cc.coupon_id = sc.coupon_id
                 INNER JOIN shop s ON sc.shop_id = s.shop_id
                 WHERE cc.used_at::date BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}'
                   AND (cc.status = 'USED' OR cc.used_at IS NOT NULL)
                 {$category_filter_sql} {$region_filter_sql}
                 GROUP BY {$date_group_expr_used}
             )
             SELECT 
                 ds.date,
                 CASE 
                     WHEN COALESCE(i.issued_count, 0) > 0 
                     THEN (COALESCE(u.used_count, 0)::float / i.issued_count::float * 100)
                     ELSE 0 
                 END as usage_rate
             FROM date_series ds
             LEFT JOIN issued_daily i ON ds.date = i.date
             LEFT JOIN used_daily u ON ds.date = u.date
             ORDER BY ds.date ASC ";
    
    $result = sql_query_pg($sql);
    $coupon_usage_rate_trend = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $coupon_usage_rate_trend[] = [
                'date' => $row['date'] ?? '',
                'usage_rate' => (float)($row['usage_rate'] ?? 0)
            ];
        }
    }

    // 7. 쿠폰 사용 시 매출 기여도 분석 (상위 20개)
    $sql = " SELECT 
                 s.shop_id,
                 s.name as shop_name,
                 COUNT(DISTINCT asd.shopdetail_id) as coupon_order_count,
                 COALESCE(SUM(asd.balance_amount), 0) as total_sales_with_coupon,
                 COALESCE(SUM(asd.coupon_amount), 0) as total_discount,
                 COALESCE(SUM(asd.balance_amount - asd.coupon_amount), 0) as net_sales
             FROM shop s
             INNER JOIN appointment_shop_detail asd ON s.shop_id = asd.shop_id
             INNER JOIN customer_coupons cc ON asd.customer_coupon_id = cc.customer_coupon_id
             WHERE asd.coupon_amount IS NOT NULL
               AND asd.coupon_amount > 0
               AND asd.appointment_datetime::date BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}'
               AND asd.status != 'BOOKED'
             {$category_filter_sql} {$region_filter_sql}
             GROUP BY s.shop_id, s.name
             ORDER BY total_sales_with_coupon DESC
             LIMIT 20 ";
    
    $result = sql_query_pg($sql);
    $coupon_sales_contribution = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $coupon_sales_contribution[] = [
                'shop_id' => $row['shop_id'] ?? '',
                'shop_name' => $row['shop_name'] ?? '',
                'coupon_order_count' => (int)($row['coupon_order_count'] ?? 0),
                'total_sales_with_coupon' => (float)($row['total_sales_with_coupon'] ?? 0),
                'total_discount' => (float)($row['total_discount'] ?? 0),
                'net_sales' => (float)($row['net_sales'] ?? 0)
            ];
        }
    }

    // 8. 가맹점별 쿠폰 마케팅 상세 통계 (상위 50개)
    $sql = " SELECT 
                 s.shop_id,
                 s.name as shop_name,
                 sc.name as category_name,
                 COUNT(DISTINCT cc.customer_coupon_id) FILTER (WHERE cc.issued_at::date BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}') as issued_count,
                 COUNT(DISTINCT cc.customer_coupon_id) FILTER (WHERE cc.used_at::date BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}' AND (cc.status = 'USED' OR cc.used_at IS NOT NULL)) as used_count,
                 CASE 
                     WHEN COUNT(DISTINCT cc.customer_coupon_id) FILTER (WHERE cc.issued_at::date BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}') > 0 
                     THEN (COUNT(DISTINCT cc.customer_coupon_id) FILTER (WHERE cc.used_at::date BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}' AND (cc.status = 'USED' OR cc.used_at IS NOT NULL))::float / COUNT(DISTINCT cc.customer_coupon_id) FILTER (WHERE cc.issued_at::date BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}')::float * 100)
                     ELSE 0 
                 END as usage_rate,
                 COALESCE(SUM(asd.coupon_amount), 0) as total_discount,
                 COUNT(DISTINCT asd.shopdetail_id) FILTER (WHERE asd.coupon_amount IS NOT NULL AND asd.coupon_amount > 0) as coupon_order_count,
                 COALESCE(SUM(asd.balance_amount), 0) as total_sales_with_coupon,
                 COALESCE(SUM(asd.balance_amount - asd.coupon_amount), 0) as net_sales
             FROM shop s
             LEFT JOIN shop_category_relation scr ON s.shop_id = scr.shop_id
             LEFT JOIN shop_categories sc ON scr.category_id = sc.category_id
             LEFT JOIN shop_coupons shop_c ON s.shop_id = shop_c.shop_id
             LEFT JOIN customer_coupons cc ON shop_c.coupon_id = cc.coupon_id
               AND (cc.issued_at::date BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}' OR cc.used_at::date BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}')
             LEFT JOIN appointment_shop_detail asd ON asd.customer_coupon_id = cc.customer_coupon_id
               AND asd.appointment_datetime::date BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}'
               AND asd.status != 'BOOKED'
             WHERE 1=1 {$category_filter_sql} {$region_filter_sql}
             GROUP BY s.shop_id, s.name, sc.name
             HAVING COUNT(DISTINCT cc.customer_coupon_id) > 0 OR COUNT(DISTINCT asd.shopdetail_id) > 0
             ORDER BY total_sales_with_coupon DESC, usage_rate DESC
             LIMIT 50 ";
    
    $result = sql_query_pg($sql);
    $shop_coupon_detail = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $shop_coupon_detail[] = [
                'shop_id' => $row['shop_id'] ?? '',
                'shop_name' => $row['shop_name'] ?? '',
                'category_name' => $row['category_name'] ?? '',
                'issued_count' => (int)($row['issued_count'] ?? 0),
                'used_count' => (int)($row['used_count'] ?? 0),
                'usage_rate' => (float)($row['usage_rate'] ?? 0),
                'total_discount' => (float)($row['total_discount'] ?? 0),
                'coupon_order_count' => (int)($row['coupon_order_count'] ?? 0),
                'total_sales_with_coupon' => (float)($row['total_sales_with_coupon'] ?? 0),
                'net_sales' => (float)($row['net_sales'] ?? 0)
            ];
        }
    }

    // 응답 데이터 구성
    $response = [
        'success' => true,
        'period_type' => $period_type,
        'range_start' => $range_start,
        'range_end' => $range_end,
        'summary' => [
            'total_issued_count' => $total_issued_count,
            'total_used_count' => $total_used_count,
            'usage_rate' => $usage_rate,
            'total_discount_amount' => $total_discount_amount,
            'avg_order_amount_with_coupon' => $avg_order_amount_with_coupon,
            'estimated_sales_increase' => $estimated_sales_increase
        ],
        'coupon_issue_use_trend' => $coupon_issue_use_trend,
        'discount_amount_trend' => $discount_amount_trend,
        'shop_coupon_issue_use' => $shop_coupon_issue_use,
        'coupon_type_distribution' => $coupon_type_distribution,
        'coupon_usage_rate_trend' => $coupon_usage_rate_trend,
        'coupon_sales_contribution' => $coupon_sales_contribution,
        'shop_coupon_detail' => $shop_coupon_detail
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '데이터 조회 중 오류가 발생했습니다: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>

