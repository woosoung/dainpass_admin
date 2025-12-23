<?php
$sub_menu = "970500";
include_once('./_common.php');

// 가맹점측 관리자 접근 권한 체크 (예약/쿠폰 페이지와 동일 패턴 사용)
$has_access = false;
$shop_id = 0;
$shop_info = null;

if ($is_member && $member['mb_id']) {
    // MySQL에서 회원 정보 확인
    // 플랫폼 관리자(mb_level >= 6)는 mb_2 = 'N'일 수 있으므로 mb_2 조건을 다르게 적용
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

        // mb_1 = '0'인 경우: 플랫폼 관리자 (가맹점 지정 안됨)
        if ($mb_1_value === '0' || $mb_1_value === '') {
            $g5['title'] = '쿠폰통계';
            include_once(G5_ADMIN_PATH.'/admin.head.php');
            echo '<div class="local_desc01 local_desc text-center py-[200px]">';
            echo '<p>업체 데이터가 없습니다.</p>';
            echo '</div>';
            include_once(G5_ADMIN_PATH.'/admin.tail.php');
            exit;
        }

        // mb_1에 shop_id 값이 있는 경우: 해당 shop_id로 shop 테이블 조회
        if (!empty($mb_1_value)) {
            // PostgreSQL에서 shop_id 확인 (shop_id는 bigint이므로 정수로 비교)
            $shop_id_check = (int)$mb_1_value;
            $shop_sql = " SELECT shop_id, shop_name, name, status ".
                       " FROM {$g5['shop_table']} ".
                       " WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);

            if ($shop_row && $shop_row['shop_id']) {
                if ($shop_row['status'] == 'pending')
                    alert('아직 승인이 되지 않았습니다.');
                if ($shop_row['status'] == 'closed')
                    alert('폐업되었습니다.');
                if ($shop_row['status'] == 'shutdown')
                    alert('접근이 제한되었습니다. 플랫폼 관리자에게 문의하세요.');

                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
                $shop_info = $shop_row;
            } else {
                // shop_id에 해당하는 레코드가 없는 경우
                $g5['title'] = '쿠폰통계';
                include_once(G5_ADMIN_PATH.'/admin.head.php');
                echo '<div class="local_desc01 local_desc text-center py-[200px]">';
                echo '<p>업체 데이터가 없습니다.</p>';
                echo '</div>';
                include_once(G5_ADMIN_PATH.'/admin.tail.php');
                exit;
            }
        }
    }
}

// 접근 권한이 없으면 메시지 표시
if (!$has_access) {
    $g5['title'] = '쿠폰통계';
    include_once(G5_ADMIN_PATH.'/admin.head.php');
    echo '<div class="local_desc01 local_desc text-center py-[200px]">';
    echo '<p>접속할 수 없는 페이지 입니다.</p>';
    echo '</div>';
    include_once(G5_ADMIN_PATH.'/admin.tail.php');
    exit;
}

@auth_check($auth[$sub_menu], 'r');

// JS / CSS
add_javascript('<script src="'.G5_Z_URL.'/js/chartjs/chart.min.js"></script>', 0);

$g5['title'] = '쿠폰통계';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name'] ? $shop_info['shop_name'] : (isset($shop_info['name']) ? $shop_info['name'] : 'ID: ' . $shop_id);

// 기본 기간: 최근 30일 (JS에서 다시 세팅하지만 초기값용)
$today = date('Y-m-d');
$default_start = date('Y-m-d', strtotime('-29 days'));
?>

<div class="local_desc01 local_desc mb-4">
    <p>
        가맹점의 쿠폰 통계를 조회합니다.<br>
        <strong>가맹점: <?php echo get_text($shop_display_name); ?></strong>
    </p>
</div>

<div class="mb-4 flex flex-wrap items-center gap-2 date-range-selector">
    <select id="period_type" class="frm_input">
        <option value="daily">일별</option>
        <option value="weekly">주별</option>
        <option value="monthly">월별</option>
        <option value="custom">기간 지정</option>
    </select>
    <input type="date" id="start_date" class="frm_input" value="<?php echo $default_start; ?>">
    <input type="date" id="end_date" class="frm_input" value="<?php echo $today; ?>">
    <button type="button" id="search_btn" class="btn_submit btn">조회</button>
</div>

<!-- 주요 지표 카드 영역 -->
<div class="statistics-cards grid gap-4 mb-6" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
    <!-- 쿠폰 통계 카드 -->
    <!-- 총 쿠폰 발급 수 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">총 쿠폰 발급 수</div>
        <div class="text-2xl font-bold mb-1" id="total_coupon_issued">- 개</div>
        <div class="text-xs text-gray-600">
            기간 내 발급된 쿠폰 수
        </div>
    </div>
    
    <!-- 총 쿠폰 사용 수 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">총 쿠폰 사용 수</div>
        <div class="text-2xl font-bold mb-1" id="total_coupon_used">- 개</div>
        <div class="text-xs text-gray-600">
            기간 내 사용된 쿠폰 수
        </div>
    </div>
    
    <!-- 쿠폰 사용률 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">쿠폰 사용률</div>
        <div class="text-2xl font-bold mb-1" id="coupon_usage_rate">- %</div>
        <div class="text-xs text-gray-600">
            사용 수 / 발급 수
        </div>
    </div>
    
    <!-- 총 쿠폰 할인 금액 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">총 쿠폰 할인 금액</div>
        <div class="text-2xl font-bold mb-1" id="total_coupon_discount">- 원</div>
        <div class="text-xs text-gray-600">
            기간 내 쿠폰 할인 금액 합계
        </div>
    </div>
</div>

<!-- 차트 영역 -->
<!-- 쿠폰 추이 차트 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">기간별 쿠폰 발급/사용 추이</h3>
        <canvas id="coupon_issue_use_trend_chart" height="120"></canvas>
    </div>
    
    <!-- 할인 금액 추이 -->
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">기간별 할인 금액 추이</h3>
        <canvas id="discount_amount_trend_chart" height="120"></canvas>
    </div>
</div>

<!-- 쿠폰별 사용률 차트 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">쿠폰별 사용률 (상위 10개)</h3>
        <canvas id="coupon_usage_rate_chart" height="120"></canvas>
    </div>
</div>

<!-- 쿠폰별 상세 통계 테이블 -->
<div class="statistics-tables border rounded p-4 bg-white shadow-sm mb-6">
    <h3 class="mb-2 font-semibold">쿠폰별 상세 통계 (상위 20개)</h3>
    <div class="overflow-x-auto">
        <table class="tbl_head01 w-full text-sm" id="coupon_detail_table">
            <thead>
            <tr>
                <th scope="col" class="text-center">쿠폰명</th>
                <th scope="col" class="text-center">쿠폰코드</th>
                <th scope="col" class="text-center">발급 수</th>
                <th scope="col" class="text-center">사용 수</th>
                <th scope="col" class="text-center">사용률 (%)</th>
                <th scope="col" class="text-center">할인 금액 합계</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="6" class="text-center text-gray-500">조회된 데이터가 없습니다.</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
var SHOP_STATISTICS_SHOP_ID = <?php echo (int)$shop_id; ?>;
</script>

<?php
include_once('./js/shop_statistics_coupon.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

