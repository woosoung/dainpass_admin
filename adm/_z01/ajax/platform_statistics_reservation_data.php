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

// 기간 계산 함수
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
$category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? trim($_POST['category_id']) : '';
$region = isset($_POST['region']) && $_POST['region'] !== '' ? trim($_POST['region']) : '';
$status = isset($_POST['status']) && $_POST['status'] !== '' ? trim($_POST['status']) : '';

// 기간 계산
list($range_start, $range_end) = calculate_date_range($period_type, $start_date, $end_date);

// 기간 일수 계산
$start_dt = new DateTime($range_start);
$end_dt = new DateTime($range_end);
$days_diff = $start_dt->diff($end_dt)->days + 1;

// PostgreSQL 이스케이프 헬퍼
global $g5;
$pg_link = isset($g5['connect_pg']) ? $g5['connect_pg'] : null;
$esc = function($v) use ($pg_link) {
    $s = (string)$v;
    return $pg_link && function_exists('pg_escape_string') ? pg_escape_string($pg_link, $s) : addslashes($s);
};

// 업종 필터링 조건
$category_filter_sql = '';
if ($category_id !== '' && $category_id !== '0') {
    $category_id_escaped = $esc($category_id);
    $category_id_length = strlen($category_id);
    if ($category_id_length == 2) {
        $category_filter_sql = " AND EXISTS (
            SELECT 1 FROM {$g5['shop_category_relation_table']} scr
            INNER JOIN {$g5['shop_table']} s_cat ON scr.shop_id = s_cat.shop_id
            WHERE s_cat.shop_id = asd.shop_id AND LEFT(scr.category_id, 2) = '{$category_id_escaped}'
        )";
    } else {
        $category_filter_sql = " AND EXISTS (
            SELECT 1 FROM {$g5['shop_category_relation_table']} scr
            INNER JOIN {$g5['shop_table']} s_cat ON scr.shop_id = s_cat.shop_id
            WHERE s_cat.shop_id = asd.shop_id AND scr.category_id = '{$category_id_escaped}'
        )";
    }
}

// 지역 필터링 조건
$region_filter_sql = '';
if ($region !== '') {
    $region_escaped = $esc($region);
    $region_filter_sql = " AND EXISTS (
        SELECT 1 FROM {$g5['shop_table']} s_region
        WHERE s_region.shop_id = asd.shop_id AND (
            CASE 
                WHEN s_region.addr1 IS NOT NULL AND s_region.addr1 != '' THEN 
                    CASE
                        WHEN s_region.addr1 LIKE '서울%' THEN '서울특별시'
                        WHEN s_region.addr1 LIKE '부산%' THEN '부산광역시'
                        WHEN s_region.addr1 LIKE '대구%' THEN '대구광역시'
                        WHEN s_region.addr1 LIKE '인천%' THEN '인천광역시'
                        WHEN s_region.addr1 LIKE '광주%' THEN '광주광역시'
                        WHEN s_region.addr1 LIKE '대전%' THEN '대전광역시'
                        WHEN s_region.addr1 LIKE '울산%' THEN '울산광역시'
                        WHEN s_region.addr1 LIKE '세종%' THEN '세종특별자치시'
                        WHEN s_region.addr1 LIKE '경기%' THEN '경기도'
                        WHEN s_region.addr1 LIKE '강원%' THEN '강원도'
                        WHEN s_region.addr1 LIKE '충북%' OR s_region.addr1 LIKE '충청북도%' THEN '충청북도'
                        WHEN s_region.addr1 LIKE '충남%' OR s_region.addr1 LIKE '충청남도%' THEN '충청남도'
                        WHEN s_region.addr1 LIKE '전북%' OR s_region.addr1 LIKE '전라북도%' THEN '전라북도'
                        WHEN s_region.addr1 LIKE '전남%' OR s_region.addr1 LIKE '전라남도%' THEN '전라남도'
                        WHEN s_region.addr1 LIKE '경북%' OR s_region.addr1 LIKE '경상북도%' THEN '경상북도'
                        WHEN s_region.addr1 LIKE '경남%' OR s_region.addr1 LIKE '경상남도%' THEN '경상남도'
                        WHEN s_region.addr1 LIKE '제주%' THEN '제주특별자치도'
                        ELSE NULL
                    END = '{$region_escaped}'
                ELSE FALSE
            END
        )
    )";
}

