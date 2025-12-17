<script>
document.addEventListener('DOMContentLoaded', function(){
    const copyIcons = document.querySelectorAll(".copy_url");
    const rsortableEl = document.getElementById('review_imgs');
    
    //초기화시 리뷰이미지의 sort순서번호 업데이트
    if(rsortableEl) updateSortNumbersReview();
    
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

    // 리프레시 $('#review_imgs').sortable('refresh');
    if(rsortableEl) {
        $(rsortableEl).sortable({
            update: function() {
                $(this).sortable('refresh'); // 구조갱신
                updateSortNumbersReview(); // 순서번호 업데이트
            }
        });
    }

    //########### 리뷰관련 멀티파일 ##############
    $('#multi_file_rvwi').MultiFile();
});

// sortable .review_li 요소를 순회하면서 .sp_sort안에 순서번호를 업데이트하는 함수
async function updateSortNumbersReview() {
    const reviewItems = document.querySelectorAll('.review_li');
    let review_ids = '';
    reviewItems.forEach((li, index) => {
        // li의 속성중에 data-id값을 review_ids에 콤마로 구분해서 추가
        const dataId = li.getAttribute('data-id');
        if (dataId) {
            review_ids += (review_ids ? ',' : '') + dataId;
        }
        const sortSpan = li.querySelector('.sp_sort');
        if (sortSpan) {
            sortSpan.textContent = index + 1; // 순서대로 1, 2, 3, ...
        }
    });
    const reviewInput = document.querySelector('input[name="rvwi_ids"]');
    if(reviewInput) reviewInput.value = review_ids;
    //fle_sort 업데이트
    const url = '<?=G5_Z_URL?>/ajax/fle_sort_change.php';
    try{
        const data = {
            brc_ids: review_ids,
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

function freviewform_submit(f) {
    return true;
}
</script>
