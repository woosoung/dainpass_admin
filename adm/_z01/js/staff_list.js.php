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

document.addEventListener('DOMContentLoaded', function(){
    // 폼 제출 시 검증
    const form = document.fstafflist;
    if (form) {
        form.addEventListener('submit', function(e) {
            const actButton = document.pressed;
            const wInput = form.querySelector('input[name="w"]');

            // 선택수정 버튼을 눌렀을 때만 검증
            if (actButton === '선택수정') {
                // w 값을 'u'로 설정
                if (wInput) wInput.value = 'w';

                // 체크된 항목이 있는지 확인
                const checkedItems = form.querySelectorAll('input[name="chk[]"]:checked');
                if (checkedItems.length === 0) {
                    alert('수정할 항목을 선택해 주세요.');
                    e.preventDefault();
                    return false;
                }

                // 슬롯당 최대고객수 필드 검증
                const maxCustomersInputs = form.querySelectorAll('input[name^="max_customers_per_slot["]');
                for (let i = 0; i < maxCustomersInputs.length; i++) {
                    const input = maxCustomersInputs[i];
                    const value = parseInt(input.value);

                    if (isNaN(value) || value < 1) {
                        alert('슬롯당 최대고객수는 1명 이상이어야 합니다.');
                        input.focus();
                        e.preventDefault();
                        return false;
                    }

                    if (value > 100) {
                        alert('슬롯당 최대고객수는 최대 100명까지 입력 가능합니다.');
                        input.focus();
                        e.preventDefault();
                        return false;
                    }
                }
            } else if (actButton === '선택삭제') {
                // w 값을 'd'로 설정
                if (wInput) wInput.value = 'd';

                // 체크된 항목이 있는지 확인
                const checkedItems = form.querySelectorAll('input[name="chk[]"]:checked');
                if (checkedItems.length === 0) {
                    alert('삭제할 항목을 선택해 주세요.');
                    e.preventDefault();
                    return false;
                }

                // 삭제 확인
                if (!confirm('선택한 직원을 삭제하시겠습니까?')) {
                    e.preventDefault();
                    return false;
                }
            }

            return true;
        });
    }
});
</script>

