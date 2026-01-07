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

// 요청 파라미터 받기
$period_type = isset($_POST['period_type']) ? $_POST['period_type'] : 'daily';
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
$category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? trim($_POST['category_id']) : '';
$region = isset($_POST['region']) && $_POST['region'] !== '' ? trim($_POST['region']) : '';

// 기간 계산
list($range_start, $range_end) = calculate_date_range($period_type, $start_date, $end_date);

// PostgreSQL 이스케이프 헬퍼
global $g5;
$pg_link = isset($g5['connect_pg']) ? $g5['connect_pg'] : null;
$esc = function($v) use ($pg_link) {
    $s = (string)$v;
    return $pg_link && function_exists('pg_escape_string') ? pg_escape_string($pg_link, $s) : addslashes($s);
};

// 업종 필터링 조건 (shop 테이블 기준)
// category_id는 문자열이므로 문자열 비교 필요 ("0" 또는 빈 문자열은 전체를 의미)
// 1차 업종(2자리, 예: "10")을 선택하면 하위 분류(4자리, 예: "1010", "1020")도 포함해야 함
// 2차 업종(4자리, 예: "1010")을 선택하면 정확히 일치하는 것만 필터링
$category_filter_sql = '';
if ($category_id !== '' && $category_id !== '0') {
    $category_id_escaped = $esc($category_id);
    $category_id_length = strlen($category_id);
    if ($category_id_length == 2) {
        // 1차 업종(2자리): 해당 카테고리로 시작하는 모든 하위 분류 포함
        $category_filter_sql = " AND EXISTS (
            SELECT 1 FROM {$g5['shop_category_relation_table']} scr
            WHERE scr.shop_id = s.shop_id AND LEFT(scr.category_id, 2) = '{$category_id_escaped}'
        )";
    } else {
        // 2차 업종(4자리) 이상: 정확히 일치하는 것만
        $category_filter_sql = " AND EXISTS (
            SELECT 1 FROM {$g5['shop_category_relation_table']} scr
            WHERE scr.shop_id = s.shop_id AND scr.category_id = '{$category_id_escaped}'
        )";
    }
}

