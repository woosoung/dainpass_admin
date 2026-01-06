<?php
$sub_menu = '960200';
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

// shop_faq 에디터 이미지 업로드 시 S3에 저장하도록 세션 설정 (공지사항과 동일하게 파일 맨 위에서 설정)
if (!isset($_SESSION)) {
    @session_start();
}
$_SESSION['shop_faq_upload'] = true;

@auth_check($auth[$sub_menu], 'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];
$shop_info = $result['shop_info'];

// 파라미터 검증
$allowed_w = array('', 'u');
$w = isset($_GET['w']) ? clean_xss_tags($_GET['w']) : '';
$w = in_array($w, $allowed_w) ? $w : '';

$fm_id = isset($_GET['fm_id']) ? (int)$_GET['fm_id'] : 0;
$fm_id = ($fm_id > 0 && $fm_id <= 2147483647) ? $fm_id : 0;

$fa_id = isset($_GET['fa_id']) ? (int)$_GET['fa_id'] : 0;
$fa_id = ($fa_id >= 0 && $fa_id <= 2147483647) ? $fa_id : 0;

// fm_id 필수 체크
if (!$fm_id) {
    alert('잘못된 접근입니다.', './shop_faqmasterlist.php');
    exit;
}

// 해당 가맹점의 FAQ 마스터 확인
$fm_sql = " SELECT fm_id, shop_id, fm_subject
            FROM faq_master
            WHERE fm_id = {$fm_id}
              AND shop_id = {$shop_id} ";
$fm = sql_fetch_pg($fm_sql);

if (!$fm || !$fm['fm_id']) {
    alert('등록된 FAQ 마스터가 없거나, 다른 가맹점의 데이터입니다.', './shop_faqmasterlist.php');
    exit;
}

$html_title = 'FAQ '.$fm['fm_subject'];

// 기본 FAQ 항목 데이터
$fa = array(
    'fa_id'       => 0,
    'fm_id'       => $fm['fm_id'],
    'fa_question' => '',
    'fa_answer'   => '',
    'fa_order'    => 0,
);

if ($w === 'u') {
    $html_title .= ' 수정';

    // 해당 마스터에 속한 항목만 수정 가능
    $sql = " SELECT fa_id, fm_id, fa_question, fa_answer, fa_order
             FROM faq
             WHERE fa_id = {$fa_id}
               AND fm_id = {$fm_id} ";
    $fa_row = sql_fetch_pg($sql);
    if (!$fa_row || !$fa_row['fa_id']) {
        alert('등록된 자료가 없습니다.', './shop_faqlist.php?fm_id='.$fm_id);
        exit;
    }

    $fa = $fa_row;
    
    // 수정 모드일 때 fa_id를 세션에 저장 (에디터 업로드 시 사용)
    $_SESSION['shop_faq_shop_id'] = $shop_id;
    $_SESSION['shop_faq_fm_id'] = $fm_id;
    $_SESSION['shop_faq_fa_id'] = $fa_row['fa_id']; // 수정 모드: 실제 fa_id 저장
} else {
    $html_title .= ' 항목 입력';
    
    // 신규 등록 모드일 때는 세션에서 fa_id 제거 (임시 경로 사용)
    $_SESSION['shop_faq_shop_id'] = $shop_id;
    $_SESSION['shop_faq_fm_id'] = $fm_id;
    if (isset($_SESSION['shop_faq_fa_id'])) {
        unset($_SESSION['shop_faq_fa_id']); // 신규 등록: fa_id 제거
    }
}

$g5['title'] = $html_title.' 관리';

include_once(G5_ADMIN_PATH.'/admin.head.php');
include_once(G5_Z_PATH.'/css/_adm_tailwind_utility_class.php');
?>

<form name="frmshopfaqform" action="./shop_faqformupdate.php" onsubmit="return frmshopfaqform_check(this);" method="post">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="fm_id" value="<?php echo (int) $fm_id; ?>">
<input type="hidden" name="fa_id" value="<?php echo (int) $fa_id; ?>">
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
        <th scope="row"><label for="fa_order">출력순서</label></th>
        <td>
            <?php echo help('숫자가 작을수록 FAQ 페이지에서 먼저 출력됩니다.'); ?>
            <input type="number" name="fa_order" value="<?php echo (int)$fa['fa_order']; ?>" id="fa_order" class="frm_input" min="-1000" max="1000" size="10">
            <?php if ($w === 'u') { ?>
                <a href="<?php echo G5_BBS_URL; ?>/faq.php?fm_id=<?php echo (int) $fm_id; ?>" class="btn_frmline">내용보기</a>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <th scope="row">질문</th>
        <td>
            <?php 
            // 에디터 업로드 시 필드 구분을 위해 세션에 저장
            $_SESSION['shop_faq_current_field'] = 'fa_question';
            
            // 에디터에 표시할 때는 HTML을 유지해야 이미지가 표시됨 (공지사항과 동일)
            $is_dhtml_editor = false;
            if ($config['cf_editor'] && (!is_mobile() || (defined('G5_IS_MOBILE_DHTML_USE') && G5_IS_MOBILE_DHTML_USE))) {
                $is_dhtml_editor = true;
            }
            
            $question_content = '';
            if ($is_dhtml_editor) {
                $question_content = isset($fa['fa_question']) ? $fa['fa_question'] : '';
                if (!empty($question_content)) {
                    if (strpos($question_content, '&quot;') !== false || strpos($question_content, '&amp;') !== false) {
                        $question_content = html_entity_decode($question_content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    }
                    if (strpos($question_content, 'amazonaws.com') !== false) {
                        $question_content = preg_replace_callback('/src=["\']([^"\']*amazonaws\.com[^"\']*)["\']/i', function($matches) {
                            $url = $matches[1];
                            $decoded_url = urldecode($url);
                            return 'src="' . $decoded_url . '"';
                        }, $question_content);
                    }
                    $question_content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $question_content);
                    $question_content = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $question_content);
                    $question_content = preg_replace('/javascript:/i', '', $question_content);
                }
            } else {
                $question_content = get_text(html_purifier($fa['fa_question']), 0);
            }
            
            echo editor_html('fa_question', $question_content, $is_dhtml_editor); 
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row">답변</th>
        <td>
            <?php 
            // 에디터 업로드 시 필드 구분을 위해 세션에 저장
            $_SESSION['shop_faq_current_field'] = 'fa_answer';
            
            $answer_content = '';
            if ($is_dhtml_editor) {
                $answer_content = isset($fa['fa_answer']) ? $fa['fa_answer'] : '';
                if (!empty($answer_content)) {
                    if (strpos($answer_content, '&quot;') !== false || strpos($answer_content, '&amp;') !== false) {
                        $answer_content = html_entity_decode($answer_content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    }
                    if (strpos($answer_content, 'amazonaws.com') !== false) {
                        $answer_content = preg_replace_callback('/src=["\']([^"\']*amazonaws\.com[^"\']*)["\']/i', function($matches) {
                            $url = $matches[1];
                            $decoded_url = urldecode($url);
                            return 'src="' . $decoded_url . '"';
                        }, $answer_content);
                    }
                    $answer_content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $answer_content);
                    $answer_content = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $answer_content);
                    $answer_content = preg_replace('/javascript:/i', '', $answer_content);
                }
            } else {
                $answer_content = get_text(html_purifier($fa['fa_answer']), 0);
            }
            
            echo editor_html('fa_answer', $answer_content, $is_dhtml_editor); 
            ?>
        </td>
    </tr>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top btn_confirm">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
    <a href="./shop_faqlist.php?fm_id=<?php echo (int) $fm_id; ?>" class="btn btn_02">목록</a>
