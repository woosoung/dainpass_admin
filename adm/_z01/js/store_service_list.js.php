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

    // 폼 제출 시 검증
    const form = document.fstorelist;
    if (form) {
        form.addEventListener('submit', function(e) {
            const actButton = document.pressed;

            // 선택수정 버튼을 눌렀을 때만 검증
            if (actButton === '선택수정') {
                // 체크된 항목이 있는지 확인
                const checkedItems = form.querySelectorAll('input[name="chk[]"]:checked');
                if (checkedItems.length === 0) {
                    alert('수정할 항목을 선택해 주세요.');
                    e.preventDefault();
                    return false;
                }

                // 소요시간 필드 검증
                const serviceTimeInputs = form.querySelectorAll('input[name^="service_time["]');
                for (let i = 0; i < serviceTimeInputs.length; i++) {
                    const input = serviceTimeInputs[i];
                    const value = parseInt(input.value);

                    if (isNaN(value) || value < 0) {
                        alert('소요시간은 0분 이상이어야 합니다.');
                        input.focus();
                        e.preventDefault();
                        return false;
                    }

                    if (value > 1440) {
                        alert('소요시간은 최대 1440분(24시간)까지 입력 가능합니다.');
                        input.focus();
                        e.preventDefault();
                        return false;
                    }
                }
            } else if (actButton === '선택삭제') {
                // 체크된 항목이 있는지 확인
                const checkedItems = form.querySelectorAll('input[name="chk[]"]:checked');
                if (checkedItems.length === 0) {
                    alert('삭제할 항목을 선택해 주세요.');
                    e.preventDefault();
                    return false;
                }

                // 삭제 확인
                if (!confirm('선택한 서비스를 삭제하시겠습니까?')) {
                    e.preventDefault();
                    return false;
                }
            }

            return true;
        });
    }
});
</script>

