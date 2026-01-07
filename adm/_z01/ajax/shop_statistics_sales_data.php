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

if (!function_exists('get_sales_summary')) {
function get_sales_summary($shop_id, $range_start, $range_end)
{
    // 오늘 기준
    $today = date('Y-m-d');
    $month_start = date('Y-m-01');
    $prev_month_start = date('Y-m-01', strtotime('-1 month'));
    $prev_month_end   = date('Y-m-t', strtotime('-1 month'));

    // 오늘 매출 & 예약 & 취소
    $sql_today = "
        SELECT 
            COALESCE(SUM(asd.balance_amount), 0) AS total_sales,
            COUNT(*) AS appointment_count
        FROM appointment_shop_detail AS asd
        WHERE asd.shop_id = {$shop_id}
          AND asd.appointment_datetime >= '{$today} 00:00:00'
          AND asd.appointment_datetime <= '{$today} 23:59:59'
          AND asd.status IN ('COMPLETED', 'CANCELLED')
    ";
    $row_today = sql_fetch_pg($sql_today);
    // sql_fetch_pg가 false를 반환할 수 있으므로 안전하게 처리
    if ($row_today === false || !is_array($row_today)) {
        $row_today = ['total_sales' => 0, 'appointment_count' => 0];
    }

    $sql_today_cancel = "
        SELECT COALESCE(SUM(psc.cancel_amount), 0) AS cancel_amount,
               COUNT(*) AS cancel_count
        FROM payments_shop_cancel AS psc
        WHERE psc.appointment_id IN (
            SELECT appointment_id
            FROM appointment_shop_detail
            WHERE shop_id = {$shop_id}
        )
          AND psc.created_at >= '{$today} 00:00:00'
          AND psc.created_at <= '{$today} 23:59:59'
    ";
    $row_today_cancel = sql_fetch_pg($sql_today_cancel);
    // sql_fetch_pg가 false를 반환할 수 있으므로 안전하게 처리
    if ($row_today_cancel === false || !is_array($row_today_cancel)) {
        $row_today_cancel = ['cancel_amount' => 0, 'cancel_count' => 0];
    }

    // 이번 달 매출
    $sql_month = "
        SELECT 
            COALESCE(SUM(asd.balance_amount), 0) AS total_sales,
            COUNT(*) AS appointment_count
        FROM appointment_shop_detail AS asd
        WHERE asd.shop_id = {$shop_id}
          AND asd.appointment_datetime >= '{$month_start} 00:00:00'
          AND asd.appointment_datetime <= '{$today} 23:59:59'
          AND asd.status IN ('COMPLETED', 'CANCELLED')
    ";
    $row_month = sql_fetch_pg($sql_month);
    // sql_fetch_pg가 false를 반환할 수 있으므로 안전하게 처리
    if ($row_month === false || !is_array($row_month)) {
        $row_month = ['total_sales' => 0, 'appointment_count' => 0];
    }

    // 전월 매출 (증감률 계산용)
    $sql_prev_month = "
        SELECT 
            COALESCE(SUM(asd.balance_amount), 0) AS total_sales,
            COUNT(*) AS appointment_count
        FROM appointment_shop_detail AS asd
        WHERE asd.shop_id = {$shop_id}
          AND asd.appointment_datetime >= '{$prev_month_start} 00:00:00'
          AND asd.appointment_datetime <= '{$prev_month_end} 23:59:59'
          AND asd.status IN ('COMPLETED', 'CANCELLED')
    ";
    $row_prev_month = sql_fetch_pg($sql_prev_month);
    // sql_fetch_pg가 false를 반환할 수 있으므로 안전하게 처리
    if ($row_prev_month === false || !is_array($row_prev_month)) {
        $row_prev_month = ['total_sales' => 0, 'appointment_count' => 0];
    }

    $month_vs_prev_rate = 0.0;
    if (!empty($row_prev_month['total_sales']) && $row_prev_month['total_sales'] > 0) {
        $month_vs_prev_rate = (($row_month['total_sales'] - $row_prev_month['total_sales']) / $row_prev_month['total_sales']) * 100.0;
    }

    // 누적 매출
    $sql_total = "
        SELECT 
            COALESCE(SUM(asd.balance_amount), 0) AS total_sales,
            COUNT(*) AS appointment_count
        FROM appointment_shop_detail AS asd
        WHERE asd.shop_id = {$shop_id}
          AND asd.status IN ('COMPLETED', 'CANCELLED')
    ";
    $row_total = sql_fetch_pg($sql_total);
    // sql_fetch_pg가 false를 반환할 수 있으므로 안전하게 처리
    if ($row_total === false || !is_array($row_total)) {
        $row_total = ['total_sales' => 0, 'appointment_count' => 0];
    }

    // 선택 기간(대시보드 카드 취소 통계 계산에 활용)
    $sql_range_base = "
        SELECT 
            COALESCE(SUM(asd.balance_amount), 0) AS total_sales,
            COUNT(*) AS appointment_count,
            COALESCE(SUM(asd.org_balance_amount - asd.balance_amount), 0) AS cancel_amount,
            COUNT(CASE WHEN asd.status = 'CANCELLED' THEN 1 END) AS cancel_count
        FROM appointment_shop_detail AS asd
        WHERE asd.shop_id = {$shop_id}
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
          AND asd.status IN ('COMPLETED', 'CANCELLED')
    ";
    $row_range = sql_fetch_pg($sql_range_base);
    // sql_fetch_pg가 false를 반환할 수 있으므로 안전하게 처리
    if ($row_range === false || !is_array($row_range)) {
        $row_range = ['total_sales' => 0, 'appointment_count' => 0, 'cancel_amount' => 0, 'cancel_count' => 0];
    }

    $cancel_rate = 0.0;
    if (!empty($row_range['appointment_count']) && $row_range['appointment_count'] > 0) {
        $cancel_rate = ($row_range['cancel_count'] / $row_range['appointment_count']) * 100.0;
    }

    $avg_amount = 0.0;
    if (!empty($row_range['appointment_count']) && $row_range['appointment_count'] > 0) {
        $avg_amount = $row_range['total_sales'] / $row_range['appointment_count'];
    }

    return [
        'today_sales_amount'        => (int)(isset($row_today['total_sales']) ? $row_today['total_sales'] : 0),
        'today_appointment_count'   => (int)(isset($row_today['appointment_count']) ? $row_today['appointment_count'] : 0),
        'today_cancel_amount'       => (int)(isset($row_today_cancel['cancel_amount']) ? $row_today_cancel['cancel_amount'] : 0),
        'today_cancel_count'        => (int)(isset($row_today_cancel['cancel_count']) ? $row_today_cancel['cancel_count'] : 0),

        'month_sales_amount'        => (int)(isset($row_month['total_sales']) ? $row_month['total_sales'] : 0),
        'month_appointment_count'   => (int)(isset($row_month['appointment_count']) ? $row_month['appointment_count'] : 0),
        'prev_month_sales_amount'   => (int)(isset($row_prev_month['total_sales']) ? $row_prev_month['total_sales'] : 0),
        'prev_month_appointment_count' => (int)(isset($row_prev_month['appointment_count']) ? $row_prev_month['appointment_count'] : 0),
        'month_vs_prev_rate'        => $month_vs_prev_rate,

        'total_sales_amount'        => (int)(isset($row_total['total_sales']) ? $row_total['total_sales'] : 0),
        'total_appointment_count'   => (int)(isset($row_total['appointment_count']) ? $row_total['appointment_count'] : 0),

        'range_total_sales_amount'  => (int)(isset($row_range['total_sales']) ? $row_range['total_sales'] : 0),
        'range_appointment_count'   => (int)(isset($row_range['appointment_count']) ? $row_range['appointment_count'] : 0),
        'range_cancel_amount'       => (int)(isset($row_range['cancel_amount']) ? $row_range['cancel_amount'] : 0),
        'range_cancel_count'        => (int)(isset($row_range['cancel_count']) ? $row_range['cancel_count'] : 0),
        'range_cancel_rate'         => $cancel_rate,
        'range_avg_amount'          => $avg_amount,
    ];
}
}

