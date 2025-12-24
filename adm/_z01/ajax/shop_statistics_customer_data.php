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

// 고객 통계 요약 정보
if (!function_exists('get_customer_summary')) {
function get_customer_summary($shop_id, $range_start, $range_end)
{
    // 신규 고객 수
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
    $sql_existing = "
        SELECT COUNT(DISTINCT sa.customer_id) AS existing_customer_count
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
    $existing_customer_count = (int)$row_existing['existing_customer_count'];

    // 신규/기존 고객 비율 계산
    $total_customer_count = $new_customer_count + $existing_customer_count;
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
        ) AS customer_stats
    ";
    $row_avg_amount = sql_fetch_pg($sql_avg_amount);
    $avg_amount_per_customer = (float)($row_avg_amount['avg_amount_per_customer'] ?? 0);

    // 평균 예약 빈도 (고객당)
    $sql_avg_frequency = "
        SELECT 
            AVG(appointment_count) AS avg_appointment_frequency
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
        ) AS customer_frequency
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
    $sql_conversion = "
        SELECT 
            COUNT(DISTINCT wl.customer_id) AS wish_customer_count,
            COUNT(DISTINCT CASE 
                WHEN sa.appointment_id IS NOT NULL THEN wl.customer_id 
            END) AS converted_customer_count,
            CASE 
                WHEN COUNT(DISTINCT wl.customer_id) > 0 THEN
                    ROUND((COUNT(DISTINCT CASE 
                        WHEN sa.appointment_id IS NOT NULL THEN wl.customer_id 
                    END)::numeric / COUNT(DISTINCT wl.customer_id)) * 100, 2)
                ELSE 0
            END AS conversion_rate
        FROM wish_list wl
        LEFT JOIN shop_appointments sa ON wl.customer_id = sa.customer_id
          AND sa.status != 'BOOKED'
          AND sa.is_deleted = 'N'
          AND sa.appointment_id IN (
            SELECT appointment_id
            FROM appointment_shop_detail
            WHERE shop_id = {$shop_id}
              AND status != 'BOOKED'
              AND appointment_datetime >= wl.created_at
              AND appointment_datetime >= '{$range_start} 00:00:00'
              AND appointment_datetime <= '{$range_end} 23:59:59'
          )
        WHERE wl.shop_id = {$shop_id}
          AND wl.created_at >= '{$range_start} 00:00:00'
          AND wl.created_at <= '{$range_end} 23:59:59'
    ";
    $row_conversion = sql_fetch_pg($sql_conversion);
    $wish_conversion_rate = (float)($row_conversion['conversion_rate'] ?? 0);

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
    $summary = get_customer_summary($shop_id, $range_start, $range_end);
    
    return [
        ['type' => '신규', 'count' => $summary['new_customer_count']],
        ['type' => '기존', 'count' => $summary['existing_customer_count']],
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
            COALESCE(c.nickname, '고객 ' || sa.customer_id::text) AS customer_name,
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
        GROUP BY sa.customer_id, c.nickname
        ORDER BY total_amount DESC
        LIMIT 10
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'customer_id' => (int)$row['customer_id'],
                'customer_name' => $row['customer_name'],
                'appointment_count' => (int)$row['appointment_count'],
                'total_amount' => (int)$row['total_amount'],
                'avg_amount' => (float)$row['avg_amount'],
            ];
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
            frequency_range,
            COUNT(*) AS customer_count
        FROM (
            SELECT 
                sa.customer_id,
                COUNT(*) AS appointment_count,
                CASE 
                    WHEN COUNT(*) = 1 THEN '1회'
                    WHEN COUNT(*) BETWEEN 2 AND 3 THEN '2-3회'
                    WHEN COUNT(*) BETWEEN 4 AND 5 THEN '4-5회'
                    WHEN COUNT(*) >= 6 THEN '6회 이상'
                END AS frequency_range
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
        ) AS customer_frequency
        GROUP BY frequency_range
        ORDER BY 
            CASE frequency_range
                WHEN '1회' THEN 1
                WHEN '2-3회' THEN 2
                WHEN '4-5회' THEN 3
                WHEN '6회 이상' THEN 4
            END
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    // 모든 범위 초기화
    $range_map = [
        '1회' => 0,
        '2-3회' => 0,
        '4-5회' => 0,
        '6회 이상' => 0,
    ];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $range = $row['frequency_range'];
            if (isset($range_map[$range])) {
                $range_map[$range] = (int)$row['customer_count'];
            }
        }
    }

    // 배열로 변환
    foreach ($range_map as $range => $count) {
        $rows[] = [
            'range' => $range,
            'count' => $count,
        ];
    }

    return $rows;
}
}

