<?php
$sub_menu = "970300";
include_once('./_common.php');

// 가맹점측 관리자 접근 권한 체크 (예약/쿠폰 페이지와 동일 패턴 사용)
$has_access = false;
$shop_id = 0;
$shop_info = null;

if ($is_member && $member['mb_id']) {
    // MySQL에서 회원 정보 확인
    // 플랫폼 관리자(mb_level >= 6)는 mb_2 = 'N'일 수 있으므로 mb_2 조건을 다르게 적용
    $mb_sql = " SELECT mb_id, mb_level, mb_1, mb_2, mb_leave_date, mb_intercept_date ".
              " FROM {$g5['member_table']} ".
              " WHERE mb_id = '{$member['mb_id']}' ".
              " AND mb_level >= 4 ".
              " AND ( ".
              "     mb_level >= 6 ".
              "     OR (mb_level < 6 AND mb_2 = 'Y') ".
              " ) ".
              " AND (mb_leave_date = '' OR mb_leave_date IS NULL) ".
              " AND (mb_intercept_date = '' OR mb_intercept_date IS NULL) ";
    $mb_row = sql_fetch($mb_sql, 1);

    if ($mb_row && $mb_row['mb_id']) {
        $mb_1_value = trim($mb_row['mb_1']);

        // mb_1 = '0'인 경우: 플랫폼 관리자 (가맹점 지정 안됨)
        if ($mb_1_value === '0' || $mb_1_value === '') {
            $g5['title'] = '고객통계';
            include_once(G5_ADMIN_PATH.'/admin.head.php');
            echo '<div class="local_desc01 local_desc text-center py-[200px]">';
            echo '<p>업체 데이터가 없습니다.</p>';
            echo '</div>';
            include_once(G5_ADMIN_PATH.'/admin.tail.php');
            exit;
        }

        // mb_1에 shop_id 값이 있는 경우: 해당 shop_id로 shop 테이블 조회
        if (!empty($mb_1_value)) {
            // PostgreSQL에서 shop_id 확인 (shop_id는 bigint이므로 정수로 비교)
            $shop_id_check = (int)$mb_1_value;
            $shop_sql = " SELECT shop_id, shop_name, name, status ".
                       " FROM {$g5['shop_table']} ".
                       " WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);

            if ($shop_row && $shop_row['shop_id']) {
                if ($shop_row['status'] == 'pending')
                    alert('아직 승인이 되지 않았습니다.');
                if ($shop_row['status'] == 'closed')
                    alert('폐업되었습니다.');
                if ($shop_row['status'] == 'shutdown')
                    alert('접근이 제한되었습니다. 플랫폼 관리자에게 문의하세요.');

                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
                $shop_info = $shop_row;
            } else {
                // shop_id에 해당하는 레코드가 없는 경우
                $g5['title'] = '고객통계';
                include_once(G5_ADMIN_PATH.'/admin.head.php');
                echo '<div class="local_desc01 local_desc text-center py-[200px]">';
                echo '<p>업체 데이터가 없습니다.</p>';
                echo '</div>';
                include_once(G5_ADMIN_PATH.'/admin.tail.php');
                exit;
            }
        }
    }
}

// 접근 권한이 없으면 메시지 표시
if (!$has_access) {
    $g5['title'] = '고객통계';
    include_once(G5_ADMIN_PATH.'/admin.head.php');
    echo '<div class="local_desc01 local_desc text-center py-[200px]">';
    echo '<p>접속할 수 없는 페이지 입니다.</p>';
    echo '</div>';
    include_once(G5_ADMIN_PATH.'/admin.tail.php');
    exit;
}

@auth_check($auth[$sub_menu], 'r');

// JS / CSS
add_javascript('<script src="'.G5_Z_URL.'/js/chartjs/chart.min.js"></script>', 0);

$g5['title'] = '고객통계';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name'] ? $shop_info['shop_name'] : (isset($shop_info['name']) ? $shop_info['name'] : 'ID: ' . $shop_id);

// 기본 기간: 최근 30일 (JS에서 다시 세팅하지만 초기값용)
$today = date('Y-m-d');
$default_start = date('Y-m-d', strtotime('-29 days'));
?>

