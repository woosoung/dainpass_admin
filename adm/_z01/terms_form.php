<?php
$sub_menu = "910280";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

@auth_check($auth[$sub_menu], 'w');

// 모든 약관 조회
$sql = " SELECT * FROM {$g5['service_terms_table']} ORDER BY st_order ASC, st_id ASC ";
$result = sql_query_pg($sql);

$terms = array();
if ($result && is_object($result) && isset($result->result)) {
    while ($row = sql_fetch_array_pg($result->result)) {
        $terms[] = $row;
    }
}

// 약관이 없으면 기본 약관들 생성
if (empty($terms)) {
    $default_terms = array(
        array('st_code' => 'AGE', 'st_title' => '18세 이상 확인', 'st_version' => 'v1.0', 'st_required' => 'Y', 'st_order' => 1, 'st_summary' => '[필수] 만 18세 이상입니다.'),
        array('st_code' => 'SERVICE', 'st_title' => '이용약관 동의', 'st_version' => 'v1.0', 'st_required' => 'Y', 'st_order' => 2, 'st_summary' => '[필수] 이용약관 동의'),
        array('st_code' => 'PRIVACY', 'st_title' => '개인정보 수집 및 이용 동의', 'st_version' => 'v1.0', 'st_required' => 'Y', 'st_order' => 3, 'st_summary' => '[필수] 개인정보 수집 및 이용 동의'),
        array('st_code' => 'LOCATION', 'st_title' => '위치정보 이용약관 동의', 'st_version' => 'v1.0', 'st_required' => 'Y', 'st_order' => 4, 'st_summary' => '[필수] 위치정보 이용약관 동의'),
        array('st_code' => 'MARKETING', 'st_title' => '마케팅 정보 수신 동의', 'st_version' => 'v1.0', 'st_required' => 'N', 'st_order' => 5, 'st_summary' => '[선택] 마케팅 정보 수신 동의'),
        array('st_code' => 'CONSENT', 'st_title' => '개인정보 제 3자 제공 동의', 'st_version' => 'v1.0', 'st_required' => 'N', 'st_order' => 6, 'st_summary' => '[선택] 개인정보 제 3자 제공 동의'),
    );
    
    foreach ($default_terms as $default_term) {
        $terms[] = array_merge($default_term, array(
            'st_id' => 0,
            'st_content' => '',
            'st_created_at' => date('Y-m-d H:i:s')
        ));
    }
}

$g5['title'] = '서비스 약관 관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');

// 섹션 앵커 링크 생성
$term_anchor = '<ul class="anchor">';
$term_count = count($terms);
$term_sections = array();
for ($i = 0; $i < $term_count; $i++) {
    $term = $terms[$i];
    $term_title = $term['st_title'] ?? '';
    $term_code = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($term['st_code'] ?? ''));
    $section_id = 'anc_term_'.$term_code;
    $term_sections[$i] = $section_id;
    $term_anchor .= '<li><a href="#'.$section_id.'">'.get_text($term_title).'</a></li>';
}
$term_anchor .= '</ul>';
?>

<form name="frm" action="./terms_form_update.php" onsubmit="return frm_check(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="token" value="">

