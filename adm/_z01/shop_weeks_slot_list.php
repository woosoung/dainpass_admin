<?php
$sub_menu = "930700";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

@auth_check($auth[$sub_menu], 'r');

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

// 영업시간 슬롯 조회 (해당 shop_id만)
$sql = " SELECT s.*, 
                COALESCE(sh.shop_name, sh.name, '') AS shop_display_name
         FROM business_hour_slots AS s
         LEFT JOIN {$g5['shop_table']} AS sh ON s.shop_id = sh.shop_id
         WHERE s.shop_id = {$shop_id}
         ORDER BY s.weekday ASC, s.slot_seq ASC ";

$result = sql_query_pg($sql);
$slots = array();
if ($result && $result->result) {
    while ($row = sql_fetch_array_pg($result->result)) {
        $slots[] = $row;
    }
}

$g5['title'] = '가맹점별 요일별 영업시간 관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name'] ? $shop_info['shop_name'] : (isset($shop_info['name']) ? $shop_info['name'] : 'ID: ' . $shop_id);
?>

<div class="local_desc01 local_desc">
    <p>
        가맹점별 요일별 영업시간을 관리합니다.<br>
    </p>
</div>

<div class="btn_fixed_top">
    <a href="./shop_weeks_slot_list.php" class="btn_01 btn">목록</a>
    <button type="button" onclick="addSlot();" class="btn_02 btn">시간대 추가</button>
</div>

<form name="frm" method="post" action="./shop_weeks_slot_list_update.php" onsubmit="return frm_check(this);">
<input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
<input type="hidden" name="shop_id" id="shop_id" value="<?php echo $shop_id; ?>">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
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
        echo '<tr><td colspan="6" class="empty_table">등록된 시간대가 없습니다.</td></tr>';
    } else {
        foreach ($slots as $slot) {
            $slot_shop_id = isset($slot['shop_id']) ? $slot['shop_id'] : '';
            $weekday = isset($slot['weekday']) ? (int)$slot['weekday'] : 0;
            $slot_seq = isset($slot['slot_seq']) ? (int)$slot['slot_seq'] : 0;
            $open_time = isset($slot['open_time']) ? $slot['open_time'] : '';
            $close_time = isset($slot['close_time']) ? $slot['close_time'] : '';
            $is_open = isset($slot['is_open']) && ($slot['is_open'] == 't' || $slot['is_open'] === true || $slot['is_open'] == '1');
    ?>
    <tr>
        <td><?php echo isset($weekdays[$weekday]) ? $weekdays[$weekday] : ''; ?></td>
        <td><?php echo $slot_seq; ?></td>
        <td><?php echo $open_time ? substr($open_time, 0, 5) : ''; ?></td>
        <td><?php echo $close_time ? substr($close_time, 0, 5) : ''; ?></td>
        <td><?php echo $is_open ? '<span class="txt_yes">영업</span>' : '<span class="txt_no">휴무</span>'; ?></td>
        <td class="td_mng">
            <a href="javascript:void(0);" onclick="editSlot(<?php echo $slot_shop_id; ?>, <?php echo $weekday; ?>, <?php echo $slot_seq; ?>, '<?php echo addslashes($open_time); ?>', '<?php echo addslashes($close_time); ?>', <?php echo $is_open ? 'true' : 'false'; ?>);" class="btn btn_03">수정</a>
            <a href="javascript:void(0);" onclick="deleteSlot(<?php echo $slot_shop_id; ?>, <?php echo $weekday; ?>, <?php echo $slot_seq; ?>);" class="btn btn_02">삭제</a>
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
                            <input type="number" name="slot_seq" id="modal_slot_seq" class="frm_input required" min="1" max="99" required>
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
include_once('./js/shop_weeks_slot_list.js.php');
?>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

