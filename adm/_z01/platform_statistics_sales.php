<?php
$sub_menu = "500300";
include_once('./_common.php');

// 플랫폼 관리자만 접근 가능 ($is_manager == true)
if (!$is_manager) {
        alert('플랫폼 관리자만 접근할 수 있습니다.');
}

@auth_check($auth[$sub_menu], 'r');

// JS / CSS
add_javascript('<script src="'.G5_Z_URL.'/js/chartjs/chart.min.js"></script>', 0);

$g5['title'] = '플랫폼 매출 통계';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

// 기본 기간: 한 달 전부터 오늘까지 (JS에서 다시 세팅하지만 초기값용)
$today = date('Y-m-d');
$default_start = date('Y-m-d', strtotime('-1 month'));

// 모든 업종 목록 가져오기 (0 포함, 계층 구조로 정렬) - platform_statistics_shop.php와 동일한 구조
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
        플랫폼 전체 매출에 대한 통계 및 분석을 조회합니다.
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
        <div class="text-sm text-gray-500 mb-1">총 매출액</div>
        <div class="text-2xl font-bold mb-1" id="total_sales">-</div>
        <div class="text-xs text-gray-600">선택 기간 내 전체 매출</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">총 정산액</div>
        <div class="text-2xl font-bold mb-1" id="total_settlement">-</div>
        <div class="text-xs text-gray-600">선택 기간 내 정산 완료 금액</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">평균 일 매출</div>
        <div class="text-2xl font-bold mb-1" id="avg_daily_sales">-</div>
        <div class="text-xs text-gray-600">일평균 매출액</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">총 결제 건수</div>
        <div class="text-2xl font-bold mb-1" id="total_payment_count">-</div>
        <div class="text-xs text-gray-600">선택 기간 내 결제 건수</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">평균 결제 금액</div>
        <div class="text-2xl font-bold mb-1" id="avg_payment_amount">-</div>
        <div class="text-xs text-gray-600">건당 평균 결제 금액</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">정산 대기 금액</div>
        <div class="text-2xl font-bold mb-1" id="pending_settlement">-</div>
        <div class="text-xs text-gray-600">아직 정산되지 않은 금액</div>
    </div>
</div>

<!-- 차트 영역: 첫 번째 줄 - 기간별 매출 추이, 월별 매출 비교 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">기간별 플랫폼 매출 추이</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="sales_trend_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>기간별 플랫폼 매출 추이를 통해 매출 성장 패턴을 확인할 수 있습니다.</li>
                <li>상승 추세는 플랫폼의 성장을 의미하며, 하락 추세가 지속되면 마케팅 전략 재검토가 필요합니다.</li>
                <li>특정 기간의 급격한 변화는 이벤트나 시장 변화의 영향을 분석할 수 있습니다.</li>
                <li>주기적인 패턴을 파악하여 예측 모델 수립에 활용할 수 있습니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">월별 매출 비교</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="monthly_sales_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>월별 매출 비교를 통해 계절성과 성장 추세를 파악할 수 있습니다.</li>
                <li>특정 월의 급격한 변화는 마케팅 이벤트나 시장 변화의 영향을 분석할 수 있습니다.</li>
                <li>전년 동월 대비 성장률을 계산하여 성장 속도를 측정할 수 있습니다.</li>
                <li>계절적 패턴을 파악하여 마케팅 전략 수립에 활용할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 두 번째 줄 - 결제 수단별 매출 분포, 업종별 매출 분포 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">결제 수단별 매출 분포</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="payment_method_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>결제 수단별 매출 분포를 통해 고객의 선호 결제 방식을 파악할 수 있습니다.</li>
                <li>특정 결제 수단에 집중되어 있다면 해당 결제 수단의 프로모션을 강화할 수 있습니다.</li>
                <li>결제 수단별 수수료 구조를 고려하여 수익성을 분석할 수 있습니다.</li>
                <li>고객 편의성을 높이기 위한 결제 수단 확대를 검토할 수 있습니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">업종별 매출 분포</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="category_sales_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>업종별 매출 분포를 통해 플랫폼의 주요 수익원을 파악할 수 있습니다.</li>
                <li>고수익 업종의 특징을 분석하여 다른 업종에도 적용할 수 있습니다.</li>
                <li>저수익 업종에 대한 지원 정책을 고려할 수 있습니다.</li>
                <li>업종별 매출 격차를 파악하여 균형잡힌 플랫폼 구성에 활용할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 세 번째 줄 - 시간대별 매출 분포, 요일별 매출 분포 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">시간대별 매출 분포</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="hourly_sales_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>시간대별 매출 분포를 통해 고객의 이용 패턴을 파악할 수 있습니다.</li>
                <li>피크 시간대를 분석하여 마케팅 및 운영 전략을 수립할 수 있습니다.</li>
                <li>저조한 시간대에 대한 프로모션을 기획할 수 있습니다.</li>
                <li>서버 부하 예측 및 리소스 배분 계획 수립에 활용할 수 있습니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">요일별 매출 분포</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="weekly_sales_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>요일별 매출 분포를 통해 주중/주말 이용 패턴을 파악할 수 있습니다.</li>
                <li>주말 매출이 높다면 주말 프로모션을 강화할 수 있습니다.</li>
                <li>주중 저조한 요일에 대한 마케팅 전략을 수립할 수 있습니다.</li>
                <li>요일별 트렌드를 분석하여 장기적인 운영 계획에 반영할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 네 번째 줄 - 가맹점별 매출 기여도 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">가맹점별 매출 기여도 (상위 20개)</h3>
        <div style="position: relative; height: 400px;">
            <canvas id="shop_contribution_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>가맹점별 매출 기여도를 통해 플랫폼 매출에 기여하는 주요 가맹점을 확인할 수 있습니다.</li>
                <li>상위 가맹점의 성공 요인을 분석하여 다른 가맹점에 적용할 수 있습니다.</li>
                <li>매출 집중도를 파악하여 플랫폼 리스크 관리를 할 수 있습니다.</li>
                <li>상위 가맹점과의 파트너십 강화를 통해 플랫폼 성장을 도모할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 표 데이터: 정산 처리 내역 -->
<div class="statistics-tables border rounded p-4 bg-white shadow-sm mb-6">
    <h3 class="mb-2 font-semibold">정산 처리 내역 (최근 50건)</h3>
    <div class="overflow-x-auto">
        <table class="tbl_head01 w-full text-sm" id="settlement_table">
            <thead>
            <tr>
                <th scope="col">정산일시</th>
                <th scope="col">가맹점명</th>
                <th scope="col">정산 금액</th>
                <th scope="col">정산 기간</th>
                <th scope="col">정산 유형</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="5" class="text-center text-gray-500">조회된 데이터가 없습니다.</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<?php
include_once('./js/platform_statistics_sales.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

