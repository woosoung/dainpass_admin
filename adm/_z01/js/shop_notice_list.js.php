<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
function flist_submit(f) {
    return true;
}

function fsearch_submit(f) {
    // 검색어 입력 검증
    if (f.stx && f.stx.value) {
        var stx = f.stx.value.trim();

        // 최대 길이 검증 (100자)
        if (stx.length > 100) {
            alert('검색어는 최대 100자까지 입력 가능합니다.');
            f.stx.focus();
            return false;
        }

        // XSS 위험 문자 검증
        var dangerous_chars = /<script|<iframe|javascript:|onerror=|onload=/i;
        if (dangerous_chars.test(stx)) {
            alert('검색어에 허용되지 않는 문자가 포함되어 있습니다.');
            f.stx.focus();
            return false;
        }
    }

    return true;
}

function flist_delete_submit() {
    var f = document.flist;
    var checked_count = 0;

    for (var i=0; i<f.elements.length; i++) {
        if (f.elements[i].name == "chk[]" && f.elements[i].checked) {
            checked_count++;
        }
    }

    if (checked_count == 0) {
        alert('선택된 항목이 없습니다.');
        return false;
    }

    // 최대 선택 개수 제한
    if (checked_count > 100) {
        alert('한 번에 최대 100개까지만 삭제할 수 있습니다.');
        return false;
    }

    if (!confirm('선택한 ' + checked_count + '개의 공지사항을 삭제하시겠습니까?')) {
        return false;
    }

    // 토큰 확인
    if (!f.token || !f.token.value) {
        alert('토큰 정보가 올바르지 않습니다. 페이지를 새로고침해주세요.');
        return false;
    }

    f.act.value = 'delete';
    f.submit();

    return true;
}

function check_all(f) {
    var chk = document.getElementsByName("chk[]");

    for (var i=0; i<chk.length; i++) {
        chk[i].checked = f.checked;
    }
}
</script>

