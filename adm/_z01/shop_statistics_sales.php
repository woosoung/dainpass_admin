<?php
$sub_menu = "970100";
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
            $g5['title'] = '매출/정산 통계';
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
                $g5['title'] = '매출/정산 통계';
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
    $g5['title'] = '매출/정산 통계';
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

$g5['title'] = '매출/정산 통계';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name'] ? $shop_info['shop_name'] : (isset($shop_info['name']) ? $shop_info['name'] : 'ID: ' . $shop_id);

// 기본 기간: 한 달 전부터 오늘까지 (JS에서 다시 세팅하지만 초기값용)
$today = date('Y-m-d');
$default_start = date('Y-m-d', strtotime('-1 month'));
?>

<div class="local_desc01 local_desc mb-4">
    <p>
        가맹점의 매출 및 결제 통계를 조회합니다.<br>
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
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">오늘의 매출</div>
        <div class="text-2xl font-bold mb-1" id="today_sales_amount">- 원</div>
        <div class="text-xs text-gray-600">
            예약건수: <span id="today_appointment_count">-</span>건 · 취소금액: <span id="today_cancel_amount">- 원</span>
        </div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">이번 달 매출</div>
        <div class="text-2xl font-bold mb-1" id="month_sales_amount">- 원</div>
        <div class="text-xs text-gray-600">
            예약건수: <span id="month_appointment_count">-</span>건 · 전월 대비: <span id="month_vs_prev_rate">- %</span>
        </div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">누적 매출</div>
        <div class="text-2xl font-bold mb-1" id="total_sales_amount">- 원</div>
        <div class="text-xs text-gray-600">
            누적 예약건수: <span id="total_appointment_count">-</span>건
        </div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">취소 통계</div>
        <div class="text-xl font-bold mb-1" id="range_cancel_amount">- 원</div>
        <div class="text-xs text-gray-600">
            취소건수: <span id="range_cancel_count">-</span>건 · 취소율: <span id="range_cancel_rate">- %</span>
        </div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">정산 순매출</div>
        <div class="text-2xl font-bold mb-1" id="settlement_net_amount">- 원</div>
        <div class="text-xs text-gray-600">
            실 매출: <span id="settlement_total_sales">- 원</span> · 차감액: <span id="settlement_deduction_amount">- 원</span> (<span id="settlement_deduction_rate">-</span>%)
        </div>
    </div>
</div>

<!-- 차트 영역: 첫 번째 줄 - 기간별 매출 추이, 정산 금액 추이 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">기간별 매출 추이</h3>
        <canvas id="sales_trend_chart" height="120"></canvas>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">정산 금액 추이</h3>
        <canvas id="settlement_trend_chart" height="120"></canvas>
    </div>
</div>

<!-- 차트 영역: 두 번째 줄 - 정산 처리 내역, 결제 수단별 통계, 상세 통계 요약 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm overflow-x-auto">
        <h3 class="mb-2 font-semibold">정산 처리 내역</h3>
        <table class="tbl_head01 w-full text-sm" id="settlement_table">
            <thead>
            <tr>
                <th scope="col">정산일</th>
                <th scope="col">정산기간</th>
                <th scope="col">정산금액</th>
                <th scope="col">상태</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="4" class="text-center text-gray-500">조회된 정산 내역이 없습니다.</td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">결제 수단별 통계</h3>
        <canvas id="payment_method_chart" height="120"></canvas>
    </div>
    <div class="statistics-tables border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">상세 통계 요약</h3>
        <div id="sales_detail_summary" class="text-sm text-gray-700">
            선택한 기간의 매출, 예약건수, 평균 객단가, 취소금액/취소율이 이곳에 요약으로 표시됩니다.
        </div>
    </div>
</div>

<script>
var SHOP_STATISTICS_SHOP_ID = <?php echo (int)$shop_id; ?>;
</script>

<?php
include_once('./js/shop_statistics_sales.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
