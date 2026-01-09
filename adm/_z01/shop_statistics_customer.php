<?php
$sub_menu = "970300";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

@auth_check($auth[$sub_menu], 'r');

// JS / CSS
add_javascript('<script src="'.G5_Z_URL.'/js/chartjs/chart.min.js"></script>', 0);

$g5['title'] = '고객통계';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name'] ? $shop_info['shop_name'] : (isset($shop_info['name']) ? $shop_info['name'] : 'ID: ' . $shop_id);

// 기본 기간: 최근 30일
$today = date('Y-m-d');
$default_start = date('Y-m-d', strtotime('-30 days'));
?>

<div class="mb-4 local_desc01 local_desc">
    <p>
        가맹점의 고객 통계를 조회합니다.
    </p>
    <?php echo get_shop_display_name($shop_info, $shop_id); ?>
</div>

<?php
// 통계 기간 선택 툴
render_statistics_date_range_selector($default_start, $today); ?>

<!-- 주요 지표 카드 영역 -->
<div class="grid gap-4 mb-6 statistics-cards" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
    <!-- 신규 고객 수 -->
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">신규 고객 수</div>
        <div class="mb-1 text-2xl font-bold" id="new_customer_count">- 명</div>
        <div class="text-xs text-gray-600">
            신규 고객 비율: <span id="new_customer_rate">-</span>%
        </div>
    </div>
    
    <!-- 기존 고객 수 -->
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">기존 고객 수</div>
        <div class="mb-1 text-2xl font-bold" id="existing_customer_count">- 명</div>
        <div class="text-xs text-gray-600">
            기존 고객 비율: <span id="existing_customer_rate">-</span>%
        </div>
    </div>
    
    <!-- 평균 예약 금액 -->
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">평균 예약 금액</div>
        <div class="mb-1 text-2xl font-bold" id="avg_amount_per_customer">- 원</div>
        <div class="text-xs text-gray-600">
            고객당 평균 결제 금액
        </div>
    </div>
    
    <!-- 평균 예약 빈도 -->
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">평균 예약 빈도</div>
        <div class="mb-1 text-2xl font-bold" id="avg_appointment_frequency">- 회</div>
        <div class="text-xs text-gray-600">
            고객당 평균 예약 횟수
        </div>
    </div>
    
    <!-- VIP 고객 수 -->
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">VIP 고객 수</div>
        <div class="mb-1 text-2xl font-bold" id="vip_customer_count">- 명</div>
        <div class="text-xs text-gray-600">
            누적 결제 상위 고객
        </div>
    </div>
    
    <!-- 찜 → 예약 전환률 -->
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">찜 → 예약 전환률</div>
        <div class="mb-1 text-2xl font-bold" id="wish_conversion_rate">- %</div>
        <div class="text-xs text-gray-600">
            찜 목록 추가: <span id="wish_count">-</span>건
        </div>
    </div>
</div>

<!-- 차트 영역 -->
<!-- 신규/기존 고객 비율, 고객별 예약 금액 분포, 예약 빈도 분포 (3열 배치) -->
<div class="grid gap-6 mb-6 charts-area" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr);">
    <div class="p-4 bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">신규/기존 고객 비율</h3>
        <canvas id="customer_type_chart" height="120"></canvas>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li><strong>신규 고객</strong>: 선택한 기간 동안 첫 예약을 한 고객입니다.</li>
                <li><strong>기존 고객</strong>: 선택한 기간 이전에 이미 예약한 경험이 있는 고객입니다.</li>
                <li>신규 고객 비율이 높으면 신규 유입이 활발함을 의미합니다.</li>
                <li>기존 고객 비율이 높으면 재방문 고객이 많아 고객 충성도가 높음을 의미합니다.</li>
            </ul>
        </div>
    </div>
    
    <!-- 고객별 예약 금액 분포 -->
    <div class="p-4 bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">고객별 예약 금액 분포 (상위 10명)</h3>
        <canvas id="customer_amount_chart" height="120"></canvas>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li>선택한 기간 동안 예약 금액이 높은 상위 10명의 고객을 보여줍니다.</li>
                <li>고객별 총 예약 금액을 기준으로 순위가 결정됩니다.</li>
                <li>상위 고객들의 예약 패턴을 분석하여 맞춤형 서비스를 제공할 수 있습니다.</li>
                <li>특정 고객의 금액이 비정상적으로 높으면 VIP 관리 대상으로 고려하세요.</li>
            </ul>
        </div>
    </div>
    
    <!-- 예약 빈도 분포 -->
    <div class="p-4 bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">예약 빈도 분포</h3>
        <canvas id="appointment_frequency_chart" height="120"></canvas>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li>고객들이 선택한 기간 동안 예약한 횟수별 분포를 보여줍니다.</li>
                <li>1회 예약 고객이 많으면 신규 고객 유입이 많거나 재방문율이 낮음을 의미합니다.</li>
                <li>5회 이상 예약 고객이 많으면 충성 고객층이 두터움을 의미합니다.</li>
                <li>재방문 고객 비율을 높이기 위한 마케팅 전략 수립에 활용하세요.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 찜 목록 추가 추이, 찜 → 예약 전환률 추이 (2열 배치) -->
