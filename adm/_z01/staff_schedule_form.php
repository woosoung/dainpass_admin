<?php
$sub_menu = "930500";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

// 검색 조건 및 페이징 정보
$ser_staff_id = isset($_REQUEST['ser_staff_id']) ? (int)$_REQUEST['ser_staff_id'] : 0;
$ser_date_from = isset($_REQUEST['ser_date_from']) ? trim($_REQUEST['ser_date_from']) : '';
$ser_date_to = isset($_REQUEST['ser_date_to']) ? trim($_REQUEST['ser_date_to']) : '';
$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;

// 입력값 검증
if ($ser_staff_id < 0) $ser_staff_id = 0;
if ($page < 1) $page = 1;

// 날짜 형식 검증
if ($ser_date_from && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $ser_date_from)) {
    $ser_date_from = '';
}
if ($ser_date_to && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $ser_date_to)) {
    $ser_date_to = '';
}

$qstr = '';
if ($ser_staff_id) $qstr .= '&ser_staff_id='.(int)$ser_staff_id;
if ($ser_date_from) $qstr .= '&ser_date_from='.urlencode($ser_date_from);
if ($ser_date_to) $qstr .= '&ser_date_to='.urlencode($ser_date_to);
if ($page > 1) $qstr .= '&page='.(int)$page;

// w 값에 따른 처리 (추가/수정)
$w = isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '';
$schedule_id = isset($_REQUEST['schedule_id']) ? (int)$_REQUEST['schedule_id'] : 0;

// w 값 검증 (빈 문자열 또는 'u'만 허용)
if ($w !== '' && $w !== 'u') {
    alert('잘못된 접근입니다.');
}

// schedule_id 검증
if ($schedule_id < 0) $schedule_id = 0;

// 직원 목록 가져오기
$staff_sql = "
    SELECT staff_id, name, phone, specialty, title
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
        // 날짜 유효성 추가 검증
        $date_parts = explode('-', $work_date_param);
        if (checkdate((int)$date_parts[1], (int)$date_parts[2], (int)$date_parts[0])) {
            $schedule['work_date'] = $work_date_param;
        } else {
            $schedule['work_date'] = date('Y-m-d');
        }
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
        INNER JOIN staff s ON ss.staff_id = s.staff_id
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
    <?php echo get_shop_display_name($shop_info, $shop_id, ''); ?>
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
                <option value="<?php echo $staff['staff_id']; ?>"
                    <?php echo ($schedule['staff_id'] == $staff['staff_id']) ? 'selected' : ''; ?>>
                    <?php
                    echo htmlspecialchars($staff['name'], ENT_QUOTES, 'UTF-8');
                    if ($staff['title']) {
                        echo ' ('.htmlspecialchars($staff['title'], ENT_QUOTES, 'UTF-8').')';
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
            <input type="date" name="work_date" value="<?php echo htmlspecialchars($schedule['work_date'], ENT_QUOTES, 'UTF-8'); ?>" id="work_date" class="frm_input required" required style="width:200px;">
        </td>
        <th scope="row">요일</th>
        <td>
            <span id="work_day_of_week" class="text-gray-600"></span>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="start_time">근무 시작 시간<strong class="sound_only">필수</strong></label></th>
        <td>
            <input type="time" name="start_time" value="<?php echo htmlspecialchars($schedule['start_time'], ENT_QUOTES, 'UTF-8'); ?>" id="start_time" class="frm_input required" required style="width:150px;">
        </td>
        <th scope="row"><label for="end_time">근무 종료 시간<strong class="sound_only">필수</strong></label></th>
        <td>
            <input type="time" name="end_time" value="<?php echo htmlspecialchars($schedule['end_time'], ENT_QUOTES, 'UTF-8'); ?>" id="end_time" class="frm_input required" required style="width:150px;">
        </td>
    </tr>
    <tr>
        <th scope="row">근무 시간</th>
        <td colspan="3">
            <span id="work_duration" class="font-bold text-blue-600"></span>
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

