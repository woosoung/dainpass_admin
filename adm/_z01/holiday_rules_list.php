<?php
$sub_menu = "930800";
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
            $g5['title'] = '정기휴무규칙';
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
                $g5['title'] = '정기휴무규칙';
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
    $g5['title'] = '정기휴무규칙';
    include_once(G5_ADMIN_PATH.'/admin.head.php');
    echo '<div class="local_desc01 local_desc text-center py-[200px]">';
    echo '<p>접속할 수 없는 페이지 입니다.</p>';
    echo '</div>';
    include_once(G5_ADMIN_PATH.'/admin.tail.php');
    exit;
}

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

// 페이징 설정
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = $page > 0 ? $page : 1;
$rows_per_page = 30;
$offset = ($page - 1) * $rows_per_page;

// 검색 조건
$sst = isset($_GET['sst']) ? clean_xss_tags($_GET['sst']) : 'holiday_rule_id';
$sod = isset($_GET['sod']) ? clean_xss_tags($_GET['sod']) : 'desc';
$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$sfl2 = isset($_GET['sfl2']) ? clean_xss_tags($_GET['sfl2']) : ''; // holiday_type 필터

$where_sql = " WHERE shop_id = {$shop_id} ";

if ($sfl && $stx) {
    switch ($sfl) {
        case 'description':
            $where_sql .= " AND description LIKE '%{$stx}%' ";
            break;
        case 'weekday':
            // 요일 이름으로 검색 (예: "화요일" -> 2)
            $weekday_num = null;
            $stx_trimmed = trim($stx);
            
            // 숫자로 입력된 경우
            if (is_numeric($stx_trimmed) && $stx_trimmed >= 0 && $stx_trimmed <= 6) {
                $weekday_num = (int)$stx_trimmed;
            } else {
                // 요일 이름으로 검색
                foreach ($weekdays as $num => $name) {
                    if (strpos($name, $stx_trimmed) !== false || strpos($stx_trimmed, $name) !== false) {
                        $weekday_num = $num;
                        break;
                    }
                }
            }
            
            if ($weekday_num !== null) {
                $where_sql .= " AND weekday = {$weekday_num} ";
            } else {
                // 매칭되는 요일이 없으면 결과 없음
                $where_sql .= " AND 1=0 ";
            }
            break;
    }
}

if ($sfl2) {
    $where_sql .= " AND holiday_type = '{$sfl2}' ";
}

// 전체 레코드 수
$count_sql = " SELECT COUNT(*) as cnt FROM holiday_rules {$where_sql} ";
$count_row = sql_fetch_pg($count_sql);
$total_count = $count_row['cnt'];

// 페이징 계산
$total_page = ceil($total_count / $rows_per_page);

// 목록 조회
$sql = " SELECT * FROM holiday_rules 
         {$where_sql} 
         ORDER BY {$sst} {$sod} 
         LIMIT {$rows_per_page} OFFSET {$offset} ";
$result = sql_query_pg($sql);

// qstr 생성
$qstr = "page={$page}&sst={$sst}&sod={$sod}&sfl={$sfl}&stx=".urlencode($stx)."&sfl2={$sfl2}";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '정기휴무규칙';
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
        <label for="sfl2">휴무유형</label>
        <select name="sfl2" id="sfl2" class="frm_input">
            <option value="">전체</option>
            <option value="weekly"<?php echo $sfl2 == 'weekly' ? ' selected' : '' ?>>매주</option>
            <option value="monthly"<?php echo $sfl2 == 'monthly' ? ' selected' : '' ?>>매월</option>
        </select>
    </div>
    
    <div>
        <label for="sfl" class="sound_only">검색대상</label>
        <select name="sfl" id="sfl" class="frm_input">
            <option value="">선택</option>
            <option value="description"<?php echo $sfl == 'description' ? ' selected' : '' ?>>설명</option>
            <option value="weekday"<?php echo $sfl == 'weekday' ? ' selected' : '' ?>>요일</option>
        </select>
        <label for="stx" class="sound_only">검색어</label>
        <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
        <input type="submit" value="검색" class="btn_submit">
    </div>
</div>
</form>

<div class="local_desc01 local_desc">
    <p>
        가맹점의 정기휴무 규칙을 관리합니다.<br>
        <strong>가맹점: <?php echo get_text($shop_display_name); ?></strong>
    </p>
</div>

