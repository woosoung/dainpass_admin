<?php
$sub_menu = "500100";
include_once('./_common.php');

// 플랫폼 관리자만 접근 가능 ($is_manager == true)
if (!$is_manager) {
    alert('플랫폼 관리자만 접근할 수 있습니다.');
}

@auth_check($auth[$sub_menu], 'r');

// JS / CSS
add_javascript('<script src="'.G5_Z_URL.'/js/chartjs/chart.min.js"></script>', 0);

$g5['title'] = '가맹점 통계';
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
        플랫폼 전체 가맹점에 대한 통계 및 분석을 조회합니다.
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
    <!-- <button type="button" id="export_btn" class="btn_submit btn" accesskey='s'>데이터 내보내기</button> -->
</div>

<!-- 주요 지표 카드 영역 -->
<div class="statistics-cards grid gap-4 mb-6" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">전체 가맹점 수</div>
        <div class="text-2xl font-bold mb-1" id="total_shop_count">-</div>
        <div class="text-xs text-gray-600">전체 등록된 가맹점</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">활성 가맹점 수</div>
        <div class="text-2xl font-bold mb-1" id="active_shop_count">-</div>
        <div class="text-xs text-gray-600">정상 운영 중인 가맹점</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">신규 가맹점 수</div>
        <div class="text-2xl font-bold mb-1" id="new_shop_count">-</div>
        <div class="text-xs text-gray-600">기간 내 신규 등록</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">대기 중인 가맹점 수</div>
        <div class="text-2xl font-bold mb-1" id="pending_shop_count">-</div>
        <div class="text-xs text-gray-600">승인 대기 중</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">폐업 가맹점 수</div>
        <div class="text-2xl font-bold mb-1" id="closed_shop_count">-</div>
        <div class="text-xs text-gray-600">폐업 처리된 가맹점</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">가맹점 활성화율</div>
        <div class="text-2xl font-bold mb-1" id="activation_rate">-</div>
        <div class="text-xs text-gray-600">활성 / 전체 비율</div>
    </div>
</div>

