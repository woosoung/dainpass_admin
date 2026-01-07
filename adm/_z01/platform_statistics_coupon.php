<?php
$sub_menu = "500600";
include_once('./_common.php');

// 플랫폼 관리자만 접근 가능 ($is_manager == true)
if (!$is_manager) {
        alert('플랫폼 관리자만 접근할 수 있습니다.');
}

@auth_check($auth[$sub_menu], 'r');

// JS / CSS
add_javascript('<script src="'.G5_Z_URL.'/js/chartjs/chart.min.js"></script>', 0);

$g5['title'] = '쿠폰/마케팅 통계';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

// 기본 기간: 한 달 전부터 오늘까지 (JS에서 다시 세팅하지만 초기값용)
$today = date('Y-m-d');
$default_start = date('Y-m-d', strtotime('-1 month'));

// 모든 업종 목록 가져오기 (0 포함, 계층 구조로 정렬) - default_slot_list.php와 동일한 구조
$categories = array();
$categories['0'] = '모든 업종 기본';

// 1차 분류(2자리) 가져오기
$sql_primary = " SELECT category_id, name 
                  FROM {$g5['shop_categories_table']} 
                  WHERE use_yn = 'Y' 
                  AND char_length(category_id) = 2
                  ORDER BY category_id ASC ";
$result_primary = sql_query_pg($sql_primary);

if ($result_primary && is_object($result_primary) && isset($result_primary->result)) {
    while ($row = sql_fetch_array_pg($result_primary->result)) {
        $primary_id = isset($row['category_id']) ? $row['category_id'] : '';
        $primary_name = isset($row['name']) ? $row['name'] : '';
        
        if ($primary_id) {
            // 1차 분류 추가
            $categories[$primary_id] = $primary_name;
            
            // 해당 1차 분류의 2차 분류(4자리) 가져오기
            $primary_id_escaped = addslashes($primary_id);
            $sql_secondary = " SELECT category_id, name 
                               FROM {$g5['shop_categories_table']} 
                               WHERE use_yn = 'Y' 
                               AND char_length(category_id) = 4
                               AND left(category_id, 2) = '{$primary_id_escaped}'
                               ORDER BY category_id ASC ";
            $result_secondary = sql_query_pg($sql_secondary);
            
            if ($result_secondary && is_object($result_secondary) && isset($result_secondary->result)) {
                while ($row_sec = sql_fetch_array_pg($result_secondary->result)) {
                    $secondary_id = isset($row_sec['category_id']) ? $row_sec['category_id'] : '';
                    $secondary_name = isset($row_sec['name']) ? $row_sec['name'] : '';
                    
                    if ($secondary_id) {
                        // 2차 분류 추가 (부모명 포함)
                        $categories[$secondary_id] = $primary_name . ' > ' . $secondary_name;
                    }
                }
            }
        }
    }
}

// 기본 대도시 목록 (항상 표시)
$regions = array();
$regions[''] = '전체 지역';
$regions['서울특별시'] = '서울특별시';
$regions['부산광역시'] = '부산광역시';
$regions['대구광역시'] = '대구광역시';
$regions['인천광역시'] = '인천광역시';
$regions['광주광역시'] = '광주광역시';
$regions['대전광역시'] = '대전광역시';
$regions['울산광역시'] = '울산광역시';
$regions['세종특별자치시'] = '세종특별자치시';
$regions['경기도'] = '경기도';
$regions['강원도'] = '강원도';
$regions['충청북도'] = '충청북도';
$regions['충청남도'] = '충청남도';
$regions['전라북도'] = '전라북도';
$regions['전라남도'] = '전라남도';
$regions['경상북도'] = '경상북도';
$regions['경상남도'] = '경상남도';
$regions['제주특별자치도'] = '제주특별자치도';
?>

<div class="local_desc01 local_desc mb-4">
    <p>
        플랫폼 전체 쿠폰 및 마케팅 활동에 대한 통계 및 분석을 조회합니다.
        쿠폰 발급은 마케팅 활동으로 간주하며, 이러한 활동이 매출에 미치는 영향을 분석하여 플랫폼 입장에서 유용한 인사이트를 제공합니다.
    </p>
