<?php
$sub_menu = '960200';
include_once('./_common.php');

@auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

// 입력 검증 - 화이트리스트 방식
$allowed_w = array('', 'u');
$w = isset($_GET['w']) ? clean_xss_tags($_GET['w']) : '';
$w = in_array($w, $allowed_w) ? $w : '';

$fm_id = isset($_GET['fm_id']) ? (int)$_GET['fm_id'] : 0;
$fm_id = ($fm_id >= 0 && $fm_id <= 2147483647) ? $fm_id : 0;

$html_title = 'FAQ 마스터';
$fm = array(
    'fm_id'      => 0,
    'shop_id'    => $shop_id,
    'fm_subject' => '',
    'fm_order'   => 0,
);

if ($w === 'u') {
    $html_title .= ' 수정';

    // 해당 가맹점의 마스터만 수정 가능
    $sql = " SELECT fm_id, shop_id, fm_subject, fm_order
             FROM faq_master
             WHERE fm_id = {$fm_id}
               AND shop_id = {$shop_id} ";
    $fm_row = sql_fetch_pg($sql);
    if (!$fm_row || !$fm_row['fm_id']) {
        alert('등록된 자료가 없거나, 다른 가맹점의 FAQ 마스터입니다.', './shop_faqmasterlist.php');
        exit;
    }

    $fm = $fm_row;
} else {
    $html_title .= ' 입력';
}

$g5['title'] = $html_title.' 관리';

include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

$shop_display_name = isset($shop_info['shop_name']) && $shop_info['shop_name']
    ? $shop_info['shop_name']
    : (isset($shop_info['name']) && $shop_info['name'] ? $shop_info['name'] : 'ID: '.$shop_id);
?>

<div class="local_desc01 local_desc">
    <p>
        가맹점의 FAQ 마스터를 <?php echo $w === 'u' ? '수정' : '등록'; ?>합니다.<br>
    </p>
    <?php echo get_shop_display_name($shop_info, $shop_id); ?>
</div>

<form name="frmshopfaqmasterform" action="./shop_faqmasterformupdate.php" method="post" onsubmit="return frmshopfaqmasterform_check(this);">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="fm_id" value="<?php echo (int) $fm['fm_id']; ?>">
    <input type="hidden" name="shop_id" value="<?php echo (int) $shop_id; ?>">
    <input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">

    <div class="tbl_frm01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th scope="row"><label for="fm_order">출력순서</label></th>
                    <td>
                        <?php echo help('숫자가 작을수록 FAQ 분류에서 먼저 출력됩니다.'); ?>
                        <input type="number" name="fm_order" value="<?php echo (int)$fm['fm_order']; ?>" id="fm_order" class="frm_input" min="-1000" max="1000" size="10">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="fm_subject">제목<strong class="sound_only">필수</strong></label></th>
                    <td>
                        <input type="text" name="fm_subject" value="<?php echo htmlspecialchars($fm['fm_subject'], ENT_QUOTES, 'UTF-8'); ?>" id="fm_subject" required class="frm_input required" size="70" maxlength="100">
                        <?php if ($w === 'u') { ?>
                            <a href="./shop_faqlist.php?fm_id=<?php echo (int) $fm['fm_id']; ?>&amp;fm_subject=<?php echo urlencode($fm['fm_subject']); ?>" class="btn_frmline">FAQ 항목관리</a>
                        <?php } ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="btn_fixed_top btn_confirm">
        <a href="./shop_faqmasterlist.php" class="btn btn_02">목록</a>
        <button type="submit" class="btn_submit btn">확인</button>
    </div>
</form>

<script>
function frmshopfaqmasterform_check(f) {
    // 제목 검증
    if (!f.fm_subject.value || f.fm_subject.value.trim() === '') {
        alert('제목을 입력해 주세요.');
        f.fm_subject.focus();
        return false;
    }

    // 제목 길이 제한 (100자)
    if (f.fm_subject.value.length > 100) {
        alert('제목은 최대 100자까지 입력 가능합니다.');
        f.fm_subject.focus();
        return false;
    }

    // 제목 XSS 패턴 체크
    var dangerous_chars = /<script|<iframe|javascript:|onerror=|onload=/i;
    if (dangerous_chars.test(f.fm_subject.value)) {
        alert('제목에 허용되지 않는 문자가 포함되어 있습니다.');
        f.fm_subject.focus();
        return false;
    }

    // 출력순서 범위 검증
    if (f.fm_order.value !== '') {
        var order = parseInt(f.fm_order.value);
        if (isNaN(order) || order < -1000 || order > 1000) {
            alert('출력순서는 -1000에서 1000 사이의 정수여야 합니다.');
            f.fm_order.focus();
            return false;
        }
    }

    return true;
}
</script>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
