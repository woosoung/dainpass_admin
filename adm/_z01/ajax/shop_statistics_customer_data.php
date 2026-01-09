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

// 고객 통계 요약 정보
if (!function_exists('get_customer_summary')) {
function get_customer_summary($shop_id, $range_start, $range_end)
{
    // 전체 고객 수 (BOOKED 상태 제외)
    $sql_total = "
        SELECT COUNT(DISTINCT sa.customer_id) AS total_customer_count
        FROM shop_appointments sa
        INNER JOIN appointment_shop_detail asd ON sa.appointment_id = asd.appointment_id
        WHERE asd.shop_id = {$shop_id}
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
          AND sa.status != 'BOOKED'
          AND asd.status != 'BOOKED'
          AND sa.is_deleted = 'N'
          AND sa.customer_id IS NOT NULL
    ";
    $row_total = sql_fetch_pg($sql_total);
    $total_customer_count = (int)$row_total['total_customer_count'];

    // 신규 고객 수 (선택한 기간 이전에 예약한 적이 없는 고객)
    $sql_new = "
        SELECT COUNT(DISTINCT sa.customer_id) AS new_customer_count
        FROM shop_appointments sa
        INNER JOIN appointment_shop_detail asd ON sa.appointment_id = asd.appointment_id
        WHERE asd.shop_id = {$shop_id}
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
          AND sa.status != 'BOOKED'
          AND asd.status != 'BOOKED'
          AND sa.is_deleted = 'N'
          AND sa.customer_id IS NOT NULL
          AND sa.customer_id NOT IN (
              SELECT DISTINCT sa2.customer_id
              FROM shop_appointments sa2
              INNER JOIN appointment_shop_detail asd2 ON sa2.appointment_id = asd2.appointment_id
              WHERE asd2.shop_id = {$shop_id}
                AND asd2.appointment_datetime < '{$range_start} 00:00:00'
                AND sa2.status != 'BOOKED'
                AND asd2.status != 'BOOKED'
                AND sa2.is_deleted = 'N'
                AND sa2.customer_id IS NOT NULL
          )
    ";
    $row_new = sql_fetch_pg($sql_new);
    $new_customer_count = (int)$row_new['new_customer_count'];

    // 기존 고객 수
    $existing_customer_count = $total_customer_count - $new_customer_count;

    // 신규/기존 고객 비율
    $new_customer_rate = 0.0;
    $existing_customer_rate = 0.0;
    if ($total_customer_count > 0) {
        $new_customer_rate = round(($new_customer_count / $total_customer_count) * 100.0, 2);
        $existing_customer_rate = round(($existing_customer_count / $total_customer_count) * 100.0, 2);
    }

    // 평균 예약 금액 (고객당)
    $sql_avg_amount = "
        SELECT 
            AVG(customer_avg_amount) AS avg_amount_per_customer
        FROM (
            SELECT 
                sa.customer_id,
                AVG(asd.balance_amount) AS customer_avg_amount
            FROM shop_appointments sa
            INNER JOIN appointment_shop_detail asd ON sa.appointment_id = asd.appointment_id
            WHERE asd.shop_id = {$shop_id}
              AND asd.appointment_datetime >= '{$range_start} 00:00:00'
              AND asd.appointment_datetime <= '{$range_end} 23:59:59'
              AND sa.status != 'BOOKED'
              AND asd.status != 'BOOKED'
              AND sa.is_deleted = 'N'
              AND sa.customer_id IS NOT NULL
            GROUP BY sa.customer_id
        ) AS customer_amounts
    ";
    $row_avg_amount = sql_fetch_pg($sql_avg_amount);
    $avg_amount_per_customer = (float)($row_avg_amount['avg_amount_per_customer'] ?? 0);

    // 평균 예약 빈도 (고객당 평균 예약 횟수)
    $sql_avg_frequency = "
        SELECT 
            CASE 
                WHEN COUNT(DISTINCT sa.customer_id) > 0 
                THEN ROUND(COUNT(*)::numeric / COUNT(DISTINCT sa.customer_id), 2)
                ELSE 0
            END AS avg_appointment_frequency
        FROM shop_appointments sa
        INNER JOIN appointment_shop_detail asd ON sa.appointment_id = asd.appointment_id
        WHERE asd.shop_id = {$shop_id}
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
          AND sa.status != 'BOOKED'
          AND asd.status != 'BOOKED'
          AND sa.is_deleted = 'N'
          AND sa.customer_id IS NOT NULL
    ";
    $row_avg_frequency = sql_fetch_pg($sql_avg_frequency);
    $avg_appointment_frequency = (float)($row_avg_frequency['avg_appointment_frequency'] ?? 0);

    // VIP 고객 수 (누적 결제 금액 상위 10명 기준)
    $sql_vip = "
        SELECT COUNT(*) AS vip_customer_count
        FROM (
            SELECT 
                sa.customer_id,
                SUM(asd.balance_amount) AS total_amount
            FROM shop_appointments sa
            INNER JOIN appointment_shop_detail asd ON sa.appointment_id = asd.appointment_id
            WHERE asd.shop_id = {$shop_id}
              AND asd.appointment_datetime >= '{$range_start} 00:00:00'
              AND asd.appointment_datetime <= '{$range_end} 23:59:59'
              AND sa.status != 'BOOKED'
              AND asd.status != 'BOOKED'
              AND sa.is_deleted = 'N'
              AND sa.customer_id IS NOT NULL
            GROUP BY sa.customer_id
            ORDER BY total_amount DESC
            LIMIT 10
        ) AS vip_customers
    ";
    $row_vip = sql_fetch_pg($sql_vip);
    $vip_customer_count = (int)$row_vip['vip_customer_count'];

    // 찜 목록 추가 수
    $sql_wish = "
        SELECT COUNT(*) AS wish_count
        FROM wish_list
        WHERE shop_id = {$shop_id}
          AND created_at >= '{$range_start} 00:00:00'
          AND created_at <= '{$range_end} 23:59:59'
    ";
    $row_wish = sql_fetch_pg($sql_wish);
    $wish_count = (int)$row_wish['wish_count'];

    // 찜 → 예약 전환률
    // 찜 목록에 추가한 후 실제 예약한 고객 수
    $sql_conversion = "
        SELECT COUNT(DISTINCT wl.customer_id) AS converted_count
        FROM wish_list wl
        WHERE wl.shop_id = {$shop_id}
          AND wl.created_at >= '{$range_start} 00:00:00'
          AND wl.created_at <= '{$range_end} 23:59:59'
          AND EXISTS (
              SELECT 1
              FROM shop_appointments sa
              INNER JOIN appointment_shop_detail asd ON sa.appointment_id = asd.appointment_id
              WHERE sa.customer_id = wl.customer_id
                AND asd.shop_id = wl.shop_id
                AND asd.appointment_datetime > wl.created_at
                AND asd.appointment_datetime >= '{$range_start} 00:00:00'
                AND asd.appointment_datetime <= '{$range_end} 23:59:59'
                AND sa.status != 'BOOKED'
                AND asd.status != 'BOOKED'
                AND sa.is_deleted = 'N'
          )
    ";
    $row_conversion = sql_fetch_pg($sql_conversion);
    $converted_count = (int)$row_conversion['converted_count'];
    
    // 전체 찜 목록 수로 전환률 계산
    $wish_conversion_rate = 0.0;
    if ($wish_count > 0) {
        $wish_conversion_rate = round(($converted_count / $wish_count) * 100.0, 2);
    }

    return [
        'new_customer_count' => $new_customer_count,
        'existing_customer_count' => $existing_customer_count,
        'new_customer_rate' => $new_customer_rate,
        'existing_customer_rate' => $existing_customer_rate,
        'avg_amount_per_customer' => $avg_amount_per_customer,
        'avg_appointment_frequency' => $avg_appointment_frequency,
        'vip_customer_count' => $vip_customer_count,
        'wish_count' => $wish_count,
        'wish_conversion_rate' => $wish_conversion_rate,
    ];
}
}

