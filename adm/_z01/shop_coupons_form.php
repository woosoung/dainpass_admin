<?php
$sub_menu = "940100";
include_once('./_common.php');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = (int)$result['shop_id'];
$shop_info = $result['shop_info'];

@auth_check($auth[$sub_menu], 'w');

// w 파라미터 확인 - 화이트리스트 검증 (u: 수정, 그 외: 신규)
$w_input = isset($_GET['w']) ? clean_xss_tags($_GET['w']) : '';
$allowed_w = array('u');
$w = in_array($w_input, $allowed_w) ? $w_input : '';

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

    // 쿠폰코드 4-4-4 형식으로 표시 (12자리인 경우만)
    if (isset($coupon['coupon_code']) && strlen($coupon['coupon_code']) == 12 && ctype_alnum($coupon['coupon_code'])) {
        $coupon['coupon_code'] = substr($coupon['coupon_code'], 0, 4) . '-' . substr($coupon['coupon_code'], 4, 4) . '-' . substr($coupon['coupon_code'], 8, 4);
    }
}

// qstr 생성 - 화이트리스트 검증
$qstr = '';

// page
if (isset($_GET['page']) && $_GET['page']) {
    $page_param = (int)$_GET['page'];
    if ($page_param > 0) {
        $qstr .= '&page=' . $page_param;
    }
}

// sst (정렬 필드)
$allowed_sst = array('coupon_id', 'coupon_code', 'coupon_name', 'discount_type', 'valid_from', 'valid_until', 'is_active', 'created_at');
if (isset($_GET['sst']) && in_array($_GET['sst'], $allowed_sst)) {
    $qstr .= '&sst=' . urlencode($_GET['sst']);
}

// sod (정렬 방향)
$allowed_sod = array('asc', 'desc');
if (isset($_GET['sod']) && in_array($_GET['sod'], $allowed_sod)) {
    $qstr .= '&sod=' . urlencode($_GET['sod']);
}

// sfl (검색 필드)
$allowed_sfl = array('', 'coupon_code', 'coupon_name', 'description');
if (isset($_GET['sfl']) && in_array($_GET['sfl'], $allowed_sfl)) {
    $qstr .= '&sfl=' . urlencode($_GET['sfl']);
}

// stx (검색어)
if (isset($_GET['stx']) && $_GET['stx']) {
    $qstr .= '&stx=' . urlencode(clean_xss_tags($_GET['stx']));
}

// sfl2 (활성화 상태 필터)
$allowed_sfl2 = array('', 'active', 'inactive');
if (isset($_GET['sfl2']) && in_array($_GET['sfl2'], $allowed_sfl2)) {
    $qstr .= '&sfl2=' . urlencode($_GET['sfl2']);
}

$html_title = ($w == 'u') ? '수정' : '등록';
$g5['title'] = '쿠폰 ' . $html_title;

// 최초 등록 모드일 때 쿠폰코드 자동 생성
$auto_coupon_code = '';
if ($w != 'u') {
    // 12자리 영문숫자 조합 난수 생성 (혼동되는 문자 제외: I, O, 0, 1)
    // 전체 시스템에서 유일해야 하므로 12자리로 충돌 확률 최소화
    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $max_attempts = 10;

    for ($attempt = 0; $attempt < $max_attempts; $attempt++) {
        $random_string = '';
        for ($i = 0; $i < 12; $i++) {
            $random_string .= $characters[rand(0, strlen($characters) - 1)];
        }

        // 전체 시스템에서 중복 체크
        $check_sql = "SELECT coupon_id FROM shop_coupons WHERE coupon_code = '{$random_string}'";
        $check_row = sql_fetch_pg($check_sql);

        if (!$check_row) {
            $auto_coupon_code = $random_string;
            break;
        }
    }

    // 10번 시도해도 중복이면 타임스탬프 포함 (실제로는 거의 불가능)
    if (!$auto_coupon_code) {
        $auto_coupon_code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 12));
    }

    // 4-4-4 형식으로 표시 (예: ABCD-EFGH-1234)
    if ($auto_coupon_code && strlen($auto_coupon_code) == 12) {
        $auto_coupon_code = substr($auto_coupon_code, 0, 4) . '-' . substr($auto_coupon_code, 4, 4) . '-' . substr($auto_coupon_code, 8, 4);
    }
}

include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
?>

<div class="local_desc01 local_desc">
    <p>쿠폰 정보를 <?php echo $html_title; ?>해 주세요.</p>
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
                <?php echo help("쿠폰코드는 자동으로 생성됩니다."); ?>
                <input type="text" name="coupon_code" value="<?php echo $auto_coupon_code; ?>" id="coupon_code" class="frm_input required" required maxlength="50" readonly>
                <button type="button" onclick="regenerateCouponCode();" class="btn btn_03" style="margin-left:5px;">코드 재생성</button>
            <?php } ?>
        </td>
        <th scope="row">쿠폰명<strong class="sound_only">필수</strong></th>
        <td>
            <?php echo help("쿠폰의 제목을 입력하세요. (예: 5천원 할인 쿠폰)"); ?>
            <input type="text" name="coupon_name" value="<?php echo isset($coupon['coupon_name']) ? htmlspecialchars($coupon['coupon_name']) : ''; ?>" id="coupon_name" class="frm_input required" required maxlength="50">
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
            <input type="number" name="discount_value" value="<?php echo isset($coupon['discount_value']) ? $coupon['discount_value'] : ''; ?>" id="discount_value" class="frm_input required" required min="1" max="100000000">
            <span id="discount_unit">원</span>
        </td>
    </tr>
    <tr id="tr_max_discount_amt" style="display:none;">
        <th scope="row">최대할인금액</th>
        <td colspan="3">
            <?php echo help("백분율 할인 시 적용되는 최대 할인 금액을 입력하세요. (선택사항, 최대 1억원)"); ?>
            <input type="number" name="max_discount_amt" value="<?php echo isset($coupon['max_discount_amt']) ? $coupon['max_discount_amt'] : ''; ?>" id="max_discount_amt" class="frm_input" min="0" max="100000000">
            <span>원</span>
        </td>
    </tr>
    <tr>
        <th scope="row">최소결제금액</th>
        <td>
            <?php echo help("쿠폰을 사용하기 위한 최소 결제 금액을 입력하세요. (선택사항, 최대 1억원)"); ?>
            <input type="number" name="min_purchase_amt" value="<?php echo isset($coupon['min_purchase_amt']) ? $coupon['min_purchase_amt'] : ''; ?>" id="min_purchase_amt" class="frm_input" min="0" max="100000000">
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
            <?php echo help("전체 쿠폰 발급 가능 수량을 입력하세요. (선택사항, 비워두면 무제한, 최대 100만장)"); ?>
            <input type="number" name="total_limit" value="<?php echo isset($coupon['total_limit']) ? $coupon['total_limit'] : ''; ?>" id="total_limit" class="frm_input" min="1" max="1000000">
            <span>장</span>
        </td>
    </tr>
    <tr>
        <th scope="row">1인당발급한도<strong class="sound_only">필수</strong></th>
        <td>
            <?php echo help("한 명의 사용자가 발급받을 수 있는 쿠폰의 최대 수량을 입력하세요. (최대 1,000장)"); ?>
            <input type="number" name="issued_limit" value="<?php echo isset($coupon['issued_limit']) ? $coupon['issued_limit'] : '1'; ?>" id="issued_limit" class="frm_input required" required min="1" max="1000">
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
include_once('./js/shop_coupons_form.js.php');
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>
