<?php
$sub_menu = "500700";
include_once('./_common.php');

// 플랫폼 관리자만 접근 가능 ($is_manager == true)
if (!$is_manager) {
        alert('플랫폼 관리자만 접근할 수 있습니다.');
}

@auth_check($auth[$sub_menu], 'r');

// JS / CSS
add_javascript('<script src="'.G5_Z_URL.'/js/chartjs/chart.min.js"></script>', 0);

$g5['title'] = '정산 통계';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

// 기본 기간: 한 달 전부터 오늘까지 (JS에서 다시 세팅하지만 초기값용)
$today = date('Y-m-d');
$default_start = date('Y-m-d', strtotime('-1 month'));

// 정산 상태 목록 (shop_settlements 테이블은 대문자 사용)
$settlement_statuses = array();
$settlement_statuses[''] = '전체 상태';
$settlement_statuses['COMPLETED'] = '완료';
$settlement_statuses['PENDING'] = '대기';

// 정산 주기 목록
$settlement_cycles = array();
$settlement_cycles[''] = '전체 주기';
$settlement_cycles['daily'] = '일일';
$settlement_cycles['weekly'] = '주간';
$settlement_cycles['monthly'] = '월간';
?>

<div class="local_desc01 local_desc mb-4">
    <p>
        플랫폼 전체 정산에 대한 통계 및 분석을 조회합니다.
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
    <select id="status_filter" class="frm_input">
        <?php foreach ($settlement_statuses as $status_key => $status_name) { ?>
            <option value="<?php echo htmlspecialchars($status_key); ?>">
                <?php echo get_text($status_name); ?>
            </option>
        <?php } ?>
    </select>
    <select id="cycle_filter" class="frm_input">
        <?php foreach ($settlement_cycles as $cycle_key => $cycle_name) { ?>
            <option value="<?php echo htmlspecialchars($cycle_key); ?>">
                <?php echo get_text($cycle_name); ?>
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
        <div class="text-sm text-gray-500 mb-1">전체 정산 금액</div>
        <div class="text-2xl font-bold mb-1" id="total_settlement_amount">-</div>
        <div class="text-xs text-gray-600">전체 정산 완료 금액</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">대기 중인 정산 금액</div>
        <div class="text-2xl font-bold mb-1" id="pending_settlement_amount">-</div>
        <div class="text-xs text-gray-600">정산 대기 상태 금액</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">기간 내 정산 금액</div>
        <div class="text-2xl font-bold mb-1" id="period_settlement_amount">-</div>
        <div class="text-xs text-gray-600">선택 기간 내 정산 완료 금액</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">정산 완료 건수</div>
        <div class="text-2xl font-bold mb-1" id="completed_count">-</div>
        <div class="text-xs text-gray-600">정산 완료된 건수</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">정산 대기 건수</div>
        <div class="text-2xl font-bold mb-1" id="pending_count">-</div>
        <div class="text-xs text-gray-600">정산 대기 중인 건수</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">평균 정산 금액</div>
        <div class="text-2xl font-bold mb-1" id="avg_settlement_amount">-</div>
        <div class="text-xs text-gray-600">정산 완료 건의 평균 금액</div>
    </div>
</div>

<!-- 차트 영역: 첫 번째 줄 - 정산 상태별 분포, 기간별 정산 금액 추이 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 2fr) minmax(0, 4fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">정산 상태별 분포</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="settlement_status_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>정산 상태별 분포를 통해 플랫폼 내 정산 처리 현황을 파악할 수 있습니다.</li>
                <li>완료된 정산 비율이 높을수록 정산 프로세스가 원활하게 운영되고 있음을 의미합니다.</li>
                <li>대기 중인 정산이 많으면 정산 처리 프로세스 검토가 필요합니다.</li>
                <li>실패한 정산이 많으면 정산 시스템 점검이 필요합니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">기간별 정산 금액 추이</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="settlement_trend_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>기간별 정산 금액 추이를 통해 플랫폼의 정산 성장 속도를 확인할 수 있습니다.</li>
                <li>급격한 증가나 감소 패턴을 분석하여 정산 프로세스 개선 전략을 수립할 수 있습니다.</li>
                <li>상승 추세는 플랫폼 정산이 성공적으로 진행되고 있음을 의미합니다.</li>
                <li>하락 추세가 지속되면 정산 정책 재검토가 필요합니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 두 번째 줄 - 정산 주기별 분포, 가맹점별 정산 금액 순위 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">정산 주기별 분포</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="settlement_cycle_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>정산 주기별 분포를 통해 플랫폼 내 정산 주기 패턴을 파악할 수 있습니다.</li>
                <li>대부분의 가맹점이 선호하는 정산 주기를 확인하여 정산 정책을 최적화할 수 있습니다.</li>
                <li>주기별 정산 금액을 비교하여 효율적인 정산 주기를 결정할 수 있습니다.</li>
                <li>가맹점별 정산 주기 선호도를 분석하여 맞춤형 정산 서비스를 제공할 수 있습니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">가맹점별 정산 금액 순위 (상위 20개)</h3>
        <div style="position: relative; height: 400px;">
            <canvas id="shop_settlement_rank_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>가맹점별 정산 금액 순위를 통해 플랫폼 정산에 기여하는 주요 가맹점을 확인할 수 있습니다.</li>
                <li>상위 가맹점의 정산 패턴을 분석하여 다른 가맹점에 적용할 수 있습니다.</li>
                <li>정산 집중도를 파악하여 플랫폼 리스크 관리를 할 수 있습니다.</li>
                <li>상위 가맹점과의 파트너십 강화를 통해 플랫폼 성장을 도모할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 표 데이터: 정산 처리 내역 -->
<div class="statistics-tables border rounded p-4 bg-white shadow-sm mb-6">
    <h3 class="mb-2 font-semibold">정산 처리 내역 (상위 20개)</h3>
    <div class="overflow-x-auto">
        <table class="tbl_head01 w-full text-sm" id="settlement_detail_table">
            <thead>
            <tr>
                <th scope="col">정산 ID</th>
                <th scope="col">가맹점명</th>
                <th scope="col">정산 금액</th>
                <th scope="col">정산 상태</th>
                <th scope="col">정산 일자</th>
                <th scope="col">정산 주기</th>
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

<?php
include_once('./js/platform_statistics_settlement.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

