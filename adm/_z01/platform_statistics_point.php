<?php
$sub_menu = "500650";
include_once('./_common.php');

// 플랫폼 관리자만 접근 가능 ($is_manager == true)
if (!$is_manager) {
        alert('플랫폼 관리자만 접근할 수 있습니다.');
}

@auth_check($auth[$sub_menu], 'r');

// JS / CSS
add_javascript('<script src="'.G5_Z_URL.'/js/chartjs/chart.min.js"></script>', 0);

$g5['title'] = '포인트 통계';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

// 기본 기간: 한 달 전부터 오늘까지 (JS에서 다시 세팅하지만 초기값용)
$today = date('Y-m-d');
$default_start = date('Y-m-d', strtotime('-1 month'));
?>

<div class="local_desc01 local_desc mb-4">
    <p>
        플랫폼 전체 포인트 적립/적립취소/사용/사용취소 통계 및 분석을 조회합니다.
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
    <button type="button" id="search_btn" class="btn_submit btn">조회</button>
</div>

<div class="btn_fixed_top">
    <!-- <button type="button" id="export_btn" class="btn btn_02">데이터 내보내기</button> -->
</div>

<!-- 주요 지표 카드 영역 -->
<div class="statistics-cards grid gap-4 mb-6" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">총 포인트 적립량 (누적)</div>
        <div class="text-2xl font-bold mb-1" id="total_earned">-</div>
        <div class="text-xs text-gray-600">전체 기간 적립량</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">총 포인트 적립취소량 (누적)</div>
        <div class="text-2xl font-bold mb-1" id="total_earned_cancelled">-</div>
        <div class="text-xs text-gray-600">전체 기간 적립취소량</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">총 포인트 사용량 (누적)</div>
        <div class="text-2xl font-bold mb-1" id="total_used">-</div>
        <div class="text-xs text-gray-600">전체 기간 사용량</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">총 포인트 사용취소량 (누적)</div>
        <div class="text-2xl font-bold mb-1" id="total_used_cancelled">-</div>
        <div class="text-xs text-gray-600">전체 기간 사용취소량</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">현재 플랫폼 전체 포인트 잔액</div>
        <div class="text-2xl font-bold mb-1" id="total_balance">-</div>
        <div class="text-xs text-gray-600">현재 시점 잔액 합계</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">포인트 사용 회원 수</div>
        <div class="text-2xl font-bold mb-1" id="used_member_count">-</div>
        <div class="text-xs text-gray-600">포인트 사용 이력 있는 회원</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">평균 회원당 포인트 잔액</div>
        <div class="text-2xl font-bold mb-1" id="avg_balance">-</div>
        <div class="text-xs text-gray-600">보유 회원 평균 잔액</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">포인트 사용률</div>
        <div class="text-2xl font-bold mb-1" id="usage_rate">-</div>
        <div class="text-xs text-gray-600">사용량 / 적립량</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">포인트 적립취소율</div>
        <div class="text-2xl font-bold mb-1" id="earned_cancel_rate">-</div>
        <div class="text-xs text-gray-600">적립취소량 / 적립량</div>
    </div>
</div>

<!-- 차트 영역: 첫 번째 줄 - 포인트 거래 유형별 분포, 기간별 포인트 적립/적립취소/사용/사용취소 추이 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 2fr) minmax(0, 4fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">포인트 거래 유형별 분포</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="transaction_type_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>포인트 거래 유형별 분포를 통해 포인트 시스템의 전체적인 흐름을 한눈에 파악할 수 있습니다.</li>
                <li>적립 비율이 높을수록 포인트 적립 시스템이 활발함을 의미합니다.</li>
                <li>사용 비율이 높을수록 적립된 포인트가 활발히 활용되고 있음을 의미합니다.</li>
                <li>적립취소 및 사용취소 비율을 통해 고객 만족도와 환불 패턴을 분석할 수 있습니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">기간별 포인트 적립/적립취소/사용/사용취소 추이</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="point_trend_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>기간별 포인트 흐름을 분석하여 적립/적립취소/사용/사용취소 패턴을 파악할 수 있습니다.</li>
                <li>적립 추세가 상승하면 플랫폼 활동이 증가하고 있음을 의미합니다.</li>
                <li>사용 추세와 적립 추세를 비교하여 포인트 활용도를 평가할 수 있습니다.</li>
                <li>적립취소 및 사용취소 패턴을 분석하여 고객 만족도를 평가할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 두 번째 줄 - 월별 포인트 순 증가량, 포인트 잔액 구간별 회원 분포 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">월별 포인트 순 증가량</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="monthly_net_increase_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>월별 포인트 순 증가량을 통해 플랫폼의 포인트 잔액 증가 추세를 파악할 수 있습니다.</li>
                <li>양수 값은 적립량이 사용량을 초과하여 포인트 잔액이 증가했음을 의미합니다.</li>
                <li>음수 값은 사용량이 적립량을 초과하여 포인트 잔액이 감소했음을 의미합니다.</li>
                <li>순 증가량 = 적립 - 적립취소 - 사용 + 사용취소로 계산됩니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">포인트 잔액 구간별 회원 분포</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="balance_range_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>포인트 잔액 구간별 회원 분포를 통해 포인트 보유 패턴을 분석할 수 있습니다.</li>
                <li>저잔액 구간 회원이 많으면 포인트 사용이 활발함을 의미합니다.</li>
                <li>고잔액 구간 회원이 많으면 포인트 활용 유도 정책이 필요할 수 있습니다.</li>
                <li>분포 패턴을 분석하여 타겟 마케팅 전략을 수립할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 세 번째 줄 - 회원별 포인트 보유량 순위 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">회원별 포인트 보유량 순위 (상위 50명)</h3>
        <div style="position: relative; height: 400px;">
            <canvas id="member_balance_rank_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>포인트 보유량이 많은 회원들을 파악하여 마케팅 타겟팅에 활용할 수 있습니다.</li>
                <li>고보유량 회원에게 맞춤형 프로모션을 제공할 수 있습니다.</li>
                <li>보유량이 높은 회원의 사용 패턴을 분석하여 포인트 활용 정책을 수립할 수 있습니다.</li>
                <li>VIP 회원 관리 전략 수립에 활용할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 표 데이터: 포인트 거래 내역 -->
<div class="statistics-tables border rounded p-4 bg-white shadow-sm mb-6">
    <h3 class="mb-2 font-semibold">포인트 거래 내역 (최근 50건)</h3>
    <div class="overflow-x-auto">
        <table class="tbl_head01 w-full text-sm" id="point_transaction_table">
            <thead>
            <tr>
                <th scope="col">거래일시</th>
                <th scope="col">회원ID</th>
                <th scope="col">회원명</th>
                <th scope="col">거래유형</th>
                <th scope="col">포인트량</th>
                <th scope="col">잔액</th>
                <th scope="col">예약ID</th>
                <th scope="col">메모</th>
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
include_once('./js/platform_statistics_point.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

