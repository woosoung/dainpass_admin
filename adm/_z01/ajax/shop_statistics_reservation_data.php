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

// 예약 통계 요약 정보
if (!function_exists('get_reservation_summary')) {
function get_reservation_summary($shop_id, $range_start, $range_end)
{
    // 전체 예약 건수
    $sql_total = "
        SELECT COUNT(*) AS total_count
        FROM appointment_shop_detail
        WHERE shop_id = {$shop_id}
          AND appointment_datetime >= '{$range_start} 00:00:00'
          AND appointment_datetime <= '{$range_end} 23:59:59'
    ";
    $row_total = sql_fetch_pg($sql_total);
    $total_count = (int)$row_total['total_count'];

    // 활성 예약 건수
    $sql_active = "
        SELECT COUNT(*) AS active_count
        FROM shop_appointments sa
        INNER JOIN appointment_shop_detail asd ON sa.appointment_id = asd.appointment_id
        WHERE asd.shop_id = {$shop_id}
          AND sa.is_deleted = 'N'
          AND sa.status IN ('BOOKED', 'CONFIRMED')
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
    ";
    $row_active = sql_fetch_pg($sql_active);
    $active_count = (int)$row_active['active_count'];

    // 취소율 통계
    $sql_cancel = "
        SELECT 
            COUNT(*) AS total_count,
            COUNT(CASE WHEN status = 'CANCELLED' OR request_cancel_datetime IS NOT NULL THEN 1 END) AS cancel_count,
            ROUND(
                (COUNT(CASE WHEN status = 'CANCELLED' OR request_cancel_datetime IS NOT NULL THEN 1 END)::numeric / NULLIF(COUNT(*), 0)) * 100, 
                2
            ) AS cancel_rate
        FROM appointment_shop_detail
        WHERE shop_id = {$shop_id}
          AND appointment_datetime >= '{$range_start} 00:00:00'
          AND appointment_datetime <= '{$range_end} 23:59:59'
    ";
    $row_cancel = sql_fetch_pg($sql_cancel);
    $cancel_count = (int)$row_cancel['cancel_count'];
    $cancel_rate = (float)$row_cancel['cancel_rate'];

    // 재방문율 통계
    // 고유 고객 수
    $sql_unique_customer = "
        SELECT COUNT(DISTINCT customer_id) AS unique_customer_count
        FROM shop_appointments
        WHERE customer_id IS NOT NULL
          AND appointment_id IN (
              SELECT appointment_id
              FROM appointment_shop_detail
              WHERE shop_id = {$shop_id}
                AND appointment_datetime >= '{$range_start} 00:00:00'
                AND appointment_datetime <= '{$range_end} 23:59:59'
          )
    ";
    $row_unique = sql_fetch_pg($sql_unique_customer);
    $unique_customer_count = (int)$row_unique['unique_customer_count'];

    // 재방문 고객 수 (2회 이상 예약)
    $sql_repeat = "
        SELECT COUNT(*) AS repeat_customer_count
        FROM (
            SELECT customer_id, COUNT(*) AS appointment_count
            FROM shop_appointments
            WHERE customer_id IS NOT NULL
              AND appointment_id IN (
                  SELECT appointment_id
                  FROM appointment_shop_detail
                  WHERE shop_id = {$shop_id}
                    AND appointment_datetime >= '{$range_start} 00:00:00'
                    AND appointment_datetime <= '{$range_end} 23:59:59'
              )
            GROUP BY customer_id
            HAVING COUNT(*) >= 2
        ) AS repeat_customers
    ";
    $row_repeat = sql_fetch_pg($sql_repeat);
    $repeat_customer_count = (int)$row_repeat['repeat_customer_count'];

    // 재방문율 계산
    $repeat_visit_rate = 0.0;
    if ($unique_customer_count > 0) {
        $repeat_visit_rate = round(($repeat_customer_count / $unique_customer_count) * 100.0, 2);
    }

    // 평균 예약 횟수 (고객당)
    $avg_appointment_per_customer = 0.0;
    if ($unique_customer_count > 0) {
        $avg_appointment_per_customer = round($total_count / $unique_customer_count, 2);
    }

    return [
        'total_appointment_count' => $total_count,
        'active_appointment_count' => $active_count,
        'cancel_count' => $cancel_count,
        'cancel_rate' => $cancel_rate,
        'unique_customer_count' => $unique_customer_count,
        'repeat_customer_count' => $repeat_customer_count,
        'repeat_visit_rate' => $repeat_visit_rate,
        'avg_appointment_per_customer' => $avg_appointment_per_customer,
    ];
}
}

