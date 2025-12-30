<script>
// 가맹점 선택 팝업 열기
function open_shop_popup() {
    const winShop = window.open(
        './_win_shop_select.php',
        'winShop',
        'width=900, height=700, left=100, top=100, scrollbars=yes'
    );
    if (winShop) {
        winShop.focus();
    }
}

// 팝업에서 가맹점 선택 시 호출될 함수
function set_selected_shop(shop_id, shop_name) {
    document.getElementById('ser_shop_id').value = shop_id;
    document.getElementById('ser_shop_name_display').value = shop_name || '';
}

// 가맹점 선택 초기화
function clear_shop_selection() {
    document.getElementById('ser_shop_id').value = '';
    document.getElementById('ser_shop_name_display').value = '';
}

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
</script>
