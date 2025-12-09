<?php
$sub_menu = "920950";
include_once('./_common.php');

// 플랫폼 관리자 접근 권한 체크
$has_access = false;

if ($is_member && $member['mb_id']) {
    $mb_sql = " SELECT mb_id, mb_level, mb_1, mb_2, mb_leave_date, mb_intercept_date 
                FROM {$g5['member_table']} 
                WHERE mb_id = '{$member['mb_id']}' 
                AND mb_level >= 6 
                AND (mb_leave_date = '' OR mb_leave_date IS NULL)
                AND (mb_intercept_date = '' OR mb_intercept_date IS NULL) ";
    $mb_row = sql_fetch($mb_sql, 1);
    
    if ($mb_row && $mb_row['mb_id']) {
        $has_access = true;
    }
}

// 접근 권한이 없으면 메시지 표시
if (!$has_access) {
    $g5['title'] = '업종별 특별휴무/영업일시 (달력)';
    include_once(G5_ADMIN_PATH.'/admin.head.php');
    echo '<div class="local_desc01 local_desc text-center py-[200px]">';
    echo '<p>접속할 수 없는 페이지 입니다.</p>';
    echo '</div>';
    include_once(G5_ADMIN_PATH.'/admin.tail.php');
    exit;
}

@auth_check($auth[$sub_menu], 'r');

// 검색 파라미터
$sca = isset($_GET['sca']) ? trim($_GET['sca']) : '0'; // category_id (초기 접근 시 '0')

// 모든 업종 목록 가져오기 (0 포함, 계층 구조로 정렬)
$categories = array();
$categories['0'] = '업종공통';

// 1차 분류(2자리) 가져오기
$sql_primary = " SELECT category_id, name 
                  FROM {$g5['shop_categories_table']} 
                  WHERE use_yn = 'Y' 
                  AND char_length(category_id) = 2
                  ORDER BY category_id ASC ";
$result_primary = sql_query_pg($sql_primary);

