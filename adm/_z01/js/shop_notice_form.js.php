<?php
if (!defined('_GNUBOARD_')) exit;

// 에디터 설정
$is_dhtml_editor = false;
if ($config['cf_editor'] && (!is_mobile() || (defined('G5_IS_MOBILE_DHTML_USE') && G5_IS_MOBILE_DHTML_USE))) {
    $is_dhtml_editor = true;
}

$editor_js = '';
$editor_js .= get_editor_js('content', $is_dhtml_editor);
$editor_js .= chk_editor_js('content', $is_dhtml_editor);
?>
<script>
function form_check(f) {
    // 에디터 내용을 textarea에 넣어주는 코드 (폼 제출 전에 실행)
    <?php echo $editor_js; ?>
    
    if (!f.subject.value || f.subject.value.trim() == '') {
        alert('제목을 입력해주세요.');
        f.subject.focus();
        return false;
    }
    
    <?php if ($is_dhtml_editor) { ?>
    // 에디터 내용 체크 및 textarea에 값 설정
    if (typeof ed_content !== 'undefined') {
        var content = ed_content.outputBodyHTML();
        // tx_content textarea에 값 설정 (cheditor5의 경우)
        var tx_content = document.getElementById('tx_content');
        if (tx_content) {
            tx_content.value = content;
        }
        // content textarea에도 값 설정 (일부 에디터의 경우)
        var content_textarea = document.getElementById('content');
        if (content_textarea) {
            content_textarea.value = content;
        }
        
        if (!content || content.trim() == '' || content == '<p>&nbsp;</p>' || content == '<p><br></p>' || content == '<div><br></div>' || content == '<p></p>' || content == '<br>' || content == '&nbsp;') {
            alert('내용을 입력해주세요.');
            ed_content.returnFalse();
            return false;
        }
    }
    <?php } else { ?>
    if (!f.content.value || f.content.value.trim() == '') {
        alert('내용을 입력해주세요.');
        f.content.focus();
        return false;
    }
    <?php } ?>
    
    return true;
}
</script>


