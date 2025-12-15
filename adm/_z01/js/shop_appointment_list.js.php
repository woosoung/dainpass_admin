<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
function flist_submit(f) {
    return true;
}

function check_all(f) {
    var chk = document.getElementsByName("chk[]");
    
    for (var i=0; i<chk.length; i++) {
        chk[i].checked = f.checked;
    }
}

function flist_status_update_submit() {
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
    
    var new_status = prompt('변경할 상태를 입력하세요.\n(COMPLETED: 결제완료, CANCELLED: 취소됨)', '');
    
    if (!new_status) {
        return false;
    }
    
    if (new_status !== 'COMPLETED' && new_status !== 'CANCELLED') {
        alert('올바른 상태를 입력하세요. (COMPLETED, CANCELLED만 가능)');
        return false;
    }
    
    if (new_status === 'BOOKED') {
        alert('BOOKED 상태로는 변경할 수 없습니다.');
        return false;
    }
    
    if (!confirm('선택한 ' + checked_count + '개의 예약 상태를 "' + new_status + '"로 변경하시겠습니까?')) {
        return false;
    }
    
    // 토큰 확인
    if (!f.token || !f.token.value) {
        alert('토큰 정보가 올바르지 않습니다. 페이지를 새로고침해주세요.');
        return false;
    }
    
    f.act.value = 'status_update';
    
    // new_status를 hidden input으로 추가
    var newStatusInput = document.createElement('input');
    newStatusInput.type = 'hidden';
    newStatusInput.name = 'new_status';
    newStatusInput.value = new_status;
    f.appendChild(newStatusInput);
    
    f.submit();
}
</script>
