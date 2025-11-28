<script>
function check_all(f) {
    if (f.chkall.checked) {
        for (var i = 0; i < f.length; i++) {
            if (f[i].name == "chk[]") {
                f[i].checked = true;
            }
        }
    } else {
        for (var i = 0; i < f.length; i++) {
            if (f[i].name == "chk[]") {
                f[i].checked = false;
            }
        }
    }
}

// 시그니처 서비스 변경 시 처리
document.addEventListener('DOMContentLoaded', function(){
    const signatureSelects = document.querySelectorAll('.signature_select');
    signatureSelects.forEach(function(select) {
        select.addEventListener('change', function(e) {
            if (this.value === 'Y') {
                // 다른 시그니처 선택들을 N으로 변경
                signatureSelects.forEach(function(otherSelect) {
                    if (otherSelect !== select && otherSelect.value === 'Y') {
                        otherSelect.value = 'N';
                    }
                });
            }
        });
    });
});
</script>

