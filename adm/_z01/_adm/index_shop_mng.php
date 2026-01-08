<?php
if (!defined('_GNUBOARD_')) exit; /// 개별 페이지 접근 불가

// JS / CSS
add_javascript('<script src="'.G5_Z_URL.'/js/chartjs/chart.min.js"></script>', 0);
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

// jQuery UI datepicker 플러그인
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name'] ? $shop_info['shop_name'] : (isset($shop_info['name']) ? $shop_info['name'] : 'ID: ' . $shop_id);

// 기본 기간: 한 달 전부터 오늘까지
$today = date('Y-m-d');
$default_start = date('Y-m-d', strtotime('-30 days'));
?>

<div class="mb-4 local_desc01 local_desc">
    <p>
        가맹점 대시보드입니다.
    </p>
    <?php echo get_shop_display_name($shop_info, $shop_id); ?>
</div>

<div class="flex flex-wrap items-center gap-2 mb-4 date-range-selector">
    <!-- 세그먼트 컨트롤 형태의 빠른 선택 버튼 -->
    <div class="quick-period-segment">
        <button type="button" class="quick-period-btn" data-days="7">최근 7일</button>
        <button type="button" class="quick-period-btn" data-days="30">30일</button>
        <button type="button" class="quick-period-btn" data-days="90">90일</button>
        <button type="button" class="quick-period-btn" data-days="180">180일</button>
    </div>

    <select id="period_type" class="frm_input">
        <option value="daily">일별</option>
        <option value="weekly">주별</option>
        <option value="monthly">월별</option>
    </select>
    <input type="text" id="start_date" class="frm_input" value="<?php echo $default_start; ?>" placeholder="시작일" readonly style="width: 120px;">
    <span class="text-gray-400">~</span>
    <input type="text" id="end_date" class="frm_input" value="<?php echo $today; ?>" placeholder="종료일" readonly style="width: 120px;">
    <button type="button" id="search_btn" class="btn_submit btn">조회</button>
</div>

<!-- 주요 지표 카드 영역 -->
<div class="grid gap-4 mb-6 statistics-cards" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
    <!-- 매출/정산 통계 카드 -->
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">오늘의 매출</div>
        <div class="mb-1 text-2xl font-bold" id="today_sales_amount">- 원</div>
        <div class="text-xs text-gray-600">
            예약건수: <span id="today_appointment_count">-</span>건
        </div>
    </div>
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">이번 달 매출</div>
        <div class="mb-1 text-2xl font-bold" id="month_sales_amount">- 원</div>
        <div class="text-xs text-gray-600">
            예약건수: <span id="month_appointment_count">-</span>건
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
            실 매출: <span id="settlement_total_sales">- 원</span>
        </div>
    </div>
    
    <!-- 예약/운영 통계 카드 -->
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">전체 예약 건수</div>
        <div class="mb-1 text-2xl font-bold" id="total_appointment_count">- 건</div>
        <div class="text-xs text-gray-600">
            활성 예약: <span id="active_appointment_count">-</span>건
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
    
    <!-- 고객 통계 카드 -->
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">신규 고객 수</div>
        <div class="mb-1 text-2xl font-bold" id="new_customer_count">- 명</div>
        <div class="text-xs text-gray-600">
            기존 고객: <span id="existing_customer_count">-</span>명
        </div>
    </div>
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">평균 예약 금액</div>
        <div class="mb-1 text-2xl font-bold" id="avg_amount_per_customer">- 원</div>
        <div class="text-xs text-gray-600">
            고객당 평균 결제 금액
        </div>
    </div>
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">평균 예약 빈도</div>
        <div class="mb-1 text-2xl font-bold" id="avg_appointment_frequency">- 회</div>
        <div class="text-xs text-gray-600">
            고객당 평균 예약 횟수
        </div>
    </div>
    
    <!-- 서비스/리뷰 통계 카드 -->
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">총 서비스 수</div>
        <div class="mb-1 text-2xl font-bold" id="total_services">- 개</div>
        <div class="text-xs text-gray-600">
            활성화된 서비스 수
        </div>
    </div>
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">총 서비스 매출</div>
        <div class="mb-1 text-2xl font-bold" id="total_service_sales">- 원</div>
        <div class="text-xs text-gray-600">
            기간 내 서비스 매출 합계
        </div>
    </div>
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">평균 평점</div>
        <div class="mb-1 text-2xl font-bold" id="avg_rating">- 점</div>
        <div class="text-xs text-gray-600">
            리뷰 건수: <span id="review_count">-</span>건
        </div>
    </div>
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">서비스별 평균 예약 건수</div>
        <div class="mb-1 text-2xl font-bold" id="avg_appointment_per_service">- 건</div>
        <div class="text-xs text-gray-600">
            서비스당 평균 예약 건수
        </div>
    </div>
    
    <!-- 쿠폰 통계 카드 -->
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">총 쿠폰 발급 수</div>
        <div class="mb-1 text-2xl font-bold" id="total_coupon_issued">- 개</div>
        <div class="text-xs text-gray-600">
            총 쿠폰 사용 수: <span id="total_coupon_used">-</span>개
        </div>
    </div>
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">쿠폰 사용률</div>
        <div class="mb-1 text-2xl font-bold" id="coupon_usage_rate">- %</div>
        <div class="text-xs text-gray-600">
            사용 수 / 발급 수
        </div>
    </div>
