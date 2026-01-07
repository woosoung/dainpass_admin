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

// 기간 계산
list($range_start, $range_end) = calculate_date_range($period_type, $start_date, $end_date);

// 업종 필터링 조건
$category_filter_sql = '';
if ($category_id !== '' && $category_id !== '0') {
    global $g5;
    $pg_link = isset($g5['connect_pg']) ? $g5['connect_pg'] : null;
    if ($pg_link && function_exists('pg_escape_string')) {
        $category_id_escaped = pg_escape_string($pg_link, $category_id);
} else {
        $category_id_escaped = addslashes($category_id);
    }
    
    $category_id_length = strlen($category_id);
    if ($category_id_length == 2) {
        $category_filter_sql = " AND EXISTS (
            SELECT 1 FROM {$g5['shop_category_relation_table']} scr
            INNER JOIN {$g5['shop_table']} s_cat ON scr.shop_id = s_cat.shop_id
            WHERE scr.shop_id = asd.shop_id AND LEFT(scr.category_id, 2) = '{$category_id_escaped}'
        )";
    } else {
        $category_filter_sql = " AND EXISTS (
            SELECT 1 FROM {$g5['shop_category_relation_table']} scr
            INNER JOIN {$g5['shop_table']} s_cat ON scr.shop_id = s_cat.shop_id
            WHERE scr.shop_id = asd.shop_id AND scr.category_id = '{$category_id_escaped}'
        )";
    }
}