// 신규/기존 고객 분포
if (!function_exists('get_customer_type_distribution')) {
function get_customer_type_distribution($shop_id, $range_start, $range_end)
{
    // 신규 고객 수
    $sql_new = "
        SELECT COUNT(DISTINCT sa.customer_id) AS count
        FROM shop_appointments sa
        INNER JOIN appointment_shop_detail asd ON sa.appointment_id = asd.appointment_id
        WHERE asd.shop_id = {$shop_id}
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
          AND sa.status != 'BOOKED'
          AND asd.status != 'BOOKED'
          AND sa.is_deleted = 'N'
          AND sa.customer_id IS NOT NULL
          AND sa.customer_id NOT IN (
              SELECT DISTINCT sa2.customer_id
              FROM shop_appointments sa2
              INNER JOIN appointment_shop_detail asd2 ON sa2.appointment_id = asd2.appointment_id
              WHERE asd2.shop_id = {$shop_id}
                AND asd2.appointment_datetime < '{$range_start} 00:00:00'
                AND sa2.status != 'BOOKED'
                AND asd2.status != 'BOOKED'
                AND sa2.is_deleted = 'N'
                AND sa2.customer_id IS NOT NULL
          )
    ";
    $row_new = sql_fetch_pg($sql_new);
    $new_count = (int)$row_new['count'];

    // 기존 고객 수
    $sql_existing = "
        SELECT COUNT(DISTINCT sa.customer_id) AS count
        FROM shop_appointments sa
        INNER JOIN appointment_shop_detail asd ON sa.appointment_id = asd.appointment_id
        WHERE asd.shop_id = {$shop_id}
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
          AND sa.status != 'BOOKED'
          AND asd.status != 'BOOKED'
          AND sa.is_deleted = 'N'
          AND sa.customer_id IS NOT NULL
          AND sa.customer_id IN (
              SELECT DISTINCT sa2.customer_id
              FROM shop_appointments sa2
              INNER JOIN appointment_shop_detail asd2 ON sa2.appointment_id = asd2.appointment_id
              WHERE asd2.shop_id = {$shop_id}
                AND asd2.appointment_datetime < '{$range_start} 00:00:00'
                AND sa2.status != 'BOOKED'
                AND asd2.status != 'BOOKED'
                AND sa2.is_deleted = 'N'
                AND sa2.customer_id IS NOT NULL
          )
    ";
    $row_existing = sql_fetch_pg($sql_existing);
    $existing_count = (int)$row_existing['count'];

    return [
        ['type' => '신규 고객', 'count' => $new_count],
        ['type' => '기존 고객', 'count' => $existing_count],
    ];
}
}