if (!function_exists('get_daily_sales')) {
function get_daily_sales($shop_id, $range_start, $range_end, $period_type = 'daily')
{
    // period_type에 따라 날짜 그룹화 방식 결정
    if ($period_type == 'weekly') {
        // 주별: 해당 주의 첫 번째 날짜로 그룹화 (월요일 기준)
        $date_group_expr = "DATE_TRUNC('week', CAST(appointment_datetime AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('week', date_series.date)::DATE";
        // 각 주의 월요일만 추출 (DOW: 0=일요일, 1=월요일, ..., 6=토요일)
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
        sales_data AS (
            SELECT 
                {$date_group_expr} AS period_date,
                COALESCE(SUM(asd.balance_amount), 0) AS total_sales,
                COALESCE(SUM(asd.org_balance_amount - asd.balance_amount), 0) AS cancel_amount,
                COUNT(*) AS appointment_count,
                COUNT(CASE WHEN asd.status = 'CANCELLED' THEN 1 END) AS cancel_count
            FROM appointment_shop_detail AS asd
            WHERE asd.shop_id = {$shop_id}
              AND asd.appointment_datetime >= '{$range_start} 00:00:00'
              AND asd.appointment_datetime <= '{$range_end} 23:59:59'
              AND asd.status IN ('COMPLETED', 'CANCELLED')
            GROUP BY {$date_group_expr}
        )
        SELECT 
            dp.period_date AS date,
            COALESCE(sd.total_sales, 0) AS total_sales,
            COALESCE(sd.cancel_amount, 0) AS cancel_amount,
            COALESCE(sd.appointment_count, 0) AS appointment_count,
            COALESCE(sd.cancel_count, 0) AS cancel_count
        FROM date_periods dp
        LEFT JOIN sales_data sd ON dp.period_date = sd.period_date
        ORDER BY dp.period_date ASC
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $total_sales = (int)$row['total_sales'];
            $cancel_amount = (int)$row['cancel_amount'];
            $net_sales = $total_sales - $cancel_amount;

            $rows[] = [
                'date'              => $row['date'],
                'total_sales'       => $total_sales,
                'cancel_amount'     => $cancel_amount,
                'net_sales'         => $net_sales,
                'appointment_count' => (int)$row['appointment_count'],
                'cancel_count'      => (int)$row['cancel_count'],
            ];
        }
    }

    return $rows;
}
}

