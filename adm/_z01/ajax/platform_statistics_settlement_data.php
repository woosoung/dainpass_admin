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
$status_filter = isset($_POST['status']) && $_POST['status'] !== '' ? trim($_POST['status']) : '';
$cycle_filter = isset($_POST['cycle']) && $_POST['cycle'] !== '' ? trim($_POST['cycle']) : '';

// 기간 계산
list($range_start, $range_end) = calculate_date_range($period_type, $start_date, $end_date);

// 정산 상태 필터링 조건 (shop_settlements 테이블은 대문자 사용)
$status_filter_sql = '';
if ($status_filter !== '') {
    global $g5;
    // 소문자로 전달된 경우 대문자로 변환
    $status_filter_upper = strtoupper($status_filter);
    $pg_link = isset($g5['connect_pg']) ? $g5['connect_pg'] : null;
    if ($pg_link && function_exists('pg_escape_string')) {
        $status_escaped = pg_escape_string($pg_link, $status_filter_upper);
    } else {
        $status_escaped = addslashes($status_filter_upper);
    }
    // JOIN이 있는 쿼리용 (ss 별칭 사용)
    $status_filter_sql = " AND ss.settlement_status = '{$status_escaped}' ";
}

// 정산 주기 필터링 조건 (shop 테이블의 settlement_cycle 사용)
$cycle_filter_sql = '';
if ($cycle_filter !== '') {
    global $g5;
    $pg_link = isset($g5['connect_pg']) ? $g5['connect_pg'] : null;
    if ($pg_link && function_exists('pg_escape_string')) {
        $cycle_escaped = pg_escape_string($pg_link, $cycle_filter);
    } else {
        $cycle_escaped = addslashes($cycle_filter);
    }
    $cycle_filter_sql = " AND s.settlement_cycle = '{$cycle_escaped}' ";
}