// 예약 상태 필터링 조건
$status_filter_sql = '';
if ($status !== '') {
    $status_escaped = $esc($status);
    $status_filter_sql = " AND asd.status = '{$status_escaped}'";
}

try {
    // 공통 WHERE 조건
    $common_where = " WHERE asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}' 
                      {$category_filter_sql} {$region_filter_sql} {$status_filter_sql}";
    
    // 1. 주요 지표 카드 데이터
    
    // 총 예약 건수
    $sql = " SELECT COUNT(*) as cnt 
             FROM appointment_shop_detail asd
             {$common_where}";
    $row = sql_fetch_pg($sql);
    $total_reservation_count = (int)($row['cnt'] ?? 0);
    
    // 완료된 예약 건수
    $sql = " SELECT COUNT(*) as cnt 
             FROM appointment_shop_detail asd
             WHERE asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
               AND asd.status = 'COMPLETED'
               {$category_filter_sql} {$region_filter_sql}";
    $row = sql_fetch_pg($sql);
    $completed_count = (int)($row['cnt'] ?? 0);
    
    // 취소된 예약 건수
    $sql = " SELECT COUNT(*) as cnt 
             FROM appointment_shop_detail asd
             WHERE asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
               AND asd.status IN ('CANCELLED', 'CANCELED', 'CANCEL')
               {$category_filter_sql} {$region_filter_sql}";
    $row = sql_fetch_pg($sql);
    $cancelled_count = (int)($row['cnt'] ?? 0);
    
    // 예약 완료율
    $completion_rate = 0.0;
    if ($total_reservation_count > 0) {
        $completion_rate = round(($completed_count / $total_reservation_count) * 100, 1);
    }
    
    // 예약 취소율
    $cancellation_rate = 0.0;
    if ($total_reservation_count > 0) {
        $cancellation_rate = round(($cancelled_count / $total_reservation_count) * 100, 1);
    }
    
    // 평균 일 예약 건수
    $avg_daily_reservation = 0;
    if ($days_diff > 0) {
        $avg_daily_reservation = round($total_reservation_count / $days_diff, 1);
    }
    
    // 2. 기간별 예약 건수 추이
    if ($period_type == 'weekly') {
        $date_group_expr = "DATE_TRUNC('week', asd.appointment_datetime::date)::DATE";
    } elseif ($period_type == 'monthly') {
        $date_group_expr = "DATE_TRUNC('month', asd.appointment_datetime::date)::DATE";
    } else {
        $date_group_expr = "asd.appointment_datetime::date";
    }
    
    $sql = " SELECT {$date_group_expr} as date,
                    COUNT(*) as reservation_count
             FROM appointment_shop_detail asd
             {$common_where}
             GROUP BY {$date_group_expr}
             ORDER BY date ASC";
    $result = sql_query_pg($sql);
    $reservation_trend = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $reservation_trend[] = [
                'date' => $row['date'],
                'count' => (int)$row['reservation_count']
            ];
        }
    }
    
    // 3. 예약 상태별 분포
    $sql = " SELECT asd.status,
                    COUNT(*) as reservation_count
             FROM appointment_shop_detail asd
             WHERE asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
               {$category_filter_sql} {$region_filter_sql}
             GROUP BY asd.status
             ORDER BY reservation_count DESC";
    $result = sql_query_pg($sql);
    $status_distribution = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $status_distribution[] = [
                'status' => $row['status'] ?: '미지정',
                'count' => (int)$row['reservation_count']
            ];
        }
    }
    
    // 4. 시간대별 예약 건수
    $sql = " SELECT EXTRACT(HOUR FROM asd.appointment_datetime) as hour,
                    COUNT(*) as reservation_count
             FROM appointment_shop_detail asd
             {$common_where}
             GROUP BY EXTRACT(HOUR FROM asd.appointment_datetime)
             ORDER BY hour ASC";
    $result = sql_query_pg($sql);
    $hourly_reservation = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $hourly_reservation[] = [
                'hour' => (int)$row['hour'],
                'count' => (int)$row['reservation_count']
            ];
        }
    }
    
    // 5. 요일별 예약 건수
    $sql = " SELECT EXTRACT(DOW FROM asd.appointment_datetime) as day_num,
                    CASE EXTRACT(DOW FROM asd.appointment_datetime)
                        WHEN 0 THEN '일요일'
                        WHEN 1 THEN '월요일'
                        WHEN 2 THEN '화요일'
                        WHEN 3 THEN '수요일'
                        WHEN 4 THEN '목요일'
                        WHEN 5 THEN '금요일'
                        WHEN 6 THEN '토요일'
                    END as day_of_week,
                    COUNT(*) as reservation_count
             FROM appointment_shop_detail asd
             {$common_where}
             GROUP BY EXTRACT(DOW FROM asd.appointment_datetime)
             ORDER BY day_num ASC";
    $result = sql_query_pg($sql);
    $weekly_reservation = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $weekly_reservation[] = [
                'day_num' => (int)$row['day_num'],
                'day_of_week' => $row['day_of_week'],
                'count' => (int)$row['reservation_count']
            ];
        }
    }
    
    // 6. 업종별 예약 건수 분포
    $sql = " SELECT sc.name as category_name,
                    COUNT(*) as reservation_count
             FROM appointment_shop_detail asd
             INNER JOIN {$g5['shop_table']} s ON asd.shop_id = s.shop_id
             LEFT JOIN {$g5['shop_category_relation_table']} scr ON s.shop_id = scr.shop_id
             LEFT JOIN {$g5['shop_categories_table']} sc ON scr.category_id = sc.category_id
             WHERE asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
               {$status_filter_sql}";
    if ($category_id !== '' && $category_id !== '0') {
        $category_id_escaped = $esc($category_id);
        $category_id_length = strlen($category_id);
        if ($category_id_length == 2) {
            $sql .= " AND LEFT(scr.category_id, 2) = '{$category_id_escaped}'";
        } else {
            $sql .= " AND scr.category_id = '{$category_id_escaped}'";
        }
    }
    if ($region !== '') {
        $sql .= $region_filter_sql;
    }
    $sql .= " GROUP BY sc.category_id, sc.name
              ORDER BY reservation_count DESC
              LIMIT 10";
    $result = sql_query_pg($sql);
    $category_reservation = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $category_reservation[] = [
                'category_name' => $row['category_name'] ?: '미지정',
                'count' => (int)$row['reservation_count']
            ];
        }
    }
    
    // 7. 지역별 예약 건수 분포
    $sql = " SELECT 
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
                            ELSE '기타'
                        END
                    ELSE '미지정'
                END as region,
                COUNT(*) as reservation_count
             FROM appointment_shop_detail asd
             INNER JOIN {$g5['shop_table']} s ON asd.shop_id = s.shop_id
             WHERE asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
               {$status_filter_sql}";
    if ($category_id !== '' && $category_id !== '0') {
        $sql .= " AND EXISTS (
            SELECT 1 FROM {$g5['shop_category_relation_table']} scr
            WHERE scr.shop_id = s.shop_id";
        $category_id_escaped = $esc($category_id);
        $category_id_length = strlen($category_id);
        if ($category_id_length == 2) {
            $sql .= " AND LEFT(scr.category_id, 2) = '{$category_id_escaped}'";
        } else {
            $sql .= " AND scr.category_id = '{$category_id_escaped}'";
        }
        $sql .= ")";
    }
    if ($region !== '') {
        $sql .= $region_filter_sql;
    }
    $sql .= " GROUP BY region
              ORDER BY reservation_count DESC";
    $result = sql_query_pg($sql);
    $region_reservation = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $region_reservation[] = [
                'region' => $row['region'] ?: '미지정',
                'count' => (int)$row['reservation_count']
            ];
        }
    }
    
    // 8. 가맹점별 예약 건수 순위 (상위 20개)
    $sql = " SELECT s.shop_id,
                    COALESCE(s.shop_name, s.name) as shop_name,
                    COUNT(*) as reservation_count
             FROM appointment_shop_detail asd
             INNER JOIN {$g5['shop_table']} s ON asd.shop_id = s.shop_id
             WHERE asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
               {$status_filter_sql}";
    if ($category_id !== '' && $category_id !== '0') {
        $sql .= " AND EXISTS (
            SELECT 1 FROM {$g5['shop_category_relation_table']} scr
            WHERE scr.shop_id = s.shop_id";
        $category_id_escaped = $esc($category_id);
        $category_id_length = strlen($category_id);
        if ($category_id_length == 2) {
            $sql .= " AND LEFT(scr.category_id, 2) = '{$category_id_escaped}'";
        } else {
            $sql .= " AND scr.category_id = '{$category_id_escaped}'";
        }
        $sql .= ")";
    }
    if ($region !== '') {
        $sql .= $region_filter_sql;
    }
    $sql .= " GROUP BY s.shop_id, s.shop_name, s.name
              ORDER BY reservation_count DESC
              LIMIT 20";
    $result = sql_query_pg($sql);
    $shop_reservation_rank = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $shop_reservation_rank[] = [
                'shop_id' => (int)$row['shop_id'],
                'shop_name' => $row['shop_name'],
                'count' => (int)$row['reservation_count']
            ];
        }
    }
    
    // 9. 예약 상세 내역 (최근 50건)
    // appointment_shop_detail에는 created_at이 없으므로 shop_appointments 테이블을 조인하여 가져옴
    // BOOKED 상태는 제외
    $sql = " SELECT asd.shopdetail_id,
                    asd.appointment_id,
                    COALESCE(s.shop_name, s.name) as shop_name,
                    asd.appointment_datetime,
                    asd.status,
                    asd.balance_amount,
                    sa.created_at
             FROM appointment_shop_detail asd
             LEFT JOIN {$g5['shop_table']} s ON asd.shop_id = s.shop_id
             LEFT JOIN shop_appointments sa ON asd.appointment_id = sa.appointment_id
             WHERE asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
               AND asd.status != 'BOOKED'
               {$category_filter_sql} {$region_filter_sql} {$status_filter_sql}
             ORDER BY asd.appointment_datetime DESC
             LIMIT 50";
    $result = sql_query_pg($sql);
    $reservation_list = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $reservation_list[] = [
                'shopdetail_id' => (int)$row['shopdetail_id'],
                'appointment_id' => $row['appointment_id'],
                'shop_name' => $row['shop_name'] ?: '-',
                'appointment_datetime' => $row['appointment_datetime'],
                'status' => $row['status'] ?: '-',
                'balance_amount' => (int)($row['balance_amount'] ?? 0),
                'created_at' => $row['created_at']
            ];
        }
    }
    
    // 응답 데이터 구성
    echo json_encode([
        'success' => true,
        'data' => [
            'summary' => [
                'total_reservation_count' => $total_reservation_count,
                'completed_count' => $completed_count,
                'cancelled_count' => $cancelled_count,
                'completion_rate' => $completion_rate,
                'cancellation_rate' => $cancellation_rate,
                'avg_daily_reservation' => $avg_daily_reservation
            ],
            'reservation_trend' => $reservation_trend,
            'status_distribution' => $status_distribution,
            'hourly_reservation' => $hourly_reservation,
            'weekly_reservation' => $weekly_reservation,
            'category_reservation' => $category_reservation,
            'region_reservation' => $region_reservation,
            'shop_reservation_rank' => $shop_reservation_rank,
            'reservation_list' => $reservation_list,
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
?>