// 찜 목록 추가 추이 (일별/주별/월별)
if (!function_exists('get_wish_trend')) {
function get_wish_trend($shop_id, $range_start, $range_end, $period_type = 'daily')
{
    // period_type에 따라 날짜 그룹화 방식 결정
    if ($period_type == 'weekly') {
        // 주별: 해당 주의 첫 번째 날짜로 그룹화 (월요일 기준)
        $date_group_expr = "DATE_TRUNC('week', CAST(created_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('week', date_series.date)::DATE";
        // 각 주의 월요일만 추출 (DOW: 0=일요일, 1=월요일, ..., 6=토요일)
        $date_series_filter = "AND EXTRACT(DOW FROM date_series.date) = 1"; // 월요일만
    } elseif ($period_type == 'monthly') {
        // 월별: 해당 월의 첫 번째 날짜로 그룹화
        $date_group_expr = "DATE_TRUNC('month', CAST(created_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('month', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DAY FROM date_series.date) = 1"; // 매월 1일만
    } else {
        // 일별
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
            COALESCE(w.wish_count, 0) AS wish_count
        FROM date_periods dp
        LEFT JOIN wish_data w ON dp.period_date = w.period_date
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

// 찜 → 예약 전환률 추이 (일별/주별/월별)
if (!function_exists('get_wish_conversion_trend')) {
function get_wish_conversion_trend($shop_id, $range_start, $range_end, $period_type = 'daily')
{
    // period_type에 따라 날짜 그룹화 방식 결정
    if ($period_type == 'weekly') {
        // 주별: 해당 주의 첫 번째 날짜로 그룹화 (월요일 기준)
        $date_group_expr = "DATE_TRUNC('week', CAST(wl.created_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('week', date_series.date)::DATE";
        // 각 주의 월요일만 추출 (DOW: 0=일요일, 1=월요일, ..., 6=토요일)
        $date_series_filter = "AND EXTRACT(DOW FROM date_series.date) = 1"; // 월요일만
    } elseif ($period_type == 'monthly') {
        // 월별: 해당 월의 첫 번째 날짜로 그룹화
        $date_group_expr = "DATE_TRUNC('month', CAST(wl.created_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('month', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DAY FROM date_series.date) = 1"; // 매월 1일만
    } else {
        // 일별
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
        conversion_data AS (
            SELECT 
                {$date_group_expr} AS period_date,
                COUNT(DISTINCT wl.customer_id) AS wish_customer_count,
                COUNT(DISTINCT CASE 
                    WHEN sa.appointment_id IS NOT NULL THEN wl.customer_id 
                END) AS converted_customer_count,
                CASE 
                    WHEN COUNT(DISTINCT wl.customer_id) > 0 THEN
                        ROUND((COUNT(DISTINCT CASE 
                            WHEN sa.appointment_id IS NOT NULL THEN wl.customer_id 
                        END)::numeric / COUNT(DISTINCT wl.customer_id)) * 100, 2)
                    ELSE 0
                END AS conversion_rate
            FROM wish_list wl
            LEFT JOIN shop_appointments sa ON wl.customer_id = sa.customer_id
              AND sa.status != 'BOOKED'
              AND sa.is_deleted = 'N'
              AND sa.appointment_id IN (
                SELECT appointment_id
                FROM appointment_shop_detail
                WHERE shop_id = {$shop_id}
                  AND status != 'BOOKED'
                  AND appointment_datetime >= wl.created_at
                  AND appointment_datetime >= '{$range_start} 00:00:00'
                  AND appointment_datetime <= '{$range_end} 23:59:59'
              )
            WHERE wl.shop_id = {$shop_id}
              AND wl.created_at >= '{$range_start} 00:00:00'
              AND wl.created_at <= '{$range_end} 23:59:59'
            GROUP BY {$date_group_expr}
        )
        SELECT 
            dp.period_date AS date,
            COALESCE(c.conversion_rate, 0) AS conversion_rate
        FROM date_periods dp
        LEFT JOIN conversion_data c ON dp.period_date = c.period_date
        ORDER BY dp.period_date ASC
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'date' => $row['date'],
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
            // user_id(nickname) 형식으로 표시
            $user_id = $row['user_id'] ? $row['user_id'] : ('고객 ' . $row['customer_id']);
            $nickname = $row['nickname'] ? '(' . $row['nickname'] . ')' : '';
            $customer_display = $user_id . $nickname;
            
            $rows[] = [
                'rank' => $rank++,
                'customer_id' => (int)$row['customer_id'],
                'user_id' => $row['user_id'],
                'nickname' => $row['nickname'],
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
                    echo json_encode(['success' => false, 'message' => '아직 승인이 되지 않았습니다.']);
                    exit;
                }
                if ($shop_row['status'] == 'closed') {
                    echo json_encode(['success' => false, 'message' => '폐업된 업체입니다.']);
                    exit;
                }
                if ($shop_row['status'] == 'shutdown') {
                    echo json_encode(['success' => false, 'message' => '접근이 제한된 업체입니다. 플랫폼 관리자에게 문의하세요.']);
                    exit;
                }

                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
            }
        }
    }
}

if (!$has_access || !$shop_id) {
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

    // 통계 데이터 조회
    $summary = get_customer_summary($shop_id, $range_start, $range_end);
    $customer_type_distribution = get_customer_type_distribution($shop_id, $range_start, $range_end);
    $top_customers = get_top_customers($shop_id, $range_start, $range_end);
    $frequency_distribution = get_frequency_distribution($shop_id, $range_start, $range_end);
    $wish_trend = get_wish_trend($shop_id, $range_start, $range_end, $period_type);
    $wish_conversion_trend = get_wish_conversion_trend($shop_id, $range_start, $range_end, $period_type);
    $vip_customers = get_vip_customers($shop_id, $range_start, $range_end);

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
    echo json_encode([
        'success' => false,
        'message' => '데이터 조회 중 오류가 발생했습니다: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
exit;