</div>

<!-- 필터링 영역 -->
<div class="mb-4 flex flex-wrap items-center gap-2 date-range-selector">
    <select id="period_type" class="frm_input">
        <option value="daily">일별</option>
        <option value="weekly">주별</option>
        <option value="monthly">월별</option>
        <option value="custom">기간 지정</option>
    </select>
    <input type="date" id="start_date" class="frm_input" value="<?php echo $default_start; ?>">
    <input type="date" id="end_date" class="frm_input" value="<?php echo $today; ?>">
    <select id="category_filter" class="frm_input">
        <?php foreach ($categories as $cat_id => $cat_name) { ?>
            <option value="<?php echo $cat_id; ?>">
                <?php echo get_text($cat_name); ?>
            </option>
        <?php } ?>
    </select>
    <select id="region_filter" class="frm_input">
        <?php foreach ($regions as $region_key => $region_name) { ?>
            <option value="<?php echo htmlspecialchars($region_key); ?>">
                <?php echo get_text($region_name); ?>
            </option>
        <?php } ?>
    </select>
    <button type="button" id="search_btn" class="btn_submit btn">조회</button>
</div>

<div class="btn_fixed_top">
    <!-- <button type="button" id="export_btn" class="btn btn_02">데이터 내보내기</button> -->
</div>

<!-- 주요 지표 카드 영역 -->
<div class="statistics-cards grid gap-4 mb-6" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">총 쿠폰 발급 건수</div>
        <div class="text-2xl font-bold mb-1" id="total_issued_count">-</div>
        <div class="text-xs text-gray-600">기간 내 발급된 쿠폰</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">총 쿠폰 사용 건수</div>
        <div class="text-2xl font-bold mb-1" id="total_used_count">-</div>
        <div class="text-xs text-gray-600">기간 내 사용된 쿠폰</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">쿠폰 사용률</div>
        <div class="text-2xl font-bold mb-1" id="usage_rate">-</div>
        <div class="text-xs text-gray-600">발급 대비 사용 비율</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">총 할인 금액</div>
        <div class="text-2xl font-bold mb-1" id="total_discount_amount">-</div>
        <div class="text-xs text-gray-600">기간 내 총 할인액</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">쿠폰 사용 시 평균 주문 금액</div>
        <div class="text-2xl font-bold mb-1" id="avg_order_amount_with_coupon">-</div>
        <div class="text-xs text-gray-600">쿠폰 사용 예약 평균 금액</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">예상 매출 증가액</div>
        <div class="text-2xl font-bold mb-1" id="estimated_sales_increase">-</div>
        <div class="text-xs text-gray-600">쿠폰 사용으로 인한 매출 증가</div>
    </div>
</div>

