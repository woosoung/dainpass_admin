<?php
$sub_menu = "920800";
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'r');

// 검색 파라미터
$sca = isset($_GET['sca']) ? trim($_GET['sca']) : '0'; // category_id (초기 접근 시 '0')

// 요일 배열
$weekdays = array(
    0 => '일요일',
    1 => '월요일',
    2 => '화요일',
    3 => '수요일',
    4 => '목요일',
    5 => '금요일',
    6 => '토요일'
);

// 모든 업종 목록 가져오기 (0 포함, 계층 구조로 정렬)
$categories = array();
$categories['0'] = '모든 업종 기본';

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

// 영업시간 슬롯 조회
$where = array();

if ($sca !== '' && $sca !== 'all') {
    // 특정 업종 선택
    $sca_escaped = sql_real_escape_string($sca);
    $where[] = "s.category_id = '{$sca_escaped}'";
} else if ($sca === 'all') {
    // 전체 선택 - where 조건 없음 (모든 레코드 표시)
} else {
    // 초기 접근 시 category_id = 0만 표시
    $where[] = "s.category_id = '0'";
}

$where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$sql = " SELECT s.*, 
                COALESCE(c.name, '모든 업종 기본') AS category_name,
                CASE 
                    WHEN s.category_id = '0' THEN '모든 업종 기본'
                    WHEN char_length(s.category_id) = 2 THEN c.name
                    WHEN char_length(s.category_id) = 4 THEN 
                        COALESCE(p.name || ' > ' || c.name, c.name)
                    ELSE c.name
                END AS display_name
         FROM {$g5['default_business_hour_slots_table']} AS s
         LEFT JOIN {$g5['shop_categories_table']} AS c ON s.category_id = c.category_id
         LEFT JOIN {$g5['shop_categories_table']} AS p ON char_length(s.category_id) = 4 AND p.category_id = left(s.category_id, 2)
         {$where_sql}
         ORDER BY s.category_id ASC, s.weekday ASC, s.slot_seq ASC ";

$result = sql_query_pg($sql);
$slots = array();
if ($result && $result->result) {
    while ($row = sql_fetch_array_pg($result->result)) {
        $slots[] = $row;
    }
}

$g5['title'] = '업종별 기본 영업시간 관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
?>

<div class="local_desc01 local_desc">
    <p>
        업종별 기본 영업시간을 관리합니다.<br>
        <!-- - category_id = 0: 모든 업종에 대한 기본 영업시간<br>
        - category_id가 2자리: 1차 업종(분류)의 영업시간<br>
        - category_id가 4자리: 2차 업종(분류)의 영업시간 -->
    </p>
</div>

<form name="fsearch" method="get" class="local_sch01 local_sch">
    <div class="sch_last">
        <label for="sca" class="sound_only">업종 선택</label>
        <select name="sca" id="sca" onchange="this.form.submit();">
            <?php foreach ($categories as $cat_id => $cat_name) { ?>
                <option value="<?php echo $cat_id; ?>" <?php echo ($sca == $cat_id) ? 'selected' : ''; ?>>
                    <?php echo get_text($cat_name); ?>
                </option>
            <?php } ?>
        </select>
    </div>
</form>

<div class="btn_fixed_top">
    <a href="./default_slot_list.php?sca=<?php echo $sca; ?>" class="btn_01 btn">목록</a>
    <button type="button" onclick="addSlot();" class="btn_02 btn">시간대 추가</button>
</div>

<form name="frm" method="post" action="./default_slot_list_update.php" onsubmit="return frm_check(this);">
<input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
<input type="hidden" name="sca" value="<?php echo $sca; ?>">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">업종</th>
        <th scope="col">요일</th>
        <th scope="col">순서</th>
        <th scope="col">시작시간</th>
        <th scope="col">종료시간</th>
        <th scope="col">영업여부</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (empty($slots)) {
        echo '<tr><td colspan="7" class="empty_table">등록된 시간대가 없습니다.</td></tr>';
    } else {
        foreach ($slots as $slot) {
            $category_id = isset($slot['category_id']) ? $slot['category_id'] : '';
            $weekday = isset($slot['weekday']) ? (int)$slot['weekday'] : 0;
            $slot_seq = isset($slot['slot_seq']) ? (int)$slot['slot_seq'] : 0;
            $open_time = isset($slot['open_time']) ? $slot['open_time'] : '';
            $close_time = isset($slot['close_time']) ? $slot['close_time'] : '';
            $is_open = isset($slot['is_open']) && ($slot['is_open'] == 't' || $slot['is_open'] === true || $slot['is_open'] == '1');
            $display_name = isset($slot['display_name']) && $slot['display_name'] ? $slot['display_name'] : '모든 업종 기본';
    ?>
    <tr>
        <td><?php echo get_text($display_name); ?></td>
        <td><?php echo isset($weekdays[$weekday]) ? $weekdays[$weekday] : ''; ?></td>
        <td><?php echo $slot_seq; ?></td>
        <td><?php echo $open_time ? substr($open_time, 0, 5) : ''; ?></td>
        <td><?php echo $close_time ? substr($close_time, 0, 5) : ''; ?></td>
        <td><?php echo $is_open ? '<span class="txt_yes">영업</span>' : '<span class="txt_no">휴무</span>'; ?></td>
        <td class="td_mng">
            <a href="javascript:void(0);" onclick="editSlot('<?php echo addslashes($category_id); ?>', <?php echo $weekday; ?>, <?php echo $slot_seq; ?>, '<?php echo addslashes($open_time); ?>', '<?php echo addslashes($close_time); ?>', <?php echo $is_open ? 'true' : 'false'; ?>);" class="btn btn_03">수정</a>
            <a href="javascript:void(0);" onclick="deleteSlot('<?php echo addslashes($category_id); ?>', <?php echo $weekday; ?>, <?php echo $slot_seq; ?>);" class="btn btn_02">삭제</a>
        </td>
    </tr>
    <?php
        }
    }
    ?>
    </tbody>
    </table>
