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

// 플랫폼 관리자 접근 권한 확인
$is_platform_admin = false;
if ($is_member && isset($member['mb_level']) && $member['mb_level'] >= 6) {
    $mb_1_value = isset($member['mb_1']) ? trim($member['mb_1']) : '';
    if ($mb_1_value === '0' || $mb_1_value === '') {
        $is_platform_admin = true;
    }
}

if (!$is_platform_admin) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => '접속할 수 없는 페이지 입니다.']);
    exit;
}

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

// PostgreSQL 이스케이프 헬퍼
global $g5;
$pg_link = isset($g5['connect_pg']) ? $g5['connect_pg'] : null;
$pg_escape_string_safe = function($v) use ($pg_link) {
    if ($pg_link && function_exists('pg_escape_string')) {
        return pg_escape_string($pg_link, $v);
    }
    return addslashes($v);
};

// 요청 파라미터 받기
$period_type = isset($_POST['period_type']) ? $_POST['period_type'] : 'daily';
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';

// 기간 계산
list($range_start, $range_end) = calculate_date_range($period_type, $start_date, $end_date);

// 날짜 이스케이프
$today = date('Y-m-d');
$today_escaped = $pg_escape_string_safe($today);
$range_start_escaped = $pg_escape_string_safe($range_start);
$range_end_escaped = $pg_escape_string_safe($range_end);

// customers 테이블명
$customers_table = isset($g5['customers_table']) ? $g5['customers_table'] : 'customers';

