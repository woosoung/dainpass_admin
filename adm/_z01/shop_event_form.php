<?php
$sub_menu = "940300";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

@auth_check($auth[$sub_menu], 'w');

// w 파라미터 확인 - 화이트리스트 검증
$allowed_w = array('', 'u');
$w = isset($_GET['w']) ? clean_xss_tags($_GET['w']) : '';
$w = in_array($w, $allowed_w) ? $w : '';

$discount_id = isset($_GET['discount_id']) ? (int)$_GET['discount_id'] : 0;
$discount_id = ($discount_id > 0 && $discount_id <= 2147483647) ? $discount_id : 0;

// 수정 모드인 경우 데이터 조회
$discount = array();
if ($w == 'u' && $discount_id > 0) {
    $sql = " SELECT * FROM shop_discounts WHERE discount_id = " . (int)$discount_id . " AND shop_id = " . (int)$shop_id . " ";
    $discount = sql_fetch_pg($sql);
    
    if (!$discount || !$discount['discount_id']) {
        alert('이벤트 정보를 찾을 수 없습니다.', './shop_event_list.php');
        exit;
    }
    
    // boolean 값 처리
    if (isset($discount['is_active'])) {
        $discount['is_active'] = ($discount['is_active'] == 't' || $discount['is_active'] === true || $discount['is_active'] == '1' || $discount['is_active'] === 'true');
    }
    
    // datetime 값 처리
    if (isset($discount['start_datetime'])) {
        $discount['start_datetime'] = date('Y-m-d\TH:i', strtotime($discount['start_datetime']));
    }
    if (isset($discount['end_datetime']) && $discount['end_datetime']) {
        $discount['end_datetime'] = date('Y-m-d\TH:i', strtotime($discount['end_datetime']));
    }
}

// 활성화된 서비스 목록 조회
$services_sql = " SELECT service_id, service_name
                 FROM shop_services
                 WHERE shop_id = " . (int)$shop_id . "
                 AND status = 'active'
                 ORDER BY service_name ";
$services_result = sql_query_pg($services_sql);
$services_list = array();
if ($services_result && is_object($services_result) && isset($services_result->result)) {
    while ($service_row = sql_fetch_array_pg($services_result->result)) {
        $services_list[] = $service_row;
    }
}

// qstr 생성 - 화이트리스트 검증
$allowed_sst = array('discount_id', 'discount_title', 'discount_scope', 'discount_type', 'start_datetime', 'end_datetime', 'is_active', 'created_at');
$allowed_sod = array('asc', 'desc');
$allowed_sfl = array('', 'discount_title');
$allowed_sfl2 = array('', 'active', 'inactive');
$allowed_sfl3 = array('', 'SHOP', 'SERVICE');

$qstr = '';
if (isset($_GET['page']) && $_GET['page']) {
    $page = (int)$_GET['page'];
    $page = ($page > 0 && $page <= 10000) ? $page : 1;
    $qstr .= '&page=' . $page;
}
if (isset($_GET['sst']) && $_GET['sst']) {
    $sst = clean_xss_tags($_GET['sst']);
    if (in_array($sst, $allowed_sst)) {
        $qstr .= '&sst=' . urlencode($sst);
    }
}
if (isset($_GET['sod']) && $_GET['sod']) {
    $sod = clean_xss_tags($_GET['sod']);
    if (in_array($sod, $allowed_sod)) {
        $qstr .= '&sod=' . urlencode($sod);
    }
}
if (isset($_GET['sfl']) && $_GET['sfl']) {
    $sfl = clean_xss_tags($_GET['sfl']);
    if (in_array($sfl, $allowed_sfl)) {
        $qstr .= '&sfl=' . urlencode($sfl);
    }
}
if (isset($_GET['stx']) && $_GET['stx']) {
    $stx = clean_xss_tags($_GET['stx']);
    $stx = substr($stx, 0, 100);
    $qstr .= '&stx=' . urlencode($stx);
}
if (isset($_GET['sfl2']) && $_GET['sfl2']) {
    $sfl2 = clean_xss_tags($_GET['sfl2']);
    if (in_array($sfl2, $allowed_sfl2)) {
        $qstr .= '&sfl2=' . urlencode($sfl2);
    }
}
if (isset($_GET['sfl3']) && $_GET['sfl3']) {
    $sfl3 = clean_xss_tags($_GET['sfl3']);
    if (in_array($sfl3, $allowed_sfl3)) {
        $qstr .= '&sfl3=' . urlencode($sfl3);
    }
}

$html_title = ($w == 'u') ? '수정' : '등록';
$g5['title'] = '이벤트 ' . $html_title;

include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
?>

<div class="local_desc01 local_desc">
    <p>이벤트 정보를 <?php echo $html_title; ?>해 주세요.</p>
    <?php echo get_shop_display_name($shop_info, $shop_id); ?>
</div>