<!-- 차트 영역: 첫 번째 줄 - 가맹점 상태별 분포, 업종별 가맹점 수 분포 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 2fr) minmax(0, 4fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">가맹점 상태별 분포</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="shop_status_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>가맹점 상태별 분포를 통해 플랫폼 내 가맹점의 운영 상태를 파악할 수 있습니다.</li>
                <li>활성 가맹점 비율이 높을수록 플랫폼이 건강하게 운영되고 있음을 의미합니다.</li>
                <li>대기 중인 가맹점이 많으면 승인 프로세스 검토가 필요합니다.</li>
                <li>폐업 가맹점 비율이 높으면 가맹점 이탈 방지 정책 수립이 필요합니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm shop-category-chart-wrapper">
        <h3 class="mb-2 font-semibold">업종별 가맹점 수 분포</h3>
        <div class="shop-category-chart-scroll" style="position: relative; height: 300px; overflow-x: auto; overflow-y: hidden;">
            <div class="shop-category-chart-inner" style="min-width: 100%; display: inline-block; position: relative;">
                <canvas id="shop_category_chart"></canvas>
            </div>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>업종별 가맹점 분포를 통해 플랫폼의 서비스 구성 비율을 확인할 수 있습니다.</li>
                <li>특정 업종에 편중되어 있다면 다양성 확대를 고려해볼 수 있습니다.</li>
                <li>각 업종의 비율을 파악하여 균형잡힌 플랫폼 구성을 목표로 할 수 있습니다.</li>
                <li>신규 가맹점 유치 시 부족한 업종에 대한 마케팅을 강화할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 두 번째 줄 - 가맹점 신규 등록 추이, 업종별 평균 매출 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">가맹점 신규 등록 추이</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="new_shop_trend_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>가맹점 신규 등록 추이를 통해 플랫폼의 성장 속도를 확인할 수 있습니다.</li>
                <li>급격한 증가나 감소 패턴을 분석하여 마케팅 전략을 조정할 수 있습니다.</li>
                <li>상승 추세는 플랫폼 확장이 성공적으로 진행되고 있음을 의미합니다.</li>
                <li>하락 추세가 지속되면 가맹점 유치 전략 재검토가 필요합니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">업종별 평균 매출</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="category_avg_sales_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>업종별 평균 매출을 비교하여 수익성이 높은 업종을 파악할 수 있습니다.</li>
                <li>평균 매출이 낮은 업종에 대한 지원 정책을 고려할 수 있습니다.</li>
                <li>고수익 업종의 특징을 분석하여 다른 업종에도 적용할 수 있습니다.</li>
                <li>업종별 매출 격차를 파악하여 균형잡힌 플랫폼 구성에 활용할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 세 번째 줄 - 가맹점별 매출 순위, 가맹점별 예약 건수 순위 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">가맹점별 매출 순위 (상위 20개)</h3>
        <div style="position: relative; height: 400px;">
            <canvas id="shop_sales_rank_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>가맹점별 매출 순위를 통해 플랫폼 매출에 기여하는 주요 가맹점을 확인할 수 있습니다.</li>
                <li>상위 가맹점의 성공 요인을 분석하여 다른 가맹점에 적용할 수 있습니다.</li>
                <li>매출 집중도를 파악하여 플랫폼 리스크 관리를 할 수 있습니다.</li>
                <li>상위 가맹점과의 파트너십 강화를 통해 플랫폼 성장을 도모할 수 있습니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">가맹점별 예약 건수 순위 (상위 20개)</h3>
        <div style="position: relative; height: 400px;">
            <canvas id="shop_appointment_rank_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>가맹점별 예약 건수를 통해 고객 선호도를 파악할 수 있습니다.</li>
                <li>예약 건수가 많은 가맹점의 서비스 품질과 마케팅 방법을 분석하여 벤치마킹할 수 있습니다.</li>
                <li>예약 활성도가 높은 가맹점의 운영 방식을 다른 가맹점에 공유할 수 있습니다.</li>
                <li>예약 건수와 매출을 함께 분석하여 효율적인 가맹점을 식별할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 네 번째 줄 - 지역별 가맹점 분포 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">지역별 가맹점 분포</h3>
        <div class="mb-2">
            <button type="button" class="btn btn_small chart-type-btn active" data-chart="region_shop_chart" data-type="pie">파이</button>
            <button type="button" class="btn btn_small chart-type-btn" data-chart="region_shop_chart" data-type="bar">막대</button>
        </div>
        <div style="position: relative; height: 300px;">
            <canvas id="region_shop_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>지역별 가맹점 분포를 통해 서비스 제공 지역의 포화도와 미개발 지역을 파악할 수 있습니다.</li>
                <li>지역별 확장 전략 수립에 활용할 수 있습니다.</li>
                <li>가맹점이 집중된 지역의 특성을 분석하여 신규 진입 지역 선정에 도움을 줄 수 있습니다.</li>
                <li>지역별 고객 수요와 가맹점 밀도를 비교하여 최적의 서비스 지역을 결정할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 표 데이터: 가맹점별 상세 통계 -->
<div class="statistics-tables border rounded p-4 bg-white shadow-sm mb-6">
    <h3 class="mb-2 font-semibold">가맹점별 상세 통계 (상위 20개)</h3>
    <div class="overflow-x-auto">
        <table class="tbl_head01 w-full text-sm" id="shop_detail_table">
            <thead>
            <tr>
                <th scope="col">가맹점명</th>
                <th scope="col">업종</th>
                <th scope="col">매출</th>
                <th scope="col">예약건수</th>
                <th scope="col">평균 매출</th>
                <th scope="col">상태</th>
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

<style>
/* 업종별 가맹점 수 분포 차트의 legend 텍스트가 잘리지 않도록 처리 */
#shop_category_chart {
    max-width: 100%;
}
/* 업종별 가맹점 수 분포 차트 컨테이너 - 가로 스크롤 지원 */
.shop-category-chart-scroll {
    overflow-x: auto !important;
    overflow-y: hidden !important;
    -webkit-overflow-scrolling: touch;
}
.shop-category-chart-scroll > div {
    min-width: 100%;
    display: inline-block;
}
.shop-category-chart-inner {
    min-width: 100% !important;
}
/* Chart.js가 렌더링한 차트와 legend를 포함한 전체 영역이 스크롤 가능하도록 */
.shop-category-chart-inner {
    position: relative !important;
    white-space: nowrap !important;
}
.shop-category-chart-inner canvas {
    max-width: none !important;
    display: inline-block !important;
    vertical-align: top !important;
}
/* Chart.js가 생성한 차트 컨테이너 (canvas + legend를 포함) */
.shop-category-chart-inner > div {
    display: inline-block !important;
    position: relative !important;
    min-width: 100% !important;
    overflow: visible !important;
    white-space: normal !important;
}
/* legend가 차트 영역 밖으로 나가도 보이도록 */
.shop-category-chart-inner .chartjs-legend {
    overflow: visible !important;
    white-space: normal !important;
}
/* Chart.js legend 텍스트가 잘리지 않도록 */
.shop-category-chart-wrapper .chartjs-legend,
.shop-category-chart-wrapper canvas + div {
    overflow: visible !important;
}
.shop-category-chart-wrapper .chartjs-legend li,
.shop-category-chart-wrapper .chartjs-legend span {
    white-space: normal !important;
    word-wrap: break-word !important;
    max-width: none !important;
    overflow: visible !important;
}
/* 업종별 가맹점 수 분포 차트의 legend를 더 작게 표시 */
.shop-category-chart-wrapper #shop_category_chart + div .chartjs-legend li {
    font-size: 10px !important;
    line-height: 1.3 !important;
    margin-bottom: 2px !important;
}
.shop-category-chart-wrapper #shop_category_chart + div .chartjs-legend li span {
    font-size: 10px !important;
}
/* 차트와 legend가 컨테이너 너비를 초과할 때 스크롤 가능하도록 */
.shop-category-chart-wrapper canvas + div {
    min-width: fit-content;
}
/* 스크롤바 스타일링 (선택사항) */
.shop-category-chart-scroll::-webkit-scrollbar {
    height: 8px;
}
.shop-category-chart-scroll::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}
.shop-category-chart-scroll::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}
.shop-category-chart-scroll::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<?php
include_once('./js/platform_statistics_shop.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

