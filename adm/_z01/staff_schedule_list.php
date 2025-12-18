<?php
$sub_menu = "930500";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'r');

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
            $shop_sql = " SELECT shop_id, status FROM {$g5['shop_table']} WHERE shop_id = {$shop_id_check} ";
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

// 검색 조건
$ser_staff_id = isset($_GET['ser_staff_id']) ? (int)$_GET['ser_staff_id'] : 0;
$ser_date_from = isset($_GET['ser_date_from']) ? trim($_GET['ser_date_from']) : date('Y-m-01');
$ser_date_to = isset($_GET['ser_date_to']) ? trim($_GET['ser_date_to']) : date('Y-m-t');

// 페이징 설정
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rows_per_page = 20;
$offset = ($page - 1) * $rows_per_page;

// 검색 쿼리 조건
$where_conditions = array();
$where_conditions[] = "s.store_id = {$shop_id}";

if ($ser_staff_id) {
    $where_conditions[] = "s.staff_id = {$ser_staff_id}";
}

if ($ser_date_from) {
    $where_conditions[] = "ss.work_date >= '{$ser_date_from}'";
}

if ($ser_date_to) {
    $where_conditions[] = "ss.work_date <= '{$ser_date_to}'";
}

$where_sql = implode(' AND ', $where_conditions);

// 전체 개수
$count_sql = "
    SELECT COUNT(*) as cnt
    FROM staff_schedules ss
    INNER JOIN staff s ON ss.staff_id = s.staff_id
    WHERE {$where_sql}
";

$count_result = sql_query_pg($count_sql);
$total_count = 0;
if ($count_result && is_object($count_result) && isset($count_result->result)) {
    $count_row = sql_fetch_array_pg($count_result->result);
    $total_count = (int)$count_row['cnt'];
}

$total_page = ($total_count > 0) ? ceil($total_count / $rows_per_page) : 1;

// 목록 조회
$list_sql = "
    SELECT 
        ss.schedule_id,
        ss.staff_id,
        ss.work_date,
        ss.start_time,
        ss.end_time,
        ss.created_at,
        s.name as staff_name,
        s.phone as staff_phone,
        s.staff_id
    FROM staff_schedules ss
    INNER JOIN staff s ON ss.staff_id = s.staff_id
    WHERE {$where_sql}
    ORDER BY ss.work_date DESC, ss.start_time DESC
    LIMIT {$rows_per_page} OFFSET {$offset}
";

$list_result = sql_query_pg($list_sql);
$list = array();

if ($list_result && is_object($list_result) && isset($list_result->result)) {
    while ($row = sql_fetch_array_pg($list_result->result)) {
        $list[] = $row;
    }
}

// 검색용 직원 목록
$staff_sql = "
    SELECT staff_id, name 
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

// 페이징용 쿼리스트링 (page 제외)
$qstr_paging = '';
if ($ser_staff_id) $qstr_paging .= '&ser_staff_id='.$ser_staff_id;
if ($ser_date_from) $qstr_paging .= '&ser_date_from='.$ser_date_from;
if ($ser_date_to) $qstr_paging .= '&ser_date_to='.$ser_date_to;

// 전체 쿼리스트링 (page 포함)
$qstr = "page={$page}";
if ($ser_staff_id) $qstr .= '&ser_staff_id='.$ser_staff_id;
if ($ser_date_from) $qstr .= '&ser_date_from='.$ser_date_from;
if ($ser_date_to) $qstr .= '&ser_date_to='.$ser_date_to;

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '직원별 근무시간 관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
include_once('./js/staff_schedule_list.js.php');
?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">전체 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
</div>

<!-- 검색 폼 -->
<form name="fsearch" id="fsearch" method="get" action="./staff_schedule_list.php">
<div class="local_sch01 local_sch">
    <label for="ser_staff_id" class="sound_only">직원 선택</label>
    <select name="ser_staff_id" id="ser_staff_id" class="frm_input" style="width:200px;">
        <option value="">전체 직원</option>
        <?php foreach ($staff_list as $staff): ?>
        <option value="<?php echo $staff['staff_id']; ?>" <?php echo ($ser_staff_id == $staff['staff_id']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($staff['name']); ?>
        </option>
        <?php endforeach; ?>
    </select>
    
    <label for="ser_date_from" class="sound_only">근무 시작일</label>
    <input type="date" name="ser_date_from" value="<?php echo $ser_date_from; ?>" id="ser_date_from" class="frm_input" style="width:150px;">
    
    <span>~</span>
    
    <label for="ser_date_to" class="sound_only">근무 종료일</label>
    <input type="date" name="ser_date_to" value="<?php echo $ser_date_to; ?>" id="ser_date_to" class="frm_input" style="width:150px;">
    
    <button type="submit" class="btn_submit">검색</button>
    <a href="./staff_schedule_list.php" class="btn_frmline">초기화</a>
</div>
</form>

<div class="local_desc01 local_desc">
    <p>직원별 근무 일정을 목록으로 확인하고 관리할 수 있습니다.</p>
</div>

<!-- 목록 -->
<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <colgroup>
        <col style="width:60px;">
        <col style="width:100px;">
        <col style="width:150px;">
        <col style="width:120px;">
        <col style="width:180px;">
        <col style="width:150px;">
        <col style="width:120px;">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">번호</th>
        <th scope="col">직원ID</th>
        <th scope="col">직원명</th>
        <th scope="col">근무날짜</th>
        <th scope="col">근무시간</th>
        <th scope="col">등록일시</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (count($list) > 0) {
        $num = $total_count - ($page - 1) * $rows_per_page;
        foreach ($list as $row) {
            $start_time = substr($row['start_time'], 0, 5);
            $end_time = substr($row['end_time'], 0, 5);
            $created_at = substr($row['created_at'], 0, 16);
            
            // 근무시간 계산 (분)
            $start_timestamp = strtotime($row['start_time']);
            $end_timestamp = strtotime($row['end_time']);
            $work_minutes = ($end_timestamp - $start_timestamp) / 60;
            $work_hours = floor($work_minutes / 60);
            $work_mins = $work_minutes % 60;
            $work_time_text = $work_hours.'시간';
            if ($work_mins > 0) {
                $work_time_text .= ' '.$work_mins.'분';
            }
            
            echo '<tr>';
            echo '<td class="td_num">'.$num.'</td>';
            echo '<td class="td_center">'.$row['staff_id'].'</td>';
            echo '<td class="td_left">'.htmlspecialchars($row['staff_name']).'</td>';
            echo '<td class="td_center">'.$row['work_date'].'</td>';
            echo '<td class="td_center">'.$start_time.' ~ '.$end_time.' ('.$work_time_text.')</td>';
            echo '<td class="td_center">'.$created_at.'</td>';
            echo '<td class="td_mng">';
            echo '<a href="./staff_schedule_form.php?w=u&schedule_id='.$row['schedule_id'].'&'.$qstr.'" class="btn btn_03">수정</a> ';
            echo '<a href="javascript:deleteSchedule('.$row['schedule_id'].');" class="btn btn_02">삭제</a>';
            echo '</td>';
            echo '</tr>';
            
            $num--;
        }
    } else {
        echo '<tr><td colspan="7" class="empty_table">등록된 근무일정이 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top btn_confirm">
    <a href="./staff_schedule_calendar.php" class="btn btn_02">달력보기</a>
    <a href="./staff_schedule_form.php" class="btn btn_01">근무일정 추가</a>
</div>

<!-- 페이징 -->
<?php
$write_pages = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, './staff_schedule_list.php?'.ltrim($qstr_paging, '&').'&page=');
echo $write_pages;
?>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

