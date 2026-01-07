<?php
$sub_menu = "500500";
include_once('./_common.php');

// 플랫폼 관리자만 접근 가능 ($is_manager == true)
if (!$is_manager) {
        alert('플랫폼 관리자만 접근할 수 있습니다.');
}

@auth_check($auth[$sub_menu], 'r');

// JS / CSS
add_javascript('<script src="'.G5_Z_URL.'/js/chartjs/chart.min.js"></script>', 0);

$g5['title'] = '서비스/리뷰 통계';
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
        플랫폼 전체 서비스 및 리뷰에 대한 통계 및 분석을 조회합니다.
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
        <div class="text-sm text-gray-500 mb-1">총 리뷰 수</div>
        <div class="text-2xl font-bold mb-1" id="total_review_count">-</div>
        <div class="text-xs text-gray-600">기간 내 작성된 리뷰</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">평균 평점</div>
        <div class="text-2xl font-bold mb-1" id="avg_rating">-</div>
        <div class="text-xs text-gray-600">플랫폼 전체 평균</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">리뷰 작성률</div>
        <div class="text-2xl font-bold mb-1" id="review_rate">-</div>
        <div class="text-xs text-gray-600">예약 대비 리뷰 비율</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">총 서비스 수</div>
        <div class="text-2xl font-bold mb-1" id="total_service_count">-</div>
        <div class="text-xs text-gray-600">활성 서비스</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">서비스별 평균 예약 건수</div>
        <div class="text-2xl font-bold mb-1" id="avg_reservation_per_service">-</div>
        <div class="text-xs text-gray-600">서비스당 평균 예약</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">서비스별 평균 매출</div>
        <div class="text-2xl font-bold mb-1" id="avg_sales_per_service">-</div>
        <div class="text-xs text-gray-600">서비스당 평균 매출</div>
    </div>
</div>

<!-- 차트 영역: 첫 번째 줄 - 서비스별 예약 건수 순위, 서비스별 매출 순위 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">서비스별 예약 건수 순위 (상위 20개)</h3>
        <div style="position: relative; height: 400px;">
            <canvas id="service_reservation_rank_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>서비스별 예약 건수 순위를 통해 고객 선호 서비스를 파악할 수 있습니다.</li>
                <li>예약이 많은 서비스의 특징을 분석하여 다른 서비스에도 적용할 수 있습니다.</li>
                <li>예약 활성도가 높은 서비스의 운영 방식을 다른 서비스에 공유할 수 있습니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">서비스별 매출 순위 (상위 20개)</h3>
        <div style="position: relative; height: 400px;">
            <canvas id="service_sales_rank_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>서비스별 매출 순위를 통해 플랫폼 매출에 기여하는 주요 서비스를 확인할 수 있습니다.</li>
                <li>상위 서비스의 성공 요인을 분석하여 다른 서비스에 적용할 수 있습니다.</li>
                <li>매출 집중도를 파악하여 플랫폼 리스크 관리를 할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 두 번째 줄 - 업종별 평균 평점, 가맹점별 평균 평점 순위 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">업종별 평균 평점</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="category_rating_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>업종별 평균 평점을 통해 업종별 서비스 품질을 비교할 수 있습니다.</li>
                <li>평점이 낮은 업종에 대한 품질 개선 지원이 필요하며, 평점이 높은 업종의 특징을 다른 업종에 적용할 수 있습니다.</li>
                <li>업종별 평점 격차를 파악하여 균형잡힌 플랫폼 구성에 활용할 수 있습니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">가맹점별 평균 평점 순위 (상위 20개)</h3>
        <div style="position: relative; height: 400px;">
            <canvas id="shop_rating_rank_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>가맹점별 평균 평점 순위를 통해 서비스 품질이 우수한 가맹점을 확인할 수 있습니다.</li>
                <li>상위 가맹점의 운영 방식을 분석하여 다른 가맹점에 공유할 수 있습니다.</li>
                <li>평점이 높은 가맹점의 특징을 분석하여 벤치마킹할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 세 번째 줄 - 리뷰 평점 분포, 기간별 리뷰 작성 추이 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">리뷰 평점 분포</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="rating_distribution_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>리뷰 평점 분포를 통해 전체적인 서비스 만족도를 파악할 수 있습니다.</li>
                <li>높은 평점(4점 이상) 비율이 높을수록 고객 만족도가 높음을 의미하며, 낮은 평점(2점 이하) 비율이 높으면 서비스 품질 개선이 필요합니다.</li>
                <li>평점 분포를 분석하여 고객 만족도 개선 전략을 수립할 수 있습니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">기간별 리뷰 작성 추이</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="review_trend_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>기간별 리뷰 작성 추이를 통해 고객의 리뷰 작성 패턴을 확인할 수 있습니다.</li>
                <li>상승 추세는 고객 참여도가 높아지고 있음을 의미하며, 하락 추세가 지속되면 리뷰 작성 유도 전략이 필요합니다.</li>
                <li>리뷰 작성 패턴을 분석하여 고객 참여도 향상 방안을 수립할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 표 데이터: 서비스별 상세 통계 -->
<div class="statistics-tables border rounded p-4 bg-white shadow-sm mb-6">
    <h3 class="mb-2 font-semibold">서비스별 상세 통계 (상위 50개)</h3>
    <div class="overflow-x-auto">
        <table class="tbl_head01 w-full text-sm" id="service_detail_table">
            <thead>
            <tr>
                <th scope="col">서비스명</th>
                <th scope="col">가맹점명</th>
                <th scope="col">업종</th>
                <th scope="col">예약건수</th>
                <th scope="col">총 매출</th>
                <th scope="col">평균 매출</th>
                <th scope="col">평균 평점</th>
                <th scope="col">리뷰 수</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="8" class="text-center text-gray-500">조회된 데이터가 없습니다.</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<?php
include_once('./js/platform_statistics_service_review.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

