<?php
if (!defined('_GNUBOARD_')) exit; /// 개별 페이지 접근 불가

// JS / CSS
add_javascript('<script src="'.G5_Z_URL.'/js/chartjs/chart.min.js"></script>', 0);

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name'] ? $shop_info['shop_name'] : (isset($shop_info['name']) ? $shop_info['name'] : 'ID: ' . $shop_id);

// 기본 기간: 한 달 전부터 오늘까지
$today = date('Y-m-d');
$default_start = date('Y-m-d', strtotime('-1 month'));
?>

<div class="local_desc01 local_desc mb-4">
    <p>
        가맹점 대시보드입니다.<br>
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
    <!-- 매출/정산 통계 카드 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">오늘의 매출</div>
        <div class="text-2xl font-bold mb-1" id="today_sales_amount">- 원</div>
        <div class="text-xs text-gray-600">
            예약건수: <span id="today_appointment_count">-</span>건
        </div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">이번 달 매출</div>
        <div class="text-2xl font-bold mb-1" id="month_sales_amount">- 원</div>
        <div class="text-xs text-gray-600">
            예약건수: <span id="month_appointment_count">-</span>건
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
            실 매출: <span id="settlement_total_sales">- 원</span>
        </div>
    </div>
    
    <!-- 예약/운영 통계 카드 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">전체 예약 건수</div>
        <div class="text-2xl font-bold mb-1" id="total_appointment_count">- 건</div>
        <div class="text-xs text-gray-600">
            활성 예약: <span id="active_appointment_count">-</span>건
        </div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">재방문율</div>
        <div class="text-2xl font-bold mb-1" id="repeat_visit_rate">- %</div>
        <div class="text-xs text-gray-600">
            재방문 고객: <span id="repeat_customer_count">-</span>명
        </div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">평균 예약 횟수</div>
        <div class="text-2xl font-bold mb-1" id="avg_appointment_per_customer">- 회</div>
        <div class="text-xs text-gray-600">
            고객당 평균 예약 횟수
        </div>
    </div>
    
    <!-- 고객 통계 카드 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">신규 고객 수</div>
        <div class="text-2xl font-bold mb-1" id="new_customer_count">- 명</div>
        <div class="text-xs text-gray-600">
            기존 고객: <span id="existing_customer_count">-</span>명
        </div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">평균 예약 금액</div>
        <div class="text-2xl font-bold mb-1" id="avg_amount_per_customer">- 원</div>
        <div class="text-xs text-gray-600">
            고객당 평균 결제 금액
        </div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">평균 예약 빈도</div>
        <div class="text-2xl font-bold mb-1" id="avg_appointment_frequency">- 회</div>
        <div class="text-xs text-gray-600">
            고객당 평균 예약 횟수
        </div>
    </div>
    
    <!-- 서비스/리뷰 통계 카드 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">총 서비스 수</div>
        <div class="text-2xl font-bold mb-1" id="total_services">- 개</div>
        <div class="text-xs text-gray-600">
            활성화된 서비스 수
        </div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">총 서비스 매출</div>
        <div class="text-2xl font-bold mb-1" id="total_service_sales">- 원</div>
        <div class="text-xs text-gray-600">
            기간 내 서비스 매출 합계
        </div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">평균 평점</div>
        <div class="text-2xl font-bold mb-1" id="avg_rating">- 점</div>
        <div class="text-xs text-gray-600">
            리뷰 건수: <span id="review_count">-</span>건
        </div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">서비스별 평균 예약 건수</div>
        <div class="text-2xl font-bold mb-1" id="avg_appointment_per_service">- 건</div>
        <div class="text-xs text-gray-600">
            서비스당 평균 예약 건수
        </div>
    </div>
    
    <!-- 쿠폰 통계 카드 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">총 쿠폰 발급 수</div>
        <div class="text-2xl font-bold mb-1" id="total_coupon_issued">- 개</div>
        <div class="text-xs text-gray-600">
            총 쿠폰 사용 수: <span id="total_coupon_used">-</span>개
        </div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">쿠폰 사용률</div>
        <div class="text-2xl font-bold mb-1" id="coupon_usage_rate">- %</div>
        <div class="text-xs text-gray-600">
            사용 수 / 발급 수
        </div>
    </div>
</div>

<!-- 차트 영역: 첫 번째 줄 - 기간별 매출 추이, 기간별 예약 건수 추이 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">기간별 매출 추이</h3>
        <canvas id="sales_trend_chart" height="120"></canvas>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">기간별 예약 건수 추이</h3>
        <canvas id="appointment_trend_chart" height="120"></canvas>
    </div>
</div>

<!-- 차트 영역: 두 번째 줄 - 시간대별 예약 건수, 요일별 예약 건수, 신규/기존 고객 비율 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">시간대별 예약 건수</h3>
        <canvas id="hourly_appointment_chart" height="120"></canvas>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">요일별 예약 건수</h3>
        <canvas id="weekly_appointment_chart" height="120"></canvas>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">신규/기존 고객 비율</h3>
        <canvas id="customer_type_chart" height="120"></canvas>
    </div>
</div>

<!-- 차트 영역: 세 번째 줄 - 서비스별 예약 건수, 서비스별 매출, 고객별 예약 금액 분포 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">서비스별 예약 건수 (상위 10개)</h3>
        <canvas id="service_popularity_chart" height="120"></canvas>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">서비스별 매출 (상위 10개)</h3>
        <canvas id="service_sales_chart" height="120"></canvas>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">고객별 예약 금액 분포 (상위 10명)</h3>
        <canvas id="customer_amount_chart" height="120"></canvas>
    </div>
</div>

<!-- 차트 영역: 네 번째 줄 - 기간별 쿠폰 발급/사용 추이, 기간별 할인 금액 추이 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">기간별 쿠폰 발급/사용 추이</h3>
        <canvas id="coupon_issue_use_trend_chart" height="120"></canvas>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">기간별 할인 금액 추이</h3>
        <canvas id="discount_amount_trend_chart" height="120"></canvas>
    </div>
</div>

<!-- 정산 처리 내역 테이블 -->
<div class="statistics-tables border rounded p-4 bg-white shadow-sm mb-6">
    <h3 class="mb-2 font-semibold">정산 처리 내역 (최근 10건)</h3>
    <div class="overflow-x-auto">
        <table class="tbl_head01 w-full text-sm" id="settlement_table">
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
</div>

<script>
var SHOP_STATISTICS_SHOP_ID = <?php echo (int)$shop_id; ?>;
</script>

<?php
include_once('./js/index_shop_mng.js.php');
?>