// 지역 필터링 조건 (shop 테이블 기준)
$region_filter_sql = '';
if ($region !== '') {
    $region_escaped = $esc($region);
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

    // 전체 가맹점 수
    $sql = " SELECT COUNT(*) as cnt FROM {$g5['shop_table']} s WHERE 1=1 {$category_filter_sql} {$region_filter_sql} ";
    $row = sql_fetch_pg($sql);
    $total_shop_count = (int)($row['cnt'] ?? 0);

    // 활성 가맹점 수
    $sql = " SELECT COUNT(*) as cnt FROM {$g5['shop_table']} s WHERE s.status = 'active' {$category_filter_sql} {$region_filter_sql} ";
    $row = sql_fetch_pg($sql);
    $active_shop_count = (int)($row['cnt'] ?? 0);

    // 신규 가맹점 수 (기간 내)
    $sql = " SELECT COUNT(*) as cnt FROM {$g5['shop_table']} s
             WHERE DATE(s.created_at) BETWEEN '{$range_start}' AND '{$range_end}' {$category_filter_sql} {$region_filter_sql} ";
    $row = sql_fetch_pg($sql);
    $new_shop_count = (int)($row['cnt'] ?? 0);

    // 대기 중인 가맹점 수
    $sql = " SELECT COUNT(*) as cnt FROM {$g5['shop_table']} s WHERE s.status = 'pending' {$category_filter_sql} {$region_filter_sql} ";
    $row = sql_fetch_pg($sql);
    $pending_shop_count = (int)($row['cnt'] ?? 0);

    // 폐업 가맹점 수
    $sql = " SELECT COUNT(*) as cnt FROM {$g5['shop_table']} s WHERE s.status = 'closed' {$category_filter_sql} {$region_filter_sql} ";
    $row = sql_fetch_pg($sql);
    $closed_shop_count = (int)($row['cnt'] ?? 0);

    // 가맹점 활성화율
    $activation_rate = 0.0;
    if ($total_shop_count > 0) {
        $activation_rate = round(($active_shop_count / $total_shop_count) * 100, 1);
    }

    // 2. 가맹점 상태별 분포 (파이 차트용)
    $sql = " SELECT s.status, COUNT(*) as cnt
             FROM {$g5['shop_table']} s
             WHERE 1=1 {$category_filter_sql} {$region_filter_sql}
             GROUP BY s.status ";
    $result = sql_query_pg($sql);
    $status_distribution = [
        'active' => 0,
        'pending' => 0,
        'closed' => 0,
        'shutdown' => 0
    ];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $status = $row['status'] ?? '';
            if (isset($status_distribution[$status])) {
                $status_distribution[$status] = (int)$row['cnt'];
            }
        }
    }

    // 3. 업종별 가맹점 수 분포 (2차 업종만, 4자리)
    $category_filter_for_distribution = '';
    if ($category_id !== '' && $category_id !== '0') {
        $category_id_escaped = $esc($category_id);
        $category_id_length = strlen($category_id);
        if ($category_id_length == 2) {
            // 1차 업종(2자리): 해당 카테고리로 시작하는 모든 하위 분류 포함
            $category_filter_for_distribution = " AND LEFT(sc.category_id, 2) = '{$category_id_escaped}' ";
        } else {
            // 2차 업종(4자리) 이상: 정확히 일치하는 것만
            $category_filter_for_distribution = " AND sc.category_id = '{$category_id_escaped}' ";
        }
    }
    // 대도시 필터링을 업종별 분포에도 적용
    $region_filter_for_distribution = '';
    if ($region !== '') {
        $region_escaped = $esc($region);
        $region_filter_for_distribution = " AND (
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
    $sql = " SELECT sc.category_id, sc.name as category_name,
             COUNT(DISTINCT scr.shop_id) as shop_count
             FROM {$g5['shop_categories_table']} sc
             LEFT JOIN {$g5['shop_category_relation_table']} scr ON sc.category_id = scr.category_id
             LEFT JOIN {$g5['shop_table']} s ON scr.shop_id = s.shop_id
             WHERE char_length(sc.category_id) = 4 {$category_filter_for_distribution} {$region_filter_for_distribution}
             GROUP BY sc.category_id, sc.name
             ORDER BY shop_count DESC ";
    $result = sql_query_pg($sql);
    $category_distribution = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $category_distribution[] = [
                'category_id' => $row['category_id'],
                'category_name' => $row['category_name'], // 2차 업종명만 표시
                'shop_count' => (int)$row['shop_count']
            ];
        }
    }

    // 4. 가맹점 신규 등록 추이
    if ($period_type == 'weekly') {
        $date_group_expr = "DATE_TRUNC('week', CAST(s.created_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('week', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DOW FROM date_series.date) = 1";
    } elseif ($period_type == 'monthly') {
        $date_group_expr = "DATE_TRUNC('month', CAST(s.created_at AS DATE))::DATE";
        $date_series_expr = "DATE_TRUNC('month', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DAY FROM date_series.date) = 1";
    } else {
        $date_group_expr = "CAST(s.created_at AS DATE)";
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
        shop_data AS (
            SELECT
                {$date_group_expr} AS period_date,
                COUNT(*) AS shop_count
            FROM {$g5['shop_table']} s
            WHERE DATE(s.created_at) BETWEEN '{$range_start}' AND '{$range_end}'
              {$category_filter_sql} {$region_filter_sql}
            GROUP BY {$date_group_expr}
        )
        SELECT
            dp.period_date AS date,
            COALESCE(sd.shop_count, 0) AS shop_count
        FROM date_periods dp
        LEFT JOIN shop_data sd ON dp.period_date = sd.period_date
        ORDER BY dp.period_date ASC
    ";
    $result = sql_query_pg($sql);
    $new_shop_trend = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $new_shop_trend[] = [
                'date' => $row['date'],
                'count' => (int)$row['shop_count']
            ];
        }
    }

    // 5. 업종별 평균 매출 (기간 내 COMPLETED 예약 기준)
    $category_filter_for_avg_sales = '';
    if ($category_id !== '' && $category_id !== '0') {
        $category_id_escaped = $esc($category_id);
        $category_id_length = strlen($category_id);
        if ($category_id_length == 2) {
            // 1차 업종(2자리): 해당 카테고리로 시작하는 모든 하위 분류 포함
            $category_filter_for_avg_sales = " AND LEFT(sc.category_id, 2) = '{$category_id_escaped}' ";
        } else {
            // 2차 업종(4자리) 이상: 정확히 일치하는 것만
            $category_filter_for_avg_sales = " AND sc.category_id = '{$category_id_escaped}' ";
        }
    }
    $sql = "
        SELECT sc.category_id, sc.name as category_name,
               COALESCE(AVG(shop_sales.total_sales), 0) as avg_sales
        FROM {$g5['shop_categories_table']} sc
        LEFT JOIN {$g5['shop_category_relation_table']} scr ON sc.category_id = scr.category_id
        LEFT JOIN (
            SELECT asd.shop_id, SUM(asd.balance_amount) as total_sales
            FROM appointment_shop_detail asd
            WHERE asd.status = 'COMPLETED'
              AND asd.appointment_datetime >= '{$range_start} 00:00:00'
              AND asd.appointment_datetime <= '{$range_end} 23:59:59'
            GROUP BY asd.shop_id
        ) shop_sales ON scr.shop_id = shop_sales.shop_id
        LEFT JOIN {$g5['shop_table']} s_region ON scr.shop_id = s_region.shop_id
        WHERE 1=1 {$category_filter_for_avg_sales}";
    // 대도시 필터링을 업종별 평균 매출에도 적용
    if ($region !== '') {
        $region_escaped = $esc($region);
        $sql .= " AND (
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
        )";
    }
    $sql .= "
        GROUP BY sc.category_id, sc.name
        HAVING COUNT(DISTINCT scr.shop_id) > 0
        ORDER BY avg_sales DESC
    ";
    $result = sql_query_pg($sql);
    $category_avg_sales = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $category_avg_sales[] = [
                'category_id' => $row['category_id'],
                'category_name' => $row['category_name'],
                'avg_sales' => (int)$row['avg_sales']
            ];
        }
    }

    // 6. 가맹점별 매출 순위 (상위 20개)
    $sql = "
        SELECT s.shop_id, COALESCE(s.shop_name, s.name) as shop_name,
               COALESCE(SUM(asd.balance_amount), 0) as total_sales
        FROM {$g5['shop_table']} s
        LEFT JOIN appointment_shop_detail asd ON s.shop_id = asd.shop_id
            AND asd.status = 'COMPLETED'
            AND asd.appointment_datetime >= '{$range_start} 00:00:00'
            AND asd.appointment_datetime <= '{$range_end} 23:59:59'
        WHERE 1=1 {$category_filter_sql} {$region_filter_sql}
        GROUP BY s.shop_id, s.shop_name, s.name
        ORDER BY total_sales DESC
        LIMIT 20
    ";
    $result = sql_query_pg($sql);
    $shop_sales_rank = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $shop_sales_rank[] = [
                'shop_id' => (int)$row['shop_id'],
                'shop_name' => $row['shop_name'],
                'total_sales' => (int)$row['total_sales']
            ];
        }
    }

    // 7. 가맹점별 예약 건수 순위 (상위 20개)
    $sql = "
        SELECT s.shop_id, COALESCE(s.shop_name, s.name) as shop_name,
               COUNT(asd.appointment_id) as appointment_count
        FROM {$g5['shop_table']} s
        LEFT JOIN appointment_shop_detail asd ON s.shop_id = asd.shop_id
            AND asd.appointment_datetime >= '{$range_start} 00:00:00'
            AND asd.appointment_datetime <= '{$range_end} 23:59:59'
        WHERE 1=1 {$category_filter_sql} {$region_filter_sql}
        GROUP BY s.shop_id, s.shop_name, s.name
        ORDER BY appointment_count DESC
        LIMIT 20
    ";
    $result = sql_query_pg($sql);
    $shop_appointment_rank = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $shop_appointment_rank[] = [
                'shop_id' => (int)$row['shop_id'],
                'shop_name' => $row['shop_name'],
                'appointment_count' => (int)$row['appointment_count']
            ];
        }
    }

    // 8. 지역별 가맹점 분포 (주소 정보 기반, addr1 활용)
    $sql = "
        SELECT
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
                    END
                ELSE NULL
            END as region,
            COUNT(*) as shop_count
        FROM {$g5['shop_table']} s
        WHERE 1=1 {$category_filter_sql} {$region_filter_sql}
        GROUP BY region
        ORDER BY shop_count DESC
        LIMIT 20
    ";
    $result = sql_query_pg($sql);
    $region_distribution = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $region_distribution[] = [
                'region' => $row['region'] ?: '미지정',
                'shop_count' => (int)$row['shop_count']
            ];
        }
    }

    // 9. 가맹점별 상세 통계 (상위 20개, 매출 기준)
    $sql = "
        SELECT
            s.shop_id,
            COALESCE(s.shop_name, s.name) as shop_name,
            s.status,
            STRING_AGG(DISTINCT sc.name, ', ') as category_names,
            COALESCE(SUM(CASE WHEN asd.status = 'COMPLETED' THEN asd.balance_amount ELSE 0 END), 0) as total_sales,
            COUNT(DISTINCT asd.appointment_id) as appointment_count
        FROM {$g5['shop_table']} s
        LEFT JOIN {$g5['shop_category_relation_table']} scr ON s.shop_id = scr.shop_id
        LEFT JOIN {$g5['shop_categories_table']} sc ON scr.category_id = sc.category_id
        LEFT JOIN appointment_shop_detail asd ON s.shop_id = asd.shop_id
            AND asd.appointment_datetime >= '{$range_start} 00:00:00'
            AND asd.appointment_datetime <= '{$range_end} 23:59:59'
        WHERE 1=1 {$category_filter_sql} {$region_filter_sql}
        GROUP BY s.shop_id, s.shop_name, s.name, s.status
        ORDER BY total_sales DESC
        LIMIT 20
    ";
    $result = sql_query_pg($sql);
    $shop_detail_list = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $total_sales = (int)$row['total_sales'];
            $appointment_count = (int)$row['appointment_count'];
            $avg_sales = $appointment_count > 0 ? (int)($total_sales / $appointment_count) : 0;
            $shop_detail_list[] = [
                'shop_id' => (int)$row['shop_id'],
                'shop_name' => $row['shop_name'],
                'status' => $row['status'],
                'category_names' => $row['category_names'],
                'total_sales' => $total_sales,
                'appointment_count' => $appointment_count,
                'avg_sales' => $avg_sales
            ];
        }
    }

    // 최종 데이터 응답
    $response = [
        'success' => true,
        'message' => '데이터를 성공적으로 조회했습니다.',
        'data' => [
            'summary' => [
                'total_shop_count' => $total_shop_count,
                'active_shop_count' => $active_shop_count,
                'new_shop_count' => $new_shop_count,
                'pending_shop_count' => $pending_shop_count,
                'closed_shop_count' => $closed_shop_count,
                'activation_rate' => $activation_rate
            ],
            'status_distribution' => $status_distribution,
            'category_distribution' => $category_distribution,
            'new_shop_trend' => $new_shop_trend,
            'category_avg_sales' => $category_avg_sales,
            'shop_sales_rank' => $shop_sales_rank,
            'shop_appointment_rank' => $shop_appointment_rank,
            'region_distribution' => $region_distribution,
            'shop_detail_list' => $shop_detail_list,
            'range_start' => $range_start,
            'range_end' => $range_end
        ]
    ];

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => '데이터 처리 중 오류가 발생했습니다: ' . $e->getMessage()
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>