try {
    // 1. 주요 KPI 카드 데이터
    
    // 1-1. 오늘의 플랫폼 총 매출
    $sql = " SELECT COALESCE(SUM(balance_amount), 0) as total_amount
             FROM appointment_shop_detail
             WHERE status = 'COMPLETED'
               AND DATE(appointment_datetime) = '{$today_escaped}' ";
    $row = sql_fetch_pg($sql);
    $today_platform_sales = (int)($row['total_amount'] ?? 0);
    
    // 1-2. 전체 가맹점 수 / 활성 가맹점 수
    $sql = " SELECT COUNT(*) as cnt FROM {$g5['shop_table']} ";
    $row = sql_fetch_pg($sql);
    $total_shop_count = (int)($row['cnt'] ?? 0);
    
    $sql = " SELECT COUNT(*) as cnt FROM {$g5['shop_table']} WHERE status = 'active' ";
    $row = sql_fetch_pg($sql);
    $active_shop_count = (int)($row['cnt'] ?? 0);
    
    // 1-3. 전체 회원 수 / 활성 회원 수
    $sql = " SELECT COUNT(*) as cnt FROM {$customers_table} WHERE withdraw = 'N' ";
    $row = sql_fetch_pg($sql);
    $total_member_count = (int)($row['cnt'] ?? 0);
    
    $sql = " SELECT COUNT(DISTINCT c.customer_id) as cnt
             FROM {$customers_table} c
             INNER JOIN connection_log cl ON c.customer_id = cl.customer_id
             WHERE cl.connect_time >= CURRENT_DATE - INTERVAL '30 days'
               AND cl.connect_status = 'SUCCESS'
               AND c.withdraw = 'N' ";
    $row = sql_fetch_pg($sql);
    $active_member_count = (int)($row['cnt'] ?? 0);
    
    // 1-4. 오늘의 예약 건수
    $sql = " SELECT COUNT(*) as cnt
             FROM appointment_shop_detail
             WHERE DATE(appointment_datetime) = '{$today_escaped}' ";
    $row = sql_fetch_pg($sql);
    $today_appointment_count = (int)($row['cnt'] ?? 0);
    
    // 1-5. 플랫폼 평균 평점
    $sql = " SELECT COALESCE(AVG(sr_score), 0) as avg_rating
             FROM shop_review
             WHERE sr_deleted = 'N' ";
    $row = sql_fetch_pg($sql);
    $platform_avg_rating = round((float)($row['avg_rating'] ?? 0), 1);
    
    // 1-6. 정산 대기 금액
    $sql = " SELECT COALESCE(SUM(net_settlement_amount), 0) as total_amount
             FROM shop_settlements
             WHERE settlement_status = 'PENDING' ";
    $row = sql_fetch_pg($sql);
    $pending_settlement_amount = (int)($row['total_amount'] ?? 0);
    
    // 2. 차트 데이터
    
    // 2-1. 매출 추이
    // period_type에 따라 집계 단위 결정
    if ($period_type == 'weekly') {
        $date_group_expr = "DATE_TRUNC('week', CAST(appointment_datetime AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('week', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DOW FROM date_series.date) = 1";
    } elseif ($period_type == 'monthly') {
        $date_group_expr = "DATE_TRUNC('month', CAST(appointment_datetime AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('month', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DAY FROM date_series.date) = 1";
    } else {
        $date_group_expr = "CAST(appointment_datetime AS DATE)";
        $date_series_expr = "date_series.date";
        $date_series_filter = "";
    }
    
    $sales_trend = [];
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
             sales_data AS (
                 SELECT {$date_group_expr} AS period_date,
                        SUM(balance_amount) AS total_amount
                 FROM appointment_shop_detail
                 WHERE status = 'COMPLETED'
                   AND DATE(appointment_datetime) BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}'
                 GROUP BY {$date_group_expr}
             )
             SELECT dp.period_date AS date, COALESCE(sd.total_amount, 0) AS amount
             FROM date_periods dp
             LEFT JOIN sales_data sd ON dp.period_date = sd.period_date
             ORDER BY dp.period_date ASC ";
    $result = sql_query_pg($sql);
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $sales_trend[] = [
                'date' => $row['date'],
                'amount' => (int)($row['amount'] ?? 0)
            ];
        }
    }
    
    // 2-2. 예약 건수 추이
    $appointment_trend = [];
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
             appointment_data AS (
                 SELECT {$date_group_expr} AS period_date,
                        COUNT(*) AS total_count
                 FROM appointment_shop_detail
                 WHERE DATE(appointment_datetime) BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}'
                 GROUP BY {$date_group_expr}
             )
             SELECT dp.period_date AS date, COALESCE(ad.total_count, 0) AS count
             FROM date_periods dp
             LEFT JOIN appointment_data ad ON dp.period_date = ad.period_date
             ORDER BY dp.period_date ASC ";
    $result = sql_query_pg($sql);
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $appointment_trend[] = [
                'date' => $row['date'],
                'count' => (int)($row['count'] ?? 0)
            ];
        }
    }
    
    // 2-3. 가맹점 상태별 분포
    $shop_status_distribution = [
        'active' => 0,
        'pending' => 0,
        'closed' => 0,
        'shutdown' => 0
    ];
    $sql = " SELECT status, COUNT(*) as cnt
             FROM {$g5['shop_table']}
             GROUP BY status ";
    $result = sql_query_pg($sql);
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $status = strtolower($row['status'] ?? '');
            if (isset($shop_status_distribution[$status])) {
                $shop_status_distribution[$status] = (int)($row['cnt'] ?? 0);
            }
        }
    }
    
    // 2-4. 회원 가입 추이
    // period_type에 따라 집계 단위 결정 (회원 가입 추이용)
    if ($period_type == 'weekly') {
        $signup_date_group_expr = "DATE_TRUNC('week', CAST(created_at AS DATE))::DATE";
        $signup_date_series_expr = "DATE_TRUNC('week', date_series.date)::DATE";
        $signup_date_series_filter = "AND EXTRACT(DOW FROM date_series.date) = 1";
    } elseif ($period_type == 'monthly') {
        $signup_date_group_expr = "DATE_TRUNC('month', CAST(created_at AS DATE))::DATE";
        $signup_date_series_expr = "DATE_TRUNC('month', date_series.date)::DATE";
        $signup_date_series_filter = "AND EXTRACT(DAY FROM date_series.date) = 1";
    } else {
        $signup_date_group_expr = "DATE(created_at)";
        $signup_date_series_expr = "date_series.date";
        $signup_date_series_filter = "";
    }
    
    $member_signup_trend = [];
    $sql = " WITH date_series AS (
                SELECT generate_series(
                    '{$range_start_escaped}'::DATE,
                    '{$range_end_escaped}'::DATE,
                    '1 day'::INTERVAL
                )::DATE AS date
             ),
             date_periods AS (
                 SELECT DISTINCT {$signup_date_series_expr} AS period_date
                 FROM date_series
                 WHERE 1=1 {$signup_date_series_filter}
                 ORDER BY period_date
             ),
             signup_data AS (
                 SELECT {$signup_date_group_expr} AS period_date,
                        COUNT(*) AS total_count
                 FROM {$customers_table}
                 WHERE withdraw = 'N'
                   AND DATE(created_at) BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}'
                 GROUP BY {$signup_date_group_expr}
             )
             SELECT dp.period_date AS date, COALESCE(sd.total_count, 0) AS count
             FROM date_periods dp
             LEFT JOIN signup_data sd ON dp.period_date = sd.period_date
             ORDER BY dp.period_date ASC ";
    $result = sql_query_pg($sql);
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $member_signup_trend[] = [
                'date' => $row['date'],
                'count' => (int)($row['count'] ?? 0)
            ];
        }
    }
    
    // 3. 최근 활동 내역
    
    // 3-1. 최근 정산 내역 (최근 10건)
    $recent_settlements = [];
    $sql = " SELECT ss.settlement_id,
                    ss.appointment_datetime as settlement_date,
                    COALESCE(s.shop_name, s.name) as shop_name,
                    ss.net_settlement_amount as settlement_amount,
                    ss.settlement_status,
                    CASE
                        WHEN ss.settlement_status = 'COMPLETED' THEN '완료'
                        WHEN ss.settlement_status = 'PENDING' THEN '대기'
                        WHEN ss.settlement_status = 'PROCESSING' THEN '처리중'
                        WHEN ss.settlement_status = 'FAILED' THEN '실패'
                        ELSE ss.settlement_status
                    END as settlement_status_kr
             FROM shop_settlements ss
             JOIN {$g5['shop_table']} s ON ss.shop_id = s.shop_id
             ORDER BY ss.appointment_datetime DESC, ss.settlement_id DESC
             LIMIT 10 ";
    $result = sql_query_pg($sql);
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $recent_settlements[] = [
                'settlement_date' => $row['settlement_date'],
                'shop_name' => $row['shop_name'] ?? '',
                'settlement_amount' => (int)($row['settlement_amount'] ?? 0),
                'settlement_status' => $row['settlement_status'] ?? '',
                'settlement_status_kr' => $row['settlement_status_kr'] ?? ''
            ];
        }
    }
    
    // 3-2. 신규 가맹점 등록 (최근 10개)
    $recent_shop_registrations = [];
    $sql = " SELECT s.shop_id,
                    s.created_at,
                    COALESCE(s.shop_name, s.name) as shop_name,
                    sc.name as category_name,
                    s.status,
                    CASE
                        WHEN s.status = 'active' THEN '정상'
                        WHEN s.status = 'pending' THEN '대기'
                        WHEN s.status = 'closed' THEN '폐업'
                        WHEN s.status = 'shutdown' THEN '중지'
                        ELSE s.status
                    END as status_kr
             FROM {$g5['shop_table']} s
             LEFT JOIN {$g5['shop_category_relation_table']} scr ON s.shop_id = scr.shop_id
             LEFT JOIN {$g5['shop_categories_table']} sc ON scr.category_id = sc.category_id AND char_length(sc.category_id) = 4
             ORDER BY s.created_at DESC
             LIMIT 10 ";
    $result = sql_query_pg($sql);
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $recent_shop_registrations[] = [
                'created_at' => $row['created_at'],
                'shop_name' => $row['shop_name'] ?? '',
                'category_name' => $row['category_name'] ?? '-',
                'status' => $row['status'] ?? '',
                'status_kr' => $row['status_kr'] ?? ''
            ];
        }
    }
    
    // 3-3. 신규 회원 가입 (최근 10명)
    $recent_member_signups = [];
    $sql = " SELECT customer_id,
                    created_at,
                    user_id,
                    name,
                    nickname,
                    email
             FROM {$customers_table}
             WHERE withdraw = 'N'
             ORDER BY created_at DESC
             LIMIT 10 ";
    $result = sql_query_pg($sql);
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $recent_member_signups[] = [
                'created_at' => $row['created_at'],
                'user_id' => $row['user_id'] ?? '',
                'name' => $row['name'] ?? ($row['nickname'] ?? ''),
                'nickname' => $row['nickname'] ?? '',
                'email' => $row['email'] ?? ''
            ];
        }
    }
    
    // 통합 응답 데이터 구성
    ob_clean();
    echo json_encode([
        'success' => true,
        'period_type' => $period_type,
        'range_start' => $range_start,
        'range_end' => $range_end,
        'data' => [
            'summary' => [
                'today_platform_sales' => $today_platform_sales,
                'total_shop_count' => $total_shop_count,
                'active_shop_count' => $active_shop_count,
                'total_member_count' => $total_member_count,
                'active_member_count' => $active_member_count,
                'today_appointment_count' => $today_appointment_count,
                'platform_avg_rating' => $platform_avg_rating,
                'pending_settlement_amount' => $pending_settlement_amount
            ],
            'sales_trend' => $sales_trend,
            'appointment_trend' => $appointment_trend,
            'shop_status_distribution' => $shop_status_distribution,
            'member_signup_trend' => $member_signup_trend,
            'recent_settlements' => $recent_settlements,
            'recent_shop_registrations' => $recent_shop_registrations,
            'recent_member_signups' => $recent_member_signups
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => '데이터 조회 중 오류가 발생했습니다: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

