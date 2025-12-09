<?php
$sub_menu = "930900";
include_once('./_common.php');

// 가맹점측 관리자 접근 권한 체크
$has_access = false;
$shop_id = 0;
$shop_info = null;

if ($is_member && $member['mb_id']) {
    // MySQL에서 회원 정보 확인
    // 플랫폼 관리자(mb_level >= 6)는 mb_2 = 'N'일 수 있으므로 mb_2 조건을 다르게 적용
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
            // 플랫폼 관리자는 shop_id = 0에 해당하는 레코드가 없으므로 '업체 데이터가 없습니다.' 표시
            $g5['title'] = '특별휴무/영업 (달력)';
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
            $shop_sql = " SELECT shop_id, shop_name, name, status 
                         FROM {$g5['shop_table']} 
                         WHERE shop_id = {$shop_id_check} ";
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
                $g5['title'] = '특별휴무/영업 (달력)';
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
    $g5['title'] = '특별휴무/영업 (달력)';
    include_once(G5_ADMIN_PATH.'/admin.head.php');
    echo '<div class="local_desc01 local_desc text-center py-[200px]">';
    echo '<p>접속할 수 없는 페이지 입니다.</p>';
    echo '</div>';
    include_once(G5_ADMIN_PATH.'/admin.tail.php');
    exit;
}

@auth_check($auth[$sub_menu], 'r');

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

// 해당 월의 모든 예외일 가져오기
$start_date = date('Y-m-01', $first_day);
$end_date = date('Y-m-t', $first_day);

$exceptions_sql = "
    SELECT 
        shop_id,
        date,
        is_open,
        open_time,
        close_time,
        reason
    FROM business_exceptions
    WHERE shop_id = {$shop_id}
    AND date >= '{$start_date}'
    AND date <= '{$end_date}'
    ORDER BY date
";

$exceptions_result = sql_query_pg($exceptions_sql);
$exceptions = array();

if ($exceptions_result && is_object($exceptions_result) && isset($exceptions_result->result)) {
    while ($row = sql_fetch_array_pg($exceptions_result->result)) {
        $date_key = $row['date'];
        // PostgreSQL boolean 값 처리
        $is_open = isset($row['is_open']) && ($row['is_open'] == 't' || $row['is_open'] === true || $row['is_open'] == '1' || $row['is_open'] === 'true');
        $row['is_open_bool'] = $is_open;
        $exceptions[$date_key] = $row;
    }
}

$g5['title'] = '특별휴무/영업 (달력)';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
include_once('./js/shop_business_exceptions_calendar.js.php');

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name'] ? $shop_info['shop_name'] : (isset($shop_info['name']) ? $shop_info['name'] : 'ID: ' . $shop_id);
?>

