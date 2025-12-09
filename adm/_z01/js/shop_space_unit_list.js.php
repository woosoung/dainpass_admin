<script>
function flist_submit(f) {
    return true;
}

function flist_sort_submit() {
    var f = document.flist;
    
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
    // 캔버스 크기 기본값 설정
    canvas_width = canvas_width || 1000;
    canvas_height = canvas_height || 800;
    
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
    
    // URL 생성
    var url = './shop_space_layout_editor.php?group_id=' + group_id + '&' + qstr;
    
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

