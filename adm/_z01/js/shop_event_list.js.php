<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
function flist_submit(f) {
    return true;
}

function flist_delete_submit() {
    var f = document.flist;
    var checked_count = 0;

    // 체크박스 개수 확인
    for (var i=0; i<f.elements.length; i++) {
        if (f.elements[i].name == "chk[]" && f.elements[i].checked) {
            checked_count++;
        }
    }

    if (checked_count == 0) {
        alert('선택된 항목이 없습니다.');
        return false;
    }

    // 최대 삭제 개수 제한
    if (checked_count > 100) {
        alert('한 번에 최대 100개까지만 삭제할 수 있습니다.');
        return false;
    }

    if (!confirm('선택한 ' + checked_count + '개의 이벤트를 삭제하시겠습니까?')) {
        return false;
    }

    // 토큰 확인
    if (!f.token || !f.token.value) {
        alert('토큰 정보가 올바르지 않습니다. 페이지를 새로고침해주세요.');
        return false;
    }

    // act 필드 확인
    if (!f.act) {
        alert('필수 필드가 누락되었습니다. 페이지를 새로고침해주세요.');
        return false;
    }

    f.act.value = 'delete';
    f.submit();

    // 중복 제출 방지
    setTimeout(function() {
        var submitButtons = document.querySelectorAll('button[onclick*="flist_delete_submit"]');
        for (var i = 0; i < submitButtons.length; i++) {
            submitButtons[i].disabled = true;
        }
    }, 100);
}

function check_all(f) {
    var chk = document.getElementsByName("chk[]");
    
    for (var i=0; i<chk.length; i++) {
        chk[i].checked = f.checked;
    }
}
</script>