// 상위 고객 목록 (예약 금액 기준)
if (!function_exists('get_top_customers')) {
function get_top_customers($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            sa.customer_id,
            c.user_id,
            c.nickname,
            COUNT(*) AS appointment_count,
            SUM(asd.balance_amount) AS total_amount,
            AVG(asd.balance_amount) AS avg_amount
        FROM shop_appointments sa
        INNER JOIN appointment_shop_detail asd ON sa.appointment_id = asd.appointment_id
        LEFT JOIN customers c ON sa.customer_id = c.customer_id
        WHERE asd.shop_id = {$shop_id}
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
          AND sa.status != 'BOOKED'
          AND asd.status != 'BOOKED'
          AND sa.is_deleted = 'N'
          AND sa.customer_id IS NOT NULL
        GROUP BY sa.customer_id, c.user_id, c.nickname
        ORDER BY total_amount DESC
        LIMIT 10
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        $customer_index = 1;
        while ($row = sql_fetch_array_pg($result->result)) {
            // 닉네임만 표시, 없으면 '고객 N'으로 표시 (ID 노출 방지)
            $customer_name = $row['nickname'] ? $row['nickname'] : ('고객 ' . $customer_index);

            $rows[] = [
                'customer_id' => (int)$row['customer_id'],
                'customer_name' => $customer_name,
                'appointment_count' => (int)$row['appointment_count'],
                'total_amount' => (int)$row['total_amount'],
                'avg_amount' => (float)$row['avg_amount'],
            ];
            $customer_index++;
        }
    }

    return $rows;
}
}

