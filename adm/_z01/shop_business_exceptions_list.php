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
            $g5['title'] = '특별휴무/영업';
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
                $g5['title'] = '특별휴무/영업';
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
    $g5['title'] = '특별휴무/영업';
    include_once(G5_ADMIN_PATH.'/admin.head.php');
    echo '<div class="local_desc01 local_desc text-center py-[200px]">';
    echo '<p>접속할 수 없는 페이지 입니다.</p>';
    echo '</div>';
    include_once(G5_ADMIN_PATH.'/admin.tail.php');
    exit;
}

@auth_check($auth[$sub_menu], 'r');

// 페이징 설정
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = $page > 0 ? $page : 1;
$rows_per_page = 30;
$offset = ($page - 1) * $rows_per_page;

// 검색 조건
$sst = isset($_GET['sst']) ? clean_xss_tags($_GET['sst']) : 'date';
$sod = isset($_GET['sod']) ? clean_xss_tags($_GET['sod']) : 'desc';
$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$sfl2 = isset($_GET['sfl2']) ? clean_xss_tags($_GET['sfl2']) : ''; // is_open 필터

// 달력에서 전달된 날짜 파라미터
$add_date = isset($_GET['add_date']) ? clean_xss_tags($_GET['add_date']) : '';
$edit_date = isset($_GET['edit_date']) ? clean_xss_tags($_GET['edit_date']) : '';

// edit_date가 있으면 해당 데이터 가져오기
$edit_exception_data = null;
if ($edit_date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $edit_date)) {
    $edit_sql = " SELECT * FROM business_exceptions 
                  WHERE shop_id = {$shop_id} 
                  AND date = '{$edit_date}' ";
    $edit_result = sql_fetch_pg($edit_sql);
    if ($edit_result) {
        $edit_exception_data = $edit_result;
    }
}

$where_sql = " WHERE shop_id = {$shop_id} ";

if ($sfl && $stx) {
    switch ($sfl) {
        case 'date':
            // 날짜 검색 (YYYY-MM-DD 형식)
            $stx_trimmed = trim($stx);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $stx_trimmed)) {
                $where_sql .= " AND date = '{$stx_trimmed}' ";
            } else {
                // 부분 검색
                $where_sql .= " AND date::text LIKE '%{$stx_trimmed}%' ";
            }
            break;
        case 'reason':
            $where_sql .= " AND reason LIKE '%{$stx}%' ";
            break;
    }
}

if ($sfl2 !== '') {
    $is_open_value = ($sfl2 === 'open' || $sfl2 === '1') ? 'true' : 'false';
    $where_sql .= " AND is_open = {$is_open_value} ";
}

// 전체 레코드 수
$count_sql = " SELECT COUNT(*) as cnt FROM business_exceptions {$where_sql} ";
$count_row = sql_fetch_pg($count_sql);
$total_count = $count_row['cnt'];

// 페이징 계산
$total_page = ceil($total_count / $rows_per_page);

// 목록 조회
$sql = " SELECT * FROM business_exceptions 
         {$where_sql} 
         ORDER BY {$sst} {$sod} 
         LIMIT {$rows_per_page} OFFSET {$offset} ";
$result = sql_query_pg($sql);

// qstr 생성
$qstr = "page={$page}&sst={$sst}&sod={$sod}&sfl={$sfl}&stx=".urlencode($stx)."&sfl2={$sfl2}";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '특별휴무/영업';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name'] ? $shop_info['shop_name'] : (isset($shop_info['name']) ? $shop_info['name'] : 'ID: ' . $shop_id);
?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">전체 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
</div>

<form name="fsearch" id="fsearch" method="get">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">

<div class="local_sch01 local_sch mb-3">
    <div class="mb-2">
        <label for="sfl2">영업여부</label>
        <select name="sfl2" id="sfl2" class="frm_input">
            <option value="">전체</option>
            <option value="open"<?php echo $sfl2 == 'open' ? ' selected' : '' ?>>영업</option>
            <option value="close"<?php echo $sfl2 == 'close' ? ' selected' : '' ?>>휴무</option>
        </select>
    </div>
    
    <div>
        <label for="sfl" class="sound_only">검색대상</label>
        <select name="sfl" id="sfl" class="frm_input" onchange="toggleSearchInput();">
            <option value="">선택</option>
            <option value="date"<?php echo $sfl == 'date' ? ' selected' : '' ?>>날짜</option>
            <option value="reason"<?php echo $sfl == 'reason' ? ' selected' : '' ?>>사유</option>
        </select>
        <label for="stx" class="sound_only">검색어</label>
        <input type="<?php echo $sfl == 'date' ? 'date' : 'text'; ?>" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
        <input type="submit" value="검색" class="btn_submit">
    </div>
</div>
</form>

<script>
function toggleSearchInput() {
    var sfl = document.getElementById('sfl').value;
    var stx = document.getElementById('stx');
    var currentValue = stx.value;
    
    if (sfl === 'date') {
        // 날짜 선택 시 date 타입으로 변경
        stx.type = 'date';
        // 기존 값이 날짜 형식이 아니면 초기화
        if (currentValue && !currentValue.match(/^\d{4}-\d{2}-\d{2}$/)) {
            stx.value = '';
        }
    } else {
        // 다른 옵션 선택 시 text 타입으로 변경
        stx.type = 'text';
    }
}

