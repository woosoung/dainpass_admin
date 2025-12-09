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
    
    for (var i=0; i<f.elements.length; i++) {
        if (f.elements[i].name == "chk[]" && f.elements[i].checked) {
            checked_count++;
        }
    }
    
    if (checked_count == 0) {
        alert('선택된 항목이 없습니다.');
        return false;
    }
    
    if (!confirm('선택한 ' + checked_count + '개의 쿠폰을 삭제하시겠습니까?')) {
        return false;
    }
    
    // 토큰 확인
    if (!f.token || !f.token.value) {
        alert('토큰 정보가 올바르지 않습니다. 페이지를 새로고침해주세요.');
        return false;
    }
    
    f.act.value = 'delete';
    f.submit();
}

function check_all(f) {
    var chk = document.getElementsByName("chk[]");
    
    for (var i=0; i<chk.length; i++) {
        chk[i].checked = f.checked;
    }
}
</script>
