<script>
function flist_submit(f) {
    return true;
}

function flist_sort_submit() {
    var f = document.flist;

    // sort_order 입력값 검증
    var sort_inputs = document.querySelectorAll('input[name^="sort_order["]');
    for (var i = 0; i < sort_inputs.length; i++) {
        var value = parseInt(sort_inputs[i].value);
        if (isNaN(value) || value < 0 || value > 9999) {
            alert('정렬순서는 0 이상 9999 이하의 숫자로 입력해 주세요.');
            sort_inputs[i].focus();
            return false;
        }
    }

    // 토큰 가져오기
    var token = get_ajax_token();
    if (!token) {
        alert('토큰 정보가 올바르지 않습니다.');
        return false;
    }
    f.token.value = token;

    f.act.value = 'sort';
    f.submit();
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
    
    if (!confirm('선택한 ' + checked_count + '개의 공간 유닛을 삭제하시겠습니까?')) {
        return false;
    }
    
    // 토큰 가져오기
    var token = get_ajax_token();
    if (!token) {
        alert('토큰 정보가 올바르지 않습니다.');
        return false;
    }
    f.token.value = token;
    
    f.act.value = 'delete';
    f.submit();
}

function check_all(f) {
    var chk = document.getElementsByName("chk[]");
    
    for (var i=0; i<chk.length; i++) {
        chk[i].checked = f.checked;
    }
}

function open_layout_editor(group_id, canvas_width, canvas_height, qstr) {
    // group_id 검증
    group_id = parseInt(group_id);
    if (isNaN(group_id) || group_id <= 0) {
        alert('유효하지 않은 공간 그룹 ID입니다.');
        return false;
    }

    // 캔버스 크기 검증 및 기본값 설정
    canvas_width = parseInt(canvas_width) || 1000;
    canvas_height = parseInt(canvas_height) || 800;

    // 캔버스 크기 범위 검증 (100 이상 10000 이하)
    if (canvas_width < 100 || canvas_width > 10000) {
        canvas_width = 1000;
    }
    if (canvas_height < 100 || canvas_height > 10000) {
        canvas_height = 800;
    }

    // 팝업 크기 계산
    // 캔버스 + 사이드바(300px) + 패딩(80px) + 여유(40px)
    var popup_width = canvas_width + 300 + 80 + 40;
    // 캔버스 + 헤더(80px) + 패딩(80px) + 여유(40px)
    var popup_height = canvas_height + 80 + 80 + 40;

    // 최소/최대 크기 제한
    var min_width = 1000;
    var min_height = 700;
    var max_width = screen.width - 100;
    var max_height = screen.height - 100;

    popup_width = Math.max(min_width, Math.min(popup_width, max_width));
    popup_height = Math.max(min_height, Math.min(popup_height, max_height));

    // 팝업 위치 계산 (화면 중앙)
    var left = (screen.width - popup_width) / 2;
    var top = (screen.height - popup_height) / 2;

    // qstr 검증 (undefined, null 체크)
    qstr = qstr || '';

    // URL 생성 (group_id는 이미 검증된 정수)
    var url = './shop_space_layout_editor.php?group_id=' + group_id;
    if (qstr) {
        url += '&' + qstr;
    }

    // 팝업 옵션
    var popup_options = 'width=' + popup_width +
                       ',height=' + popup_height +
                       ',left=' + left +
                       ',top=' + top +
                       ',scrollbars=yes' +
                       ',resizable=yes' +
                       ',status=yes';

    // 팝업 열기
    var popup = window.open(url, 'layout_editor_' + group_id, popup_options);

    if (popup) {
        popup.focus();
    } else {
        alert('팝업 차단을 해제해 주세요.');
    }

    return false;
}
</script>