// 페이지 로드 시 초기 설정
document.addEventListener('DOMContentLoaded', function() {
    toggleSearchInput();
});
</script>

<div class="local_desc01 local_desc">
    <p>
        가맹점의 특별휴무/영업일을 관리합니다.<br>
        <strong>가맹점: <?php echo get_text($shop_display_name); ?></strong>
    </p>
</div>

<form name="flist" id="flist" action="./shop_business_exceptions_list_update.php" method="post" onsubmit="return flist_submit(this);">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sfl2" value="<?php echo $sfl2 ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
<input type="hidden" name="act" value="">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title'] ?> 목록</caption>
    <colgroup>
        <col style="width: 50px;">
        <col style="width: 120px;">
        <col style="width: 100px;">
        <col style="width: 120px;">
        <col style="width: 120px;">
        <col>
        <col style="width: 120px;">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">날짜</th>
        <th scope="col">영업여부</th>
        <th scope="col">영업시작시간</th>
        <th scope="col">영업종료시간</th>
        <th scope="col">사유</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $num = $total_count - ($page - 1) * $rows_per_page;
    if ($result && is_object($result) && isset($result->result)) {
        for ($i=0; $row=sql_fetch_array_pg($result->result); $i++) {
            $exception_date = $row['date'];
            // PostgreSQL boolean 값 처리: 't', true, '1' 등을 모두 고려
            $is_open = isset($row['is_open']) && ($row['is_open'] == 't' || $row['is_open'] === true || $row['is_open'] == '1' || $row['is_open'] === 'true');
            $open_time = $row['open_time'];
            $close_time = $row['close_time'];
            $reason = $row['reason'];
            
            $is_open_text = $is_open ? '영업' : '휴무';
            $open_time_text = $open_time ? substr($open_time, 0, 5) : '-';
            $close_time_text = $close_time ? substr($close_time, 0, 5) : '-';
            $reason_text = $reason ? htmlspecialchars($reason) : '-';
    ?>
    <tr>
        <td class="td_chk">
            <input type="checkbox" name="chk[]" value="<?php echo htmlspecialchars($exception_date) ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_left"><?php echo $exception_date ?></td>
        <td class="td_left"><?php echo $is_open_text ?></td>
        <td class="td_left"><?php echo $open_time_text ?></td>
        <td class="td_left"><?php echo $close_time_text ?></td>
        <td class="td_left"><?php echo $reason_text ?></td>
        <td class="td_mng">
            <a href="javascript:void(0);" onclick="editException('<?php echo $exception_date; ?>', <?php echo $is_open ? 'true' : 'false'; ?>, '<?php echo $open_time ? substr($open_time, 0, 5) : ''; ?>', '<?php echo $close_time ? substr($close_time, 0, 5) : ''; ?>', '<?php echo addslashes($reason ? $reason : ''); ?>');" class="btn btn_03">수정</a>
        </td>
    </tr>
    <?php
            $num--;
        }
    }
    
    if ($i == 0) {
        echo '<tr><td colspan="7" class="td_empty">등록된 특별휴무/영업일이 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top btn_confirm">
    <a href="./shop_business_exceptions_calendar.php" class="btn btn_03">달력보기</a>
    <button type="button" onclick="flist_delete_submit();" class="btn btn_02">선택삭제</button>
    <button type="button" onclick="addException();" class="btn btn_01">신규등록</button>
</div>

</form>

<?php
// 페이징
$write_pages = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, './shop_business_exceptions_list.php?'.$qstr.'&page=');
echo $write_pages;
?>

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
// edit_date 데이터를 JavaScript에 전달
if ($edit_exception_data) {
    $edit_is_open = isset($edit_exception_data['is_open']) && ($edit_exception_data['is_open'] == 't' || $edit_exception_data['is_open'] === true || $edit_exception_data['is_open'] == '1' || $edit_exception_data['is_open'] === 'true');
    $edit_open_time = $edit_exception_data['open_time'] ? substr($edit_exception_data['open_time'], 0, 5) : '';
    $edit_close_time = $edit_exception_data['close_time'] ? substr($edit_exception_data['close_time'], 0, 5) : '';
    $edit_reason = $edit_exception_data['reason'] ? addslashes($edit_exception_data['reason']) : '';
    echo '<script>';
    echo 'var editExceptionData = {';
    echo '    date: "'.$edit_date.'",';
    echo '    is_open: '.($edit_is_open ? 'true' : 'false').',';
    echo '    open_time: "'.$edit_open_time.'",';
    echo '    close_time: "'.$edit_close_time.'",';
    echo '    reason: "'.$edit_reason.'"';
    echo '};';
    echo '</script>';
} else {
    echo '<script>var editExceptionData = null;</script>';
}
if ($add_date) {
    echo '<script>var addDateParam = "'.$add_date.'";</script>';
} else {
    echo '<script>var addDateParam = null;</script>';
}
include_once('./js/shop_business_exceptions_list.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