// 예약 빈도 분포
if (!function_exists('get_frequency_distribution')) {
function get_frequency_distribution($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            CASE 
                WHEN appointment_count = 1 THEN '1회'
                WHEN appointment_count = 2 THEN '2회'
                WHEN appointment_count = 3 THEN '3회'
                WHEN appointment_count = 4 THEN '4회'
                WHEN appointment_count >= 5 AND appointment_count < 10 THEN '5-9회'
                WHEN appointment_count >= 10 THEN '10회 이상'
                ELSE '기타'
            END AS frequency_range,
            COUNT(*) AS count
        FROM (
            SELECT 
                sa.customer_id,
                COUNT(*) AS appointment_count
            FROM shop_appointments sa
            INNER JOIN appointment_shop_detail asd ON sa.appointment_id = asd.appointment_id
            WHERE asd.shop_id = {$shop_id}
              AND asd.appointment_datetime >= '{$range_start} 00:00:00'
              AND asd.appointment_datetime <= '{$range_end} 23:59:59'
              AND sa.status != 'BOOKED'
              AND asd.status != 'BOOKED'
              AND sa.is_deleted = 'N'
              AND sa.customer_id IS NOT NULL
            GROUP BY sa.customer_id
        ) AS customer_frequencies
        GROUP BY frequency_range
        ORDER BY 
            CASE frequency_range
                WHEN '1회' THEN 1
                WHEN '2회' THEN 2
                WHEN '3회' THEN 3
                WHEN '4회' THEN 4
                WHEN '5-9회' THEN 5
                WHEN '10회 이상' THEN 6
                ELSE 7
            END
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'range' => $row['frequency_range'],
                'count' => (int)$row['count'],
            ];
        }
    }

    return $rows;
}
}

// 찜 목록 추가 추이
if (!function_exists('get_wish_trend')) {
function get_wish_trend($shop_id, $range_start, $range_end, $period_type = 'daily')
{
    // period_type에 따라 날짜 그룹화 방식 결정
    if ($period_type == 'weekly') {
        $date_group_expr = "DATE_TRUNC('week', CAST(created_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('week', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DOW FROM date_series.date) = 1"; // 월요일만
    } elseif ($period_type == 'monthly') {
        $date_group_expr = "DATE_TRUNC('month', CAST(created_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('month', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DAY FROM date_series.date) = 1"; // 매월 1일만
    } else {
        $date_group_expr = "CAST(created_at AS DATE)";
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
        wish_data AS (
            SELECT 
                {$date_group_expr} AS period_date,
                COUNT(*) AS wish_count
            FROM wish_list
            WHERE shop_id = {$shop_id}
              AND created_at >= '{$range_start} 00:00:00'
              AND created_at <= '{$range_end} 23:59:59'
            GROUP BY {$date_group_expr}
        )
        SELECT 
            dp.period_date AS date,
            COALESCE(wd.wish_count, 0) AS wish_count
        FROM date_periods dp
        LEFT JOIN wish_data wd ON dp.period_date = wd.period_date
        ORDER BY dp.period_date ASC
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'date' => $row['date'],
                'wish_count' => (int)$row['wish_count'],
            ];
        }
    }

    return $rows;
}
}

// 찜 → 예약 전환률 추이
if (!function_exists('get_wish_conversion_trend')) {
function get_wish_conversion_trend($shop_id, $range_start, $range_end, $period_type = 'daily')
{
    // period_type에 따라 날짜 그룹화 방식 결정
    if ($period_type == 'weekly') {
        $date_group_expr = "DATE_TRUNC('week', CAST(wl.created_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('week', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DOW FROM date_series.date) = 1"; // 월요일만
    } elseif ($period_type == 'monthly') {
        $date_group_expr = "DATE_TRUNC('month', CAST(wl.created_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('month', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DAY FROM date_series.date) = 1"; // 매월 1일만
    } else {
        $date_group_expr = "CAST(wl.created_at AS DATE)";
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
        wish_data AS (
            SELECT 
                {$date_group_expr} AS period_date,
                COUNT(DISTINCT wl.customer_id) AS total_wish_count,
                COUNT(DISTINCT CASE 
                    WHEN EXISTS (
                        SELECT 1
                        FROM shop_appointments sa
                        INNER JOIN appointment_shop_detail asd ON sa.appointment_id = asd.appointment_id
                        WHERE sa.customer_id = wl.customer_id
                          AND asd.shop_id = wl.shop_id
                          AND asd.appointment_datetime > wl.created_at
                          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
                          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
                          AND sa.status != 'BOOKED'
                          AND asd.status != 'BOOKED'
                          AND sa.is_deleted = 'N'
                    ) THEN wl.customer_id
                END) AS converted_count
            FROM wish_list wl
            WHERE wl.shop_id = {$shop_id}
              AND wl.created_at >= '{$range_start} 00:00:00'
              AND wl.created_at <= '{$range_end} 23:59:59'
            GROUP BY {$date_group_expr}
        )
        SELECT 
            dp.period_date AS date,
            COALESCE(wd.total_wish_count, 0) AS total_wish_count,
            COALESCE(wd.converted_count, 0) AS converted_count,
            CASE 
                WHEN COALESCE(wd.total_wish_count, 0) > 0 
                THEN ROUND((COALESCE(wd.converted_count, 0)::numeric / wd.total_wish_count) * 100, 2)
                ELSE 0
            END AS conversion_rate
        FROM date_periods dp
        LEFT JOIN wish_data wd ON dp.period_date = wd.period_date
        ORDER BY dp.period_date ASC
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'date' => $row['date'],
                'total_wish_count' => (int)$row['total_wish_count'],
                'converted_count' => (int)$row['converted_count'],
                'conversion_rate' => (float)$row['conversion_rate'],
            ];
        }
    }

    return $rows;
}
}

