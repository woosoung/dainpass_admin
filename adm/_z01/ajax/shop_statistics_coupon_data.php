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

// 쿠폰 통계 요약 정보
if (!function_exists('get_coupon_summary')) {
function get_coupon_summary($shop_id, $range_start, $range_end)
{
    // 총 쿠폰 발급 수
    $sql_issued = "
        SELECT COUNT(*) AS total_issued
        FROM customer_coupons cc
        INNER JOIN shop_coupons sc ON cc.coupon_id = sc.coupon_id
        WHERE sc.shop_id = {$shop_id}
          AND cc.issued_at >= '{$range_start} 00:00:00'
          AND cc.issued_at <= '{$range_end} 23:59:59'
    ";
    $row_issued = sql_fetch_pg($sql_issued);
    $total_issued = (int)($row_issued['total_issued'] ?? 0);

    // 총 쿠폰 사용 수
    $sql_used = "
        SELECT COUNT(*) AS total_used
        FROM customer_coupons cc
        INNER JOIN shop_coupons sc ON cc.coupon_id = sc.coupon_id
        WHERE sc.shop_id = {$shop_id}
          AND cc.used_at >= '{$range_start} 00:00:00'
          AND cc.used_at <= '{$range_end} 23:59:59'
          AND (cc.status = 'USED' OR cc.used_at IS NOT NULL)
    ";
    $row_used = sql_fetch_pg($sql_used);
    $total_used = (int)($row_used['total_used'] ?? 0);

    // 총 쿠폰 할인 금액
    $sql_discount = "
        SELECT COALESCE(SUM(coupon_amount), 0) AS total_discount_amount
        FROM appointment_shop_detail
        WHERE shop_id = {$shop_id}
          AND coupon_amount IS NOT NULL
          AND coupon_amount > 0
          AND appointment_datetime >= '{$range_start} 00:00:00'
          AND appointment_datetime <= '{$range_end} 23:59:59'
          AND status != 'BOOKED'
    ";
    $row_discount = sql_fetch_pg($sql_discount);
    $total_discount = (int)($row_discount['total_discount_amount'] ?? 0);

    return [
        'total_coupon_issued' => $total_issued,
        'total_coupon_used' => $total_used,
        'total_coupon_discount' => $total_discount,
    ];
}
}

// 기간별 쿠폰 발급/사용 추이
if (!function_exists('get_coupon_issue_use_trend')) {
function get_coupon_issue_use_trend($shop_id, $range_start, $range_end)
{
    $sql = "
        WITH issued_daily AS (
            SELECT 
                CAST(cc.issued_at AS DATE) AS date,
                COUNT(*) AS issued_count
            FROM customer_coupons cc
            INNER JOIN shop_coupons sc ON cc.coupon_id = sc.coupon_id
            WHERE sc.shop_id = {$shop_id}
              AND cc.issued_at >= '{$range_start} 00:00:00'
              AND cc.issued_at <= '{$range_end} 23:59:59'
            GROUP BY CAST(cc.issued_at AS DATE)
        ),
        used_daily AS (
            SELECT 
                CAST(cc.used_at AS DATE) AS date,
                COUNT(*) AS used_count
            FROM customer_coupons cc
            INNER JOIN shop_coupons sc ON cc.coupon_id = sc.coupon_id
            WHERE sc.shop_id = {$shop_id}
              AND cc.used_at >= '{$range_start} 00:00:00'
              AND cc.used_at <= '{$range_end} 23:59:59'
              AND (cc.status = 'USED' OR cc.used_at IS NOT NULL)
            GROUP BY CAST(cc.used_at AS DATE)
        )
        SELECT 
            COALESCE(i.date, u.date) AS date,
            COALESCE(i.issued_count, 0) AS issued_count,
            COALESCE(u.used_count, 0) AS used_count
        FROM issued_daily i
        FULL OUTER JOIN used_daily u ON i.date = u.date
        ORDER BY date ASC
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'date' => $row['date'],
                'issued_count' => (int)$row['issued_count'],
                'used_count' => (int)$row['used_count'],
            ];
        }
    }

    return $rows;
}
}

// 쿠폰별 사용률 (상위 10개)
if (!function_exists('get_coupon_usage_rate')) {
function get_coupon_usage_rate($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            sc.coupon_id,
            sc.coupon_name,
            COUNT(cc.customer_coupon_id) AS total_issued,
            COUNT(CASE WHEN cc.used_at IS NOT NULL OR cc.status = 'USED' THEN 1 END) AS total_used,
            CASE 
                WHEN COUNT(cc.customer_coupon_id) > 0 THEN
                    (COUNT(CASE WHEN cc.used_at IS NOT NULL OR cc.status = 'USED' THEN 1 END)::numeric / COUNT(cc.customer_coupon_id)) * 100
                ELSE 0
            END AS usage_rate
        FROM shop_coupons sc
        LEFT JOIN customer_coupons cc ON sc.coupon_id = cc.coupon_id
            AND cc.issued_at >= '{$range_start} 00:00:00'
            AND cc.issued_at <= '{$range_end} 23:59:59'
        WHERE sc.shop_id = {$shop_id}
        GROUP BY sc.coupon_id, sc.coupon_name
        HAVING COUNT(cc.customer_coupon_id) > 0
        ORDER BY usage_rate DESC, total_issued DESC
        LIMIT 10
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'coupon_id' => (int)$row['coupon_id'],
                'coupon_name' => $row['coupon_name'] ?? '',
                'total_issued' => (int)$row['total_issued'],
                'total_used' => (int)$row['total_used'],
                'usage_rate' => (float)$row['usage_rate'],
            ];
        }
    }

    return $rows;
}
}

