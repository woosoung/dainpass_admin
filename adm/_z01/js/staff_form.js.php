<script>
document.addEventListener('DOMContentLoaded', function(){
    const copyIcons = document.querySelectorAll(".copy_url");
    const ssortableEl = document.getElementById('staff_imgs');
    
    //초기화시 직원이미지의 sort순서번호 업데이트
    if(ssortableEl) updateSortNumbersStaff();
    
    copyIcons.forEach(icon => {
        icon.addEventListener("click", function () {
            const targetSpan = this.nextElementSibling;
            if (targetSpan && targetSpan.classList.contains("copied_url")) {
            const text = targetSpan.textContent;
            navigator.clipboard.writeText(text)
                .then(() => alert("텍스트가 복사되었습니다!"))
                .catch(err => {
                alert("복사에 실패했습니다.");
                console.error("Clipboard copy failed:", err);
                });
            }
        });
    });

    // 리프레시 $('#staff_imgs').sortable('refresh');
    if(ssortableEl) {
        $(ssortableEl).sortable({
            update: function() {
                $(this).sortable('refresh'); // 구조갱신
                updateSortNumbersStaff(); // 순서번호 업데이트
            }
        });
    }

    //########### 직원관련 멀티파일 ##############
    $('#multi_file_stfi').MultiFile();

    // 전화번호 필드 숫자만 입력 가능하도록 처리
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            // 숫자가 아닌 문자 제거
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        phoneInput.addEventListener('keypress', function(e) {
            // 숫자가 아닌 키 입력 차단
            if (!/[0-9]/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'Tab' && e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') {
                e.preventDefault();
            }
        });

        phoneInput.addEventListener('paste', function(e) {
            // 붙여넣기 시 숫자만 추출
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const numericOnly = pastedText.replace(/[^0-9]/g, '');
            document.execCommand('insertText', false, numericOnly);
        });
    }
});

// sortable .staff_li 요소를 순회하면서 .sp_sort안에 순서번호를 업데이트하는 함수
async function updateSortNumbersStaff() {
    const staffItems = document.querySelectorAll('.staff_li');
    let staff_ids = '';
    staffItems.forEach((li, index) => {
        // li의 속성중에 data-id값을 staff_ids에 콤마로 구분해서 추가
        const dataId = li.getAttribute('data-id');
        if (dataId) {
            staff_ids += (staff_ids ? ',' : '') + dataId;
        }
        const sortSpan = li.querySelector('.sp_sort');
        if (sortSpan) {
            sortSpan.textContent = index + 1; // 순서대로 1, 2, 3, ...
        }
    });
    const staffInput = document.querySelector('input[name="stfi_ids"]');
    if(staffInput) staffInput.value = staff_ids;
    //fle_sort 업데이트
    const url = '<?=G5_Z_URL?>/ajax/fle_sort_change.php';
    try{
        const data = {
            brc_ids: staff_ids,
        };
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
        });
        const rst = await res.text();
        
        if(!res.ok){
            throw new Error('네트워크 상태가 불안정합니다.');
        }

    }catch(e){
        console.error('Error:',e);
    }
}

function form01_submit(f) {
    if (f.name.value.trim() === '') {
        alert('이름을 입력해 주십시오.');
        f.name.focus();
        return false;
    }

    if (f.name.value.trim().length > 10) {
        alert('이름은 최대 10자까지 입력 가능합니다.');
        f.name.focus();
        return false;
    }

    if (f.phone.value.trim().length > 0) {
        const phonePattern = /^[0-9]*$/;
        if (!phonePattern.test(f.phone.value.trim())) {
            alert('전화번호는 숫자만 입력 가능합니다.');
            f.phone.focus();
            return false;
        }

        if (f.phone.value.trim().length > 20) {
            alert('전화번호는 최대 20자까지 입력 가능합니다.');
            f.phone.focus();
            return false;
        }
    }

    if (f.title.value.trim().length > 30) {
        alert('직책은 최대 30자까지 입력 가능합니다.');
        f.title.focus();
        return false;
    }

    if (f.specialty.value.trim().length > 100) {
        alert('전문분야는 최대 100자까지 입력 가능합니다.');
        f.specialty.focus();
        return false;
    }

    if (f.max_customers_per_slot.value.trim() === '' || parseInt(f.max_customers_per_slot.value) < 1) {
        alert('슬롯당 최대고객수를 입력해 주십시오.');
        f.max_customers_per_slot.focus();
        return false;
    }

    const maxCustomers = parseInt(f.max_customers_per_slot.value);
    if (maxCustomers > 100) {
        alert('슬롯당 최대고객수는 최대 100명까지 입력 가능합니다.');
        f.max_customers_per_slot.focus();
        return false;
    }

    return true;
}
</script>