// VIP 고객 목록 (누적 결제 금액 상위 10명)
if (!function_exists('get_vip_customers')) {
function get_vip_customers($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            sa.customer_id,
            c.user_id,
            c.nickname,
            COUNT(*) AS appointment_count,
            SUM(asd.balance_amount) AS total_amount,
            AVG(asd.balance_amount) AS avg_amount
        FROM shop_appointments sa
        INNER JOIN appointment_shop_detail asd ON sa.appointment_id = asd.appointment_id
        LEFT JOIN customers c ON sa.customer_id = c.customer_id
        WHERE asd.shop_id = {$shop_id}
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
          AND sa.status != 'BOOKED'
          AND asd.status != 'BOOKED'
          AND sa.is_deleted = 'N'
          AND sa.customer_id IS NOT NULL
        GROUP BY sa.customer_id, c.user_id, c.nickname
        ORDER BY total_amount DESC
        LIMIT 10
    ";

    $result = sql_query_pg($sql);
    $rows = [];
    $rank = 1;

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            // 닉네임만 표시, 없으면 'VIP 고객 N'으로 표시 (ID 노출 방지)
            $customer_display = $row['nickname'] ? $row['nickname'] : ('VIP 고객 ' . $rank);

            $rows[] = [
                'rank' => $rank++,
                'customer_id' => (int)$row['customer_id'],
                'customer_display' => $customer_display,
                'appointment_count' => (int)$row['appointment_count'],
                'total_amount' => (int)$row['total_amount'],
                'avg_amount' => (float)$row['avg_amount'],
            ];
        }
    }

    return $rows;
}
}

// 공통: 가맹점 접근 권한 및 shop_id 확인
// 단독 ajax 호출일 때만 실행되도록 가드
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
        $summary = get_customer_summary($shop_id, $range_start, $range_end);
        $customer_type_distribution = get_customer_type_distribution($shop_id, $range_start, $range_end);
        $top_customers = get_top_customers($shop_id, $range_start, $range_end);
        $frequency_distribution = get_frequency_distribution($shop_id, $range_start, $range_end);
        $wish_trend = get_wish_trend($shop_id, $range_start, $range_end, $period_type);
        $wish_conversion_trend = get_wish_conversion_trend($shop_id, $range_start, $range_end, $period_type);
        $vip_customers = get_vip_customers($shop_id, $range_start, $range_end);

        // 출력 버퍼 정리 후 JSON 출력
        ob_clean();
        echo json_encode([
            'success' => true,
            'period_type' => $period_type,
            'range_start' => $range_start,
            'range_end' => $range_end,
            'summary' => $summary,
            'customer_type_distribution' => $customer_type_distribution,
            'top_customers' => $top_customers,
            'frequency_distribution' => $frequency_distribution,
            'wish_trend' => $wish_trend,
            'wish_conversion_trend' => $wish_conversion_trend,
            'vip_customers' => $vip_customers,
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        ob_clean();
        // 출력 버퍼 정리 후 JSON 출력
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => '데이터 조회 중 오류가 발생했습니다: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    ob_end_flush();
    exit;
}

