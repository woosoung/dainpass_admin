<?php
$sub_menu = "930500";
include_once('./_common.php');

// 가맹점측 관리자 접근 권한 체크
$has_access = false;
$shop_id = 0;

if ($is_member && $member['mb_id']) {
    // MySQL에서 회원 정보 확인
    $mb_sql = " SELECT mb_id, mb_level, mb_1, mb_2, mb_leave_date, mb_intercept_date 
                FROM {$g5['member_table']} 
                WHERE mb_id = '{$member['mb_id']}' 
                AND mb_level >= 4 
                AND (
                    mb_level >= 6 
                    OR (mb_level < 6 AND mb_2 = 'Y')
                )
                AND (mb_leave_date = '' OR mb_leave_date IS NULL)
                AND (mb_intercept_date = '' OR mb_intercept_date IS NULL) ";
    $mb_row = sql_fetch($mb_sql, 1);
    
    if ($mb_row && $mb_row['mb_id']) {
        $mb_1_value = trim($mb_row['mb_1']);
        
        // mb_1 = '0'인 경우: 플랫폼 관리자
        if ($mb_1_value === '0' || $mb_1_value === '') {
            $g5['title'] = '직원별 근무시간 관리';
            include_once(G5_ADMIN_PATH.'/admin.head.php');
            echo '<div class="local_desc01 local_desc text-center py-[200px]">';
            echo '<p>업체 데이터가 없습니다.</p>';
            echo '</div>';
            include_once(G5_ADMIN_PATH.'/admin.tail.php');
            exit;
        }
        
        // mb_1에 shop_id 값이 있는 경우
        if (!empty($mb_1_value)) {
            $shop_id_check = (int)$mb_1_value;
            $shop_sql = " SELECT shop_id FROM {$g5['shop_table']} WHERE shop_id = {$shop_id_check} ";
            $shop_row = sql_fetch_pg($shop_sql);
            
            if ($shop_row && $shop_row['shop_id']) {
                $has_access = true;
                $shop_id = (int)$shop_row['shop_id'];
            } else {
                $g5['title'] = '직원별 근무시간 관리';
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
    $g5['title'] = '직원별 근무시간 관리';
    include_once(G5_ADMIN_PATH.'/admin.head.php');
    echo '<div class="local_desc01 local_desc text-center py-[200px]">';
    echo '<p>접속할 수 없는 페이지 입니다.</p>';
    echo '</div>';
    include_once(G5_ADMIN_PATH.'/admin.tail.php');
    exit;
}

// 현재 년월 가져오기
$current_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$current_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');

// 년월 유효성 체크
if ($current_month < 1) {
    $current_month = 12;
    $current_year--;
} elseif ($current_month > 12) {
    $current_month = 1;
    $current_year++;
}

// 해당 월의 첫날과 마지막 날
$first_day = mktime(0, 0, 0, $current_month, 1, $current_year);
$last_day = mktime(0, 0, 0, $current_month + 1, 0, $current_year);
$days_in_month = date('t', $first_day);
$first_weekday = date('w', $first_day); // 0(일) ~ 6(토)

// 해당 월의 모든 스케줄 가져오기
$start_date = date('Y-m-01', $first_day);
$end_date = date('Y-m-t', $first_day);

$schedule_sql = "
    SELECT 
        ss.schedule_id,
        ss.staff_id,
        ss.work_date,
        ss.start_time,
        ss.end_time,
        s.name as staff_name
    FROM staff_schedules ss
    INNER JOIN staff s ON ss.staff_id = s.steps_id
    WHERE s.store_id = {$shop_id}
    AND ss.work_date >= '{$start_date}'
    AND ss.work_date <= '{$end_date}'
    ORDER BY ss.work_date, ss.start_time
";

$schedule_result = sql_query_pg($schedule_sql);
$schedules = array();

if ($schedule_result && is_object($schedule_result) && isset($schedule_result->result)) {
    while ($row = sql_fetch_array_pg($schedule_result->result)) {
        $date_key = $row['work_date'];
        if (!isset($schedules[$date_key])) {
            $schedules[$date_key] = array();
        }
        $schedules[$date_key][] = $row;
    }
}

$g5['title'] = '직원별 근무시간 관리 (달력)';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
include_once('./js/staff_schedule_calendar.js.php');
?>

<style>
.calendar-container {
    width: 100%;
    margin: 0 auto;
    padding: 20px;
}

.calendar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.calendar-nav {
    display: flex;
    align-items: center;
    gap: 15px;
}

.calendar-nav button {
    background: #fff;
    border: 1px solid #ddd;
    padding: 8px 15px;
    cursor: pointer;
    border-radius: 4px;
    font-size: 16px;
}

.calendar-nav button:hover {
    background: #e9ecef;
}

.calendar-nav select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: #ddd;
    border: 1px solid #ddd;
}

.calendar-day-header {
    background: #495057;
    color: white;
    padding: 12px;
    text-align: center;
    font-weight: bold;
}

.calendar-day-header.sunday {
    color: #ff6b6b;
}

.calendar-day-header.saturday {
    color: #4dabf7;
}

.calendar-cell {
    background: white;
    min-height: 225px;
    padding: 8px;
    position: relative;
}

.calendar-cell.other-month {
    background: #f8f9fa;
}

.btn-add-schedule-cell {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 24px;
    height: 24px;
    padding-top:3px;
    background: #228be6;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    line-height: 1;
    cursor: pointer;
    opacity: 0;
    transition: opacity 0.2s, background 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.calendar-cell:hover .btn-add-schedule-cell {
    opacity: 1;
}

.btn-add-schedule-cell:hover {
    background: #1c7ed6;
    transform: scale(1.1);
}

.calendar-date {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 8px;
}

.calendar-date.sunday {
    color: #ff6b6b;
}

.calendar-date.saturday {
    color: #4dabf7;
}

.calendar-date.weekday {
    color: #212529;
}

.schedule-list {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.schedule-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #e7f5ff;
    border: 1px solid #74c0fc;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.schedule-label:hover {
    background: #d0ebff;
    transform: translateX(2px);
}

.schedule-info {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.schedule-delete {
    color: #fa5252;
    cursor: pointer;
    padding: 0 4px;
    margin-left: 4px;
    font-weight: bold;
}

.schedule-delete:hover {
    color: #c92a2a;
}

.btn-add-schedule {
    display: inline-block;
    margin-left: 10px;
    padding: 8px 16px;
    background: #228be6;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
}

.btn-add-schedule:hover {
    background: #1c7ed6;
}

.btn-list {
    display: inline-block;
    padding: 8px 16px;
    background: #868e96;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
}

.btn-list:hover {
    background: #495057;
}
</style>

<div class="local_desc01 local_desc">
    <p>직원별 근무 일정을 달력에서 확인하고 관리할 수 있습니다.</p>
</div>

<div class="calendar-container">
    <div class="calendar-header">
        <div>
            <a href="./staff_schedule_list.php" class="btn-list">목록으로</a>
            <a href="./staff_schedule_form.php" class="btn-add-schedule">+ 근무일정 추가</a>
        </div>
        <div class="calendar-nav">
            <button onclick="changeMonth(-1)">◀</button>
            <select id="year-select" onchange="changeYearMonth()">
                <?php
                for ($y = $current_year - 5; $y <= $current_year + 1; $y++) {
                    $selected = ($y == $current_year) ? 'selected' : '';
                    echo "<option value='{$y}' {$selected}>{$y}년</option>";
                }
                ?>
            </select>
            <select id="month-select" onchange="changeYearMonth()">
                <?php
                for ($m = 1; $m <= 12; $m++) {
                    $selected = ($m == $current_month) ? 'selected' : '';
                    echo "<option value='{$m}' {$selected}>{$m}월</option>";
                }
                ?>
            </select>
            <button onclick="changeMonth(1)">▶</button>
        </div>
    </div>

    <div class="calendar-grid">
        <!-- 요일 헤더 -->
        <div class="calendar-day-header sunday">일</div>
        <div class="calendar-day-header">월</div>
        <div class="calendar-day-header">화</div>
        <div class="calendar-day-header">수</div>
        <div class="calendar-day-header">목</div>
        <div class="calendar-day-header">금</div>
        <div class="calendar-day-header saturday">토</div>

        <?php
        // 첫 주의 빈 칸
        for ($i = 0; $i < $first_weekday; $i++) {
            echo '<div class="calendar-cell other-month"></div>';
        }

        // 날짜 출력
        for ($day = 1; $day <= $days_in_month; $day++) {
            $date = sprintf('%04d-%02d-%02d', $current_year, $current_month, $day);
            $timestamp = mktime(0, 0, 0, $current_month, $day, $current_year);
            $weekday = date('w', $timestamp);
            
            // 요일별 클래스
            $day_class = 'weekday';
            if ($weekday == 0) $day_class = 'sunday';
            elseif ($weekday == 6) $day_class = 'saturday';
            
            echo '<div class="calendar-cell" data-date="'.$date.'">';
            echo '<button class="btn-add-schedule-cell" onclick="addScheduleForDate(\''.$date.'\')" title="이 날짜에 근무일정 추가">+</button>';
            echo '<div class="calendar-date '.$day_class.'">'.$day.'</div>';
            
            // 해당 날짜의 스케줄 출력
            if (isset($schedules[$date])) {
                echo '<div class="schedule-list">';
                foreach ($schedules[$date] as $schedule) {
                    $start_time = substr($schedule['start_time'], 0, 5);
                    $end_time = substr($schedule['end_time'], 0, 5);
                    echo '<div class="schedule-label" data-schedule-id="'.$schedule['schedule_id'].'">';
                    echo '<span class="schedule-info" onclick="editSchedule('.$schedule['schedule_id'].')">';
                    echo htmlspecialchars($schedule['staff_name']).' ('.$start_time.'~'.$end_time.')';
                    echo '</span>';
                    echo '<span class="schedule-delete" onclick="deleteSchedule(event, '.$schedule['schedule_id'].')">✕</span>';
                    echo '</div>';
                }
                echo '</div>';
            }
            
            echo '</div>';
        }

        // 마지막 주의 빈 칸
        $last_weekday = date('w', $last_day);
        for ($i = $last_weekday + 1; $i < 7; $i++) {
            echo '<div class="calendar-cell other-month"></div>';
        }
        ?>
    </div>
</div>

<input type="hidden" id="current-year" value="<?php echo $current_year; ?>">
<input type="hidden" id="current-month" value="<?php echo $current_month; ?>">

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

