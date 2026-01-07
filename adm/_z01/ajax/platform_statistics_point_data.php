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

// 기간 계산
list($range_start, $range_end) = calculate_date_range($period_type, $start_date, $end_date);

try {
    // 1. 주요 지표 카드 데이터
    
    // 1-1. 총 포인트 적립량 (누적)
    $sql = "SELECT COALESCE(SUM(amount), 0) as total_earned
            FROM point_transactions
            WHERE TRIM(type) = '적립'";
    $row = sql_fetch_pg($sql);
    $total_earned = (int)($row['total_earned'] ?? 0);
    
    // 1-2. 총 포인트 적립취소량 (누적)
    $sql = "SELECT COALESCE(SUM(ABS(amount)), 0) as total_earned_cancelled
            FROM point_transactions
            WHERE TRIM(type) = '적립취소'";
    $row = sql_fetch_pg($sql);
    $total_earned_cancelled = (int)($row['total_earned_cancelled'] ?? 0);
    
    // 1-3. 총 포인트 사용량 (누적)
    $sql = "SELECT COALESCE(SUM(ABS(amount)), 0) as total_used
            FROM point_transactions
            WHERE TRIM(type) = '사용'";
    $row = sql_fetch_pg($sql);
    $total_used = (int)($row['total_used'] ?? 0);
    
    // 1-4. 총 포인트 사용취소량 (누적)
    $sql = "SELECT COALESCE(SUM(amount), 0) as total_used_cancelled
            FROM point_transactions
            WHERE TRIM(type) = '사용취소'";
    $row = sql_fetch_pg($sql);
    $total_used_cancelled = (int)($row['total_used_cancelled'] ?? 0);
    
    // 1-5. 현재 플랫폼 전체 포인트 잔액
    // 잔액 = 적립 - 적립취소 - 사용 + 사용취소
    $sql = "WITH point_summary AS (
                SELECT customer_id,
                       SUM(CASE WHEN TRIM(type) = '적립' THEN amount ELSE 0 END) as earned,
                       SUM(CASE WHEN TRIM(type) = '적립취소' THEN ABS(amount) ELSE 0 END) as earned_cancelled,
                       SUM(CASE WHEN TRIM(type) = '사용' THEN ABS(amount) ELSE 0 END) as used,
                       SUM(CASE WHEN TRIM(type) = '사용취소' THEN amount ELSE 0 END) as used_cancelled
                FROM point_transactions
                GROUP BY customer_id
            )
            SELECT COALESCE(SUM(earned - earned_cancelled - used + used_cancelled), 0) as total_balance
            FROM point_summary
            WHERE (earned - earned_cancelled - used + used_cancelled) > 0";
    $row = sql_fetch_pg($sql);
    $total_balance = (int)($row['total_balance'] ?? 0);
    
    // 1-6. 포인트 사용 회원 수
    $sql = "SELECT COUNT(DISTINCT customer_id) as used_member_count
            FROM point_transactions
            WHERE TRIM(type) = '사용'";
    $row = sql_fetch_pg($sql);
    $used_member_count = (int)($row['used_member_count'] ?? 0);
    
    // 1-7. 평균 회원당 포인트 잔액
    // 잔액 = 적립 - 적립취소 - 사용 + 사용취소
    $sql = "WITH point_summary AS (
                SELECT customer_id,
                       SUM(CASE WHEN TRIM(type) = '적립' THEN amount ELSE 0 END) - 
                       SUM(CASE WHEN TRIM(type) = '적립취소' THEN ABS(amount) ELSE 0 END) - 
                       SUM(CASE WHEN TRIM(type) = '사용' THEN ABS(amount) ELSE 0 END) + 
                       SUM(CASE WHEN TRIM(type) = '사용취소' THEN amount ELSE 0 END) as balance
                FROM point_transactions
                GROUP BY customer_id
                HAVING SUM(CASE WHEN TRIM(type) = '적립' THEN amount ELSE 0 END) - 
                       SUM(CASE WHEN TRIM(type) = '적립취소' THEN ABS(amount) ELSE 0 END) - 
                       SUM(CASE WHEN TRIM(type) = '사용' THEN ABS(amount) ELSE 0 END) + 
                       SUM(CASE WHEN TRIM(type) = '사용취소' THEN amount ELSE 0 END) > 0
            )
            SELECT COALESCE(AVG(balance), 0) as avg_balance
            FROM point_summary";
    $row = sql_fetch_pg($sql);
    $avg_balance = (float)($row['avg_balance'] ?? 0);
    
    // 1-8. 포인트 사용률 (순 사용량 / 순 적립량)
    // 순 사용량 = 사용 - 사용취소, 순 적립량 = 적립 - 적립취소
    $net_earned = $total_earned - $total_earned_cancelled;
    $net_used = $total_used - $total_used_cancelled;
    $usage_rate = 0.0;
    if ($net_earned > 0) {
        $usage_rate = round(($net_used / $net_earned) * 100, 1);
    }
    
    // 1-9. 포인트 적립취소율 (적립취소량 / 적립량)
    $earned_cancel_rate = 0.0;
    if ($total_earned > 0) {
        $earned_cancel_rate = round(($total_earned_cancelled / $total_earned) * 100, 1);
    }
    
    // 2. 차트 데이터
    
    // 2-1. 기간별 포인트 적립/적립취소/사용/사용취소 추이 (선 그래프)
    if ($period_type == 'weekly') {
        $date_group_expr = "DATE_TRUNC('week', created_at)::DATE";
        $date_series_expr = "DATE_TRUNC('week', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DOW FROM date_series.date) = 1";
    } elseif ($period_type == 'monthly') {
        $date_group_expr = "DATE_TRUNC('month', created_at)::DATE";
        $date_series_expr = "DATE_TRUNC('month', date_series.date)::DATE";
        $date_series_filter = "AND EXTRACT(DAY FROM date_series.date) = 1";
    } else {
        $date_group_expr = "DATE(created_at)";
        $date_series_expr = "date_series.date";
        $date_series_filter = "";
    }
    
    $sql = "WITH date_series AS (
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
            earned_data AS (
                SELECT {$date_group_expr} as date,
                       SUM(amount) as earned_amount
                FROM point_transactions
                WHERE TRIM(type) = '적립'
                  AND DATE(created_at) BETWEEN '{$range_start}' AND '{$range_end}'
                GROUP BY {$date_group_expr}
            ),
            earned_cancelled_data AS (
                SELECT {$date_group_expr} as date,
                       SUM(ABS(amount)) as earned_cancelled_amount
                FROM point_transactions
                WHERE TRIM(type) = '적립취소'
                  AND DATE(created_at) BETWEEN '{$range_start}' AND '{$range_end}'
                GROUP BY {$date_group_expr}
            ),
            used_data AS (
                SELECT {$date_group_expr} as date,
                       SUM(ABS(amount)) as used_amount
                FROM point_transactions
                WHERE TRIM(type) = '사용'
                  AND DATE(created_at) BETWEEN '{$range_start}' AND '{$range_end}'
                GROUP BY {$date_group_expr}
            ),
            used_cancelled_data AS (
                SELECT {$date_group_expr} as date,
                       SUM(amount) as used_cancelled_amount
                FROM point_transactions
                WHERE TRIM(type) = '사용취소'
                  AND DATE(created_at) BETWEEN '{$range_start}' AND '{$range_end}'
                GROUP BY {$date_group_expr}
            )
            SELECT 
                dp.period_date AS date,
                COALESCE(e.earned_amount, 0) as earned,
                COALESCE(ec.earned_cancelled_amount, 0) as earned_cancelled,
                COALESCE(u.used_amount, 0) as used,
                COALESCE(uc.used_cancelled_amount, 0) as used_cancelled
            FROM date_periods dp
            LEFT JOIN earned_data e ON dp.period_date = e.date
            LEFT JOIN earned_cancelled_data ec ON dp.period_date = ec.date
            LEFT JOIN used_data u ON dp.period_date = u.date
            LEFT JOIN used_cancelled_data uc ON dp.period_date = uc.date
            ORDER BY dp.period_date ASC";
    $result = sql_query_pg($sql);
    $point_trend = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $point_trend[] = [
                'date' => $row['date'],
                'earned' => (int)($row['earned'] ?? 0),
                'earned_cancelled' => (int)($row['earned_cancelled'] ?? 0),
                'used' => (int)($row['used'] ?? 0),
                'used_cancelled' => (int)($row['used_cancelled'] ?? 0)
            ];
        }
    }
    
    // 2-2. 포인트 거래 유형별 분포 (파이 차트)
    $sql = "SELECT 
                TRIM(type) as type,
                SUM(CASE 
                    WHEN TRIM(type) = '적립' THEN amount
                    WHEN TRIM(type) = '적립취소' THEN ABS(amount)
                    WHEN TRIM(type) = '사용' THEN ABS(amount)
                    WHEN TRIM(type) = '사용취소' THEN amount
                    ELSE 0 
                END) as total_amount
            FROM point_transactions
            GROUP BY TRIM(type)";
    $result = sql_query_pg($sql);
    $transaction_type_distribution = [
        '적립' => 0,
        '적립취소' => 0,
        '사용' => 0,
        '사용취소' => 0
    ];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $type = trim($row['type'] ?? '');
            if (isset($transaction_type_distribution[$type])) {
                $transaction_type_distribution[$type] = (int)($row['total_amount'] ?? 0);
            }
        }
    }
    
    // 2-3. 월별 포인트 순 증가량 (막대 차트)
    // 순 증가량 = 적립 - 적립취소 - 사용 + 사용취소
    $sql = "SELECT 
                DATE_TRUNC('month', created_at)::DATE as month,
                SUM(CASE WHEN TRIM(type) = '적립' THEN amount ELSE 0 END) - 
                SUM(CASE WHEN TRIM(type) = '적립취소' THEN ABS(amount) ELSE 0 END) - 
                SUM(CASE WHEN TRIM(type) = '사용' THEN ABS(amount) ELSE 0 END) + 
                SUM(CASE WHEN TRIM(type) = '사용취소' THEN amount ELSE 0 END) as net_increase
            FROM point_transactions
            WHERE created_at >= DATE_TRUNC('year', CURRENT_DATE)
            GROUP BY DATE_TRUNC('month', created_at)::DATE
            ORDER BY month ASC";
    $result = sql_query_pg($sql);
    $monthly_net_increase = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $monthly_net_increase[] = [
                'month' => $row['month'],
                'net_increase' => (int)($row['net_increase'] ?? 0)
            ];
        }
    }
    
    // 2-4. 포인트 잔액 구간별 회원 분포 (막대 차트)
    // 잔액 = 적립 - 적립취소 - 사용 + 사용취소
    $sql = "WITH point_summary AS (
                SELECT customer_id,
                       SUM(CASE WHEN TRIM(type) = '적립' THEN amount ELSE 0 END) - 
                       SUM(CASE WHEN TRIM(type) = '적립취소' THEN ABS(amount) ELSE 0 END) - 
                       SUM(CASE WHEN TRIM(type) = '사용' THEN ABS(amount) ELSE 0 END) + 
                       SUM(CASE WHEN TRIM(type) = '사용취소' THEN amount ELSE 0 END) as balance
                FROM point_transactions
                GROUP BY customer_id
            ),
            balance_ranges AS (
                SELECT 
                    CASE 
                        WHEN balance = 0 THEN '0P'
                        WHEN balance BETWEEN 1 AND 1000 THEN '1~1,000P'
                        WHEN balance BETWEEN 1001 AND 5000 THEN '1,001~5,000P'
                        WHEN balance BETWEEN 5001 AND 10000 THEN '5,001~10,000P'
                        WHEN balance BETWEEN 10001 AND 50000 THEN '10,001~50,000P'
                        WHEN balance BETWEEN 50001 AND 100000 THEN '50,001~100,000P'
                        ELSE '100,001P 이상'
                    END as range_label,
                    COUNT(*) as member_count
                FROM point_summary
                GROUP BY 
                    CASE 
                        WHEN balance = 0 THEN '0P'
                        WHEN balance BETWEEN 1 AND 1000 THEN '1~1,000P'
                        WHEN balance BETWEEN 1001 AND 5000 THEN '1,001~5,000P'
                        WHEN balance BETWEEN 5001 AND 10000 THEN '5,001~10,000P'
                        WHEN balance BETWEEN 10001 AND 50000 THEN '10,001~50,000P'
                        WHEN balance BETWEEN 50001 AND 100000 THEN '50,001~100,000P'
                        ELSE '100,001P 이상'
                    END
            )
            SELECT range_label, member_count
            FROM balance_ranges
            ORDER BY 
                CASE range_label
                    WHEN '0P' THEN 1
                    WHEN '1~1,000P' THEN 2
                    WHEN '1,001~5,000P' THEN 3
                    WHEN '5,001~10,000P' THEN 4
                    WHEN '10,001~50,000P' THEN 5
                    WHEN '50,001~100,000P' THEN 6
                    ELSE 7
                END";
    $result = sql_query_pg($sql);
    $balance_range_distribution = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $balance_range_distribution[] = [
                'range_label' => $row['range_label'] ?? '미지정',
                'member_count' => (int)($row['member_count'] ?? 0)
            ];
        }
    }
    
    // 2-5. 회원별 포인트 보유량 순위 (상위 50명)
    // 잔액 = 적립 - 적립취소 - 사용 + 사용취소
    $sql = "WITH point_summary AS (
                SELECT customer_id,
                       SUM(CASE WHEN TRIM(type) = '적립' THEN amount ELSE 0 END) - 
                       SUM(CASE WHEN TRIM(type) = '적립취소' THEN ABS(amount) ELSE 0 END) - 
                       SUM(CASE WHEN TRIM(type) = '사용' THEN ABS(amount) ELSE 0 END) + 
                       SUM(CASE WHEN TRIM(type) = '사용취소' THEN amount ELSE 0 END) as balance
                FROM point_transactions
                GROUP BY customer_id
                HAVING SUM(CASE WHEN TRIM(type) = '적립' THEN amount ELSE 0 END) - 
                       SUM(CASE WHEN TRIM(type) = '적립취소' THEN ABS(amount) ELSE 0 END) - 
                       SUM(CASE WHEN TRIM(type) = '사용' THEN ABS(amount) ELSE 0 END) + 
                       SUM(CASE WHEN TRIM(type) = '사용취소' THEN amount ELSE 0 END) > 0
            )
            SELECT 
                c.customer_id,
                c.name as customer_name,
                c.user_id,
                ps.balance
            FROM point_summary ps
            INNER JOIN customers c ON ps.customer_id = c.customer_id
            ORDER BY ps.balance DESC
            LIMIT 50";
    $result = sql_query_pg($sql);
    $member_balance_rank = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            $member_balance_rank[] = [
                'customer_id' => (int)($row['customer_id'] ?? 0),
                'customer_name' => $row['customer_name'] ?? '미지정',
                'user_id' => $row['user_id'] ?? '',
                'balance' => (int)($row['balance'] ?? 0)
            ];
        }
    }
    
    // 3. 표 데이터: 포인트 거래 내역 (최근 50건)
    // 포인트 변화량 계산: 적립(+), 적립취소(-), 사용(-), 사용취소(+)
    $sql = "WITH point_with_calc AS (
                SELECT 
                    point_id,
                    customer_id,
                    created_at,
                    TRIM(type) as type,
                    amount,
                    CASE 
                        WHEN TRIM(type) = '적립' THEN amount 
                        WHEN TRIM(type) = '적립취소' THEN -ABS(amount)
                        WHEN TRIM(type) = '사용' THEN -ABS(amount) 
                        WHEN TRIM(type) = '사용취소' THEN amount
                        ELSE 0 
                    END as point_change
                FROM point_transactions
            ),
            point_with_balance AS (
                SELECT 
                    point_id,
                    customer_id,
                    created_at,
                    type,
                    amount,
                    SUM(point_change) OVER (PARTITION BY customer_id ORDER BY created_at, point_id ROWS UNBOUNDED PRECEDING) as balance_after
                FROM point_with_calc
            )
            SELECT 
                pt.point_id,
                pt.created_at as transaction_date,
                c.customer_id,
                c.name as customer_name,
                c.user_id,
                TRIM(pt.type) as transaction_type,
                pt.amount,
                pt.memo,
                pt.payment_id,
                pt.related_id as appointment_id,
                COALESCE(pwb.balance_after, 0) as balance_after
            FROM point_transactions pt
            LEFT JOIN customers c ON pt.customer_id = c.customer_id
            LEFT JOIN point_with_balance pwb ON pt.point_id = pwb.point_id
            ORDER BY pt.created_at DESC, pt.point_id DESC
            LIMIT 50";
    $result = sql_query_pg($sql);
    $transaction_list = [];
    if ($result && is_object($result) && isset($result->result)) {
        while ($row = sql_fetch_array_pg($result->result)) {
            // 거래유형 한글 변환 (공백 제거된 값으로 비교)
            $type = trim($row['transaction_type'] ?? '');
            $type_kr = '미지정';
            switch ($type) {
                case '적립': $type_kr = '적립'; break;
                case '적립취소': $type_kr = '적립취소'; break;
                case '사용': $type_kr = '사용'; break;
                case '사용취소': $type_kr = '사용취소'; break;
            }
            
            $transaction_list[] = [
                'point_id' => (int)($row['point_id'] ?? 0),
                'transaction_date' => $row['transaction_date'] ?? '',
                'customer_id' => (int)($row['customer_id'] ?? 0),
                'customer_name' => $row['customer_name'] ?? '미지정',
                'user_id' => $row['user_id'] ?? '',
                'transaction_type' => $type,
                'transaction_type_kr' => $type_kr,
                'amount' => (int)($row['amount'] ?? 0),
                'balance_after' => (int)($row['balance_after'] ?? 0),
                'appointment_id' => $row['appointment_id'] ? (int)$row['appointment_id'] : null,
                'memo' => $row['memo'] ?? ''
            ];
        }
    }
    
    // 응답 데이터 구성
echo json_encode([
    'success' => true,
    'data' => [
            'summary' => [
                'total_earned' => $total_earned,
                'total_earned_cancelled' => $total_earned_cancelled,
                'total_used' => $total_used,
                'total_used_cancelled' => $total_used_cancelled,
                'total_balance' => $total_balance,
                'used_member_count' => $used_member_count,
                'avg_balance' => round($avg_balance, 0),
                'usage_rate' => $usage_rate,
                'earned_cancel_rate' => $earned_cancel_rate
            ],
            'point_trend' => $point_trend,
            'transaction_type_distribution' => $transaction_type_distribution,
            'monthly_net_increase' => $monthly_net_increase,
            'balance_range_distribution' => $balance_range_distribution,
            'member_balance_rank' => $member_balance_rank,
            'transaction_list' => $transaction_list,
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

