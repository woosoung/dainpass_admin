<?php
$sub_menu = "970400";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

@auth_check($auth[$sub_menu], 'r');

// JS / CSS
add_javascript('<script src="'.G5_Z_URL.'/js/chartjs/chart.min.js"></script>', 0);

$g5['title'] = '서비스/리뷰통계';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name'] ? $shop_info['shop_name'] : (isset($shop_info['name']) ? $shop_info['name'] : 'ID: ' . $shop_id);

// 기본 기간: 최근 30일
$today = date('Y-m-d');
$default_start = date('Y-m-d', strtotime('-30 days'));
?>

<div class="mb-4 local_desc01 local_desc">
    <p>
        가맹점의 서비스 및 리뷰 통계를 조회합니다.
    </p>
    <?php echo get_shop_display_name($shop_info, $shop_id); ?>
</div>

<?php
// 통계 기간 선택 툴
render_statistics_date_range_selector($default_start, $today); ?>

<!-- 주요 지표 카드 영역 -->
<div class="grid gap-4 mb-6 statistics-cards" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
    <!-- 총 서비스 수 -->
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">총 서비스 수</div>
        <div class="mb-1 text-2xl font-bold" id="total_services">- 개</div>
        <div class="text-xs text-gray-600">
            활성화된 서비스 수
        </div>
    </div>
    
    <!-- 서비스별 평균 가격 -->
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">서비스별 평균 가격</div>
        <div class="mb-1 text-2xl font-bold" id="avg_service_price">- 원</div>
        <div class="text-xs text-gray-600">
            전체 서비스 평균 가격
        </div>
    </div>
    
    <!-- 총 서비스 매출 -->
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">총 서비스 매출</div>
        <div class="mb-1 text-2xl font-bold" id="total_service_sales">- 원</div>
        <div class="text-xs text-gray-600">
            기간 내 서비스 매출 합계
        </div>
    </div>
    
    <!-- 평균 평점 -->
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">평균 평점</div>
        <div class="mb-1 text-2xl font-bold" id="avg_rating">- 점</div>
        <div class="text-xs text-gray-600">
            전체 리뷰 평균 평점
        </div>
    </div>
    
    <!-- 리뷰 건수 -->
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">리뷰 건수</div>
        <div class="mb-1 text-2xl font-bold" id="review_count">- 건</div>
        <div class="text-xs text-gray-600">
            기간 내 작성된 리뷰 수
        </div>
    </div>
    
    <!-- 서비스별 평균 예약 건수 -->
    <div class="px-4 py-3 bg-white border rounded shadow-sm card">
        <div class="mb-1 text-sm text-gray-500">서비스별 평균 예약 건수</div>
        <div class="mb-1 text-2xl font-bold" id="avg_appointment_per_service">- 건</div>
        <div class="text-xs text-gray-600">
            서비스당 평균 예약 건수
        </div>
    </div>
</div>

<!-- 차트 영역 -->
<!-- 서비스별 인기도 및 매출 차트 -->
<div class="grid gap-6 mb-6 charts-area" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
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
    
    <!-- 서비스별 매출 -->
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
</div>

<!-- 리뷰 추이 차트 -->
<div class="grid gap-6 mb-6 charts-area" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="p-4 bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">기간별 리뷰 작성 추이</h3>
        <canvas id="review_trend_chart" height="120"></canvas>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li>선택한 기간 동안 일별/주별/월별로 작성된 리뷰 건수의 추이를 보여줍니다.</li>
                <li>리뷰 작성이 증가하는 추세는 서비스 이용이 활발함을 의미합니다.</li>
                <li>리뷰가 급격히 감소하는 기간은 서비스 품질이나 고객 만족도 개선이 필요할 수 있습니다.</li>
                <li>기간별 비교를 통해 마케팅 캠페인이나 이벤트의 효과를 측정할 수 있습니다.</li>
            </ul>
        </div>
    </div>
    
    <!-- 평균 평점 추이 -->
    <div class="p-4 bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">기간별 평균 평점 추이</h3>
        <canvas id="rating_trend_chart" height="120"></canvas>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li>선택한 기간 동안 일별/주별/월별 평균 평점의 변화 추이를 보여줍니다.</li>
                <li>평점이 상승 추세면 서비스 품질이 개선되고 있음을 의미합니다.</li>
                <li>평점이 하락 추세면 서비스 개선이 필요하며, 고객 피드백을 확인해야 합니다.</li>
                <li>평점이 일정하게 유지되면 안정적인 서비스 품질을 제공하고 있음을 의미합니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 평점 분포 차트 및 서비스별 상세 통계 -->
<div class="grid gap-6 mb-6 charts-area" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="p-4 bg-white border rounded shadow-sm chart-container">
        <h3 class="mb-2 font-semibold">평점별 리뷰 건수 분포</h3>
        <canvas id="rating_distribution_chart" height="120"></canvas>
        <div class="pt-2 mt-3 text-xs text-gray-600 border-t">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="space-y-1 list-disc list-inside">
                <li>1점부터 5점까지 각 평점별로 작성된 리뷰 건수를 보여줍니다.</li>
                <li>5점 리뷰가 많으면 고객 만족도가 높은 서비스를 제공하고 있음을 의미합니다.</li>
                <li>1~2점 리뷰가 많으면 서비스 개선이 시급하며, 고객 피드백을 반영해야 합니다.</li>
                <li>평점 분포를 통해 전체적인 서비스 품질 수준을 한눈에 파악할 수 있습니다.</li>
            </ul>
        </div>
    </div>
    
    <!-- 서비스별 상세 통계 테이블 -->
    <div class="p-4 bg-white border rounded shadow-sm statistics-tables">
        <h3 class="mb-2 font-semibold">서비스별 상세 통계 (상위 20개)</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm tbl_head01" id="service_detail_table">
                <thead>
                <tr>
                    <th scope="col" class="text-center">서비스명</th>
                    <th scope="col" class="text-center">가격</th>
                    <th scope="col" class="text-center">예약 건수</th>
                    <th scope="col" class="text-center">총 매출</th>
                    <th scope="col" class="text-center">평균 매출</th>
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
                <li><strong>예약 건수</strong>: 선택한 기간 동안 해당 서비스의 예약 발생 건수입니다.</li>
                <li><strong>총 매출</strong>: 해당 서비스의 전체 매출 합계입니다.</li>
                <li><strong>평균 매출</strong>: 예약 건수당 평균 매출로, 서비스의 수익성을 파악할 수 있습니다.</li>
                <li>상위 서비스의 특징을 분석하여 마케팅 전략 수립 및 서비스 포트폴리오 최적화에 활용하세요.</li>
            </ul>
        </div>
    </div>
</div>

<?php
include_once('./js/shop_statistics_service_review.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

