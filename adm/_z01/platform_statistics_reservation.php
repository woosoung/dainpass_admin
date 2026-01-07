<?php
$sub_menu = "500400";
include_once('./_common.php');

// 플랫폼 관리자만 접근 가능 ($is_manager == true)
if (!$is_manager) {
        alert('플랫폼 관리자만 접근할 수 있습니다.');
}

@auth_check($auth[$sub_menu], 'r');

// JS / CSS
add_javascript('<script src="'.G5_Z_URL.'/js/chartjs/chart.min.js"></script>', 0);

$g5['title'] = '예약/운영 통계';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

// 기본 기간: 한 달 전부터 오늘까지 (JS에서 다시 세팅하지만 초기값용)
$today = date('Y-m-d');
$default_start = date('Y-m-d', strtotime('-1 month'));

// 모든 업종 목록 가져오기 (0 포함, 계층 구조로 정렬)
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

// 예약 상태 목록
$statuses = array();
$statuses[''] = '전체 상태';
$statuses['COMPLETED'] = '완료';
$statuses['PENDING'] = '대기';
$statuses['CANCELLED'] = '취소';
$statuses['CONFIRMED'] = '확정';
$statuses['NO_SHOW'] = '노쇼';
?>

<div class="local_desc01 local_desc mb-4">
    <p>
        플랫폼 전체 예약 및 운영에 대한 통계 및 분석을 조회합니다.
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
    <select id="status_filter" class="frm_input">
        <?php foreach ($statuses as $status_key => $status_name) { ?>
            <option value="<?php echo htmlspecialchars($status_key); ?>">
                <?php echo get_text($status_name); ?>
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
        <div class="text-sm text-gray-500 mb-1">총 예약 건수</div>
        <div class="text-2xl font-bold mb-1" id="total_reservation_count">-</div>
        <div class="text-xs text-gray-600">전체 예약 건수</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">완료된 예약 건수</div>
        <div class="text-2xl font-bold mb-1" id="completed_count">-</div>
        <div class="text-xs text-gray-600">완료된 예약</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">취소된 예약 건수</div>
        <div class="text-2xl font-bold mb-1" id="cancelled_count">-</div>
        <div class="text-xs text-gray-600">취소된 예약</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">예약 완료율</div>
        <div class="text-2xl font-bold mb-1" id="completion_rate">-</div>
        <div class="text-xs text-gray-600">완료 / 전체 비율</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">예약 취소율</div>
        <div class="text-2xl font-bold mb-1" id="cancellation_rate">-</div>
        <div class="text-xs text-gray-600">취소 / 전체 비율</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">평균 일 예약 건수</div>
        <div class="text-2xl font-bold mb-1" id="avg_daily_reservation">-</div>
        <div class="text-xs text-gray-600">일평균 예약 건수</div>
    </div>
</div>

<!-- 차트 영역: 첫 번째 줄 - 기간별 예약 건수 추이, 예약 상태별 분포 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">기간별 예약 건수 추이</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="reservation_trend_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>기간별 예약 건수 추이를 통해 예약 패턴과 성장 추세를 확인할 수 있습니다.</li>
                <li>상승 추세는 플랫폼의 성장을 의미하며, 하락 추세가 지속되면 마케팅 전략 재검토가 필요합니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">예약 상태별 분포</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="status_distribution_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>예약 상태별 분포를 통해 예약 처리 현황을 파악할 수 있습니다.</li>
                <li>취소율이 높다면 예약 프로세스 개선이 필요하며, 완료율이 높다면 서비스 품질이 우수함을 의미합니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 두 번째 줄 - 시간대별 예약 건수, 요일별 예약 건수 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">시간대별 예약 건수</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="hourly_reservation_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>시간대별 예약 건수를 통해 고객의 이용 패턴을 파악할 수 있습니다.</li>
                <li>피크 시간대를 분석하여 마케팅 및 운영 전략을 수립할 수 있습니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">요일별 예약 건수</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="weekly_reservation_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>요일별 예약 건수를 통해 주중/주말 이용 패턴을 파악할 수 있습니다.</li>
                <li>주말 예약이 많다면 주말 프로모션을 강화할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 세 번째 줄 - 업종별 예약 건수 분포, 지역별 예약 건수 분포 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">업종별 예약 건수 분포</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="category_reservation_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>업종별 예약 건수 분포를 통해 플랫폼의 주요 예약 업종을 파악할 수 있습니다.</li>
                <li>인기 업종의 특징을 분석하여 다른 업종에도 적용할 수 있습니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">지역별 예약 건수 분포</h3>
        <div class="mb-2">
            <button type="button" class="btn btn_small chart-type-btn active" data-chart="region_reservation_chart" data-type="pie">파이</button>
            <button type="button" class="btn btn_small chart-type-btn" data-chart="region_reservation_chart" data-type="bar">막대</button>
        </div>
        <div style="position: relative; height: 300px;">
            <canvas id="region_reservation_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>지역별 예약 건수 분포를 통해 지역별 수요를 파악할 수 있습니다.</li>
                <li>예약이 집중된 지역과 저조한 지역을 분석하여 마케팅 전략을 수립할 수 있습니다.</li>
                <li>가맹점 통계의 지역별 가맹점 분포와 함께 보면 지역별 효율성을 분석할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 네 번째 줄 - 가맹점별 예약 건수 순위 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">가맹점별 예약 건수 순위 (상위 20개)</h3>
        <div style="position: relative; height: 400px;">
            <canvas id="shop_reservation_rank_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>가맹점별 예약 건수 순위를 통해 고객 선호도를 파악할 수 있습니다.</li>
                <li>예약 건수가 많은 가맹점의 서비스 품질과 마케팅 방법을 분석하여 벤치마킹할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 표 데이터: 예약 상세 내역 -->
<div class="statistics-tables border rounded p-4 bg-white shadow-sm mb-6">
    <h3 class="mb-2 font-semibold">예약 상세 내역 (최근 50건)</h3>
    <div class="overflow-x-auto">
        <table class="tbl_head01 w-full text-sm" id="reservation_detail_table">
            <thead>
            <tr>
                <th scope="col">예약일시</th>
                <th scope="col">가맹점명</th>
                <th scope="col">예약 상태</th>
                <th scope="col">예약 금액</th>
                <th scope="col">생성일시</th>
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
include_once('./js/platform_statistics_reservation.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

