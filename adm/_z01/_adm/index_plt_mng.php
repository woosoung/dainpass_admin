<?php
if (!defined('_GNUBOARD_')) exit; /// 개별 페이지 접근 불가

// JS / CSS
add_javascript('<script src="'.G5_Z_URL.'/js/chartjs/chart.min.js"></script>', 0);

// 기본 기간: 한 달 전부터 오늘까지
$today = date('Y-m-d');
$default_start = date('Y-m-d', strtotime('-30 days'));
?>

<div class="local_desc01 local_desc mb-4">
    <p>
        플랫폼 통합 대시보드입니다.<br>
        <strong>플랫폼 전체 현황을 한눈에 확인할 수 있습니다.</strong>
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
    <!-- 오늘의 플랫폼 총 매출 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">오늘의 플랫폼 총 매출</div>
        <div class="text-2xl font-bold mb-1" id="today_platform_sales">- 원</div>
    </div>
    
    <!-- 전체 가맹점 수 / 활성 가맹점 수 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">전체 가맹점 수</div>
        <div class="text-2xl font-bold mb-1" id="total_shop_count">- 개</div>
        <div class="text-xs text-gray-600">
            활성 가맹점: <span id="active_shop_count">-</span>개
        </div>
    </div>
    
    <!-- 전체 회원 수 / 활성 회원 수 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">전체 회원 수</div>
        <div class="text-2xl font-bold mb-1" id="total_member_count">- 명</div>
        <div class="text-xs text-gray-600">
            활성 회원: <span id="active_member_count">-</span>명
        </div>
    </div>
    
    <!-- 오늘의 예약 건수 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">오늘의 예약 건수</div>
        <div class="text-2xl font-bold mb-1" id="today_appointment_count">- 건</div>
    </div>
    
    <!-- 플랫폼 평균 평점 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">플랫폼 평균 평점</div>
        <div class="text-2xl font-bold mb-1" id="platform_avg_rating">- 점</div>
    </div>
    
    <!-- 정산 대기 금액 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">정산 대기 금액</div>
        <div class="text-2xl font-bold mb-1" id="pending_settlement_amount">- 원</div>
    </div>
</div>

<!-- 차트 영역: 첫 번째 줄 - 매출 추이, 예약 건수 추이 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">최근 30일 매출 추이</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="sales_trend_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>최근 30일 매출 추이를 통해 플랫폼의 매출 성장 속도를 확인할 수 있습니다.</li>
                <li>상승 추세는 플랫폼 확장이 성공적으로 진행되고 있음을 의미합니다.</li>
                <li>일별/주별/월별 선택에 따라 집계 단위가 달라집니다.</li>
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">최근 30일 예약 건수 추이</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="appointment_trend_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>최근 30일 예약 건수 추이를 통해 플랫폼 이용 패턴을 확인할 수 있습니다.</li>
                <li>상승 추세는 고객 유입이 증가하고 있음을 의미합니다.</li>
                <li>일별/주별/월별 선택에 따라 집계 단위가 달라집니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 차트 영역: 두 번째 줄 - 가맹점 상태별 분포, 회원 가입 추이 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 3fr) minmax(0, 7fr);">
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
            </ul>
        </div>
    </div>
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">회원 가입 추이 (최근 30일)</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="member_signup_trend_chart"></canvas>
        </div>
        <div class="mt-3 text-xs text-gray-600 border-t pt-2">
            <p class="mb-1"><strong>해석 방법:</strong></p>
            <ul class="list-disc list-inside space-y-1">
                <li>회원 가입 추이를 통해 플랫폼의 성장 속도를 확인할 수 있습니다.</li>
                <li>급격한 증가나 감소 패턴을 분석하여 마케팅 전략을 조정할 수 있습니다.</li>
                <li>일별/주별/월별 선택에 따라 집계 단위가 달라집니다.</li>
            </ul>
        </div>
    </div>
</div>

<!-- 최근 활동 내역 영역 -->
<!-- 최근 정산 내역 -->
<div class="statistics-tables border rounded p-4 bg-white shadow-sm mb-6">
    <h3 class="mb-2 font-semibold">최근 정산 내역 (최근 10건)</h3>
    <div class="overflow-x-auto">
        <table class="tbl_head01 w-full text-sm" id="recent_settlement_table">
            <thead>
            <tr>
                <th scope="col" class="text-center">정산일</th>
                <th scope="col" class="text-center">가맹점명</th>
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

<!-- 신규 가맹점 등록 -->
<div class="statistics-tables border rounded p-4 bg-white shadow-sm mb-6">
    <h3 class="mb-2 font-semibold">신규 가맹점 등록 (최근 10개)</h3>
    <div class="overflow-x-auto">
        <table class="tbl_head01 w-full text-sm" id="recent_shop_registration_table">
            <thead>
            <tr>
                <th scope="col" class="text-center">등록일</th>
                <th scope="col" class="text-center">가맹점명</th>
                <th scope="col" class="text-center">업종</th>
                <th scope="col" class="text-center">상태</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="4" class="text-center text-gray-500">조회된 가맹점이 없습니다.</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- 신규 회원 가입 -->
<div class="statistics-tables border rounded p-4 bg-white shadow-sm mb-6">
    <h3 class="mb-2 font-semibold">신규 회원 가입 (최근 10명)</h3>
    <div class="overflow-x-auto">
        <table class="tbl_head01 w-full text-sm" id="recent_member_signup_table">
            <thead>
            <tr>
                <th scope="col" class="text-center">가입일</th>
                <th scope="col" class="text-center">회원 ID</th>
                <th scope="col" class="text-center">회원명</th>
                <th scope="col" class="text-center">이메일</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="4" class="text-center text-gray-500">조회된 회원이 없습니다.</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<?php
include_once('./js/index_plt_mng.js.php');
?>
