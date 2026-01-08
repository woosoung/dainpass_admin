<script>
function form01_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택수정") {
        $('input[name="w"]').val('u');
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

