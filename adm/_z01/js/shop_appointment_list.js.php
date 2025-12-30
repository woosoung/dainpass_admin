<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
function flist_submit(f) {
    return true;
}

function fsearch_submit(f) {
    // 날짜 형식 검증 (YYYY-MM-DD)
    var datePattern = /^\d{4}-\d{2}-\d{2}$/;

    if (f.fr_date && f.fr_date.value) {
        var fr_date = f.fr_date.value.trim();
        if (fr_date && !datePattern.test(fr_date)) {
            alert('시작일은 YYYY-MM-DD 형식으로 입력해주세요. (예: 2024-01-01)');
            f.fr_date.focus();
            return false;
        }
    }

    if (f.to_date && f.to_date.value) {
        var to_date = f.to_date.value.trim();
        if (to_date && !datePattern.test(to_date)) {
            alert('종료일은 YYYY-MM-DD 형식으로 입력해주세요. (예: 2024-12-31)');
            f.to_date.focus();
            return false;
        }
    }

    // 시작일이 종료일보다 늦은지 확인
    if (f.fr_date && f.to_date && f.fr_date.value && f.to_date.value) {
        var fr = new Date(f.fr_date.value);
        var to = new Date(f.to_date.value);

        if (fr > to) {
            alert('시작일은 종료일보다 이전이어야 합니다.');
            f.fr_date.focus();
            return false;
        }
    }

    // 검색어 길이 제한
    if (f.stx && f.stx.value && f.stx.value.length > 100) {
        alert('검색어는 최대 100자까지 입력할 수 있습니다.');
        f.stx.focus();
        return false;
    }

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

    // 체크된 항목 확인
    for (var i=0; i<f.elements.length; i++) {
        if (f.elements[i].name == "chk[]" && f.elements[i].checked) {
            checked_count++;
        }
    }

    // 선택 항목 없음
    if (checked_count == 0) {
        alert('선택된 항목이 없습니다.');
        return false;
    }

    // 최대 선택 개수 제한
    if (checked_count > 100) {
        alert('한 번에 최대 100개까지만 변경할 수 있습니다.');
        return false;
    }

    // 최종 확인
    if (!confirm('선택한 ' + checked_count + '개의 예약 상태를 변경하시겠습니까?')) {
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

    f.act.value = 'status_update';

    // 중복 제출 방지
    var submitButtons = document.querySelectorAll('button[onclick*="flist_status_update_submit"]');
    for (var i = 0; i < submitButtons.length; i++) {
        submitButtons[i].disabled = true;
    }

    // 3초 후 버튼 다시 활성화 (에러 발생 시 재시도 가능하도록)
    setTimeout(function() {
        for (var i = 0; i < submitButtons.length; i++) {
            submitButtons[i].disabled = false;
        }
    }, 3000);

    f.submit();
}
</script>