<?php
for ($i = 0; $i < $term_count; $i++) {
    $term = $terms[$i];
    $term_id = $term['st_id'] ?? 0;
    $term_code_raw = $term['st_code'] ?? '';
    $term_title = $term['st_title'] ?? '';
    $section_id = $term_sections[$i] ?? 'anc_term_'.$i;
?>
<section id="<?php echo $section_id; ?>">
    <h2 class="h2_frm"><?php echo get_text($term_title); ?><?php if ($term['st_required'] == 'Y') { ?><span style="color: red; font-size: 0.9em; margin-left: 10px;">[필수]</span><?php } else { ?><span style="color: gray; font-size: 0.9em; margin-left: 10px;">[선택]</span><?php } ?></h2>
    <?php echo $term_anchor; ?>
    
    <input type="hidden" name="terms[<?php echo $i; ?>][st_id]" value="<?php echo $term_id; ?>">
    
    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption><?php echo get_text($term_title); ?> 입력</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="st_code_<?php echo $i; ?>">약관코드<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help("약관을 구분하는 고유 코드입니다."); ?>
                <input type="text" name="terms[<?php echo $i; ?>][st_code]" value="<?php echo get_text($term_code_raw); ?>" id="st_code_<?php echo $i; ?>" class="frm_input required" size="50" maxlength="50" required readonly>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="st_title_<?php echo $i; ?>">약관제목<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help("약관의 제목을 입력하세요."); ?>
                <input type="text" name="terms[<?php echo $i; ?>][st_title]" value="<?php echo get_text($term_title); ?>" id="st_title_<?php echo $i; ?>" class="frm_input required" size="100" maxlength="255" required>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="st_version_<?php echo $i; ?>">버전<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help("약관의 버전을 입력하세요. (예: v1.0)"); ?>
                <input type="text" name="terms[<?php echo $i; ?>][st_version]" value="<?php echo get_text($term['st_version'] ?? 'v1.0'); ?>" id="st_version_<?php echo $i; ?>" class="frm_input required" size="20" maxlength="20" required>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="st_content_<?php echo $i; ?>">약관내용<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help("약관의 상세 내용을 입력하세요."); ?>
                <?php echo editor_html('st_content_'.$i, get_text(html_purifier($term['st_content'] ?? ''), 0)); ?>
                <input type="hidden" name="terms[<?php echo $i; ?>][st_content_field]" value="st_content_<?php echo $i; ?>">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="st_required_<?php echo $i; ?>_y">필수여부<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help("약관이 필수 동의인지 선택 동의인지 설정하세요."); ?>
                <input type="radio" name="terms[<?php echo $i; ?>][st_required]" value="Y" id="st_required_<?php echo $i; ?>_y" <?php echo (($term['st_required'] ?? 'Y') == 'Y') ? 'checked' : ''; ?>>
                <label for="st_required_<?php echo $i; ?>_y">필수</label>
                &nbsp;&nbsp;
                <input type="radio" name="terms[<?php echo $i; ?>][st_required]" value="N" id="st_required_<?php echo $i; ?>_n" <?php echo (($term['st_required'] ?? 'Y') == 'N') ? 'checked' : ''; ?>>
                <label for="st_required_<?php echo $i; ?>_n">선택</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="st_order_<?php echo $i; ?>">순서<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help("약관 목록에서의 표시 순서를 설정하세요. 숫자가 작을수록 앞에 표시됩니다."); ?>
                <input type="number" name="terms[<?php echo $i; ?>][st_order]" value="<?php echo (int)($term['st_order'] ?? ($i + 1)); ?>" id="st_order_<?php echo $i; ?>" class="frm_input required" size="10" min="1" required>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="st_summary_<?php echo $i; ?>">요약</label></th>
            <td>
                <?php echo help("약관의 간단한 요약 내용을 입력하세요."); ?>
                <input type="text" name="terms[<?php echo $i; ?>][st_summary]" value="<?php echo get_text($term['st_summary'] ?? ''); ?>" id="st_summary_<?php echo $i; ?>" class="frm_input" size="100" maxlength="500">
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<?php
}
?>

<div class="btn_fixed_top">
    <input type="submit" value="모든 약관 저장" class="btn_submit btn" accesskey="s">
</div>
</form>

<script>
function frm_check(f) {
    // 모든 에디터 내용 가져오기
    <?php
    for ($i = 0; $i < $term_count; $i++) {
        echo get_editor_js('st_content_'.$i) . PHP_EOL;
    }
    ?>
    
    var msg = "";
    var focus_el = null;
    
    // 각 약관 필수 입력값 체크
    <?php for ($i = 0; $i < $term_count; $i++) { ?>
    var st_code_<?php echo $i; ?> = document.querySelector('[name="terms[<?php echo $i; ?>][st_code]"]');
    var st_title_<?php echo $i; ?> = document.querySelector('[name="terms[<?php echo $i; ?>][st_title]"]');
    var st_version_<?php echo $i; ?> = document.querySelector('[name="terms[<?php echo $i; ?>][st_version]"]');
    var st_order_<?php echo $i; ?> = document.querySelector('[name="terms[<?php echo $i; ?>][st_order]"]');
    
    if (!st_code_<?php echo $i; ?> || !st_code_<?php echo $i; ?>.value) {
        msg = '<?php echo get_text($terms[$i]['st_title'] ?? ''); ?>의 약관코드를 입력하세요.';
        focus_el = st_code_<?php echo $i; ?>;
    } else if (!st_title_<?php echo $i; ?> || !st_title_<?php echo $i; ?>.value) {
        msg = '<?php echo get_text($terms[$i]['st_title'] ?? ''); ?>의 약관제목을 입력하세요.';
        focus_el = st_title_<?php echo $i; ?>;
    } else if (!st_version_<?php echo $i; ?> || !st_version_<?php echo $i; ?>.value) {
        msg = '<?php echo get_text($terms[$i]['st_title'] ?? ''); ?>의 버전을 입력하세요.';
        focus_el = st_version_<?php echo $i; ?>;
    } else if (!st_order_<?php echo $i; ?> || !st_order_<?php echo $i; ?>.value || st_order_<?php echo $i; ?>.value < 1) {
        msg = '<?php echo get_text($terms[$i]['st_title'] ?? ''); ?>의 순서를 올바르게 입력하세요.';
        focus_el = st_order_<?php echo $i; ?>;
    }
    
    if (msg) {
        alert(msg);
        if (focus_el) focus_el.focus();
        return false;
    }
    <?php } ?>
    
    return true;
}
</script>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>