if (!function_exists('get_payment_method_statistics')) {
function get_payment_method_statistics($shop_id, $range_start, $range_end)
{
    // appointment_shop_detail의 appointment_datetime 기준으로 필터링
    // payments 테이블과 조인하되, 예약 일시 기준으로 필터링
    $sql = "
        SELECT 
            p.payment_method,
            COALESCE(SUM(p.amount), 0) AS total_amount,
            COUNT(*) AS payment_count
        FROM payments AS p
        INNER JOIN appointment_shop_detail AS asd 
            ON p.appointment_id = asd.appointment_id
        WHERE asd.shop_id = {$shop_id}
          AND (p.pay_flag = 'GENERAL' OR p.pay_flag IS NULL)
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
          AND asd.status IN ('COMPLETED', 'CANCELLED')
          AND p.status = 'DONE'
        GROUP BY p.payment_method
        ORDER BY total_amount DESC
    ";

    $result = sql_query_pg($sql);
    $rows = [];
    $total_sum = 0;

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $amount = (int)$row['total_amount'];
            $total_sum += $amount;
            $rows[] = [
                'payment_method' => $row['payment_method'],
                'total_amount'   => $amount,
                'payment_count'  => (int)$row['payment_count'],
                'ratio'          => 0,
            ];
        }
    }

    if ($total_sum > 0) {
        foreach ($rows as $idx => $item) {
            $rows[$idx]['ratio'] = round(($item['total_amount'] * 100.0) / $total_sum, 2);
        }
    }

    return $rows;
}
}

