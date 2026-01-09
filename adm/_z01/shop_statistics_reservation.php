<?php
$sub_menu = "970200";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

@auth_check($auth[$sub_menu], 'r');

// JS / CSS
add_javascript('<script src="'.G5_Z_URL.'/js/chartjs/chart.min.js"></script>', 0);

$g5['title'] = '예약/운영통계';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name'] ? $shop_info['shop_name'] : (isset($shop_info['name']) ? $shop_info['name'] : 'ID: ' . $shop_id);

// 기본 기간: 최근 30일
$today = date('Y-m-d');
$default_start = date('Y-m-d', strtotime('-30 days'));
?>

<div class="mb-4 local_desc01 local_desc">
    <p>
        가맹점의 예약 및 운영 통계를 조회합니다.
    </p>
    <?php echo get_shop_display_name($shop_info, $shop_id); ?>
</div>

<?php
// 통계 기간 선택 툴
render_statistics_date_range_selector($default_start, $today); ?>

<!-- 주요 지표 카드 영역 -->
<div class="grid gap-4 mb-6 statistics-cards" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">전체 예약 건수</div>
        <div class="mb-1 text-2xl font-bold" id="total_appointment_count">- 건</div>
        <div class="text-xs text-gray-600">
            기간 내 총 예약: <span id="range_total_count">-</span>건
        </div>
    </div>
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">활성 예약 건수</div>
        <div class="mb-1 text-2xl font-bold" id="active_appointment_count">- 건</div>
        <div class="text-xs text-gray-600">
            예정된 예약 수
        </div>
    </div>
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">취소율</div>
        <div class="mb-1 text-2xl font-bold" id="cancel_rate">- %</div>
        <div class="text-xs text-gray-600">
            취소 건수: <span id="cancel_count">-</span>건
        </div>
    </div>
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">재방문율</div>
        <div class="mb-1 text-2xl font-bold" id="repeat_visit_rate">- %</div>
        <div class="text-xs text-gray-600">
            재방문 고객: <span id="repeat_customer_count">-</span>명
        </div>
    </div>
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">평균 예약 횟수</div>
        <div class="mb-1 text-2xl font-bold" id="avg_appointment_per_customer">- 회</div>
        <div class="text-xs text-gray-600">
            고객당 평균 예약 횟수
        </div>
    </div>
</div>

<!-- 차트 영역 -->
<!-- 첫 번째 줄: 기간별 예약 건수 추이, 시간대별 예약 건수, 요일별 예약 건수 -->
<div class="grid gap-6 mb-6 charts-area" style="grid-template-columns: minmax(0, 2fr) minmax(0, 1fr) minmax(0, 1fr);">
    <div class="p-4 bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">기간별 예약 건수 추이</h3>
        <canvas id="appointment_trend_chart" height="120"></canvas>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li><strong>전체 예약</strong>: 해당 기간 내 실제 발생한 예약 건수입니다.</li>
                <li><strong>취소 예약</strong>: 해당 기간 내 취소된 예약 건수입니다.</li>
                <li>두 선의 차이가 크면 취소율이 높은 기간을 의미합니다.</li>
                <li>상승 추세는 예약 증가, 하락 추세는 예약 감소를 나타냅니다.</li>
                <li>일별/주별/월별 선택에 따라 집계 단위가 달라집니다.</li>
            </ul>
        </div>
    </div>
    <div class="p-4 bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">시간대별 예약 건수</h3>
        <canvas id="hourly_appointment_chart" height="120"></canvas>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li>0시부터 23시까지 각 시간대별 예약 건수를 보여줍니다.</li>
                <li>피크 시간대를 파악하여 인력 배치를 최적화하세요.</li>
                <li>비수기 시간대에는 할인 이벤트나 프로모션을 고려해보세요.</li>
                <li>특정 시간대에 예약이 집중되면 해당 시간대의 서비스 품질 관리가 중요합니다.</li>
            </ul>
        </div>
    </div>
    <div class="p-4 bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">요일별 예약 건수</h3>
        <canvas id="weekly_appointment_chart" height="120"></canvas>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li>일요일부터 토요일까지 각 요일별 예약 건수를 보여줍니다.</li>
                <li>주중과 주말의 예약 패턴을 비교하세요.</li>
                <li>특정 요일에 예약이 집중된다면 해당 요일의 운영 전략을 세우세요.</li>
                <li>예약이 적은 요일에는 마케팅 강화나 프로모션을 검토하세요.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 두 번째 줄: 취소율 추이, 상태별 예약 분포, 상세 통계 요약 -->
