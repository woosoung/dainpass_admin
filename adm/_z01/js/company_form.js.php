<script>
document.addEventListener('DOMContentLoaded', function(){
    const sortableEl = document.getElementById('cat_ul');
    // 리프레시 $('#cat_ul').sortable('refresh');
    $(sortableEl).sortable({
        update: function() {
            $(this).sortable('refresh'); // 구조갱신
            updateSortNumbers(); // 순서번호 업데이트
        }
    });

    // .cat_li 요소 내의 .fa-times 아이콘 클릭시 해당 li 요소를 삭제
    sortableEl.addEventListener('click', function(e) {
        if (e.target.classList.contains('fa-times')) {
            const li = e.target.closest('li');
            if (li) {
                li.remove();
                updateSortNumbers(); // 순서번호 업데이트
                $(sortableEl).sortable('refresh');
            }
        }
    });


    //############## 카테고리 항목 추가 버튼  ##############
    document.getElementById('cat_add').addEventListener('click', function() {
        let cat1Select = document.getElementById('cat1');
        let cat2Select = document.getElementById('cat2');
        if(cat1Select.value === ''){
            alert('대분류를 선택해 주십시오.');
            cat1Select.focus();
            return false;
        }
        else if(cat2Select.value === ''){
            alert('중분류를 선택해 주십시오.');
            cat2Select.focus();
            return false;
        }
        // 만약 기존 .cat_li 요소중에 data-id값이 동일한게 있으면 추가하지 않음
        const existingItem = document.querySelector(`.cat_li[data-id="${cat2Select.value}"]`);
        if (existingItem) {
            alert('이미 추가된 카테고리입니다.');
            return false;
        }

        const newItem = document.createElement('li');
        newItem.classList.add('cat_li');
        newItem.setAttribute('data-id', cat2Select.value);
        newItem.textContent = cat2Select.options[cat2Select.selectedIndex].text;
        // 기존의 .cat_li 요소의 갯수를 카운팅
        const oldCatLiCount = document.querySelectorAll('.cat_li').length;
        newItem.innerHTML = `<span class="sp_sort">${oldCatLiCount + 1}</span>`;
        newItem.innerHTML += `<span class="sp_cat">${cat2Select.options[cat2Select.selectedIndex].text}</span>`;
        newItem.innerHTML += `<i class="fa fa-times" aria-hidden="true"></i>`;
        sortableEl.appendChild(newItem);
        $(sortableEl).sortable('refresh');
        updateSortNumbers(); // 순서번호 업데이트
    });

    // 업체담당자를 설정하는 이벤트
    document.getElementById("btn_manager").addEventListener("click", function(e) {
        e.preventDefault(); // return false와 동일한 효과

        const shop_id = this.getAttribute("shop_id");
        const href = "./shop_manager_list.php?shop_id=" + shop_id;

        const winShopMember = window.open(
            href,
            "winShopMember",
            "left=100,top=100,width=520,height=600,scrollbars=1"
        );
        if (winShopMember) {
            winShopMember.focus();
        }
    });

    // console.log(cats);
    //########### 업체관련 멀티파일 ##############
    $('#multi_file_comf').MultiFile();
    $('#multi_file_comi').MultiFile();

    const com_select = document.querySelector('.com_select');
    com_select.addEventListener('click', () => {
        const url = com_select.getAttribute('data-url') + '?file_name=' + file_name;
        const win_com_select = window.open(url, "win_com_select", "width=500,height=540,scrollbars=yes");
        win_com_select.focus();
        return false;
    });
    
    document.getElementById('cat1').addEventListener('change', function(e) {
        let selectedValue = e.target.value;
        let cat2Select = document.getElementById('cat2');
        cat2Select.innerHTML = '<option value="">::중분류선택::</option>';
        if(selectedValue !== ''){ // 대분류값이 존재하는 option으로 변경되었을때
            for(let ck in cats[selectedValue]['mid']){
                let opt = document.createElement("option");
                opt.value = ck;
                opt.textContent = cats[selectedValue]['mid'][ck];
                cat2Select.appendChild(opt);
            }
        }
    });

    // ############ 관리메뉴 선택박스 관련 ############
    const inputBox = document.querySelector('input[name="mng_menus"]');
    const checkboxes = document.querySelectorAll('.mng_menu');

    // 초기값($com['mng_menus']) 기반 체크 처리
    const initVal = inputBox.value.trim();
    if (initVal) {
        const selectedVals = initVal.split(',');
        checkboxes.forEach(chk => {
            if (selectedVals.includes(chk.dataset.val)) {
                chk.checked = true;
            }
        });
    }

    // 체크박스 변경 시 input 값 업데이트
    function updateInput() {
        const checkedVals = Array.from(checkboxes)
            .filter(chk => chk.checked)
            .map(chk => chk.dataset.val);
        inputBox.value = checkedVals.join(',');
    }

    checkboxes.forEach(chk => {
        chk.addEventListener('change', updateInput);
    });
});