<div class="local_desc01 local_desc mb-4">
    <p>
        가맹점의 고객 통계를 조회합니다.<br>
        <strong>가맹점: <?php echo get_text($shop_display_name); ?></strong>
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
    <!-- 신규 고객 수 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">신규 고객 수</div>
        <div class="text-2xl font-bold mb-1" id="new_customer_count">- 명</div>
        <div class="text-xs text-gray-600">
            신규 고객 비율: <span id="new_customer_rate">-</span>%
        </div>
    </div>
    
    <!-- 기존 고객 수 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">기존 고객 수</div>
        <div class="text-2xl font-bold mb-1" id="existing_customer_count">- 명</div>
        <div class="text-xs text-gray-600">
            기존 고객 비율: <span id="existing_customer_rate">-</span>%
        </div>
    </div>
    
    <!-- 평균 예약 금액 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">평균 예약 금액</div>
        <div class="text-2xl font-bold mb-1" id="avg_amount_per_customer">- 원</div>
        <div class="text-xs text-gray-600">
            고객당 평균 결제 금액
        </div>
    </div>
    
    <!-- 평균 예약 빈도 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">평균 예약 빈도</div>
        <div class="text-2xl font-bold mb-1" id="avg_appointment_frequency">- 회</div>
        <div class="text-xs text-gray-600">
            고객당 평균 예약 횟수
        </div>
    </div>
    
    <!-- VIP 고객 수 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">VIP 고객 수</div>
        <div class="text-2xl font-bold mb-1" id="vip_customer_count">- 명</div>
        <div class="text-xs text-gray-600">
            누적 결제 상위 고객
        </div>
    </div>
    
    <!-- 찜 → 예약 전환률 -->
    <div class="card border rounded px-4 py-3 bg-white shadow-sm">
        <div class="text-sm text-gray-500 mb-1">찜 → 예약 전환률</div>
        <div class="text-2xl font-bold mb-1" id="wish_conversion_rate">- %</div>
        <div class="text-xs text-gray-600">
            찜 목록 추가: <span id="wish_count">-</span>건
        </div>
    </div>
</div>

<!-- 차트 영역 -->
<!-- 신규/기존 고객 비율 차트 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">신규/기존 고객 비율</h3>
        <canvas id="customer_type_chart" height="120"></canvas>
    </div>
    
    <!-- 고객별 예약 금액 분포 -->
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">고객별 예약 금액 분포 (상위 10명)</h3>
        <canvas id="customer_amount_chart" height="120"></canvas>
    </div>
</div>

<!-- 예약 빈도 분포 차트 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">예약 빈도 분포</h3>
        <canvas id="appointment_frequency_chart" height="120"></canvas>
    </div>
    
    <!-- 찜 목록 추가 추이 -->
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">찜 목록 추가 추이</h3>
        <canvas id="wish_trend_chart" height="120"></canvas>
    </div>
</div>

<!-- 찜 → 예약 전환률 추이 -->
<div class="charts-area grid gap-6 mb-6" style="grid-template-columns: minmax(0, 1fr);">
    <div class="chart-container border rounded p-4 bg-white shadow-sm">
        <h3 class="mb-2 font-semibold">찜 → 예약 전환률 추이</h3>
        <canvas id="wish_conversion_trend_chart" height="120"></canvas>
    </div>
</div>

<!-- VIP 고객 목록 테이블 -->
<div class="statistics-tables border rounded p-4 bg-white shadow-sm mb-6">
    <h3 class="mb-2 font-semibold">VIP 고객 목록 (누적 결제 금액 상위 10명)</h3>
    <div class="overflow-x-auto">
        <table class="tbl_head01 w-full text-sm" id="vip_customer_table">
            <thead>
            <tr>
                <th scope="col" class="text-center">순위</th>
                <th scope="col" class="text-center">고객 ID</th>
                <th scope="col" class="text-center">예약 횟수</th>
                <th scope="col" class="text-center">누적 결제 금액</th>
                <th scope="col" class="text-center">평균 결제 금액</th>
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

<script>
var SHOP_STATISTICS_SHOP_ID = <?php echo (int)$shop_id; ?>;
</script>

<?php
include_once('./js/shop_statistics_customer.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');