<form name="form01" id="form01" action="./shop_event_form_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="discount_id" value="<?php echo $discount_id ?>">
<input type="hidden" name="shop_id" value="<?php echo $shop_id; ?>">
<input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
<?php if ($qstr) { ?>
<input type="hidden" name="qstr" value="<?php echo htmlspecialchars($qstr); ?>">
<?php } ?>

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4" style="width:15%;">
        <col style="width:35%;">
        <col class="grid_4" style="width:15%;">
        <col style="width:35%;">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">이벤트제목<strong class="sound_only">필수</strong></th>
        <td>
            <?php echo help("이벤트 할인의 제목을 입력하세요. (예: 신규회원 10% 할인)"); ?>
            <input type="text" name="discount_title" value="<?php echo isset($discount['discount_title']) ? htmlspecialchars($discount['discount_title']) : ''; ?>" id="discount_title" class="frm_input required" required maxlength="100">
        </td>
        <th scope="row">활성화여부<strong class="sound_only">필수</strong></th>
        <td colspan="3">
            <?php echo help("이벤트의 활성화 상태를 선택하세요."); ?>
            <select name="is_active" id="is_active" class="frm_input required" required>
                <option value="1"<?php echo (!isset($discount['is_active']) || $discount['is_active']) ? ' selected' : ''; ?>>활성</option>
                <option value="0"<?php echo (isset($discount['is_active']) && !$discount['is_active']) ? ' selected' : ''; ?>>비활성</option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row">할인범위<strong class="sound_only">필수</strong></th>
        <td>
            <?php echo help("할인을 적용할 범위를 선택하세요."); ?>
            <select name="discount_scope" id="discount_scope" class="frm_input required" required onchange="toggleServiceField();">
                <option value="">선택하세요</option>
                <option value="SHOP"<?php echo (isset($discount['discount_scope']) && $discount['discount_scope'] == 'SHOP') ? ' selected' : ''; ?>>가맹점 전체 서비스에 적용</option>
                <option value="SERVICE"<?php echo (isset($discount['discount_scope']) && $discount['discount_scope'] == 'SERVICE') ? ' selected' : ''; ?>>특정 서비스에만 적용</option>
            </select>
        </td>
        <th scope="row" id="service_header" style="display:none;">적용할 서비스<strong class="sound_only">필수</strong></th>
        <td id="service_cell" style="display:none;">
            <?php echo help("이벤트 할인을 적용할 서비스를 선택하세요."); ?>
            <select name="service_id" id="service_id" class="frm_input">
                <option value="">서비스를 선택하세요</option>
                <?php
                foreach ($services_list as $service) {
                    $selected = '';
                    if (isset($discount['service_id']) && $discount['service_id'] == $service['service_id']) {
                        $selected = ' selected';
                    }
                    echo '<option value="' . $service['service_id'] . '"' . $selected . '>' . htmlspecialchars($service['service_name']) . '</option>';
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row">할인유형<strong class="sound_only">필수</strong></th>
        <td>
            <?php echo help("할인 유형을 선택하세요. PERCENT(백분율 할인) 또는 AMOUNT(정액 할인)"); ?>
            <select name="discount_type" id="discount_type" class="frm_input required" required>
                <option value="">선택하세요</option>
                <option value="PERCENT"<?php echo (isset($discount['discount_type']) && $discount['discount_type'] == 'PERCENT') ? ' selected' : ''; ?>>백분율 할인 (PERCENT)</option>
                <option value="AMOUNT"<?php echo (isset($discount['discount_type']) && $discount['discount_type'] == 'AMOUNT') ? ' selected' : ''; ?>>정액 할인 (AMOUNT)</option>
            </select>
        </td>
        <th scope="row">할인값<strong class="sound_only">필수</strong></th>
        <td>
            <?php echo help("할인 금액 또는 할인율을 입력하세요. (예: 10%, 5000원)"); ?>
            <input type="number" name="discount_value" value="<?php echo isset($discount['discount_value']) ? $discount['discount_value'] : ''; ?>" id="discount_value" class="frm_input required" required min="1" max="1000000000">
            <span id="discount_unit">원</span>
        </td>
    </tr>
    <tr>
        <th scope="row">시작일시<strong class="sound_only">필수</strong></th>
        <td>
            <?php echo help("이벤트 할인의 시작 일시를 선택하세요."); ?>
            <input type="datetime-local" name="start_datetime" value="<?php echo isset($discount['start_datetime']) ? $discount['start_datetime'] : date('Y-m-d\TH:i'); ?>" id="start_datetime" class="frm_input required" required>
        </td>
        <th scope="row">종료일시</th>
        <td>
            <?php echo help("이벤트 할인의 종료 일시를 선택하세요. (선택사항, 비워두면 무기한)"); ?>
            <input type="datetime-local" name="end_datetime" value="<?php echo isset($discount['end_datetime']) ? $discount['end_datetime'] : ''; ?>" id="end_datetime" class="frm_input">
        </td>
    </tr>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <a href="./shop_event_list.php<?php echo $qstr ? '?' . ltrim($qstr, '&') : ''; ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<?php
include_once('./js/shop_event_form.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>