<form name="flist" id="flist" action="./holiday_rules_list_update.php" method="post" onsubmit="return flist_submit(this);">
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
        <col style="width: 100px;">
        <col style="width: 100px;">
        <col style="width: 100px;">
        <col style="width: 100px;">
        <col>
        <col style="width: 120px;">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">규칙ID</th>
        <th scope="col">휴무유형</th>
        <th scope="col">요일</th>
        <th scope="col">주차</th>
        <th scope="col">설명</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $num = $total_count - ($page - 1) * $rows_per_page;
    if ($result && is_object($result) && isset($result->result)) {
        for ($i=0; $row=sql_fetch_array_pg($result->result); $i++) {
            $holiday_rule_id = $row['holiday_rule_id'];
            $holiday_type = $row['holiday_type'];
            $weekday = isset($row['weekday']) ? (int)$row['weekday'] : null;
            $week_of_month = isset($row['week_of_month']) ? (int)$row['week_of_month'] : null;
            $description = $row['description'];
            
            $holiday_type_text = ($holiday_type == 'weekly') ? '매주' : '매월';
            $weekday_text = isset($weekdays[$weekday]) ? $weekdays[$weekday] : '-';
            $week_of_month_text = $week_of_month ? $week_of_month . '째 주' : '-';
    ?>
    <tr>
        <td class="td_chk">
            <input type="checkbox" name="chk[]" value="<?php echo $holiday_rule_id ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_num"><?php echo $holiday_rule_id ?></td>
        <td class="td_left"><?php echo $holiday_type_text ?></td>
        <td class="td_left"><?php echo $weekday_text ?></td>
        <td class="td_left"><?php echo $week_of_month_text ?></td>
        <td class="td_left"><?php echo htmlspecialchars($description) ?></td>
        <td class="td_mng">
            <a href="javascript:void(0);" onclick="editRule(<?php echo $holiday_rule_id; ?>, '<?php echo $holiday_type; ?>', <?php echo $weekday !== null ? $weekday : 'null'; ?>, <?php echo $week_of_month !== null ? $week_of_month : 'null'; ?>, '<?php echo addslashes($description); ?>');" class="btn btn_03">수정</a>
        </td>
    </tr>
    <?php
            $num--;
        }
    }
    
    if ($i == 0) {
        echo '<tr><td colspan="7" class="td_empty">등록된 정기휴무 규칙이 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top btn_confirm">
    <button type="button" onclick="flist_delete_submit();" class="btn btn_02">선택삭제</button>
    <button type="button" onclick="addRule();" class="btn btn_01">신규등록</button>
</div>

</form>

<?php
// 페이징
$write_pages = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, './holiday_rules_list.php?'.$qstr.'&page=');
echo $write_pages;
?>

<style>
#ruleModal {
    position: fixed;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: none;
}
#ruleModal .modal_wrap {
    display: table;
    width: 100%;
    height: 100%;
}
#ruleModal .modal_content {
    position: relative;
    display: table-cell;
    width: 100%;
    height: 100%;
    vertical-align: middle;
    text-align: center;
    padding: 0 20px;
}
#ruleModal .modal_bg {
    position: absolute;
    z-index: 0;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    cursor: pointer;
}
#ruleModal .modal_box {
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
#ruleModal .modal_box h2 {
    font-size: 1.3em;
    padding: 0 0 15px 0;
    margin: 0 0 15px 0;
    border-bottom: 1px solid #ddd;
}
#ruleModal .btn_confirm {
    text-align: center;
    padding: 15px 0 0 0;
    margin-top: 15px;
    border-top: 1px solid #ddd;
}
#ruleModal .btn_confirm button {
    margin: 0 5px;
}
</style>

<!-- 추가/수정 모달 -->
<div id="ruleModal">
    <div class="modal_wrap">
        <div class="modal_content">
            <div class="modal_bg" onclick="closeModal();"></div>
            <div class="modal_box">
                <h2 id="modalTitle">정기휴무 규칙 등록</h2>
            <form name="frmRule" id="frmRule">
                <input type="hidden" name="action" id="action" value="add">
                <input type="hidden" name="shop_id" id="modal_shop_id" value="<?php echo $shop_id; ?>">
                <input type="hidden" name="holiday_rule_id" id="modal_holiday_rule_id" value="">
                
                <div class="tbl_frm01 tbl_wrap">
                    <table>
                    <colgroup>
                        <col class="grid_4">
                        <col>
                    </colgroup>
                    <tbody>
                    <tr>
                        <th scope="row"><label for="modal_holiday_type">휴무유형<strong class="sound_only">필수</strong></label></th>
                        <td>
                            <select name="holiday_type" id="modal_holiday_type" class="frm_input required" required onchange="toggleWeekOfMonth();">
                                <option value="">선택하세요</option>
                                <option value="weekly">매주</option>
                                <option value="monthly">매월</option>
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
                        <th scope="row"><label for="modal_week_of_month">주차</label></th>
                        <td>
                            <select name="week_of_month" id="modal_week_of_month" class="frm_input" disabled>
                                <option value="">::주차선택::</option>
                                <option value="1">1째 주</option>
                                <option value="2">2째 주</option>
                                <option value="3">3째 주</option>
                                <option value="4">4째 주</option>
                                <option value="5">5째 주</option>
                                <option value="6">6째 주</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="modal_description">설명</label></th>
                        <td>
                            <textarea name="description" id="modal_description" class="frm_input" rows="3" style="width:100%;"></textarea>
                        </td>
                    </tr>
                    </tbody>
                    </table>
                </div>
                
                <div class="btn_confirm">
                    <button type="button" onclick="saveRule();" class="btn_submit btn">저장</button>
                    <button type="button" onclick="closeModal();" class="btn_cancel btn btn_02">취소</button>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>

<?php
include_once('./js/holiday_rules_list.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>