// 기간별 예약 건수 추이 (일별)
if (!function_exists('get_daily_appointments')) {
function get_daily_appointments($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            CAST(appointment_datetime AS DATE) AS date,
            COUNT(*) AS total_count,
            COUNT(CASE WHEN status = 'CANCELLED' OR request_cancel_datetime IS NOT NULL THEN 1 END) AS cancel_count
        FROM appointment_shop_detail
        WHERE shop_id = {$shop_id}
          AND appointment_datetime >= '{$range_start} 00:00:00'
          AND appointment_datetime <= '{$range_end} 23:59:59'
        GROUP BY CAST(appointment_datetime AS DATE)
        ORDER BY date ASC
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'date' => $row['date'],
                'total_count' => (int)$row['total_count'],
                'cancel_count' => (int)$row['cancel_count'],
            ];
        }
    }

    return $rows;
}
}

// 상태별 예약 분포
if (!function_exists('get_status_distribution')) {
function get_status_distribution($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT status, COUNT(*) AS count
        FROM appointment_shop_detail
        WHERE shop_id = {$shop_id}
          AND appointment_datetime >= '{$range_start} 00:00:00'
          AND appointment_datetime <= '{$range_end} 23:59:59'
        GROUP BY status
        ORDER BY count DESC
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'status' => $row['status'],
                'count' => (int)$row['count'],
            ];
        }
    }

    return $rows;
}
}

// 시간대별 예약 건수 (0~23시)
if (!function_exists('get_hourly_appointments')) {
function get_hourly_appointments($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            EXTRACT(HOUR FROM appointment_datetime)::integer AS hour,
            COUNT(*) AS appointment_count
        FROM appointment_shop_detail
        WHERE shop_id = {$shop_id}
          AND appointment_datetime >= '{$range_start} 00:00:00'
          AND appointment_datetime <= '{$range_end} 23:59:59'
        GROUP BY EXTRACT(HOUR FROM appointment_datetime)
        ORDER BY hour
    ";

    $result = sql_query_pg($sql);
    $rows = [];
    $hour_map = [];

    // 0~23시 모두 포함하도록 초기화
    for ($i = 0; $i < 24; $i++) {
        $hour_map[$i] = 0;
    }

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $hour = (int)$row['hour'];
            $hour_map[$hour] = (int)$row['appointment_count'];
        }
    }

    // 배열로 변환
    foreach ($hour_map as $hour => $count) {
        $rows[] = [
            'hour' => $hour,
            'appointment_count' => $count,
        ];
    }

    return $rows;
}
}

// 요일별 예약 건수
if (!function_exists('get_weekly_appointments')) {
function get_weekly_appointments($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            EXTRACT(DOW FROM appointment_datetime)::integer AS weekday,
            COUNT(*) AS appointment_count
        FROM appointment_shop_detail
        WHERE shop_id = {$shop_id}
          AND appointment_datetime >= '{$range_start} 00:00:00'
          AND appointment_datetime <= '{$range_end} 23:59:59'
        GROUP BY EXTRACT(DOW FROM appointment_datetime)
        ORDER BY weekday
    ";

    $result = sql_query_pg($sql);
    $rows = [];
    $weekday_map = [];

    // 0(일)~6(토) 모두 포함하도록 초기화
    $weekday_names = ['일', '월', '화', '수', '목', '금', '토'];
    for ($i = 0; $i < 7; $i++) {
        $weekday_map[$i] = ['weekday' => $i, 'weekday_name' => $weekday_names[$i], 'appointment_count' => 0];
    }

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $weekday = (int)$row['weekday'];
            $weekday_map[$weekday]['appointment_count'] = (int)$row['appointment_count'];
        }
    }

    // 배열로 변환
    foreach ($weekday_map as $item) {
        $rows[] = $item;
    }

    return $rows;
}
}

