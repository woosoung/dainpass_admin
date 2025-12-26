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

// 플랫폼 관리자 권한 체크 ($is_manager == true)
if (!$is_manager) {
    echo json_encode(['success' => false, 'message' => '플랫폼 관리자만 접근할 수 있습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 기간 계산 함수 (platform_statistics_shop_data.php 참조)
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

// 요청 파라미터 받기
$period_type = isset($_POST['period_type']) ? $_POST['period_type'] : 'daily';
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';

// 기간 계산
list($range_start, $range_end) = calculate_date_range($period_type, $start_date, $end_date);

// PostgreSQL 이스케이프 처리 함수
function pg_escape_string_safe($value) {
    global $g5;
    $pg_link = isset($g5['connect_pg']) ? $g5['connect_pg'] : null;
    if ($pg_link && function_exists('pg_escape_string')) {
        return pg_escape_string($pg_link, $value);
    }
    return addslashes($value);
}

try {
    global $g5;
    
    // customers 테이블명
    $customers_table = isset($g5['customers_table']) ? $g5['customers_table'] : 'customers';
    
    // 1. 주요 지표 카드 데이터
    
    // 전체 회원 수 (탈퇴하지 않은 회원)
    $sql = " SELECT COUNT(*) as cnt FROM {$customers_table} WHERE withdraw = 'N' ";
    $row = sql_fetch_pg($sql);
    $total_member_count = (int)($row['cnt'] ?? 0);
    
    // 신규 회원 수 (기간 내)
    $range_start_escaped = pg_escape_string_safe($range_start);
    $range_end_escaped = pg_escape_string_safe($range_end);
    $sql = " SELECT COUNT(*) as cnt FROM {$customers_table} 
             WHERE DATE(created_at) BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}' 
             AND withdraw = 'N' ";
    $row = sql_fetch_pg($sql);
    $new_member_count = (int)($row['cnt'] ?? 0);
    
    // 활성 회원 수 (최근 30일 이내 로그인)
    $sql = " SELECT COUNT(DISTINCT c.customer_id) as cnt
             FROM {$customers_table} c
             INNER JOIN connection_log cl ON c.customer_id = cl.customer_id
             WHERE cl.connect_time >= CURRENT_DATE - INTERVAL '30 days'
               AND cl.connect_status = 'SUCCESS'
               AND c.withdraw = 'N' ";
    $row = sql_fetch_pg($sql);
    $active_member_count = (int)($row['cnt'] ?? 0);
    
    // 탈퇴 회원 수
    $sql = " SELECT COUNT(*) as cnt FROM {$customers_table} WHERE withdraw = 'Y' ";
    $row = sql_fetch_pg($sql);
    $leave_member_count = (int)($row['cnt'] ?? 0);
    
    // 비활성 회원 수 (90일 이상 미로그인)
    $sql = " SELECT COUNT(DISTINCT c.customer_id) as cnt
             FROM {$customers_table} c
             LEFT JOIN connection_log cl ON c.customer_id = cl.customer_id 
               AND cl.connect_time >= CURRENT_DATE - INTERVAL '90 days'
               AND cl.connect_status = 'SUCCESS'
             WHERE cl.customer_id IS NULL
               AND c.withdraw = 'N' ";
    $row = sql_fetch_pg($sql);
    $inactive_member_count = (int)($row['cnt'] ?? 0);
    
    // 회원 활성화율
    $activation_rate = 0.0;
    if ($total_member_count > 0) {
        $activation_rate = round(($active_member_count / $total_member_count) * 100, 1);
    }
    
    // 2. 신규/기존 회원 비율
    $sql = " SELECT COUNT(*) as new_count
             FROM {$customers_table}
             WHERE DATE(created_at) BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}'
               AND withdraw = 'N' ";
    $row = sql_fetch_pg($sql);
    $new_member_ratio_count = (int)($row['new_count'] ?? 0);
    
    $sql = " SELECT COUNT(*) as existing_count
             FROM {$customers_table}
             WHERE DATE(created_at) < '{$range_start_escaped}'
               AND withdraw = 'N' ";
    $row = sql_fetch_pg($sql);
    $existing_member_count = (int)($row['existing_count'] ?? 0);
    
    $new_existing_ratio = [
        'new' => $new_member_ratio_count,
        'existing' => $existing_member_count
    ];
    
    // 3. 회원 상태별 분포
    // 정상 회원 (최근 90일 이내 로그인)
    $sql = " SELECT COUNT(DISTINCT c.customer_id) as active_count
             FROM {$customers_table} c
             INNER JOIN connection_log cl ON c.customer_id = cl.customer_id
             WHERE cl.connect_time >= CURRENT_DATE - INTERVAL '90 days'
               AND cl.connect_status = 'SUCCESS'
               AND c.withdraw = 'N' ";
    $row = sql_fetch_pg($sql);
    $normal_member_count = (int)($row['active_count'] ?? 0);
    
    // 비활성 회원 (90일 이상 미로그인)
    $sql = " SELECT COUNT(DISTINCT c.customer_id) as inactive_count
             FROM {$customers_table} c
             LEFT JOIN connection_log cl ON c.customer_id = cl.customer_id 
               AND cl.connect_time >= CURRENT_DATE - INTERVAL '90 days'
               AND cl.connect_status = 'SUCCESS'
             WHERE cl.customer_id IS NULL
               AND c.withdraw = 'N' ";
    $row = sql_fetch_pg($sql);
    $inactive_status_count = (int)($row['inactive_count'] ?? 0);
    
    $member_status_distribution = [
        'normal' => $normal_member_count,
        'leave' => $leave_member_count,
        'inactive' => $inactive_status_count
    ];
    
    // 4. 회원 가입 추이
    if ($period_type == 'weekly') {
        $date_group_expr = "DATE_TRUNC('week', CAST(created_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('week', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DOW FROM date_series.date) = 1";
    } elseif ($period_type == 'monthly') {
        $date_group_expr = "DATE_TRUNC('month', CAST(created_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('month', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DAY FROM date_series.date) = 1";
    } else {
        $date_group_expr = "CAST(created_at AS DATE)";
        $date_series_expr = "date_series.date";
        $date_series_filter = "";
    }
    
    $sql = "
        WITH date_series AS (
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
        member_data AS (
            SELECT 
                {$date_group_expr} AS period_date,
                COUNT(*) AS member_count
            FROM {$customers_table}
            WHERE DATE(created_at) BETWEEN '{$range_start_escaped}' AND '{$range_end_escaped}'
              AND withdraw = 'N'
            GROUP BY {$date_group_expr}
        )
        SELECT 
            dp.period_date AS date,
            COALESCE(md.member_count, 0) AS member_count
        FROM date_periods dp
        LEFT JOIN member_data md ON dp.period_date = md.period_date
        ORDER BY dp.period_date ASC
    ";
    $result = sql_query_pg($sql);
    $member_registration_trend = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $member_registration_trend[] = [
                'date' => $row['date'],
                'count' => (int)$row['member_count']
            ];
        }
    }
    
    // 5. 회원 활성도 분포
    $sql = "
        WITH customer_last_login AS (
            SELECT 
                c.customer_id,
                MAX(cl.connect_time)::date as last_login_date
            FROM {$customers_table} c
            LEFT JOIN connection_log cl ON c.customer_id = cl.customer_id 
              AND cl.connect_status = 'SUCCESS'
            WHERE c.withdraw = 'N'
            GROUP BY c.customer_id
        )
        SELECT 
            CASE 
                WHEN last_login_date >= CURRENT_DATE - INTERVAL '7 days' THEN '최근 7일'
                WHEN last_login_date >= CURRENT_DATE - INTERVAL '30 days' THEN '최근 30일'
                WHEN last_login_date >= CURRENT_DATE - INTERVAL '90 days' THEN '최근 90일'
                WHEN last_login_date >= CURRENT_DATE - INTERVAL '180 days' THEN '최근 180일'
                WHEN last_login_date >= CURRENT_DATE - INTERVAL '1 year' THEN '최근 1년'
                WHEN last_login_date IS NOT NULL THEN '1년 이상 미접속'
                ELSE '미접속'
            END as activity_period,
            COUNT(*) as member_count
        FROM customer_last_login
        GROUP BY activity_period
    ";
    $result = sql_query_pg($sql);
    $activity_distribution_raw = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $period = $row['activity_period'] ?? '';
            $count = (int)($row['member_count'] ?? 0);
            if (!isset($activity_distribution_raw[$period])) {
                $activity_distribution_raw[$period] = 0;
            }
            $activity_distribution_raw[$period] += $count;
        }
    }
    
    // 정렬된 활성도 분포
    $activity_order = ['최근 7일', '최근 30일', '최근 90일', '최근 180일', '최근 1년', '1년 이상 미접속', '미접속'];
    $member_activity_distribution = [];
    foreach ($activity_order as $period) {
        $member_activity_distribution[] = [
            'period' => $period,
            'count' => isset($activity_distribution_raw[$period]) ? $activity_distribution_raw[$period] : 0
        ];
    }
    
    // 6. 회원별 예약 금액 분포 (상위 20명)
    $sql = "
        SELECT 
            c.customer_id,
            c.name,
            c.nickname,
            COALESCE(SUM(asd.balance_amount), 0) as total_amount,
            COUNT(asd.shopdetail_id) as appointment_count
        FROM {$customers_table} c
        INNER JOIN shop_appointments sa ON c.customer_id = sa.customer_id AND sa.is_deleted = 'N'
        INNER JOIN appointment_shop_detail asd ON sa.appointment_id = asd.appointment_id
          AND asd.status = 'COMPLETED'
          AND asd.appointment_datetime >= '{$range_start_escaped} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end_escaped} 23:59:59'
        WHERE c.withdraw = 'N'
        GROUP BY c.customer_id, c.name, c.nickname
        ORDER BY total_amount DESC
        LIMIT 20
    ";
    $result = sql_query_pg($sql);
    $member_appointment_amount = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $member_appointment_amount[] = [
                'customer_id' => (int)$row['customer_id'],
                'name' => $row['name'] ?? '',
                'nickname' => $row['nickname'] ?? '',
                'display_name' => !empty($row['nickname']) ? $row['nickname'] : ($row['name'] ?? ''),
                'total_amount' => (int)$row['total_amount'],
                'appointment_count' => (int)$row['appointment_count']
            ];
        }
    }
    
    // 7. VIP 회원 목록 (상위 20개)
    $sql = "
        SELECT 
            c.customer_id,
            c.user_id,
            c.name,
            c.nickname,
            COALESCE(SUM(asd.balance_amount), 0) as total_amount,
            COUNT(asd.shopdetail_id) as appointment_count,
            CASE 
                WHEN COUNT(asd.shopdetail_id) > 0 
                THEN COALESCE(SUM(asd.balance_amount), 0) / COUNT(asd.shopdetail_id)
                ELSE 0
            END as avg_amount,
            MAX(asd.appointment_datetime) as last_appointment_date,
            c.created_at,
            CASE WHEN c.withdraw = 'Y' THEN '탈퇴' ELSE '정상' END as status
        FROM {$customers_table} c
        INNER JOIN shop_appointments sa ON c.customer_id = sa.customer_id AND sa.is_deleted = 'N'
        INNER JOIN appointment_shop_detail asd ON sa.appointment_id = asd.appointment_id
          AND asd.status = 'COMPLETED'
          AND asd.appointment_datetime >= '{$range_start_escaped} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end_escaped} 23:59:59'
        GROUP BY c.customer_id, c.user_id, c.name, c.nickname, c.created_at, c.withdraw
        ORDER BY total_amount DESC
        LIMIT 20
    ";
    $result = sql_query_pg($sql);
    $vip_member_list = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $vip_member_list[] = [
                'customer_id' => (int)$row['customer_id'],
                'user_id' => $row['user_id'] ?? '',
                'name' => $row['name'] ?? '',
                'nickname' => $row['nickname'] ?? '',
                'display_name' => !empty($row['nickname']) ? $row['nickname'] : ($row['name'] ?? ''),
                'total_amount' => (int)$row['total_amount'],
                'appointment_count' => (int)$row['appointment_count'],
                'avg_amount' => (int)($row['avg_amount'] ?? 0),
                'last_appointment_date' => $row['last_appointment_date'] ?? '',
                'created_at' => $row['created_at'] ?? '',
                'status' => $row['status'] ?? '정상'
            ];
        }
    }
    
    // 응답 데이터 구성
    echo json_encode([
        'success' => true,
        'data' => [
            'summary' => [
                'total_member_count' => $total_member_count,
                'new_member_count' => $new_member_count,
                'active_member_count' => $active_member_count,
                'leave_member_count' => $leave_member_count,
                'inactive_member_count' => $inactive_member_count,
                'activation_rate' => $activation_rate
            ],
            'new_existing_ratio' => $new_existing_ratio,
            'member_status_distribution' => $member_status_distribution,
            'member_registration_trend' => $member_registration_trend,
            'member_activity_distribution' => $member_activity_distribution,
            'member_appointment_amount' => $member_appointment_amount,
            'vip_member_list' => $vip_member_list,
            'range_start' => $range_start,
            'range_end' => $range_end
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '데이터 조회 중 오류가 발생했습니다: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