<style>
.calendar-container {
    width: 100%;
    margin: 0 auto;
    padding: 0px;
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

.btn-add-exception-cell {
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

.calendar-cell:hover .btn-add-exception-cell {
    opacity: 1;
}

.btn-add-exception-cell:hover {
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

.exception-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
    margin-bottom: 4px;
}

.exception-label.open {
    background: #d3f9d8;
    border: 1px solid #51cf66;
}

.exception-label.close {
    background: #ffe3e3;
    border: 1px solid #ff6b6b;
}

.exception-label:hover {
    transform: translateX(2px);
}

.exception-info {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.exception-delete {
    color: #fa5252;
    cursor: pointer;
    padding: 0 4px;
    margin-left: 4px;
    font-weight: bold;
}

.exception-delete:hover {
    color: #c92a2a;
}

.btn-add-exception {
    display: inline-block;
    margin-left: 10px;
    padding: 8px 16px;
    background: #228be6;
    color: white !important;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
}

.btn-add-exception:hover {
    background: #1c7ed6;
}

.btn-list {
    display: inline-block;
    padding: 8px 16px;
    background: #868e96;
    color: white !important;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
}

.btn-list:hover {
    background: #495057;
}
</style>

<div class="local_desc01 local_desc">
    <p>
        가맹점의 특별휴무/영업일을 달력에서 확인하고 관리할 수 있습니다.<br>
        <strong>가맹점: <?php echo get_text($shop_display_name); ?></strong>
    </p>
</div>

<div class="calendar-container">
    <div class="calendar-header">
        <div>
            <a href="./shop_business_exceptions_list.php" class="btn-list">목록으로</a>
            <a href="javascript:void(0);" onclick="addException();" class="btn-add-exception">신규등록</a>
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
            echo '<button class="btn-add-exception-cell" onclick="addExceptionForDate(\''.$date.'\')" title="이 날짜에 특별휴무/영업일 추가">+</button>';
            echo '<div class="calendar-date '.$day_class.'">'.$day.'</div>';
            
            // 해당 날짜의 예외일 출력
            if (isset($exceptions[$date])) {
                $exception = $exceptions[$date];
                $is_open = $exception['is_open_bool'];
                $class_name = $is_open ? 'open' : 'close';
                $status_text = $is_open ? '영업' : '휴무';
                $time_text = '';
                
                if ($is_open && $exception['open_time'] && $exception['close_time']) {
                    $open_time = substr($exception['open_time'], 0, 5);
                    $close_time = substr($exception['close_time'], 0, 5);
                    $time_text = ' ('.$open_time.'~'.$close_time.')';
                }
                
                $reason_text = $exception['reason'] ? ' - '.htmlspecialchars($exception['reason']) : '';
                
                echo '<div class="exception-label '.$class_name.'" data-date="'.$date.'" onclick="editException(\''.$date.'\')">';
                echo '<span class="exception-info">';
                echo $status_text.$time_text.$reason_text;
                echo '</span>';
                echo '<span class="exception-delete" onclick="deleteException(event, \''.$date.'\')">✕</span>';
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
<input type="hidden" id="shop-id" value="<?php echo $shop_id; ?>">
<input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">

<style>
#exceptionModal {
    position: fixed;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: none;
}
#exceptionModal .modal_wrap {
    display: table;
    width: 100%;
    height: 100%;
}
#exceptionModal .modal_content {
    position: relative;
    display: table-cell;
    width: 100%;
    height: 100%;
    vertical-align: middle;
    text-align: center;
    padding: 0 20px;
}
#exceptionModal .modal_bg {
    position: absolute;
    z-index: 0;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    cursor: pointer;
}
#exceptionModal .modal_box {
    position: relative;
    background: #fff;
    width: 100%;
    text-align: left;
    max-width: 600px;
    display: inline-block;
    padding: 20px;
    border-radius: 5px;
    z-index: 1;
}
#exceptionModal .modal_box h2 {
    font-size: 1.3em;
    padding: 0 0 15px 0;
    margin: 0 0 15px 0;
    border-bottom: 1px solid #ddd;
}
#exceptionModal .btn_confirm {
    text-align: center;
    padding: 15px 0 0 0;
    margin-top: 15px;
    border-top: 1px solid #ddd;
}
#exceptionModal .btn_confirm button {
    margin: 0 5px;
}
</style>

<!-- 추가/수정 모달 -->
<div id="exceptionModal">
    <div class="modal_wrap">
        <div class="modal_content">
            <div class="modal_bg" onclick="closeModal();"></div>
            <div class="modal_box">
                <h2 id="modalTitle">특별휴무/영업일 등록</h2>
            <form name="frmException" id="frmException">
                <input type="hidden" name="action" id="action" value="add">
                <input type="hidden" name="shop_id" id="modal_shop_id" value="<?php echo $shop_id; ?>">
                <input type="hidden" name="original_date" id="modal_original_date" value="">
                
                <div class="tbl_frm01 tbl_wrap">
                    <table>
                    <colgroup>
                        <col class="grid_4">
                        <col>
                    </colgroup>
                    <tbody>
                    <tr>
                        <th scope="row"><label for="modal_date">날짜<strong class="sound_only">필수</strong></label></th>
                        <td>
                            <input type="date" name="date" id="modal_date" class="frm_input required" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="modal_is_open">영업여부<strong class="sound_only">필수</strong></label></th>
                        <td>
                            <select name="is_open" id="modal_is_open" class="frm_input required" required onchange="toggleBusinessHours();">
                                <option value="">선택하세요</option>
                                <option value="true">영업</option>
                                <option value="false">휴무</option>
                            </select>
                        </td>
                    </tr>
                    <tr id="tr_open_time">
                        <th scope="row"><label for="modal_open_time">영업시작시간</label></th>
                        <td>
                            <input type="time" name="open_time" id="modal_open_time" class="frm_input" step="60">
                        </td>
                    </tr>
                    <tr id="tr_close_time">
                        <th scope="row"><label for="modal_close_time">영업종료시간</label></th>
                        <td>
                            <input type="time" name="close_time" id="modal_close_time" class="frm_input" step="60">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="modal_reason">사유</label></th>
                        <td>
                            <textarea name="reason" id="modal_reason" class="frm_input" rows="3" style="width:100%;"></textarea>
                        </td>
                    </tr>
                    </tbody>
                    </table>
                </div>
                
                <div class="btn_confirm">
                    <button type="button" onclick="saveException();" class="btn_submit btn">저장</button>
                    <button type="button" onclick="closeModal();" class="btn_cancel btn btn_02">취소</button>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