// 기간별 할인 금액 추이
if (!function_exists('get_discount_amount_trend')) {
function get_discount_amount_trend($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            CAST(appointment_datetime AS DATE) AS date,
            COALESCE(SUM(coupon_amount), 0) AS discount_amount
        FROM appointment_shop_detail
        WHERE shop_id = {$shop_id}
          AND coupon_amount IS NOT NULL
          AND coupon_amount > 0
          AND appointment_datetime >= '{$range_start} 00:00:00'
          AND appointment_datetime <= '{$range_end} 23:59:59'
          AND status != 'BOOKED'
        GROUP BY CAST(appointment_datetime AS DATE)
        ORDER BY date ASC
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'date' => $row['date'],
                'discount_amount' => (int)$row['discount_amount'],
            ];
        }
    }

    return $rows;
}
}

// 쿠폰별 상세 통계
if (!function_exists('get_coupon_detail_statistics')) {
function get_coupon_detail_statistics($shop_id, $range_start, $range_end)
{
    $sql = "
        SELECT 
            sc.coupon_id,
            sc.coupon_name,
            sc.coupon_code,
            COUNT(cc.customer_coupon_id) AS issued_count,
            COUNT(CASE WHEN cc.used_at IS NOT NULL OR cc.status = 'USED' THEN 1 END) AS used_count,
            CASE 
                WHEN COUNT(cc.customer_coupon_id) > 0 THEN
                    (COUNT(CASE WHEN cc.used_at IS NOT NULL OR cc.status = 'USED' THEN 1 END)::numeric / COUNT(cc.customer_coupon_id)) * 100
                ELSE 0
            END AS usage_rate,
            COALESCE(SUM(CASE WHEN asd.coupon_amount IS NOT NULL THEN asd.coupon_amount ELSE 0 END), 0) AS total_discount_amount
        FROM shop_coupons sc
        LEFT JOIN customer_coupons cc ON sc.coupon_id = cc.coupon_id
            AND cc.issued_at >= '{$range_start} 00:00:00'
            AND cc.issued_at <= '{$range_end} 23:59:59'
        LEFT JOIN appointment_shop_detail asd ON asd.customer_coupon_id = cc.customer_coupon_id
            AND asd.appointment_datetime >= '{$range_start} 00:00:00'
            AND asd.appointment_datetime <= '{$range_end} 23:59:59'
        WHERE sc.shop_id = {$shop_id}
        GROUP BY sc.coupon_id, sc.coupon_name, sc.coupon_code
        ORDER BY issued_count DESC, used_count DESC
        LIMIT 20
    ";

    $result = sql_query_pg($sql);
    $rows = [];

    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $rows[] = [
                'coupon_id' => (int)$row['coupon_id'],
                'coupon_name' => $row['coupon_name'] ?? '',
                'coupon_code' => $row['coupon_code'] ?? '',
                'issued_count' => (int)$row['issued_count'],
                'used_count' => (int)$row['used_count'],
                'usage_rate' => (float)$row['usage_rate'],
                'total_discount_amount' => (int)$row['total_discount_amount'],
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
                    echo json_encode(['success' => false, 'message' => '아직 승인이 되지 않았습니다.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                if ($shop_row['status'] == 'closed') {
                    echo json_encode(['success' => false, 'message' => '폐업된 업체입니다.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                if ($shop_row['status'] == 'shutdown') {
                    echo json_encode(['success' => false, 'message' => '접근이 제한된 업체입니다. 플랫폼 관리자에게 문의하세요.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
            }
        }
    }
}

if (!$has_access || !$shop_id) {
    echo json_encode(['success' => false, 'message' => '접속할 수 없는 페이지 입니다.'], JSON_UNESCAPED_UNICODE);
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
    $coupon_summary = get_coupon_summary($shop_id, $range_start, $range_end);
    $coupon_issue_use_trend = get_coupon_issue_use_trend($shop_id, $range_start, $range_end);
    $coupon_usage_rate = get_coupon_usage_rate($shop_id, $range_start, $range_end);
    $discount_amount_trend = get_discount_amount_trend($shop_id, $range_start, $range_end);
    $coupon_detail_statistics = get_coupon_detail_statistics($shop_id, $range_start, $range_end);

    // 쿠폰 사용률 계산
    $coupon_usage_rate_percent = 0;
    if ($coupon_summary['total_coupon_issued'] > 0) {
        $coupon_usage_rate_percent = ($coupon_summary['total_coupon_used'] / $coupon_summary['total_coupon_issued']) * 100;
    }
    $coupon_summary['coupon_usage_rate'] = $coupon_usage_rate_percent;

    // summary
    $summary = $coupon_summary;

    echo json_encode([
        'success' => true,
        'period_type' => $period_type,
        'range_start' => $range_start,
        'range_end' => $range_end,
        'summary' => $summary,
        'coupon_issue_use_trend' => $coupon_issue_use_trend,
        'coupon_usage_rate' => $coupon_usage_rate,
        'discount_amount_trend' => $discount_amount_trend,
        'coupon_detail_statistics' => $coupon_detail_statistics,
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '데이터 조회 중 오류가 발생했습니다: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
exit;

