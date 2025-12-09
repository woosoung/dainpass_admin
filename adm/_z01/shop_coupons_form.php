<?php
$sub_menu = "940100";
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
            $g5['title'] = '쿠폰관리';
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
                $g5['title'] = '쿠폰관리';
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
    $g5['title'] = '쿠폰관리';
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
$coupon_id = isset($_GET['coupon_id']) ? (int)$_GET['coupon_id'] : 0;

// 수정 모드인 경우 데이터 조회
$coupon = array();
if ($w == 'u' && $coupon_id > 0) {
    $sql = " SELECT * FROM shop_coupons WHERE coupon_id = {$coupon_id} AND shop_id = {$shop_id} ";
    $coupon = sql_fetch_pg($sql);
    
    if (!$coupon || !$coupon['coupon_id']) {
        alert('쿠폰 정보를 찾을 수 없습니다.', './shop_coupons_list.php');
        exit;
    }
    
    // boolean 값 처리
    if (isset($coupon['is_active'])) {
        $coupon['is_active'] = ($coupon['is_active'] == 't' || $coupon['is_active'] === true || $coupon['is_active'] == '1' || $coupon['is_active'] === 'true');
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

$html_title = ($w == 'u') ? '수정' : '등록';
$g5['title'] = '쿠폰 ' . $html_title;

// 최초 등록 모드일 때 쿠폰코드 자동 생성
$auto_coupon_code = '';
if ($w != 'u') {
    // 8자리 영문숫자 조합 난수 생성
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $random_string = '';
    for ($i = 0; $i < 8; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }
    $auto_coupon_code = 'SHOP' . $shop_id . '-' . $random_string;
}

include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name'] ? $shop_info['shop_name'] : (isset($shop_info['name']) ? $shop_info['name'] : 'ID: ' . $shop_id);
?>

<div class="local_desc01 local_desc">
    <p>쿠폰 정보를 <?php echo $html_title; ?>해 주세요.</p>
    <p><strong>가맹점: <?php echo get_text($shop_display_name); ?></strong></p>
</div>

<form name="form01" id="form01" action="./shop_coupons_form_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="coupon_id" value="<?php echo $coupon_id ?>">
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
        <th scope="row">쿠폰코드<strong class="sound_only">필수</strong></th>
        <td>
            <?php if ($w == 'u') { ?>
                <?php echo help("쿠폰코드는 수정할 수 없습니다."); ?>
                <input type="text" name="coupon_code" value="<?php echo isset($coupon['coupon_code']) ? htmlspecialchars($coupon['coupon_code']) : ''; ?>" id="coupon_code" class="frm_input required" required maxlength="50" readonly>
                <small style="color:#999;">쿠폰코드는 수정할 수 없습니다.</small>
            <?php } else { ?>
                <?php echo help("쿠폰코드는 자동으로 생성됩니다. 새로고침 시 난수 부분만 변경됩니다."); ?>
                <input type="text" name="coupon_code" value="<?php echo $auto_coupon_code; ?>" id="coupon_code" class="frm_input required" required maxlength="50" readonly>
                <button type="button" onclick="regenerateCouponCode();" class="btn btn_03" style="margin-left:5px;">코드 재생성</button>
                <small style="color:#999;">형식: SHOP{가맹점ID}-{8자리난수}</small>
            <?php } ?>
        </td>
        <th scope="row">쿠폰명<strong class="sound_only">필수</strong></th>
        <td>
            <?php echo help("쿠폰의 제목을 입력하세요. (예: 5천원 할인 쿠폰)"); ?>
            <input type="text" name="coupon_name" value="<?php echo isset($coupon['coupon_name']) ? htmlspecialchars($coupon['coupon_name']) : ''; ?>" id="coupon_name" class="frm_input required" required maxlength="255">
        </td>
    </tr>
    <tr>
        <th scope="row">할인유형<strong class="sound_only">필수</strong></th>
        <td>
            <?php echo help("할인 유형을 선택하세요. PERCENT(백분율 할인) 또는 AMOUNT(정액 할인)"); ?>
            <select name="discount_type" id="discount_type" class="frm_input required" required onchange="toggleDiscountFields();">
                <option value="">선택하세요</option>
                <option value="PERCENT"<?php echo (isset($coupon['discount_type']) && $coupon['discount_type'] == 'PERCENT') ? ' selected' : ''; ?>>백분율 할인 (PERCENT)</option>
                <option value="AMOUNT"<?php echo (isset($coupon['discount_type']) && $coupon['discount_type'] == 'AMOUNT') ? ' selected' : ''; ?>>정액 할인 (AMOUNT)</option>
            </select>
        </td>
        <th scope="row">할인값<strong class="sound_only">필수</strong></th>
        <td>
            <?php echo help("할인 금액 또는 할인율을 입력하세요. (예: 10%, 5000원)"); ?>
            <input type="number" name="discount_value" value="<?php echo isset($coupon['discount_value']) ? $coupon['discount_value'] : ''; ?>" id="discount_value" class="frm_input required" required min="1">
            <span id="discount_unit">원</span>
        </td>
    </tr>
    <tr id="tr_max_discount_amt" style="display:none;">
        <th scope="row">최대할인금액</th>
        <td colspan="3">
            <?php echo help("백분율 할인 시 적용되는 최대 할인 금액을 입력하세요. (선택사항)"); ?>
            <input type="number" name="max_discount_amt" value="<?php echo isset($coupon['max_discount_amt']) ? $coupon['max_discount_amt'] : ''; ?>" id="max_discount_amt" class="frm_input" min="0">
            <span>원</span>
        </td>
    </tr>
    <tr>
        <th scope="row">최소결제금액</th>
        <td>
            <?php echo help("쿠폰을 사용하기 위한 최소 결제 금액을 입력하세요. (선택사항)"); ?>
            <input type="number" name="min_purchase_amt" value="<?php echo isset($coupon['min_purchase_amt']) ? $coupon['min_purchase_amt'] : ''; ?>" id="min_purchase_amt" class="frm_input" min="0">
            <span>원</span>
        </td>
        <th scope="row">유효기간시작일<strong class="sound_only">필수</strong></th>
        <td>
            <?php echo help("쿠폰의 유효기간 시작일을 선택하세요."); ?>
            <input type="date" name="valid_from" value="<?php echo isset($coupon['valid_from']) ? $coupon['valid_from'] : date('Y-m-d'); ?>" id="valid_from" class="frm_input required" required>
        </td>
    </tr>
    <tr>
        <th scope="row">유효기간종료일</th>
        <td>
            <?php echo help("쿠폰의 유효기간 종료일을 선택하세요. (선택사항, 비워두면 무기한)"); ?>
            <input type="date" name="valid_until" value="<?php echo isset($coupon['valid_until']) ? $coupon['valid_until'] : ''; ?>" id="valid_until" class="frm_input">
        </td>
        <th scope="row">전체발급한도</th>
        <td>
            <?php echo help("전체 쿠폰 발급 가능 수량을 입력하세요. (선택사항, 비워두면 무제한)"); ?>
            <input type="number" name="total_limit" value="<?php echo isset($coupon['total_limit']) ? $coupon['total_limit'] : ''; ?>" id="total_limit" class="frm_input" min="1">
            <span>장</span>
        </td>
    </tr>
    <tr>
        <th scope="row">1인당발급한도<strong class="sound_only">필수</strong></th>
        <td>
            <?php echo help("한 명의 사용자가 발급받을 수 있는 쿠폰의 최대 수량을 입력하세요. (예: 3장까지)"); ?>
            <input type="number" name="issued_limit" value="<?php echo isset($coupon['issued_limit']) ? $coupon['issued_limit'] : '1'; ?>" id="issued_limit" class="frm_input required" required min="1">
            <span>장</span>
        </td>
        <th scope="row">활성화여부<strong class="sound_only">필수</strong></th>
        <td>
            <?php echo help("쿠폰의 활성화 상태를 선택하세요."); ?>
            <select name="is_active" id="is_active" class="frm_input required" required>
                <option value="1"<?php echo (!isset($coupon['is_active']) || $coupon['is_active']) ? ' selected' : ''; ?>>활성</option>
                <option value="0"<?php echo (isset($coupon['is_active']) && !$coupon['is_active']) ? ' selected' : ''; ?>>비활성</option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row">상세설명</th>
        <td colspan="3">
            <?php echo help("쿠폰에 대한 상세 설명을 입력하세요. (선택사항)"); ?>
            <textarea name="description" id="description" class="frm_input" rows="5" style="width:100%;" maxlength="1000"><?php echo isset($coupon['description']) ? htmlspecialchars($coupon['description']) : ''; ?></textarea>
        </td>
    </tr>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <a href="./shop_coupons_list.php<?php echo $qstr ? '?' . ltrim($qstr, '&') : ''; ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<?php
// JavaScript에 shop_id 전달 (신규 등록 모드일 때만)
if ($w != 'u') {
    echo '<script>';
    echo 'var shopIdForCouponCode = ' . $shop_id . ';';
    echo '</script>';
}
include_once('./js/shop_coupons_form.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>