</div>

<!-- 차트 영역: 첫 번째 줄 - 기간별 매출 추이, 기간별 예약 건수 추이 -->
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
</div>

<!-- 차트 영역: 두 번째 줄 - 시간대별 예약 건수, 요일별 예약 건수, 신규/기존 고객 비율 -->
<div class="grid gap-6 mb-6 charts-area" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr);">
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
</div>

<!-- 차트 영역: 세 번째 줄 - 서비스별 예약 건수, 서비스별 매출, 고객별 예약 금액 분포 -->
<div class="grid gap-6 mb-6 charts-area" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr);">
    <div class="p-4 bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">서비스별 예약 건수 (상위 10개)</h3>
        <canvas id="service_popularity_chart" height="120"></canvas>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li>선택한 기간 동안 예약이 가장 많이 발생한 서비스 상위 10개를 보여줍니다.</li>
                <li>예약 건수가 많은 서비스는 고객들이 선호하는 인기 서비스입니다.</li>
                <li>상위 서비스의 특징을 분석하여 다른 서비스에도 적용할 수 있습니다.</li>
                <li>예약 건수가 적은 서비스는 가격 조정이나 홍보 전략 수립이 필요할 수 있습니다.</li>
            </ul>
        </div>
    </div>
    <div class="p-4 bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">서비스별 매출 (상위 10개)</h3>
        <canvas id="service_sales_chart" height="120"></canvas>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li>선택한 기간 동안 매출이 가장 높은 서비스 상위 10개를 보여줍니다.</li>
                <li>매출이 높은 서비스는 수익성 있는 핵심 서비스입니다.</li>
                <li>예약 건수와 매출을 비교하여 고가 서비스와 저가 서비스의 특성을 파악할 수 있습니다.</li>
                <li>매출 비중이 높은 서비스에 집중하여 마케팅 전략을 수립하세요.</li>
            </ul>
        </div>
    </div>
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
</div>

<!-- 차트 영역: 네 번째 줄 - 기간별 쿠폰 발급/사용 추이, 기간별 할인 금액 추이 -->
<div class="grid gap-6 mb-6 charts-area" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="p-4 bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">기간별 쿠폰 발급/사용 추이</h3>
        <canvas id="coupon_issue_use_trend_chart" height="120"></canvas>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li><strong>발급</strong>: 해당 기간에 고객에게 발급된 쿠폰의 총 개수입니다.</li>
                <li><strong>사용</strong>: 해당 기간에 실제로 사용된 쿠폰의 총 개수입니다.</li>
                <li>발급 수 대비 사용 수가 높으면 쿠폰 활용도가 좋음을 의미합니다.</li>
                <li>두 선의 차이가 크면 발급은 많지만 사용이 적은 쿠폰 정책을 검토해야 합니다.</li>
                <li>상승 추세는 쿠폰 인기도 증가, 하락 추세는 쿠폰 관심도 감소를 나타냅니다.</li>
            </ul>
        </div>
    </div>
    <div class="p-4 bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">기간별 할인 금액 추이</h3>
        <canvas id="discount_amount_trend_chart" height="120"></canvas>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li>기간별로 쿠폰 사용으로 인해 할인된 금액의 합계를 보여줍니다.</li>
                <li>할인 금액이 높으면 쿠폰 마케팅 효과가 크지만, 순수익에는 영향을 줍니다.</li>
                <li>할인 금액 추이와 매출 추이를 함께 비교하여 쿠폰의 실질적 효과를 파악하세요.</li>
                <li>급격한 증가는 프로모션 기간의 효과, 급격한 감소는 프로모션 종료를 의미합니다.</li>
                <li>안정적인 할인 금액은 고객의 쿠폰 사용 습관이 정착되었음을 나타냅니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 정산 처리 내역 테이블 -->
<div class="p-4 mb-6 bg-white border rounded shadow-sm statistics-tables">
    <h3 class="mb-2 font-semibold">정산 처리 내역 (최근 10건)</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm tbl_head01" id="settlement_table">
            <thead>
            <tr>
                <th scope="col" class="text-center">정산일</th>
                <th scope="col" class="text-center">정산기간</th>
                <th scope="col" class="text-center">정산금액</th>
                <th scope="col" class="text-center">상태</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="4" class="text-center text-gray-500">조회된 정산 내역이 없습니다.</td>
            </tr>
            </tbody>
        </table>
    </div>
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

<?php
include_once('./js/index_shop_mng.js.php');
?>