// sortable .cat_li 요소를 순회하면서 .sp_sort안에 순서번호를 업데이트하는 함수
function updateSortNumbers() {
    const categoryItems = document.querySelectorAll('.cat_li');
    let cate_ids = '';
    categoryItems.forEach((li, index) => {
        // li의 속성중에 data-id값을 cate_ids에 콤마로 구분해서 추가
        const dataId = li.getAttribute('data-id');
        if (dataId) {
            cate_ids += (cate_ids ? ',' : '') + dataId;
        }
        const sortSpan = li.querySelector('.sp_sort');
        if (sortSpan) {
            sortSpan.textContent = index + 1; // 순서대로 1, 2, 3, ...
        }
    });
    document.querySelector('input[name="category_ids"]').value = cate_ids;
}

function form01_submit(f) {
    if (f.com_name.value.trim() === '') {
        alert('업체명을 입력해 주십시오.');
        f.com_name.focus();
        return false;
    }

    if (f.com_email.value == '') {
        alert('이메일을 입력해 주십시오2.');
        f.com_email.focus();
        return false;
    }
    // 이메일 검증에 사용할 정규식 (이메일정규식)
    var emailRegExp = /^[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*@[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*.[a-zA-Z]{2,3}$/i;
    if (f.com_email.value.match(emailRegExp) == null) {
        alert("올바른 이메일 주소가 아닙니다.");
        f.com_email.focus();
        return false; 
    }


    if (f.com_president.value == '') {
        alert('대표자명을 입력해 주십시오.');
        f.com_president.focus();
        return false;
    }

    if (f.com_tel.value == '') {
        alert('업체전화번호를 입력해 주십시오.');
        f.com_tel.focus();
        return false;
    }

    // 사업자번호에 해당하는 정보가 있으면 사업자번호 검증을 함(사업자번호정규식)
    if(f.com_biz_no.value.trim() !== ''){
        var bizNoRegExp = /^(\d{3}-\d{2}-\d{5}|\d{10})$/;
        if(f.com_biz_no.value.match(bizNoRegExp) == null){
            alert("올바른 사업자번호가 아닙니다.");
            f.com_biz_no.focus();
            return false;
        }
    }

    // 홈페이지 주소가 있으면 홈페이지 주소 검증을 함(홈페이지주소정규식,도메인정규식,URL정규식,url정규식)
    if(f.com_url.value.trim() !== ''){
        var urlRegExp = /^(https?:\/\/)?(www\.)?[a-zA-Z0-9-]+(\.[a-zA-Z]{2,})+(\.[a-zA-Z]{2,})?(\S*)?$/;
        if(f.com_url.value.match(urlRegExp) == null){
            alert("올바른 홈페이지 주소가 아닙니다.");
            f.com_url.focus();
            return false;
        }
    }

    return true;
}
</script>