</div>

</form>

<script>
function frmshopfaqform_check(f)
{
    // 출력순서 범위 검증
    if (f.fa_order.value !== '') {
        var order = parseInt(f.fa_order.value);
        if (isNaN(order) || order < -1000 || order > 1000) {
            alert('출력순서는 -1000에서 1000 사이의 정수여야 합니다.');
            f.fa_order.focus();
            return false;
        }
    }

    <?php echo get_editor_js('fa_question'); ?>
    <?php echo get_editor_js('fa_answer'); ?>

    // 에디터 필수값 검증
    <?php if ($is_dhtml_editor) { ?>
    // 에디터 내용 체크
    if (typeof ed_fa_question !== 'undefined') {
        var question_content = ed_fa_question.outputBodyHTML();
        var question_text = question_content.replace(/<[^>]*>/g, '').trim();
        if (!question_text || question_text === '' || question_text === '&nbsp;') {
            alert('질문을 입력해주세요.');
            return false;
        }
    }

    if (typeof ed_fa_answer !== 'undefined') {
        var answer_content = ed_fa_answer.outputBodyHTML();
        var answer_text = answer_content.replace(/<[^>]*>/g, '').trim();
        if (!answer_text || answer_text === '' || answer_text === '&nbsp;') {
            alert('답변을 입력해주세요.');
            return false;
        }
    }
    <?php } ?>

    return true;
}
</script>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