<div class="grid gap-6 mb-6 charts-area" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <!-- 찜 목록 추가 추이 -->
    <div class="p-4 bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">찜 목록 추가 추이</h3>
        <canvas id="wish_trend_chart" height="120"></canvas>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li>선택한 기간 동안 고객들이 찜 목록에 추가한 건수의 추이를 보여줍니다.</li>
                <li>상승 추세는 가맹점에 대한 관심도가 증가함을 의미합니다.</li>
                <li>특정 시기에 찜 추가가 급증하면 마케팅 효과나 이벤트 영향일 수 있습니다.</li>
                <li>찜 추가 수가 많을수록 잠재 고객이 많아 예약 전환 가능성이 높습니다.</li>
            </ul>
        </div>
    </div>
    
    <!-- 찜 → 예약 전환률 추이 -->
    <div class="p-4 bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">찜 → 예약 전환률 추이</h3>
        <canvas id="wish_conversion_trend_chart" height="120"></canvas>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li>찜 목록에 추가한 고객 중 실제 예약으로 전환된 비율의 추이를 보여줍니다.</li>
                <li>전환률이 높으면 찜한 고객들이 실제 예약으로 이어지는 비율이 높음을 의미합니다.</li>
                <li>전환률이 낮으면 찜한 고객을 예약으로 유도하는 전략이 필요합니다.</li>
                <li>프로모션, 할인 쿠폰 제공 등으로 찜 → 예약 전환을 촉진할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- VIP 고객 목록 테이블 -->
<div class="p-4 mb-6 bg-white border rounded shadow-sm statistics-tables">
    <h3 class="mb-2 font-semibold">VIP 고객 목록 (누적 결제 금액 상위 10명)</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm tbl_head01" id="vip_customer_table">
            <thead>
            <tr>
                <th scope="col" class="text-center">순위</th>
                <th scope="col" class="text-center">고객 ID(닉네임)</th>
                <th scope="col" class="text-center">예약 횟수</th>
                <th scope="col" class="text-center">누적 결제 금액</th>
                <th scope="col" class="text-center">평균 결제 금액</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="5" class="text-center text-gray-500">조회된 데이터가 없습니다.</td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
        <p class="mb-1"><strong>해석 방법:</strong></p>
        <ul class="space-y-1 list-disc list-inside">
            <li><strong>순위</strong>: 선택한 기간 동안 누적 결제 금액 기준 상위 10명의 고객입니다.</li>
            <li><strong>예약 횟수</strong>: 해당 고객이 선택한 기간 동안 예약한 총 횟수입니다.</li>
            <li><strong>누적 결제 금액</strong>: 선택한 기간 동안 해당 고객이 결제한 총 금액입니다.</li>
            <li><strong>평균 결제 금액</strong>: 예약 횟수 대비 평균 결제 금액으로, 고객의 소비 수준을 파악할 수 있습니다.</li>
            <li>VIP 고객들에게 맞춤형 서비스나 특별 혜택을 제공하여 고객 충성도를 높이세요.</li>
        </ul>
    </div>
</div>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');