// 취소율 추이 (일별)
if (!function_exists('get_cancel_trend')) {
function get_cancel_trend($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            CAST(appointment_datetime AS DATE) AS date,
            COUNT(*) AS total_count,
            COUNT(CASE WHEN status = 'CANCELLED' OR request_cancel_datetime IS NOT NULL THEN 1 END) AS cancel_count,
            CASE 
                WHEN COUNT(*) > 0 THEN 
                    ROUND((COUNT(CASE WHEN status = 'CANCELLED' OR request_cancel_datetime IS NOT NULL THEN 1 END)::numeric / COUNT(*)) * 100, 2)
                ELSE 0
            END AS cancel_rate
        FROM appointment_shop_detail
        WHERE shop_id = {$shop_id}
          AND appointment_datetime >= '{$range_start} 00:00:00'
          AND appointment_datetime <= '{$range_end} 23:59:59'
        GROUP BY CAST(appointment_datetime AS DATE)
        ORDER BY date ASC
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'date' => $row['date'],
                'total_count' => (int)$row['total_count'],
                'cancel_count' => (int)$row['cancel_count'],
                'cancel_rate' => (float)$row['cancel_rate'],
            ];
        }
    }

    return $rows;
}
}

// 요일별 시간대별 예약 패턴
if (!function_exists('get_weekday_hour_pattern')) {
function get_weekday_hour_pattern($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            EXTRACT(DOW FROM appointment_datetime)::integer AS weekday,
            EXTRACT(HOUR FROM appointment_datetime)::integer AS hour,
            COUNT(*) AS appointment_count
        FROM appointment_shop_detail
        WHERE shop_id = {$shop_id}
          AND appointment_datetime >= '{$range_start} 00:00:00'
          AND appointment_datetime <= '{$range_end} 23:59:59'
        GROUP BY EXTRACT(DOW FROM appointment_datetime), EXTRACT(HOUR FROM appointment_datetime)
        ORDER BY weekday, hour
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    $weekday_names = ['일', '월', '화', '수', '목', '금', '토'];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'weekday' => (int)$row['weekday'],
                'weekday_name' => $weekday_names[(int)$row['weekday']],
                'hour' => (int)$row['hour'],
                'appointment_count' => (int)$row['appointment_count'],
            ];
        }
    }

    return $rows;
}
}

// 영업 시간 정보 조회
if (!function_exists('get_business_hours_data')) {
function get_business_hours_data($shop_id)
{
    $sql = "
        SELECT 
            weekday,
            slot_seq,
            open_time,
            close_time,
            is_open
        FROM business_hour_slots
        WHERE shop_id = {$shop_id}
          AND is_open = true
        ORDER BY weekday, slot_seq
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'weekday' => (int)$row['weekday'],
                'slot_seq' => (int)$row['slot_seq'],
                'open_time' => $row['open_time'],
                'close_time' => $row['close_time'],
                'is_open' => $row['is_open'],
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
    $summary = get_reservation_summary($shop_id, $range_start, $range_end);
    $daily_appointments = get_daily_appointments($shop_id, $range_start, $range_end);
    $status_distribution = get_status_distribution($shop_id, $range_start, $range_end);
    $hourly_appointments = get_hourly_appointments($shop_id, $range_start, $range_end);
    $weekly_appointments = get_weekly_appointments($shop_id, $range_start, $range_end);
    $cancel_trend = get_cancel_trend($shop_id, $range_start, $range_end);
    $weekday_hour_pattern = get_weekday_hour_pattern($shop_id, $range_start, $range_end);
    $business_hours_data = get_business_hours_data($shop_id);

    echo json_encode([
        'success' => true,
        'period_type' => $period_type,
        'range_start' => $range_start,
        'range_end' => $range_end,
        'summary' => $summary,
        'daily_appointments' => $daily_appointments,
        'status_distribution' => $status_distribution,
        'hourly_appointments' => $hourly_appointments,
        'weekly_appointments' => $weekly_appointments,
        'cancel_trend' => $cancel_trend,
        'weekday_hour_pattern' => $weekday_hour_pattern,
        'business_hours_data' => $business_hours_data,
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '데이터 조회 중 오류가 발생했습니다: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
exit;

