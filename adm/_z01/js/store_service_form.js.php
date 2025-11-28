<script>
document.addEventListener('DOMContentLoaded', function(){
    const copyIcons = document.querySelectorAll(".copy_url");
    const ssortableEl = document.getElementById('service_imgs');
    
    //초기화시 서비스이미지의 sort순서번호 업데이트
    if(ssortableEl) updateSortNumbersService();
    
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

    // 리프레시 $('#service_imgs').sortable('refresh');
    if(ssortableEl) {
        $(ssortableEl).sortable({
            update: function() {
                $(this).sortable('refresh'); // 구조갱신
                updateSortNumbersService(); // 순서번호 업데이트
            }
        });
    }

    //########### 서비스관련 멀티파일 ##############
    $('#multi_file_svci').MultiFile();

    // 시그니처 서비스 변경 시 처리
    const signatureSelect = document.getElementById('signature_yn');
    if(signatureSelect) {
        signatureSelect.addEventListener('change', function(e) {
            if (this.value === 'Y') {
                // 시그니처를 Y로 설정할 경우 확인
                if (!confirm('시그니처 서비스는 업체당 1개만 설정 가능합니다. 다른 서비스의 시그니처 설정이 해제됩니다. 계속하시겠습니까?')) {
                    this.value = 'N';
                    return false;
                }
            }
        });
    }
});

// sortable .service_li 요소를 순회하면서 .sp_sort안에 순서번호를 업데이트하는 함수
async function updateSortNumbersService() {
    const serviceItems = document.querySelectorAll('.service_li');
    let service_ids = '';
    serviceItems.forEach((li, index) => {
        // li의 속성중에 data-id값을 service_ids에 콤마로 구분해서 추가
        const dataId = li.getAttribute('data-id');
        if (dataId) {
            service_ids += (service_ids ? ',' : '') + dataId;
        }
        const sortSpan = li.querySelector('.sp_sort');
        if (sortSpan) {
            sortSpan.textContent = index + 1; // 순서대로 1, 2, 3, ...
        }
    });
    const serviceInput = document.querySelector('input[name="svci_ids"]');
    if(serviceInput) serviceInput.value = service_ids;
    //fle_sort 업데이트
    const url = '<?=G5_Z_URL?>/ajax/fle_sort_change.php';
    try{
        const data = {
            brc_ids: service_ids,
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
    if (f.service_name.value.trim() === '') {
        alert('서비스명을 입력해 주십시오.');
        f.service_name.focus();
        return false;
    }

    if (f.service_time.value.trim() === '' || parseInt(f.service_time.value) < 0) {
        alert('소요시간을 입력해 주십시오.');
        f.service_time.focus();
        return false;
    }

    // 시그니처 서비스는 1개만 가능
    if (f.signature_yn && f.signature_yn.value === 'Y') {
        // 서버에서 처리하므로 여기서는 확인만
    }

    return true;
}
</script>