// 지역 필터링 조건
$region_filter_sql = '';
if ($region !== '') {
    $pg_link = isset($g5['connect_pg']) ? $g5['connect_pg'] : null;
    if ($pg_link && function_exists('pg_escape_string')) {
        $region_escaped = pg_escape_string($pg_link, $region);
    } else {
        $region_escaped = addslashes($region);
    }
    
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

try {
    // 1. 주요 지표 카드 데이터
    
    // 총 매출액
    $sql = " SELECT COALESCE(SUM(balance_amount), 0) as total_sales
             FROM appointment_shop_detail asd
             WHERE asd.status = 'COMPLETED'
               AND asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
               {$category_filter_sql} {$region_filter_sql} ";
    $row = sql_fetch_pg($sql);
    $total_sales = (int)($row['total_sales'] ?? 0);
    
    // 총 정산액
    $sql = " SELECT COALESCE(SUM(settlement_amount), 0) as total_settlement
             FROM shop_settlement_log ssl
             WHERE ssl.settlement_at::date BETWEEN '{$range_start}' AND '{$range_end}' ";
    $row = sql_fetch_pg($sql);
    $total_settlement = (int)($row['total_settlement'] ?? 0);
    
    // 총 결제 건수
    $sql = " SELECT COUNT(*) as total_payment_count
             FROM appointment_shop_detail asd
             WHERE asd.status = 'COMPLETED'
               AND asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
               {$category_filter_sql} {$region_filter_sql} ";
    $row = sql_fetch_pg($sql);
    $total_payment_count = (int)($row['total_payment_count'] ?? 0);
    
    // 평균 일 매출
    $date_diff = (strtotime($range_end) - strtotime($range_start)) / 86400 + 1;
    $avg_daily_sales = $date_diff > 0 ? (int)($total_sales / $date_diff) : 0;
    
    // 평균 결제 금액
    $avg_payment_amount = $total_payment_count > 0 ? (int)($total_sales / $total_payment_count) : 0;
    
    // 정산 대기 금액
    $sql = " SELECT COALESCE(SUM(asd.balance_amount), 0) as pending_settlement
             FROM appointment_shop_detail asd
             LEFT JOIN shop_settlements ss ON ss.shopdetail_id = asd.shopdetail_id AND ss.pay_flag = 'GENERAL'
             WHERE asd.status = 'COMPLETED'
               AND asd.is_settlement_target = true
               AND ss.settlement_id IS NULL
               {$category_filter_sql} {$region_filter_sql} ";
    $row = sql_fetch_pg($sql);
    $pending_settlement = (int)($row['pending_settlement'] ?? 0);
    
    // 2. 기간별 플랫폼 매출 추이
    if ($period_type == 'weekly') {
        $date_group_expr = "DATE_TRUNC('week', asd.appointment_datetime::date)::DATE";
    } elseif ($period_type == 'monthly') {
        $date_group_expr = "DATE_TRUNC('month', asd.appointment_datetime::date)::DATE";
    } else {
        $date_group_expr = "asd.appointment_datetime::date";
    }
    
    $sql = " SELECT {$date_group_expr} as date,
                    COALESCE(SUM(asd.balance_amount), 0) as sales
             FROM appointment_shop_detail asd
             WHERE asd.status = 'COMPLETED'
               AND asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
               {$category_filter_sql} {$region_filter_sql}
             GROUP BY {$date_group_expr}
             ORDER BY date ASC ";
    $result = sql_query_pg($sql);
    $sales_trend = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $sales_trend[] = [
                'date' => $row['date'],
                'sales' => (int)$row['sales']
            ];
        }
    }
    
    // 3. 월별 매출 비교 (최근 12개월)
    $sql = " SELECT 
                TO_CHAR(asd.appointment_datetime, 'YYYY-MM') as month,
                COALESCE(SUM(asd.balance_amount), 0) as sales
             FROM appointment_shop_detail asd
             WHERE asd.status = 'COMPLETED'
               AND asd.appointment_datetime >= DATE_TRUNC('month', CURRENT_DATE - INTERVAL '11 months')
               {$category_filter_sql} {$region_filter_sql}
             GROUP BY TO_CHAR(asd.appointment_datetime, 'YYYY-MM')
             ORDER BY month ASC ";
    $result = sql_query_pg($sql);
    $monthly_sales = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $monthly_sales[] = [
                'month' => $row['month'],
                'sales' => (int)$row['sales']
            ];
        }
    }
    
    // 4. 결제 수단별 매출 분포
    $sql = " SELECT 
                p.payment_method,
                COALESCE(SUM(asd.balance_amount), 0) as sales_amount,
                COUNT(*) as payment_count
             FROM payments p
             INNER JOIN appointment_shop_detail asd ON p.appointment_id = asd.appointment_id
             WHERE asd.status = 'COMPLETED'
               AND asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
               {$category_filter_sql} {$region_filter_sql}
             GROUP BY p.payment_method
             ORDER BY sales_amount DESC ";
    $result = sql_query_pg($sql);
    $payment_method_distribution = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $payment_method_distribution[] = [
                'payment_method' => $row['payment_method'] ?: '미지정',
                'sales_amount' => (int)$row['sales_amount'],
                'payment_count' => (int)$row['payment_count']
            ];
        }
    }
    
    // 5. 업종별 매출 분포
    $sql = " SELECT 
                sc.name as category_name,
                COALESCE(SUM(asd.balance_amount), 0) as sales_amount
             FROM shop_categories sc
             LEFT JOIN shop_category_relation scr ON sc.category_id = scr.category_id
             LEFT JOIN appointment_shop_detail asd ON scr.shop_id = asd.shop_id
                AND asd.status = 'COMPLETED'
                AND asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
             WHERE char_length(sc.category_id) = 4
             GROUP BY sc.category_id, sc.name
             HAVING SUM(asd.balance_amount) > 0
             ORDER BY sales_amount DESC
             LIMIT 10 ";
    $result = sql_query_pg($sql);
    $category_sales = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $category_sales[] = [
                'category_name' => $row['category_name'] ?: '미지정',
                'sales_amount' => (int)$row['sales_amount']
            ];
        }
}

    // 6. 시간대별 매출 분포
    $sql = " SELECT 
                EXTRACT(HOUR FROM asd.appointment_datetime) as hour,
                COALESCE(SUM(asd.balance_amount), 0) as sales_amount,
                COUNT(*) as payment_count
             FROM appointment_shop_detail asd
             WHERE asd.status = 'COMPLETED'
               AND asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
               {$category_filter_sql} {$region_filter_sql}
             GROUP BY EXTRACT(HOUR FROM asd.appointment_datetime)
             ORDER BY hour ASC ";
    $result = sql_query_pg($sql);
    $hourly_sales = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $hourly_sales[] = [
                'hour' => (int)$row['hour'],
                'sales_amount' => (int)$row['sales_amount'],
                'payment_count' => (int)$row['payment_count']
            ];
        }
    }
    
    // 7. 요일별 매출 분포
    $sql = " SELECT 
                EXTRACT(DOW FROM asd.appointment_datetime) as day_num,
                CASE EXTRACT(DOW FROM asd.appointment_datetime)
                    WHEN 0 THEN '일'
                    WHEN 1 THEN '월'
                    WHEN 2 THEN '화'
                    WHEN 3 THEN '수'
                    WHEN 4 THEN '목'
                    WHEN 5 THEN '금'
                    WHEN 6 THEN '토'
                END as day_of_week,
                COALESCE(SUM(asd.balance_amount), 0) as sales_amount,
                COUNT(*) as payment_count
             FROM appointment_shop_detail asd
             WHERE asd.status = 'COMPLETED'
               AND asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
               {$category_filter_sql} {$region_filter_sql}
             GROUP BY EXTRACT(DOW FROM asd.appointment_datetime)
             ORDER BY day_num ASC ";
    $result = sql_query_pg($sql);
    $weekly_sales = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $weekly_sales[] = [
                'day_num' => (int)$row['day_num'],
                'day_of_week' => $row['day_of_week'] ?: '미지정',
                'sales_amount' => (int)$row['sales_amount'],
                'payment_count' => (int)$row['payment_count']
            ];
        }
    }
    
    // 8. 가맹점별 매출 기여도 (상위 20개)
    $sql = " SELECT 
                s.shop_id,
                COALESCE(s.shop_name, s.name) as shop_name,
                COALESCE(SUM(asd.balance_amount), 0) as sales_amount,
                COUNT(*) as payment_count
             FROM shop s
             INNER JOIN appointment_shop_detail asd ON s.shop_id = asd.shop_id
             WHERE asd.status = 'COMPLETED'
               AND asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
               {$category_filter_sql} {$region_filter_sql}
             GROUP BY s.shop_id, s.shop_name, s.name
             ORDER BY sales_amount DESC
             LIMIT 20 ";
    $result = sql_query_pg($sql);
    $shop_contribution = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $shop_contribution[] = [
                'shop_id' => (int)$row['shop_id'],
                'shop_name' => $row['shop_name'] ?: '미지정',
                'sales_amount' => (int)$row['sales_amount'],
                'payment_count' => (int)$row['payment_count']
            ];
        }
    }
    
    // 9. 정산 처리 내역 (최근 50건)
    $sql = " SELECT 
                ssl.ssl_id,
                ssl.shop_id,
                COALESCE(s.shop_name, s.name) as shop_name,
                ssl.settlement_at,
                ssl.settlement_amount,
                ssl.settlement_start_at,
                ssl.settlement_end_at,
                ssl.settlement_type
             FROM shop_settlement_log ssl
             LEFT JOIN shop s ON ssl.shop_id = s.shop_id
             WHERE ssl.settlement_at::date BETWEEN '{$range_start}' AND '{$range_end}'
             ORDER BY ssl.settlement_at DESC
             LIMIT 50 ";
    $result = sql_query_pg($sql);
    $settlement_logs = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $settlement_logs[] = [
                'ssl_id' => (int)$row['ssl_id'],
                'shop_id' => (int)$row['shop_id'],
                'shop_name' => $row['shop_name'] ?: '미지정',
                'settlement_at' => $row['settlement_at'],
                'settlement_amount' => (int)$row['settlement_amount'],
                'settlement_start_at' => $row['settlement_start_at'],
                'settlement_end_at' => $row['settlement_end_at'],
                'settlement_type' => $row['settlement_type'] ?: '미지정'
            ];
        }
    }
    
    // 응답 데이터 구성
echo json_encode([
    'success' => true,
        'period_type' => $period_type,
        'range_start' => $range_start,
        'range_end' => $range_end,
        'summary' => [
            'total_sales' => $total_sales,
            'total_settlement' => $total_settlement,
            'avg_daily_sales' => $avg_daily_sales,
            'total_payment_count' => $total_payment_count,
            'avg_payment_amount' => $avg_payment_amount,
            'pending_settlement' => $pending_settlement
        ],
        'sales_trend' => $sales_trend,
        'monthly_sales' => $monthly_sales,
        'payment_method_distribution' => $payment_method_distribution,
        'category_sales' => $category_sales,
        'hourly_sales' => $hourly_sales,
        'weekly_sales' => $weekly_sales,
        'shop_contribution' => $shop_contribution,
        'settlement_logs' => $settlement_logs
], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '데이터 조회 중 오류가 발생했습니다: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
exit;