<!-- 차트 영역: 첫 번째 줄 - 기간별 쿠폰 발급/사용 추이 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">기간별 쿠폰 발급/사용 추이</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="coupon_issue_use_trend_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>기간별 쿠폰 발급/사용 추이를 통해 마케팅 활동의 패턴과 효과를 확인할 수 있습니다.</li>
                <li>발급과 사용의 격차가 크면 쿠폰 유효기간이나 할인율 조정이 필요할 수 있으며, 사용 추이가 발급 추이를 따라가면 쿠폰이 즉시 활용되고 있음을 의미합니다.</li>
                <li>특정 시기에 발급이 급증하면 마케팅 캠페인의 효과를 확인할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 두 번째 줄 - 기간별 할인 금액 추이, 쿠폰 타입별 분포 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">기간별 할인 금액 추이</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="discount_amount_trend_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>기간별 할인 금액 추이를 통해 가맹점이 제공한 혜택의 규모 변화를 확인할 수 있습니다.</li>
                <li>할인 금액이 증가하는 추세는 마케팅 활동이 활발해지고 있음을 의미하며, 할인 금액과 매출 증가분을 비교하여 마케팅 효율성을 평가할 수 있습니다.</li>
                <li>특정 시기에 할인 금액이 급증하면 프로모션 기간의 효과를 확인할 수 있습니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">쿠폰 타입별 분포</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="coupon_type_distribution_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>쿠폰 타입별 분포를 통해 가맹점이 주로 사용하는 쿠폰 유형을 파악할 수 있습니다.</li>
                <li>특정 타입의 쿠폰이 집중되어 있으면 해당 타입의 효과가 우수함을 의미하며, 다양한 타입의 쿠폰을 균형있게 사용하는 가맹점은 더 효과적인 마케팅 전략을 수립할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 세 번째 줄 - 가맹점별 쿠폰 발급/사용 현황, 쿠폰 사용률 추이 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">가맹점별 쿠폰 발급/사용 현황 (상위 20개)</h3>
        <div style="position: relative; height: 400px;">
            <canvas id="shop_coupon_issue_use_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>가맹점별 쿠폰 발급/사용 현황을 통해 마케팅 활동이 활발한 가맹점을 확인할 수 있습니다.</li>
                <li>발급 대비 사용 비율이 높은 가맹점은 효과적인 쿠폰 전략을 운영하고 있음을 의미하며, 이러한 가맹점의 쿠폰 정책을 다른 가맹점에 공유할 수 있습니다.</li>
                <li>발급은 많지만 사용이 적은 가맹점은 쿠폰의 유효기간, 할인율, 타겟팅 등을 재검토할 필요가 있습니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">쿠폰 사용률 추이</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="coupon_usage_rate_trend_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>쿠폰 사용률 추이를 통해 발급된 쿠폰이 실제로 활용되는 비율의 변화를 확인할 수 있습니다.</li>
                <li>사용률이 높고 상승 추세이면 쿠폰의 실효성이 우수함을 의미하며, 낮거나 하락 추세이면 쿠폰 정책(유효기간, 할인율, 타겟팅 등)의 재검토가 필요합니다.</li>
                <li>특정 시기에 사용률이 급증하면 해당 기간의 쿠폰 전략이 효과적이었음을 의미합니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 네 번째 줄 - 쿠폰 사용 시 매출 기여도 분석 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">쿠폰 사용 시 매출 기여도 분석 (상위 20개)</h3>
        <div style="position: relative; height: 400px;">
            <canvas id="coupon_sales_contribution_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>쿠폰 사용 시 매출 기여도 분석을 통해 쿠폰 마케팅이 실제 매출에 미치는 영향을 확인할 수 있습니다.</li>
                <li>할인 금액 대비 순 매출이 높을수록 마케팅 ROI가 우수함을 의미합니다.</li>
                <li>순 매출이 높은 가맹점은 쿠폰을 효과적으로 활용하여 매출을 증대시키고 있으므로, 이러한 가맹점의 전략을 다른 가맹점에 공유할 수 있습니다.</li>
                <li>할인 금액이 크지만 순 매출이 낮은 가맹점은 쿠폰 정책의 재검토가 필요합니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 표 데이터: 가맹점별 쿠폰 마케팅 상세 통계 -->
<div class="statistics-tables border rounded p-4 bg-white shadow-sm mb-6">
    <h3 class="mb-2 font-semibold">가맹점별 쿠폰 마케팅 상세 통계 (상위 50개)</h3>
    <div class="overflow-x-auto">
        <table class="tbl_head01 w-full text-sm" id="shop_coupon_detail_table">
            <thead>
            <tr>
                <th scope="col">가맹점명</th>
                <th scope="col">업종</th>
                <th scope="col">발급 건수</th>
                <th scope="col">사용 건수</th>
                <th scope="col">사용률</th>
                <th scope="col">총 할인 금액</th>
                <th scope="col">쿠폰 사용 주문 건수</th>
                <th scope="col">쿠폰 사용 주문 총 매출</th>
                <th scope="col">순 매출</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="9" class="text-center text-gray-500">조회된 데이터가 없습니다.</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<?php
include_once('./js/platform_statistics_coupon.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

