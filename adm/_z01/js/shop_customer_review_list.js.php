<script>
function flist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }
    
    if(document.pressed == "선택삭제") {
        if (!confirm("선택한 리뷰(들)을 정말 삭제 하시겠습니까?\n복구가 어려우니 신중하게 결정 하십시오.")) {
            return false;
        }
        else {
            $('input[name="act"]').val('delete');
            f.action = './shop_customer_review_list_update.php';
            f.w.value = 'd';
        } 
    }
    return true;
}

function flist_delete_submit()
{
    var f = document.getElementById('flist');
    if (!is_checked("chk[]")) {
        alert("삭제 하실 항목을 하나 이상 선택하세요.");
        return false;
    }
    
    if (!confirm("선택한 리뷰(들)을 정말 삭제 하시겠습니까?\n복구가 어려우니 신중하게 결정 하십시오.")) {
        return false;
    }
    
    // w hidden input 추가
    var w_input = document.createElement('input');
    w_input.type = 'hidden';
    w_input.name = 'w';
    w_input.value = 'd';
    f.appendChild(w_input);
    
    f.action = './shop_customer_review_list_update.php';
    f.submit();
}
</script>