if (!function_exists('get_cancellation_statistics')) {
function get_cancellation_statistics($shop_id, $range_start, $range_end, $summary)
{
    // summary에서 이미 계산한 값 재사용
    return [
        'total_cancel_amount' => isset($summary['range_cancel_amount']) ? (int)$summary['range_cancel_amount'] : 0,
        'cancel_count'        => isset($summary['range_cancel_count']) ? (int)$summary['range_cancel_count'] : 0,
        'cancel_rate'         => isset($summary['range_cancel_rate']) ? (float)$summary['range_cancel_rate'] : 0.0,
        'total_sales_amount'  => isset($summary['range_total_sales_amount']) ? (int)$summary['range_total_sales_amount'] : 0,
        'appointment_count'   => isset($summary['range_appointment_count']) ? (int)$summary['range_appointment_count'] : 0,
    ];
}
}

if (!function_exists('get_settlement_chart')) {
function get_settlement_chart($shop_id, $range_start, $range_end, $period_type = 'daily')
{
    // period_type에 따라 날짜 그룹화 방식 결정
    if ($period_type == 'weekly') {
        // 주별: 해당 주의 첫 번째 날짜로 그룹화 (월요일 기준)
        $date_group_expr = "DATE_TRUNC('week', CAST(settlement_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('week', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DOW FROM date_series.date) = 1"; // 월요일만
    } elseif ($period_type == 'monthly') {
        // 월별: 해당 월의 첫 번째 날짜로 그룹화
        $date_group_expr = "DATE_TRUNC('month', CAST(settlement_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('month', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DAY FROM date_series.date) = 1"; // 매월 1일만
    } else {
        // 일별
        $date_group_expr = "CAST(settlement_at AS DATE)";
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
                COALESCE(SUM(settlement_amount), 0)      AS total_amount,
                COUNT(*)                                  AS settlement_count,
                COUNT(CASE WHEN status = 'done' THEN 1 END) AS done_count
            FROM shop_settlement_log
            WHERE shop_id = {$shop_id}
              AND settlement_at >= '{$range_start} 00:00:00'
              AND settlement_at <= '{$range_end} 23:59:59'
            GROUP BY {$date_group_expr}
        )
        SELECT
            dp.period_date AS month,
            COALESCE(sd.total_amount, 0) AS total_amount,
            COALESCE(sd.settlement_count, 0) AS settlement_count,
            COALESCE(sd.done_count, 0) AS done_count
        FROM date_periods dp
        LEFT JOIN settlement_data sd ON dp.period_date = sd.period_date
        ORDER BY dp.period_date ASC
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'month'            => $row['month'],
                'total_amount'     => (int)$row['total_amount'],
                'settlement_count' => (int)$row['settlement_count'],
                'done_count'       => (int)$row['done_count'],
            ];
        }
    }

    return $rows;
}
}

if (!function_exists('get_settlement_logs')) {
function get_settlement_logs($shop_id, $range_start, $range_end)
{
    // shop_settlement_log의 개별 정산 처리 내역
    $sql = "
        SELECT
            settlement_at::date        AS settlement_date,
            settlement_start_at,
            settlement_end_at,
            settlement_amount,
            status
        FROM shop_settlement_log
        WHERE shop_id = {$shop_id}
          AND settlement_at >= '{$range_start} 00:00:00'
          AND settlement_at <= '{$range_end} 23:59:59'
        ORDER BY settlement_at DESC
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'settlement_date'    => $row['settlement_date'],
                'settlement_start_at' => $row['settlement_start_at'],
                'settlement_end_at'   => $row['settlement_end_at'],
                'settlement_amount'  => (int)$row['settlement_amount'],
                'status'             => $row['status'],
            ];
        }
    }

    return $rows;
}
}

