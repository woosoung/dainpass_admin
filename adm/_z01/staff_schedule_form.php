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

// 검색 조건 및 페이징 정보
$ser_staff_id = isset($_REQUEST['ser_staff_id']) ? (int)$_REQUEST['ser_staff_id'] : 0;
$ser_date_from = isset($_REQUEST['ser_date_from']) ? trim($_REQUEST['ser_date_from']) : '';
$ser_date_to = isset($_REQUEST['ser_date_to']) ? trim($_REQUEST['ser_date_to']) : '';
$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;

$qstr = '';
if ($ser_staff_id) $qstr .= '&ser_staff_id='.$ser_staff_id;
if ($ser_date_from) $qstr .= '&ser_date_from='.$ser_date_from;
if ($ser_date_to) $qstr .= '&ser_date_to='.$ser_date_to;
if ($page > 1) $qstr .= '&page='.$page;

// w 값에 따른 처리 (추가/수정)
$w = isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '';
$schedule_id = isset($_REQUEST['schedule_id']) ? (int)$_REQUEST['schedule_id'] : 0;

// 직원 목록 가져오기
$staff_sql = "
    SELECT steps_id, name, phone, specialty, title
    FROM staff
    WHERE store_id = {$shop_id}
    ORDER BY name
";

$staff_result = sql_query_pg($staff_sql);
$staff_list = array();

if ($staff_result && is_object($staff_result) && isset($staff_result->result)) {
    while ($row = sql_fetch_array_pg($staff_result->result)) {
        $staff_list[] = $row;
    }
}

if (count($staff_list) == 0) {
    alert('등록된 직원이 없습니다. 먼저 직원을 등록해주세요.', './staff_schedule_list.php');
}

if ($w == '') {
    // 추가 모드
    $html_title = '추가';
    $schedule = array();
    $schedule['staff_id'] = 0;
    
    // URL 파라미터로 work_date가 전달되면 사용, 없으면 오늘 날짜
    $work_date_param = isset($_REQUEST['work_date']) ? trim($_REQUEST['work_date']) : '';
    if ($work_date_param && preg_match('/^\d{4}-\d{2}-\d{2}$/', $work_date_param)) {
        $schedule['work_date'] = $work_date_param;
    } else {
        $schedule['work_date'] = date('Y-m-d');
    }
    
    $schedule['start_time'] = '09:00';
    $schedule['end_time'] = '18:00';
} else if ($w == 'u') {
    // 수정 모드
    $html_title = '수정';
    
    if (!$schedule_id) {
        alert('스케줄 ID가 없습니다.');
    }
    
    // 스케줄 데이터 조회 및 권한 확인
    $schedule_sql = "
        SELECT 
            ss.schedule_id,
            ss.staff_id,
            ss.work_date,
            ss.start_time,
            ss.end_time,
            ss.created_at,
            s.name as staff_name,
            s.store_id
        FROM staff_schedules ss
        INNER JOIN staff s ON ss.staff_id = s.steps_id
        WHERE ss.schedule_id = {$schedule_id}
    ";
    
    $schedule_result = sql_query_pg($schedule_sql);
    $schedule = array();
    
    if ($schedule_result && is_object($schedule_result) && isset($schedule_result->result)) {
        $schedule = sql_fetch_array_pg($schedule_result->result);
    }
    
    if (!$schedule || !isset($schedule['schedule_id'])) {
        alert('존재하지 않는 스케줄입니다.');
    }
    
    // 가맹점 권한 확인
    if ($schedule['store_id'] != $shop_id) {
        alert('접근 권한이 없습니다.');
    }
    
    // 시간 포맷 (HH:MM)
    $schedule['start_time'] = substr($schedule['start_time'], 0, 5);
    $schedule['end_time'] = substr($schedule['end_time'], 0, 5);
} else {
    alert('잘못된 접근입니다.');
}

$g5['title'] = '직원별 근무시간 '.$html_title;

include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
include_once('./js/staff_schedule_form.js.php');
?>

<form name="form01" id="form01" action="./staff_schedule_form_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="schedule_id" value="<?php echo $schedule_id ?>">
<input type="hidden" name="shop_id" value="<?php echo $shop_id ?>">
<input type="hidden" name="ser_staff_id" value="<?php echo $ser_staff_id ?>">
<input type="hidden" name="ser_date_from" value="<?php echo $ser_date_from ?>">
<input type="hidden" name="ser_date_to" value="<?php echo $ser_date_to ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">

<div class="local_desc01 local_desc">
    <p>직원의 근무일정을 등록하거나 수정합니다.</p>
</div>

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col style="width:15%;">
        <col style="width:35%;">
        <col style="width:15%;">
        <col style="width:35%;">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="staff_id">직원 선택<strong class="sound_only">필수</strong></label></th>
        <td colspan="3">
            <select name="staff_id" id="staff_id" class="frm_input required" required style="width:300px;">
                <option value="">직원을 선택하세요</option>
                <?php foreach ($staff_list as $staff): ?>
                <option value="<?php echo $staff['steps_id']; ?>" 
                    <?php echo ($schedule['staff_id'] == $staff['steps_id']) ? 'selected' : ''; ?>>
                    <?php 
                    echo htmlspecialchars($staff['name']);
                    if ($staff['title']) {
                        echo ' ('.htmlspecialchars($staff['title']).')';
                    }
                    ?>
                </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="work_date">근무 날짜<strong class="sound_only">필수</strong></label></th>
        <td>
            <input type="date" name="work_date" value="<?php echo $schedule['work_date']; ?>" id="work_date" class="frm_input required" required style="width:200px;">
        </td>
        <th scope="row">요일</th>
        <td>
            <span id="work_day_of_week" class="text-gray-600"></span>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="start_time">근무 시작 시간<strong class="sound_only">필수</strong></label></th>
        <td>
            <input type="time" name="start_time" value="<?php echo $schedule['start_time']; ?>" id="start_time" class="frm_input required" required style="width:150px;">
        </td>
        <th scope="row"><label for="end_time">근무 종료 시간<strong class="sound_only">필수</strong></label></th>
        <td>
            <input type="time" name="end_time" value="<?php echo $schedule['end_time']; ?>" id="end_time" class="frm_input required" required style="width:150px;">
        </td>
    </tr>
    <tr>
        <th scope="row">근무 시간</th>
        <td colspan="3">
            <span id="work_duration" class="text-blue-600 font-bold"></span>
        </td>
    </tr>
    <?php if ($w == 'u' && isset($schedule['created_at'])): ?>
    <tr>
        <th scope="row">등록일시</th>
        <td colspan="3">
            <?php echo substr($schedule['created_at'], 0, 19); ?>
        </td>
    </tr>
    <?php endif; ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <a href="./staff_schedule_list.php<?php echo $qstr ? '?'.ltrim($qstr, '&') : ''; ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
    <?php if ($w == 'u'): ?>
    <button type="button" onclick="deleteScheduleForm(<?php echo $schedule_id; ?>)" class="btn btn_02">삭제</button>
    <?php endif; ?>
</div>
</form>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

