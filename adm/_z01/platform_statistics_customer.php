<?php
$sub_menu = "500200";
include_once('./_common.php');

// 플랫폼 관리자만 접근 가능 ($is_manager == true)
if (!$is_manager) {
    alert('플랫폼 관리자만 접근할 수 있습니다.');
}

@auth_check($auth[$sub_menu], 'r');

// JS / CSS
add_javascript('<script src="'.G5_Z_URL.'/js/chartjs/chart.min.js"></script>', 0);

$g5['title'] = '사용자(고객) 통계';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

// 기본 기간: 한 달 전부터 오늘까지 (JS에서 다시 세팅하지만 초기값용)
$today = date('Y-m-d');
$default_start = date('Y-m-d', strtotime('-1 month'));
?>

<div class="local_desc01 local_desc mb-4">
    <p>
        플랫폼 전체 사용자(고객)에 대한 통계 및 분석을 조회합니다.
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

<div class="btn_fixed_top">
    <!-- <button type="button" id="export_btn" class="btn btn_02">데이터 내보내기</button> -->
</div>

<!-- 주요 지표 카드 영역 -->
<div class="statistics-cards grid gap-4 mb-6" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">전체 회원 수</div>
        <div class="text-2xl font-bold mb-1" id="total_member_count">-</div>
        <div class="text-xs text-gray-600">전체 등록된 회원</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">신규 회원 수</div>
        <div class="text-2xl font-bold mb-1" id="new_member_count">-</div>
        <div class="text-xs text-gray-600">기간 내 신규 가입</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">활성 회원 수</div>
        <div class="text-2xl font-bold mb-1" id="active_member_count">-</div>
        <div class="text-xs text-gray-600">최근 30일 이내 로그인</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">탈퇴 회원 수</div>
        <div class="text-2xl font-bold mb-1" id="leave_member_count">-</div>
        <div class="text-xs text-gray-600">탈퇴 처리된 회원</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">비활성 회원 수</div>
        <div class="text-2xl font-bold mb-1" id="inactive_member_count">-</div>
        <div class="text-xs text-gray-600">90일 이상 미로그인</div>
    </div>
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">회원 활성화율</div>
        <div class="text-2xl font-bold mb-1" id="activation_rate">-</div>
        <div class="text-xs text-gray-600">활성 / 전체 비율</div>
    </div>
</div>

<!-- 차트 영역: 첫 번째 줄 - 신규/기존 회원 비율, 회원 상태별 분포 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">신규/기존 회원 비율</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="new_existing_member_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>신규/기존 회원 비율을 통해 플랫폼의 성장 패턴을 파악할 수 있습니다.</li>
                <li>신규 회원 비율이 높을수록 플랫폼 확장이 활발함을 의미합니다.</li>
                <li>기존 회원 비율이 높으면 안정적인 고객층을 보유하고 있음을 나타냅니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">회원 상태별 분포</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="member_status_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>회원 상태별 분포를 통해 플랫폼 회원의 건강도를 파악할 수 있습니다.</li>
                <li>정상 회원 비율이 높을수록 플랫폼이 안정적으로 운영되고 있음을 의미합니다.</li>
                <li>탈퇴나 비활성 회원 비율이 높으면 회원 관리 정책을 재검토할 필요가 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 두 번째 줄 - 회원 가입 추이, 회원 활성도 분포 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">회원 가입 추이</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="member_signup_trend_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>회원 가입 추이를 통해 플랫폼의 성장 속도를 확인할 수 있습니다.</li>
                <li>급격한 증가나 감소 패턴을 분석하여 마케팅 전략을 조정할 수 있습니다.</li>
                <li>상승 추세는 플랫폼 확장이 성공적으로 진행되고 있음을 의미합니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">회원 활성도 분포</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="member_activity_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>회원 활성도 분포를 통해 플랫폼 이용 패턴을 파악할 수 있습니다.</li>
                <li>최근 로그인 회원 비율이 높을수록 활성 사용자층이 두터움을 의미합니다.</li>
                <li>장기 미접속 회원 비율이 높으면 재활성화 캠페인을 고려할 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 세 번째 줄 - 회원별 예약 금액 분포 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">회원별 예약 금액 분포 (상위 20명)</h3>
        <div style="position: relative; height: 400px;">
            <canvas id="member_reservation_amount_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>회원별 예약 금액 분포를 통해 주요 고객층을 파악할 수 있습니다.</li>
                <li>상위 회원들의 예약 패턴을 분석하여 VIP 혜택 정책을 수립할 수 있습니다.</li>
                <li>특정 회원에 집중된 매출은 플랫폼 다양성 확대를 고려해볼 수 있습니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 표 데이터: VIP 회원 목록 -->
<div class="statistics-tables border rounded p-4 bg-white shadow-sm mb-6">
    <h3 class="mb-2 font-semibold">VIP 회원 목록 (상위 20명)</h3>
    <div class="overflow-x-auto">
        <table class="tbl_head01 w-full text-sm" id="vip_member_list_table">
            <thead>
            <tr>
                <th scope="col">회원 ID</th>
                <th scope="col">회원명</th>
                <th scope="col">총 예약 금액</th>
                <th scope="col">예약 건수</th>
                <th scope="col">평균 예약 금액</th>
                <th scope="col">최근 예약일</th>
                <th scope="col">가입일</th>
                <th scope="col">상태</th>
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

<style>
/* 스타일은 platform_statistics_shop.php와 동일하게 유지 */
</style>

<?php
include_once('./js/platform_statistics_customer.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