if ($result_primary && $result_primary->result) {
    while ($row = sql_fetch_array_pg($result_primary->result)) {
        $primary_id = isset($row['category_id']) ? $row['category_id'] : '';
        $primary_name = isset($row['name']) ? $row['name'] : '';
        
        if ($primary_id) {
            // 1차 분류 추가
            $categories[$primary_id] = $primary_name;
            
            // 해당 1차 분류의 2차 분류(4자리) 가져오기
            $primary_id_escaped = sql_real_escape_string($primary_id);
            $sql_secondary = " SELECT category_id, name 
                               FROM {$g5['shop_categories_table']} 
                               WHERE use_yn = 'Y' 
                               AND char_length(category_id) = 4
                               AND left(category_id, 2) = '{$primary_id_escaped}'
                               ORDER BY category_id ASC ";
            $result_secondary = sql_query_pg($sql_secondary);
            
            if ($result_secondary && $result_secondary->result) {
                while ($row_sec = sql_fetch_array_pg($result_secondary->result)) {
                    $secondary_id = isset($row_sec['category_id']) ? $row_sec['category_id'] : '';
                    $secondary_name = isset($row_sec['name']) ? $row_sec['name'] : '';
                    
                    if ($secondary_id) {
                        // 2차 분류 추가 (부모명 포함)
                        $categories[$secondary_id] = $primary_name . ' > ' . $secondary_name;
                    }
                }
            }
        }
    }
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

// 해당 월의 모든 예외일 가져오기
$start_date = date('Y-m-01', $first_day);
$end_date = date('Y-m-t', $first_day);

// WHERE 조건 구성
$where = array();
$where[] = "e.date >= '{$start_date}'";
$where[] = "e.date <= '{$end_date}'";

if ($sca !== '' && $sca !== 'all') {
    // 특정 업종 선택
    $sca_escaped = sql_real_escape_string($sca);
    $where[] = "e.category_id = '{$sca_escaped}'";
} else if ($sca === 'all') {
    // 전체 선택 - category_id 조건 없음 (모든 레코드 표시)
} else {
    // 초기 접근 시 category_id = 0만 표시
    $where[] = "e.category_id = '0'";
}

$where_sql = "WHERE " . implode(" AND ", $where);

$exceptions_sql = "
    SELECT 
        e.category_id,
        e.date,
        e.is_open,
        e.open_time,
        e.close_time,
        e.reason,
        COALESCE(c.name, '업종공통') AS category_name,
        CASE 
            WHEN e.category_id = '0' THEN '업종공통'
            WHEN char_length(e.category_id) = 2 THEN c.name
            WHEN char_length(e.category_id) = 4 THEN 
                COALESCE(p.name || ' > ' || c.name, c.name)
            ELSE c.name
        END AS display_name
    FROM default_business_exceptions AS e
    LEFT JOIN {$g5['shop_categories_table']} AS c ON e.category_id = c.category_id
    LEFT JOIN {$g5['shop_categories_table']} AS p ON char_length(e.category_id) = 4 AND p.category_id = left(e.category_id, 2)
    {$where_sql}
    ORDER BY e.date, e.category_id
";
$exceptions_result = sql_query_pg($exceptions_sql);
$exceptions = array();

if ($exceptions_result && is_object($exceptions_result) && isset($exceptions_result->result)) {
    while ($row = sql_fetch_array_pg($exceptions_result->result)) {
        $date_key = $row['date'];
        // PostgreSQL boolean 값 처리
        $is_open = isset($row['is_open']) && ($row['is_open'] == 't' || $row['is_open'] === true || $row['is_open'] == '1' || $row['is_open'] === 'true');
        $row['is_open_bool'] = $is_open;
        $row['display_name'] = isset($row['display_name']) && $row['display_name'] ? $row['display_name'] : '업종공통';
        
        // 같은 날짜에 여러 업종의 예외일이 있을 수 있으므로 배열로 저장
        if (!isset($exceptions[$date_key])) {
            $exceptions[$date_key] = array();
        }
        $exceptions[$date_key][] = $row;
    }
}

$g5['title'] = '업종별 특별휴무/영업일시 (달력)';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
include_once('./js/category_business_exceptions_calendar.js.php');
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
    font-size: 11px;
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

.exception-category {
    font-size: 10px;
    color: #666;
    margin-right: 4px;
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
        업종별 특별휴무/영업일시를 달력에서 확인하고 관리할 수 있습니다.
    </p>
</div>

<div class="local_sch01 local_sch mb-3">
    <form name="fsearch" method="get" class="sch_last">
        <label for="sca" class="sound_only">업종 선택</label>
        <select name="sca" id="sca" onchange="this.form.submit();" class="frm_input">
            <option value="all"<?php echo ($sca == 'all') ? ' selected' : ''; ?>>::전체보기::</option>
            <?php foreach ($categories as $cat_id => $cat_name) { ?>
                <option value="<?php echo $cat_id; ?>" <?php echo ($sca == $cat_id) ? 'selected' : ''; ?>>
                    <?php echo get_text($cat_name); ?>
                </option>
            <?php } ?>
        </select>
        <input type="hidden" name="year" value="<?php echo $current_year; ?>">
        <input type="hidden" name="month" value="<?php echo $current_month; ?>">
    </form>
</div>

<div class="calendar-container">
    <div class="calendar-header">
        <div>
            <a href="./category_business_exceptions_list.php?sca=<?php echo $sca; ?>" class="btn-list">목록으로</a>
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
            echo '<button class="btn-add-exception-cell" onclick="addExceptionForDate(\''.$date.'\')" title="이 날짜에 특별휴무/영업일시 추가">+</button>';
            echo '<div class="calendar-date '.$day_class.'">'.$day.'</div>';
            
            // 해당 날짜의 예외일 출력
            if (isset($exceptions[$date])) {
                foreach ($exceptions[$date] as $exception) {
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
                    $display_name = $exception['display_name'];
                    
                    echo '<div class="exception-label '.$class_name.'" data-category-id="'.htmlspecialchars($exception['category_id']).'" data-date="'.$date.'" onclick="editException(\''.htmlspecialchars($exception['category_id']).'\', \''.$date.'\')">';
                    echo '<span class="exception-info">';
                    echo '<span class="exception-category">['.htmlspecialchars($display_name).']</span>';
                    echo $status_text.$time_text.$reason_text;
                    echo '</span>';
                    echo '<span class="exception-delete" onclick="deleteException(event, \''.htmlspecialchars($exception['category_id']).'\', \''.$date.'\')">✕</span>';
                    echo '</div>';
                }
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
<input type="hidden" id="category-id" value="<?php echo $sca == 'all' ? '' : $sca; ?>">
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
                <h2 id="modalTitle">특별휴무/영업일시 등록</h2>
            <form name="frmException" id="frmException">
                <input type="hidden" name="action" id="action" value="add">
                <input type="hidden" name="original_category_id" id="modal_original_category_id" value="">
                <input type="hidden" name="original_date" id="modal_original_date" value="">
                
                <div class="tbl_frm01 tbl_wrap">
                    <table>
                    <colgroup>
                        <col class="grid_4">
                        <col>
                    </colgroup>
                    <tbody>
                    <tr>
                        <th scope="row"><label for="modal_category_id_select">업종<strong class="sound_only">필수</strong></label></th>
                        <td>
                            <select name="category_id" id="modal_category_id_select" class="frm_input required" required>
                                <option value="">선택하세요</option>
                                <?php foreach ($categories as $cat_id => $cat_name) { ?>
                                    <option value="<?php echo $cat_id; ?>" <?php echo ($sca == $cat_id && $sca != 'all') ? 'selected' : ''; ?>>
                                        <?php echo get_text($cat_name); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
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
