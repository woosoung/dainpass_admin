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
            WHERE scr.shop_id = s.shop_id AND LEFT(scr.category_id, 2) = '{$category_id_escaped}'
        )";
    } else {
        $category_filter_sql = " AND EXISTS (
            SELECT 1 FROM {$g5['shop_category_relation_table']} scr
            WHERE scr.shop_id = s.shop_id AND scr.category_id = '{$category_id_escaped}'
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

try {
    // 1. 주요 지표 카드 데이터
    
    // 총 리뷰 수
    $sql = " SELECT COUNT(*) as cnt 
             FROM shop_review sr
             INNER JOIN shop s ON sr.shop_id = s.shop_id
             WHERE sr.sr_deleted = 'N'
               AND sr.sr_created_at::date BETWEEN '{$range_start}' AND '{$range_end}'
               {$category_filter_sql} {$region_filter_sql} ";
    $row = sql_fetch_pg($sql);
    $total_review_count = (int)($row['cnt'] ?? 0);
    
    // 평균 평점
    $sql = " SELECT COALESCE(AVG(sr.sr_score), 0) as avg_rating
             FROM shop_review sr
             INNER JOIN shop s ON sr.shop_id = s.shop_id
             WHERE sr.sr_deleted = 'N'
               AND sr.sr_created_at::date BETWEEN '{$range_start}' AND '{$range_end}'
               {$category_filter_sql} {$region_filter_sql} ";
    $row = sql_fetch_pg($sql);
    $avg_rating = round((float)($row['avg_rating'] ?? 0), 1);
    
    // 완료된 예약 건수 (리뷰 작성률 계산용)
    $sql = " SELECT COUNT(*) as cnt 
             FROM appointment_shop_detail asd
             INNER JOIN shop s ON asd.shop_id = s.shop_id
             WHERE asd.status = 'COMPLETED'
               AND asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
               {$category_filter_sql} {$region_filter_sql} ";
    $row = sql_fetch_pg($sql);
    $completed_reservation_count = (int)($row['cnt'] ?? 0);
    
    // 리뷰 작성률
    $review_rate = 0.0;
    if ($completed_reservation_count > 0) {
        $review_rate = round(($total_review_count / $completed_reservation_count) * 100, 1);
    }
    
    // 총 서비스 수 (활성 - status = 'active')
    $sql = " SELECT COUNT(*) as cnt 
             FROM shop_services ss
             INNER JOIN shop s ON ss.shop_id = s.shop_id
             WHERE ss.status = 'active'
               {$category_filter_sql} {$region_filter_sql} ";
    $row = sql_fetch_pg($sql);
    $total_service_count = (int)($row['cnt'] ?? 0);
    
    // 서비스별 평균 예약 건수 계산 - 각 서비스별로 예약 건수를 집계하여 평균 계산
    $sql = " SELECT 
               ss.service_id,
               COUNT(asd.shopdetail_id) as reservation_count
             FROM shop_services ss
             INNER JOIN shop s ON ss.shop_id = s.shop_id
             LEFT JOIN appointment_shop_detail asd ON ss.shop_id = asd.shop_id
               AND asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
             WHERE ss.status = 'active'
               {$category_filter_sql} {$region_filter_sql}
             GROUP BY ss.service_id ";
    $result = sql_query_pg($sql);
    $service_reservation_counts = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $service_reservation_counts[] = (int)($row['reservation_count'] ?? 0);
        }
    }
    
    // 서비스별 평균 예약 건수
    $avg_reservation_per_service = 0;
    if (count($service_reservation_counts) > 0) {
        $total_reservation_count = array_sum($service_reservation_counts);
        $avg_reservation_per_service = (int)($total_reservation_count / count($service_reservation_counts));
    }
    
    // 서비스별 평균 매출 계산 - 각 서비스별로 매출을 집계하여 평균 계산
    $sql = " SELECT 
               ss.service_id,
               COALESCE(SUM(CASE WHEN asd.status = 'COMPLETED' THEN asd.balance_amount ELSE 0 END), 0) as sales_amount
             FROM shop_services ss
             INNER JOIN shop s ON ss.shop_id = s.shop_id
             LEFT JOIN appointment_shop_detail asd ON ss.shop_id = asd.shop_id
               AND asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
             WHERE ss.status = 'active'
               {$category_filter_sql} {$region_filter_sql}
             GROUP BY ss.service_id ";
    $result = sql_query_pg($sql);
    $service_sales_amounts = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $service_sales_amounts[] = (int)($row['sales_amount'] ?? 0);
        }
    }
    
    // 서비스별 평균 매출
    $avg_sales_per_service = 0;
    if (count($service_sales_amounts) > 0) {
        $total_sales = array_sum($service_sales_amounts);
        $avg_sales_per_service = (int)($total_sales / count($service_sales_amounts));
    }
    
    // 2. 서비스별 예약 건수 순위 (상위 20개)
    $sql = " SELECT 
               ss.service_id,
               ss.service_name,
               COALESCE(s.shop_name, s.name) as shop_name,
               COUNT(*) as reservation_count
             FROM shop_services ss
             INNER JOIN shop s ON ss.shop_id = s.shop_id
             INNER JOIN appointment_shop_detail asd ON ss.shop_id = asd.shop_id
             WHERE ss.status = 'active'
               AND asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
               {$category_filter_sql} {$region_filter_sql}
             GROUP BY ss.service_id, ss.service_name, s.shop_name, s.name
             ORDER BY reservation_count DESC
             LIMIT 20 ";
    $result = sql_query_pg($sql);
    $service_reservation_rank = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $service_reservation_rank[] = [
                'service_id' => (int)$row['service_id'],
                'service_name' => $row['service_name'] ?: '미지정',
                'shop_name' => $row['shop_name'] ?: '미지정',
                'reservation_count' => (int)$row['reservation_count']
            ];
        }
    }
    
    // 3. 서비스별 매출 순위 (상위 20개)
    $sql = " SELECT 
               ss.service_id,
               ss.service_name,
               COALESCE(s.shop_name, s.name) as shop_name,
               COALESCE(SUM(asd.balance_amount), 0) as sales_amount,
               COUNT(*) as reservation_count
             FROM shop_services ss
             INNER JOIN shop s ON ss.shop_id = s.shop_id
             INNER JOIN appointment_shop_detail asd ON ss.shop_id = asd.shop_id
             WHERE ss.status = 'active'
               AND asd.status = 'COMPLETED'
               AND asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
               {$category_filter_sql} {$region_filter_sql}
             GROUP BY ss.service_id, ss.service_name, s.shop_name, s.name
             ORDER BY sales_amount DESC
             LIMIT 20 ";
    $result = sql_query_pg($sql);
    $service_sales_rank = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $service_sales_rank[] = [
                'service_id' => (int)$row['service_id'],
                'service_name' => $row['service_name'] ?: '미지정',
                'shop_name' => $row['shop_name'] ?: '미지정',
                'sales_amount' => (int)$row['sales_amount'],
                'reservation_count' => (int)$row['reservation_count']
            ];
        }
    }
    
    // 4. 업종별 평균 평점
    $category_filter_for_rating = '';
    if ($category_id !== '' && $category_id !== '0') {
        $pg_link = isset($g5['connect_pg']) ? $g5['connect_pg'] : null;
        if ($pg_link && function_exists('pg_escape_string')) {
            $category_id_escaped = pg_escape_string($pg_link, $category_id);
        } else {
            $category_id_escaped = addslashes($category_id);
        }
        $category_id_length = strlen($category_id);
        if ($category_id_length == 2) {
            $category_filter_for_rating = " AND LEFT(sc.category_id, 2) = '{$category_id_escaped}' ";
        } else {
            $category_filter_for_rating = " AND sc.category_id = '{$category_id_escaped}' ";
        }
    }
    
    // 지역 필터 조건을 JOIN 조건으로 옮기기
    $region_join_condition = '';
    if ($region !== '') {
        $pg_link = isset($g5['connect_pg']) ? $g5['connect_pg'] : null;
        if ($pg_link && function_exists('pg_escape_string')) {
            $region_escaped = pg_escape_string($pg_link, $region);
        } else {
            $region_escaped = addslashes($region);
        }
        $region_join_condition = " AND (
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
    
    $sql = " SELECT 
               sc.name as category_name,
               COALESCE(AVG(sr.sr_score), 0) as avg_rating,
               COUNT(sr.review_id) as review_count
             FROM {$g5['shop_categories_table']} sc
             LEFT JOIN {$g5['shop_category_relation_table']} scr ON sc.category_id = scr.category_id
             LEFT JOIN shop s ON scr.shop_id = s.shop_id {$region_join_condition}
             LEFT JOIN shop_review sr ON s.shop_id = sr.shop_id
               AND sr.sr_deleted = 'N'
               AND sr.sr_created_at::date BETWEEN '{$range_start}' AND '{$range_end}'
             WHERE 1=1 {$category_filter_for_rating}
             GROUP BY sc.category_id, sc.name
             HAVING COUNT(sr.review_id) > 0
             ORDER BY avg_rating DESC ";
    $result = sql_query_pg($sql);
    $category_rating = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $category_rating[] = [
                'category_name' => $row['category_name'] ?: '미지정',
                'avg_rating' => round((float)$row['avg_rating'], 1),
                'review_count' => (int)$row['review_count']
            ];
        }
    }
    
    // 5. 가맹점별 평균 평점 순위 (상위 20개, 최소 리뷰 수 1개로 완화)
    $sql = " SELECT 
               s.shop_id,
               COALESCE(s.shop_name, s.name) as shop_name,
               COALESCE(AVG(sr.sr_score), 0) as avg_rating,
               COUNT(sr.review_id) as review_count
             FROM shop s
             INNER JOIN shop_review sr ON s.shop_id = sr.shop_id
             WHERE sr.sr_deleted = 'N'
               AND sr.sr_created_at::date BETWEEN '{$range_start}' AND '{$range_end}'
               {$category_filter_sql} {$region_filter_sql}
             GROUP BY s.shop_id, s.shop_name, s.name
             HAVING COUNT(sr.review_id) >= 1
             ORDER BY avg_rating DESC, review_count DESC
             LIMIT 20 ";
    $result = sql_query_pg($sql);
    $shop_rating_rank = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $shop_rating_rank[] = [
                'shop_id' => (int)$row['shop_id'],
                'shop_name' => $row['shop_name'] ?: '미지정',
                'avg_rating' => round((float)$row['avg_rating'], 1),
                'review_count' => (int)$row['review_count']
            ];
        }
    }
    
    // 6. 리뷰 평점 분포
    $sql = " SELECT 
               sr_score as rating,
               COUNT(*) as review_count
             FROM shop_review sr
             INNER JOIN shop s ON sr.shop_id = s.shop_id
             WHERE sr.sr_deleted = 'N'
               AND sr.sr_created_at::date BETWEEN '{$range_start}' AND '{$range_end}'
               {$category_filter_sql} {$region_filter_sql}
             GROUP BY sr_score
             ORDER BY sr_score DESC ";
    $result = sql_query_pg($sql);
    $rating_distribution = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rating_distribution[] = [
                'rating' => (int)$row['rating'],
                'review_count' => (int)$row['review_count']
            ];
        }
    }
    
    // 7. 기간별 리뷰 작성 추이
    if ($period_type == 'weekly') {
        $date_group_expr = "DATE_TRUNC('week', CAST(sr.sr_created_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('week', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DOW FROM date_series.date) = 1";
    } elseif ($period_type == 'monthly') {
        $date_group_expr = "DATE_TRUNC('month', CAST(sr.sr_created_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('month', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DAY FROM date_series.date) = 1";
    } else {
        $date_group_expr = "CAST(sr.sr_created_at AS DATE)";
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
        review_data AS (
            SELECT 
                {$date_group_expr} AS period_date,
                COUNT(*) AS review_count
            FROM shop_review sr
            INNER JOIN shop s ON sr.shop_id = s.shop_id
            WHERE sr.sr_deleted = 'N'
              AND sr.sr_created_at::date BETWEEN '{$range_start}' AND '{$range_end}'
              {$category_filter_sql} {$region_filter_sql}
            GROUP BY {$date_group_expr}
        )
        SELECT 
            dp.period_date AS date,
            COALESCE(rd.review_count, 0) AS review_count
        FROM date_periods dp
        LEFT JOIN review_data rd ON dp.period_date = rd.period_date
        ORDER BY dp.period_date ASC
    ";
    $result = sql_query_pg($sql);
    $review_trend = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $review_trend[] = [
                'date' => $row['date'],
                'count' => (int)$row['review_count']
            ];
        }
    }
    
    // 8. 서비스별 상세 통계 (상위 50개)
    $sql = " SELECT 
               ss.service_id,
               ss.service_name,
               COALESCE(s.shop_name, s.name) as shop_name,
               STRING_AGG(DISTINCT sc.name, ', ') as category_names,
               COUNT(DISTINCT asd.shopdetail_id) as reservation_count,
               COALESCE(SUM(CASE WHEN asd.status = 'COMPLETED' THEN asd.balance_amount ELSE 0 END), 0) as total_sales,
               COALESCE(AVG(CASE WHEN asd.status = 'COMPLETED' THEN asd.balance_amount END), 0) as avg_sales,
               COALESCE(AVG(sr.sr_score), 0) as avg_rating,
               COUNT(sr.review_id) as review_count
             FROM shop_services ss
             INNER JOIN shop s ON ss.shop_id = s.shop_id
             LEFT JOIN {$g5['shop_category_relation_table']} scr ON s.shop_id = scr.shop_id
             LEFT JOIN {$g5['shop_categories_table']} sc ON scr.category_id = sc.category_id
             LEFT JOIN appointment_shop_detail asd ON ss.shop_id = asd.shop_id
               AND asd.appointment_datetime::date BETWEEN '{$range_start}' AND '{$range_end}'
             LEFT JOIN shop_review sr ON s.shop_id = sr.shop_id 
               AND sr.sr_deleted = 'N'
             WHERE ss.status = 'active'
               {$category_filter_sql} {$region_filter_sql}
             GROUP BY ss.service_id, ss.service_name, s.shop_name, s.name
             ORDER BY total_sales DESC
             LIMIT 50 ";
    $result = sql_query_pg($sql);
    $service_detail = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $service_detail[] = [
                'service_id' => (int)$row['service_id'],
                'service_name' => $row['service_name'] ?: '미지정',
                'shop_name' => $row['shop_name'] ?: '미지정',
                'category_names' => $row['category_names'] ?: '-',
                'reservation_count' => (int)$row['reservation_count'],
                'total_sales' => (int)$row['total_sales'],
                'avg_sales' => (int)$row['avg_sales'],
                'avg_rating' => round((float)$row['avg_rating'], 1),
                'review_count' => (int)$row['review_count']
            ];
        }
    }
    
    // 응답 데이터 구성
    echo json_encode([
        'success' => true,
        'data' => [
            'summary' => [
                'total_review_count' => $total_review_count,
                'avg_rating' => $avg_rating,
                'review_rate' => $review_rate,
                'total_service_count' => $total_service_count,
                'avg_reservation_per_service' => $avg_reservation_per_service,
                'avg_sales_per_service' => $avg_sales_per_service
            ],
            'service_reservation_rank' => $service_reservation_rank,
            'service_sales_rank' => $service_sales_rank,
            'category_rating' => $category_rating,
            'shop_rating_rank' => $shop_rating_rank,
            'rating_distribution' => $rating_distribution,
            'review_trend' => $review_trend,
            'service_detail' => $service_detail,
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