</div>

<style>
#slotModal {
    position: fixed;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: none;
}
#slotModal .modal_wrap {
    display: table;
    width: 100%;
    height: 100%;
}
#slotModal .modal_content {
    position: relative;
    display: table-cell;
    width: 100%;
    height: 100%;
    vertical-align: middle;
    text-align: center;
    padding: 0 20px;
}
#slotModal .modal_bg {
    position: absolute;
    z-index: 0;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    cursor: pointer;
}
#slotModal .modal_box {
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
#slotModal .modal_box h2 {
    font-size: 1.3em;
    padding: 0 0 15px 0;
    margin: 0 0 15px 0;
    border-bottom: 1px solid #ddd;
}
#slotModal .btn_confirm {
    text-align: center;
    padding: 15px 0 0 0;
    margin-top: 15px;
    border-top: 1px solid #ddd;
}
#slotModal .btn_confirm button {
    margin: 0 5px;
}
</style>

<!-- 추가/수정 모달 -->
<div id="slotModal">
    <div class="modal_wrap">
        <div class="modal_content">
            <div class="modal_bg" onclick="closeModal();"></div>
            <div class="modal_box">
                <h2 id="modalTitle">시간대 추가</h2>
            <form name="frmSlot" id="frmSlot">
                <input type="hidden" name="action" id="action" value="add">
                <input type="hidden" name="old_category_id" id="old_category_id" value="">
                <input type="hidden" name="old_weekday" id="old_weekday" value="">
                <input type="hidden" name="old_slot_seq" id="old_slot_seq" value="">
                
                <div class="tbl_frm01 tbl_wrap">
                    <table>
                    <colgroup>
                        <col class="grid_4">
                        <col>
                    </colgroup>
                    <tbody>
                    <tr>
                        <th scope="row"><label for="modal_category_id">업종<strong class="sound_only">필수</strong></label></th>
                        <td>
                            <select name="category_id" id="modal_category_id" class="frm_input required" required>
                                <option value="">선택하세요</option>
                                <?php foreach ($categories as $cat_id => $cat_name) { ?>
                                    <option value="<?php echo $cat_id; ?>"><?php echo get_text($cat_name); ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="modal_weekday">요일<strong class="sound_only">필수</strong></label></th>
                        <td>
                            <select name="weekday" id="modal_weekday" class="frm_input required" required>
                                <option value="">선택하세요</option>
                                <?php foreach ($weekdays as $wd => $wd_name) { ?>
                                    <option value="<?php echo $wd; ?>"><?php echo $wd_name; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="modal_slot_seq">순서<strong class="sound_only">필수</strong></label></th>
                        <td>
                            <input type="number" name="slot_seq" id="modal_slot_seq" class="frm_input required" min="1" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="modal_open_time">시작시간<strong class="sound_only">필수</strong></label></th>
                        <td>
                            <input type="time" name="open_time" id="modal_open_time" class="frm_input required" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="modal_close_time">종료시간<strong class="sound_only">필수</strong></label></th>
                        <td>
                            <input type="time" name="close_time" id="modal_close_time" class="frm_input required" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="modal_is_open">영업여부<strong class="sound_only">필수</strong></label></th>
                        <td>
                            <input type="radio" name="is_open" id="modal_is_open_y" value="1" checked>
                            <label for="modal_is_open_y">영업</label>
                            &nbsp;&nbsp;
                            <input type="radio" name="is_open" id="modal_is_open_n" value="0">
                            <label for="modal_is_open_n">휴무</label>
                        </td>
                    </tr>
                    </tbody>
                    </table>
                </div>
                
                <div class="btn_confirm">
                    <button type="button" onclick="saveSlot();" class="btn_submit btn">저장</button>
                    <button type="button" onclick="closeModal();" class="btn_cancel btn btn_02">취소</button>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>

</form>

<?php
include_once('./js/default_slot_list.js.php');
?>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>