if (!function_exists('get_settlement_deduction_statistics')) {
function get_settlement_deduction_statistics($shop_id, $range_start, $range_end)
{
    // 실 매출: appointment_shop_detail.balance_amount (예약 기준)
    $sql_sales = "
        SELECT 
            COALESCE(SUM(asd.balance_amount), 0) AS total_sales
        FROM appointment_shop_detail AS asd
        WHERE asd.shop_id = {$shop_id}
          AND asd.appointment_datetime >= '{$range_start} 00:00:00'
          AND asd.appointment_datetime <= '{$range_end} 23:59:59'
          AND asd.status = 'COMPLETED'
    ";
    $row_sales = sql_fetch_pg($sql_sales);
    $total_sales = (int)(isset($row_sales['total_sales']) ? $row_sales['total_sales'] : 0);

    // 정산금액: shop_settlements.net_settlement_amount (정산 기준)
    $sql_settlement = "
        SELECT 
            COALESCE(SUM(ss.net_settlement_amount), 0) AS total_settlement
        FROM shop_settlements AS ss
        WHERE ss.shop_id = {$shop_id}
          AND ss.appointment_datetime >= '{$range_start} 00:00:00'
          AND ss.appointment_datetime <= '{$range_end} 23:59:59'
          AND ss.settlement_status = 'COMPLETED'
    ";
    $row_settlement = sql_fetch_pg($sql_settlement);
    $total_settlement = (int)(isset($row_settlement['total_settlement']) ? $row_settlement['total_settlement'] : 0);

    // 차감액 = 실 매출 - 정산금액 (수수료 및 기타 차감 포함)
    $deduction_amount = $total_sales - $total_settlement;
    
    // 차감율 계산
    $deduction_rate = 0.0;
    if ($total_sales > 0) {
        $deduction_rate = ($deduction_amount / $total_sales) * 100.0;
    }

    return [
        'total_sales_amount'    => $total_sales,
        'total_settlement_amount' => $total_settlement,
        'deduction_amount'       => $deduction_amount,
        'deduction_rate'        => $deduction_rate,
    ];
}
}

// 공통: 가맹점 접근 권한 및 shop_id 확인 (페이지와 동일 로직이지만 JSON으로 응답)
// 이 블록은 단독 ajax 호출일 때만 실행되고,
// 다른 파일에서 라이브러리처럼 include 할 때는 실행되지 않도록 가드한다.
if (!defined('SHOP_STAT_LIB_MODE')) {
    $result = check_shop_access();
    $shop_id = $result['shop_id'];

    if (!$shop_id) {
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
        $summary          = get_sales_summary($shop_id, $range_start, $range_end);
        $daily_sales      = get_daily_sales($shop_id, $range_start, $range_end, $period_type);
        $payment_methods  = get_payment_method_statistics($shop_id, $range_start, $range_end);
        $cancellations    = get_cancellation_statistics($shop_id, $range_start, $range_end, $summary);
        $settlement_chart = get_settlement_chart($shop_id, $range_start, $range_end, $period_type);
        $settlement_logs  = get_settlement_logs($shop_id, $range_start, $range_end);
        $settlement_deduction = get_settlement_deduction_statistics($shop_id, $range_start, $range_end);

        echo json_encode([
            'success'          => true,
            'period_type'      => $period_type,
            'range_start'      => $range_start,
            'range_end'        => $range_end,
            'summary'          => $summary,
            'daily_sales'      => $daily_sales,
            'payment_methods'  => $payment_methods,
            'cancellations'    => $cancellations,
            'settlement_chart' => $settlement_chart,
            'settlement_logs'  => $settlement_logs,
            'settlement_deduction' => $settlement_deduction,
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => '데이터 조회 중 오류가 발생했습니다: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}