<?php
$sub_menu = "500000";
include_once('./_common.php');

// 플랫폼 관리자만 접근 가능 (mb_level >= 6, mb_1 = '0')
if ($is_member && $member['mb_id']) {
    $mb_sql = " SELECT mb_id, mb_level, mb_1, mb_2, mb_leave_date, mb_intercept_date ".
              " FROM {$g5['member_table']} ".
              " WHERE mb_id = '{$member['mb_id']}' ".
              " AND mb_level >= 6 ".
              " AND mb_1 = '0' ".
              " AND (mb_leave_date = '' OR mb_leave_date IS NULL) ".
              " AND (mb_intercept_date = '' OR mb_intercept_date IS NULL) ";
    $mb_row = sql_fetch($mb_sql, 1);

    if (!$mb_row || !$mb_row['mb_id']) {
        alert('플랫폼 관리자만 접근할 수 있습니다.');
    }
} else {
    alert('로그인이 필요합니다.');
}

@auth_check($auth[$sub_menu], 'r');

// JS / CSS
add_javascript('<script src="'.G5_Z_URL.'/js/chartjs/chart.min.js"></script>', 0);

$g5['title'] = '플랫폼 통계 대시보드';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

// 기본 기간: 한 달 전부터 오늘까지 (JS에서 다시 세팅하지만 초기값용)
$today = date('Y-m-d');
$default_start = date('Y-m-d', strtotime('-1 month'));
?>

<div class="local_desc01 local_desc mb-4">
    <p>
        플랫폼 전체 현황을 한눈에 볼 수 있는 종합 대시보드입니다.
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

<!-- 주요 KPI 카드 영역 -->
<div class="statistics-cards grid gap-4 mb-6" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
    <!-- 여기에 주요 지표 카드들이 표시됩니다 -->
</div>

<!-- 차트 영역 -->
<div class="charts-container">
    <!-- 여기에 차트들이 표시됩니다 -->
</div>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