try {
    // 실제 데이터베이스 스키마에 맞게 작성
    // shop_settlements 테이블: net_settlement_amount, appointment_datetime, settlement_status (대문자)
    
    // 1. 주요 지표 카드 데이터
    
    // 전체 정산 금액 (완료된 정산만) - net_settlement_amount 사용
    $sql = " SELECT COALESCE(SUM(net_settlement_amount), 0) as total_amount 
             FROM shop_settlements 
             WHERE settlement_status = 'COMPLETED' ";
    $row = sql_fetch_pg($sql);
    $total_settlement_amount = (int)($row['total_amount'] ?? 0);
    
    // 대기 중인 정산 금액
    $sql = " SELECT COALESCE(SUM(net_settlement_amount), 0) as total_amount 
             FROM shop_settlements 
             WHERE settlement_status = 'PENDING' ";
    $row = sql_fetch_pg($sql);
    $pending_settlement_amount = (int)($row['total_amount'] ?? 0);
    
    // 기간 내 정산 금액 (완료된 정산만) - appointment_datetime 기준
    $sql = " SELECT COALESCE(SUM(net_settlement_amount), 0) as total_amount 
             FROM shop_settlements 
             WHERE settlement_status = 'COMPLETED' 
               AND DATE(appointment_datetime) BETWEEN '{$range_start}' AND '{$range_end}' ";
    $row = sql_fetch_pg($sql);
    $period_settlement_amount = (int)($row['total_amount'] ?? 0);
    
    // 정산 완료 건수
    $sql = " SELECT COUNT(*) as cnt 
             FROM shop_settlements 
             WHERE settlement_status = 'COMPLETED' ";
    $row = sql_fetch_pg($sql);
    $completed_count = (int)($row['cnt'] ?? 0);
    
    // 정산 대기 건수
    $sql = " SELECT COUNT(*) as cnt 
             FROM shop_settlements 
             WHERE settlement_status = 'PENDING' ";
    $row = sql_fetch_pg($sql);
    $pending_count = (int)($row['cnt'] ?? 0);
    
    // 평균 정산 금액
    $avg_settlement_amount = 0.0;
    if ($completed_count > 0) {
        $avg_settlement_amount = round($total_settlement_amount / $completed_count, 0);
    }
    
    // 2. 정산 상태별 분포 (파이 차트용) - net_settlement_amount 사용
    // 주기 필터는 shop 테이블 JOIN이 필요하므로 여기서는 제외
    $status_filter_for_distribution = '';
    if ($status_filter !== '') {
        global $g5;
        $status_filter_upper = strtoupper($status_filter);
        $pg_link = isset($g5['connect_pg']) ? $g5['connect_pg'] : null;
        if ($pg_link && function_exists('pg_escape_string')) {
            $status_escaped = pg_escape_string($pg_link, $status_filter_upper);
        } else {
            $status_escaped = addslashes($status_filter_upper);
        }
        $status_filter_for_distribution = " AND settlement_status = '{$status_escaped}' ";
    }
    $sql = " SELECT settlement_status, COUNT(*) as cnt, SUM(net_settlement_amount) as total_amount
             FROM shop_settlements 
             WHERE 1=1 {$status_filter_for_distribution}
             GROUP BY settlement_status ";
    $result = sql_query_pg($sql);
    $status_distribution = [
        'COMPLETED' => ['count' => 0, 'amount' => 0],
        'PENDING' => ['count' => 0, 'amount' => 0]
    ];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $status = strtoupper($row['settlement_status'] ?? '');
            if (isset($status_distribution[$status])) {
                $status_distribution[$status]['count'] = (int)$row['cnt'];
                $status_distribution[$status]['amount'] = (int)($row['total_amount'] ?? 0);
            } else {
                // 알 수 없는 상태도 추가
                $status_distribution[$status] = [
                    'count' => (int)$row['cnt'],
                    'amount' => (int)($row['total_amount'] ?? 0)
                ];
            }
        }
    }
    
    // 3. 기간별 정산 금액 추이 - appointment_datetime 기준
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
        settlement_data AS (
            SELECT 
                {$date_group_expr} AS period_date,
                SUM(net_settlement_amount) AS total_amount
            FROM shop_settlements
            WHERE settlement_status = 'COMPLETED'
              AND DATE(appointment_datetime) BETWEEN '{$range_start}' AND '{$range_end}'
            GROUP BY {$date_group_expr}
        )
        SELECT 
            dp.period_date AS date,
            COALESCE(sd.total_amount, 0) AS amount
        FROM date_periods dp
        LEFT JOIN settlement_data sd ON dp.period_date = sd.period_date
        ORDER BY dp.period_date ASC
    ";
    $result = sql_query_pg($sql);
    $settlement_trend = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $settlement_trend[] = [
                'date' => $row['date'],
                'amount' => (int)$row['amount']
            ];
        }
    }
    
    // 4. 정산 주기별 분포 (shop 테이블의 settlement_cycle 사용)
    // 검색 필터와 상관없이 모든 주기(일일, 주간, 월간)를 항상 표시
    $sql = " SELECT s.settlement_cycle, 
                    COUNT(*) as count, 
                    SUM(ss.net_settlement_amount) as total_amount
             FROM shop_settlements ss
             JOIN {$g5['shop_table']} s ON ss.shop_id = s.shop_id
             WHERE ss.settlement_status = 'COMPLETED'
             GROUP BY s.settlement_cycle
             ORDER BY 
                CASE s.settlement_cycle
                    WHEN 'daily' THEN 1
                    WHEN 'weekly' THEN 2
                    WHEN 'monthly' THEN 3
                    ELSE 4
                END ";
    $result = sql_query_pg($sql);
    $cycle_distribution = [];
    // 모든 주기를 기본값으로 초기화
    $all_cycles = ['daily' => ['count' => 0, 'amount' => 0], 'weekly' => ['count' => 0, 'amount' => 0], 'monthly' => ['count' => 0, 'amount' => 0]];
    
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $cycle = strtolower($row['settlement_cycle'] ?? '');
            if (isset($all_cycles[$cycle])) {
                $all_cycles[$cycle]['count'] = (int)$row['count'];
                $all_cycles[$cycle]['amount'] = (int)$row['total_amount'];
            }
        }
    }
    
    // 모든 주기를 순서대로 배열에 추가
    foreach (['daily', 'weekly', 'monthly'] as $cycle) {
        $cycle_distribution[] = [
            'cycle' => $cycle,
            'count' => $all_cycles[$cycle]['count'],
            'amount' => $all_cycles[$cycle]['amount']
        ];
    }
    
    // 5. 가맹점별 정산 금액 순위 (상위 20개) - appointment_datetime 기준
    $sql = "
        SELECT s.shop_id, 
               COALESCE(s.shop_name, s.name) as shop_name,
               SUM(ss.net_settlement_amount) as total_settlement
        FROM shop_settlements ss
        JOIN {$g5['shop_table']} s ON ss.shop_id = s.shop_id
        WHERE ss.settlement_status = 'COMPLETED'
          AND DATE(ss.appointment_datetime) BETWEEN '{$range_start}' AND '{$range_end}'
          {$cycle_filter_sql}
        GROUP BY s.shop_id, s.shop_name, s.name
        ORDER BY total_settlement DESC
        LIMIT 20
    ";
    $result = sql_query_pg($sql);
    $shop_settlement_rank = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $shop_settlement_rank[] = [
                'shop_id' => (int)$row['shop_id'],
                'shop_name' => $row['shop_name'] ?? '미지정',
                'total_settlement' => (int)$row['total_settlement']
            ];
        }
    }
    
    // 6. 정산 처리 내역 (상위 20개) - appointment_datetime 기준
    $sql = "
        SELECT ss.settlement_id, 
               COALESCE(s.shop_name, s.name) as shop_name,
               ss.net_settlement_amount as settlement_amount,
               ss.settlement_status,
               ss.appointment_datetime as settlement_date,
               s.settlement_cycle
        FROM shop_settlements ss
        JOIN {$g5['shop_table']} s ON ss.shop_id = s.shop_id
        WHERE DATE(ss.appointment_datetime) BETWEEN '{$range_start}' AND '{$range_end}'
          {$status_filter_sql} {$cycle_filter_sql}
        ORDER BY ss.appointment_datetime DESC
        LIMIT 20
    ";
    $result = sql_query_pg($sql);
    $settlement_detail_list = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            // 상태 한글 변환 (shop_settlements 테이블은 대문자 사용)
            $status_kr = '미지정';
            $status_upper = strtoupper($row['settlement_status'] ?? '');
            switch ($status_upper) {
                case 'COMPLETED': $status_kr = '완료'; break;
                case 'PENDING': $status_kr = '대기'; break;
                default: $status_kr = $row['settlement_status'] ?? '미지정'; break;
            }
            
            // 주기 한글 변환
            $cycle_kr = '미지정';
            $cycle_lower = strtolower($row['settlement_cycle'] ?? '');
            switch ($cycle_lower) {
                case 'daily': $cycle_kr = '일일'; break;
                case 'weekly': $cycle_kr = '주간'; break;
                case 'monthly': $cycle_kr = '월간'; break;
            }
            
            $settlement_detail_list[] = [
                'settlement_id' => (int)$row['settlement_id'],
                'shop_name' => $row['shop_name'] ?? '미지정',
                'settlement_amount' => (int)$row['settlement_amount'],
                'settlement_status' => $row['settlement_status'],
                'settlement_status_kr' => $status_kr,
                'settlement_date' => $row['settlement_date'],
                'settlement_cycle' => $row['settlement_cycle'],
                'settlement_cycle_kr' => $cycle_kr
            ];
        }
    }
    
    // 응답 데이터 구성
    echo json_encode([
        'success' => true,
        'data' => [
            'summary' => [
                'total_settlement_amount' => $total_settlement_amount,
                'pending_settlement_amount' => $pending_settlement_amount,
                'period_settlement_amount' => $period_settlement_amount,
                'completed_count' => $completed_count,
                'pending_count' => $pending_count,
                'avg_settlement_amount' => $avg_settlement_amount
            ],
            'status_distribution' => $status_distribution,
            'settlement_trend' => $settlement_trend,
            'cycle_distribution' => $cycle_distribution,
            'shop_settlement_rank' => $shop_settlement_rank,
            'settlement_detail_list' => $settlement_detail_list,
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

