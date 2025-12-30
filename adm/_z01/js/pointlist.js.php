<script>
// 전체 선택/해제 (disabled 체크박스 제외)
function check_all(f) {
    var chk = document.getElementsByName("chk[]");
    for (var i = 0; i < chk.length; i++) {
        // disabled 체크박스는 제외
        if (!chk[i].disabled) {
            chk[i].checked = f.chkall.checked;
        }
    }
}

// 회원 선택 팝업 열기
function open_customer_popup() {
    const winCustomer = window.open(
        './_win_customer_list.php',
        'winCustomer',
        'width=900, height=700, left=100, top=100, scrollbars=yes'
    );
    winCustomer.focus();
}

// 팝업에서 회원 선택 시 호출될 함수
function set_selected_customer(customer_id, customer_name) {
    document.getElementById('selected_customer_id').value = customer_id;
    document.getElementById('selected_customer_name').value = customer_name || '';
}

// 포인트 부여 폼 제출
function grant_point_submit(f) {
    var customer_id = f.selected_customer_id.value;
    var amount = f.grant_point_amount.value;
    
    if (!customer_id) {
        alert('회원을 선택해주세요.');
        return false;
    }
    
    if (!amount || amount <= 0) {
        alert('포인트 금액을 올바르게 입력해주세요.');
        return false;
    }
    
    if (!confirm('선택한 회원에게 ' + amount + ' 포인트를 부여하시겠습니까?')) {
        return false;
    }
    
    return true;
}

// 목록 폼 제출
function form01_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }
    
    if(document.pressed == "선택삭제") {
        if (!confirm("선택한 항목(들)을 정말 삭제 하시겠습니까?\n복구가 어려우니 신중하게 결정 하십시오.")) {
            return false;
        }
        else {
            $('input[name="w"]').val('d');
        } 
    }
    return true;
}

// 개별 포인트 삭제
function delete_single_point(index, point_id) {
    if (!confirm('정말 삭제하시겠습니까?\n복구가 어려우니 신중하게 결정 하십시오.')) {
        return false;
    }
    
    // 체크박스 체크
    var chk = document.getElementById('chk_' + index);
    if (chk) {
        chk.checked = true;
    }
    
    // w 값 설정
    document.form01.w.value = 'd';
    
    // document.pressed 설정
    document.pressed = '선택삭제';
    
    // 폼 제출
    document.form01.submit();
}
</script>

