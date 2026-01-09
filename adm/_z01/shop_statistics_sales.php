<?php
$sub_menu = "970100";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

@auth_check($auth[$sub_menu], 'r');

// JS / CSS
add_javascript('<script src="'.G5_Z_URL.'/js/chartjs/chart.min.js"></script>', 0);

$g5['title'] = '매출/정산 통계';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name'] ? $shop_info['shop_name'] : (isset($shop_info['name']) ? $shop_info['name'] : 'ID: ' . $shop_id);

// 기본 기간: 최근 30일
$today = date('Y-m-d');
$default_start = date('Y-m-d', strtotime('-30 days'));
?>

<div class="mb-4 local_desc01 local_desc">
    <p>
        가맹점의 매출 및 결제 통계를 조회합니다.
    </p>
    <?php echo get_shop_display_name($shop_info, $shop_id); ?>
</div>

<?php
// 통계 기간 선택 툴
render_statistics_date_range_selector($default_start, $today); ?>

<!-- 주요 지표 카드 영역 -->
<div class="grid gap-4 mb-6 statistics-cards" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">오늘의 매출</div>
        <div class="mb-1 text-2xl font-bold" id="today_sales_amount">- 원</div>
        <div class="text-xs text-gray-600">
            예약건수: <span id="today_appointment_count">-</span>건 · 취소금액: <span id="today_cancel_amount">- 원</span>
        </div>
    </div>
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">이번 달 매출</div>
        <div class="mb-1 text-2xl font-bold" id="month_sales_amount">- 원</div>
        <div class="text-xs text-gray-600">
            예약건수: <span id="month_appointment_count">-</span>건 · 전월 대비: <span id="month_vs_prev_rate">- %</span>
        </div>
    </div>
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">누적 매출</div>
        <div class="mb-1 text-2xl font-bold" id="total_sales_amount">- 원</div>
        <div class="text-xs text-gray-600">
            누적 예약건수: <span id="total_appointment_count">-</span>건
        </div>
    </div>
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">취소 통계</div>
        <div class="mb-1 text-xl font-bold" id="range_cancel_amount">- 원</div>
        <div class="text-xs text-gray-600">
            취소건수: <span id="range_cancel_count">-</span>건 · 취소율: <span id="range_cancel_rate">- %</span>
        </div>
    </div>
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">정산 순매출</div>
        <div class="mb-1 text-2xl font-bold" id="settlement_net_amount">- 원</div>
        <div class="text-xs text-gray-600">
            실 매출: <span id="settlement_total_sales">- 원</span> · 차감액: <span id="settlement_deduction_amount">- 원</span> (<span id="settlement_deduction_rate">-</span>%)
        </div>
    </div>
</div>

<!-- 차트 영역: 첫 번째 줄 - 기간별 매출 추이, 정산 금액 추이 -->
<div class="grid gap-6 mb-6 charts-area" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="p-4 bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">기간별 매출 추이</h3>
        <canvas id="sales_trend_chart" height="120"></canvas>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li><strong>총 매출</strong>: 예약 기준 실제 결제된 금액의 합계입니다.</li>
                <li><strong>순 매출</strong>: 총 매출에서 취소금액을 뺀 실제 매출입니다.</li>
                <li>두 선의 차이가 크면 취소율이 높은 기간을 의미합니다.</li>
                <li>상승 추세는 매출 증가, 하락 추세는 매출 감소를 나타냅니다.</li>
            </ul>
        </div>
    </div>
    <div class="p-4 bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">정산 금액 추이</h3>
        <canvas id="settlement_trend_chart" height="120"></canvas>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li>플랫폼에서 가맹점으로 정산한 금액의 추이를 보여줍니다.</li>
                <li>정산 금액은 수수료 및 기타 차감 후 실제 입금되는 금액입니다.</li>
                <li>매출 추이와 비교하여 수수료 차감 규모를 파악할 수 있습니다.</li>
                <li>정산 주기는 월별 또는 설정된 주기에 따라 진행됩니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 두 번째 줄 - 결제 수단별 통계, 상세 통계 요약 -->
<div class="grid gap-6 mb-6 charts-area" style="grid-template-columns: minmax(0, 3fr) minmax(0, 7fr);">
    <div class="p-4 bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">결제 수단별 통계</h3>
        <canvas id="payment_method_chart" height="120"></canvas>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li>고객들이 주로 사용하는 결제 수단의 비율을 보여줍니다.</li>
                <li>각 결제 수단의 비율(%)과 금액을 확인할 수 있습니다.</li>
                <li>카드 결제 비율이 높으면 신용카드 사용 고객이 많음을 의미합니다.</li>
                <li>결제 수단별 선호도를 파악하여 마케팅 전략 수립에 활용하세요.</li>
            </ul>
        </div>
    </div>
    <div class="p-4 bg-white border rounded shadow-sm statistics-tables">
        <h3 class="mb-2 font-semibold">상세 통계 요약</h3>
        <div id="sales_detail_summary" class="text-sm text-gray-700">
            선택한 기간의 매출, 예약건수, 평균 객단가, 취소금액/취소율이 이곳에 요약으로 표시됩니다.
        </div>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li><strong>총 매출액</strong>: 선택한 기간의 전체 매출 합계입니다.</li>
                <li><strong>평균 객단가</strong>: 예약 1건당 평균 결제 금액입니다.</li>
                <li><strong>취소율</strong>: 전체 예약 대비 취소 비율로, 높으면 개선이 필요합니다.</li>
                <li>기간을 변경하여 다양한 시점의 통계를 비교 분석하세요.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 세 번째 줄 - 정산 처리 내역 (100% 너비) -->
<div class="mb-6 charts-area">
    <div class="p-4 overflow-x-auto bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">정산 처리 내역</h3>
        <table class="w-full text-sm tbl_head01" id="settlement_table">
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
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li><strong>정산일</strong>: 플랫폼에서 정산을 처리한 날짜입니다.</li>
                <li><strong>정산기간</strong>: 해당 정산에 포함된 매출 발생 기간입니다.</li>
                <li><strong>상태</strong>: 완료(정산 완료), 대기(정산 대기), 실패(정산 실패)를 의미합니다.</li>
                <li>정산 금액은 해당 기간의 매출에서 수수료를 차감한 최종 입금액입니다.</li>
            </ul>
        </div>
    </div>
</div>

<?php
include_once('./js/shop_statistics_sales.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');