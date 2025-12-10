<?php
$sub_menu = "940300";
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
            $g5['title'] = '이벤트관리';
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
                $g5['title'] = '이벤트관리';
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
    $g5['title'] = '이벤트관리';
    include_once(G5_ADMIN_PATH.'/admin.head.php');
    echo '<div class="local_desc01 local_desc text-center py-[200px]">';
    echo '<p>접속할 수 없는 페이지 입니다.</p>';
    echo '</div>';
    include_once(G5_ADMIN_PATH.'/admin.tail.php');
    exit;
}

@auth_check($auth[$sub_menu], 'w');

// w 파라미터 확인 (u: 수정, 그 외: 신규)
$w = isset($_GET['w']) ? clean_xss_tags($_GET['w']) : '';
$discount_id = isset($_GET['discount_id']) ? (int)$_GET['discount_id'] : 0;

// 수정 모드인 경우 데이터 조회
$discount = array();
if ($w == 'u' && $discount_id > 0) {
    $sql = " SELECT * FROM shop_discounts WHERE discount_id = {$discount_id} AND shop_id = {$shop_id} ";
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
                 WHERE shop_id = {$shop_id} 
                 AND status = 'active' 
                 ORDER BY service_name ";
$services_result = sql_query_pg($services_sql);
$services_list = array();
if ($services_result && is_object($services_result) && isset($services_result->result)) {
    while ($service_row = sql_fetch_array_pg($services_result->result)) {
        $services_list[] = $service_row;
    }
}

// qstr 생성
$qstr = '';
if (isset($_GET['page']) && $_GET['page']) {
    $qstr .= '&page=' . (int)$_GET['page'];
}
if (isset($_GET['sst']) && $_GET['sst']) {
    $qstr .= '&sst=' . urlencode($_GET['sst']);
}
if (isset($_GET['sod']) && $_GET['sod']) {
    $qstr .= '&sod=' . urlencode($_GET['sod']);
}
if (isset($_GET['sfl']) && $_GET['sfl']) {
    $qstr .= '&sfl=' . urlencode($_GET['sfl']);
}
if (isset($_GET['stx']) && $_GET['stx']) {
    $qstr .= '&stx=' . urlencode($_GET['stx']);
}
if (isset($_GET['sfl2']) && $_GET['sfl2']) {
    $qstr .= '&sfl2=' . urlencode($_GET['sfl2']);
}
if (isset($_GET['sfl3']) && $_GET['sfl3']) {
    $qstr .= '&sfl3=' . urlencode($_GET['sfl3']);
}

$html_title = ($w == 'u') ? '수정' : '등록';
$g5['title'] = '이벤트 ' . $html_title;

include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name'] ? $shop_info['shop_name'] : (isset($shop_info['name']) ? $shop_info['name'] : 'ID: ' . $shop_id);
?>

<div class="local_desc01 local_desc">
    <p>이벤트 정보를 <?php echo $html_title; ?>해 주세요.</p>
    <p><strong>가맹점: <?php echo get_text($shop_display_name); ?></strong></p>
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
            <input type="text" name="discount_title" value="<?php echo isset($discount['discount_title']) ? htmlspecialchars($discount['discount_title']) : ''; ?>" id="discount_title" class="frm_input required" required maxlength="255">
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
            <?php echo help("할인 범위를 선택하세요. SHOP(가맹점 전체) 또는 SERVICE(서비스별)"); ?>
            <select name="discount_scope" id="discount_scope" class="frm_input required" required onchange="toggleServiceField();">
                <option value="">선택하세요</option>
                <option value="SHOP"<?php echo (isset($discount['discount_scope']) && $discount['discount_scope'] == 'SHOP') ? ' selected' : ''; ?>>가맹점 전체 (SHOP)</option>
                <option value="SERVICE"<?php echo (isset($discount['discount_scope']) && $discount['discount_scope'] == 'SERVICE') ? ' selected' : ''; ?>>서비스별 (SERVICE)</option>
            </select>
        </td>
        <th scope="row">서비스<strong class="sound_only">선택</strong></th>
        <td>
            <?php echo help("서비스별 할인인 경우 서비스를 선택하세요. 가맹점 전체 할인인 경우 선택하지 않아도 됩니다."); ?>
            <select name="service_id" id="service_id" class="frm_input">
                <option value="">전체 서비스</option>
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
            <small style="color:#999;">서비스별 할인인 경우에만 선택하세요.</small>
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
            <input type="number" name="discount_value" value="<?php echo isset($discount['discount_value']) ? $discount['discount_value'] : ''; ?>" id="discount_value" class="frm_input required" required min="1">
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