<div class="grid gap-6 mb-6 charts-area" style="grid-template-columns: minmax(0, 2fr) minmax(0, 1fr) minmax(0, 1fr);">
    <div class="p-4 bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">취소율 추이</h3>
        <canvas id="cancel_trend_chart" height="120"></canvas>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li>전체 예약 대비 취소 예약의 비율(%)을 기간별로 보여줍니다.</li>
                <li>취소율이 높거나 증가 추세라면 취소 원인을 분석하세요.</li>
                <li>일반적으로 취소율이 10% 이하는 양호한 수준입니다.</li>
                <li>취소 정책 개선이나 고객 커뮤니케이션 강화를 고려하세요.</li>
                <li>일별/주별/월별 선택에 따라 집계 단위가 달라집니다.</li>
            </ul>
        </div>
    </div>
    <div class="p-4 bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">상태별 예약 분포</h3>
        <canvas id="status_distribution_chart" height="120"></canvas>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li>전체 예약 중 완료, 취소 등의 상태별 비율을 보여줍니다.</li>
                <li>각 상태의 건수와 전체 대비 비율(%)을 확인할 수 있습니다.</li>
                <li>취소 비율이 높다면 운영 개선이 필요합니다.</li>
                <li>완료된 예약 비율이 높으면 안정적인 운영을 의미합니다.</li>
                <li>BOOKED 상태는 일시적 상태이므로 통계에서 제외됩니다.</li>
            </ul>
        </div>
    </div>
    <div class="p-4 overflow-x-auto bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">상세 통계 요약</h3>
        <div id="reservation_detail_summary" class="text-sm text-gray-700">
            선택한 기간의 예약 통계 요약 정보가 표시됩니다.
        </div>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li><strong>전체 예약</strong>: 선택한 기간의 전체 예약 건수입니다.</li>
                <li><strong>활성 예약</strong>: 현재 진행 중이거나 예정된 예약 건수입니다.</li>
                <li><strong>고유 고객</strong>: 예약한 고유 고객 수입니다.</li>
                <li><strong>재방문율</strong>: 한 번 이상 예약한 고객 비율로, 높을수록 고객 충성도가 높습니다.</li>
                <li><strong>취소율</strong>: 전체 예약 대비 취소 비율입니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 요일별 시간대별 예약 패턴 테이블 -->
<div class="p-4 mb-6 bg-white border rounded shadow-sm statistics-tables">
    <h3 class="mb-2 font-semibold">요일별 시간대별 예약 패턴</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm tbl_head01" id="weekday_hour_pattern_table">
            <thead>
            <tr>
                <th scope="col" class="text-center">요일</th>
                <th scope="col" class="text-center">시간대</th>
                <th scope="col" class="text-center">예약 건수</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="3" class="text-center text-gray-500">조회된 데이터가 없습니다.</td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
        <p class="mb-1"><strong>해석 방법:</strong></p>
        <ul class="space-y-1 list-disc list-inside">
            <li>특정 요일의 특정 시간대에 예약이 집중되는 패턴을 확인할 수 있습니다.</li>
            <li>피크 요일·시간대를 파악하여 스태프 스케줄을 최적화하세요.</li>
            <li>예약이 집중되는 시간대에는 재고 관리와 서비스 준비를 철저히 하세요.</li>
            <li>예약이 적은 요일·시간대에는 할인 정책이나 프로모션을 고려하세요.</li>
            <li>이 데이터를 바탕으로 세밀한 운영 전략을 수립할 수 있습니다.</li>
        </ul>
    </div>
</div>

<script>
var SHOP_STATISTICS_SHOP_ID = <?php echo (int)$shop_id; ?>;
</script>

<?php
include_once('./js/shop_statistics_reservation.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
