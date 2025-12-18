<script>
document.addEventListener('DOMContentLoaded', function(){
    const copyIcons = document.querySelectorAll(".copy_url");
    const sortableEl = document.getElementById('cat_ul');
    const bsortableEl = document.getElementById('branch_imgs');
    const kboxsortableEl = document.getElementById('keyword_box');
    //초기화시 가맹점이미지의 sort순서번호 업데이트
    if(bsortableEl) updateSortNumbersBranch();
    //초기화시 검색키워드의 sort순서번호 업데이트
    if(kboxsortableEl) updateSortKeywords();

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

    // 리프레시 $('#cat_ul').sortable('refresh');
    if(sortableEl) {
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
    }

    // 리프레시 $('#branch_imgs').sortable('refresh');
    if(bsortableEl) {
        $(bsortableEl).sortable({
            update: function() {
                $(this).sortable('refresh'); // 구조갱신
                updateSortNumbersBranch(); // 순서번호 업데이트
            }
        });
    }

    // 리프레시 $('#keyword_box').sortable('refresh');
    if(kboxsortableEl) {
        $(kboxsortableEl).sortable({
            //드래그 기준 DOM 요소
            // handle: '.sp_cont',
            update: function() {
                $(this).sortable('refresh'); // 구조갱신
                updateSortKeywords(); // 순서번호 업데이트
            }
        });
    }


    //############## 카테고리 항목 추가 버튼  ##############
    const catAddBtn = document.getElementById('cat_add');
    if(catAddBtn) {
        catAddBtn.addEventListener('click', function() {
            let cat1Select = document.getElementById('cat1');
            let cat2Select = document.getElementById('cat2');
            // 기존의 .cat_li 요소의 갯수를 카운팅
            const oldCatLiCount = document.querySelectorAll('.cat_li').length;
            if(oldCatLiCount){
                if(oldCatLiCount >= 1){
                    alert('카테고리는 최대 1개까지만 등록 가능합니다.');
                    return false;
                }
            }
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
            
            newItem.innerHTML = `<span class="sp_sort">${oldCatLiCount + 1}</span>`;
            newItem.innerHTML += `<span class="sp_cat">${cat2Select.options[cat2Select.selectedIndex].text}</span>`;
            newItem.innerHTML += `<i class="fa fa-times" aria-hidden="true"></i>`;
            sortableEl.appendChild(newItem);
            $(sortableEl).sortable('refresh');
            updateSortNumbers(); // 순서번호 업데이트
        });
    }

    // 업체담당자를 설정하는 이벤트
    const btnManager = document.getElementById("btn_manager");
    if (btnManager) {
        btnManager.addEventListener("click", function(e) {
            e.preventDefault(); // return false와 동일한 효과

            const shop_id = this.getAttribute("shop_id");
            const href = "./shop_manager_list.php?shop_id=" + shop_id;

            const winShopMember = window.open(
                href,
                "winShopMember",
                "left=100,top=100,width=620,height=760,scrollbars=1"
            );
            if (winShopMember) {
                winShopMember.focus();
            }
        });
    }

    // console.log(cats);
    //########### 업체관련 멀티파일 ##############
    $('#multi_file_comf').MultiFile();
    $('#multi_file_comi').MultiFile();
    
    const cat1Select = document.getElementById('cat1');
    if(cat1Select) {
        cat1Select.addEventListener('change', function(e) {
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
    }

    // ############ 관리메뉴 선택박스 관련 ############
    const inputBox = document.querySelector('input[name="mng_menus"]');
    const checkboxes = document.querySelectorAll('.mng_menu');

    if (inputBox) {
        // 공간관리(930600)와 공간그룹관리(930550) 동기화
        const SPACE_MENU = '930600'; // 공간관리
        const SPACE_GROUP_MENU = '930550'; // 공간그룹관리
        
        // 초기값($com['mng_menus']) 기반 체크 처리 및 동기화
        const initVal = inputBox.value.trim();
        if (initVal) {
            let selectedVals = initVal.split(',').map(v => v.trim());
            
            // 초기 로드 시 둘 중 하나만 선택되어 있으면 나머지도 자동 선택
            if (selectedVals.includes(SPACE_MENU) && !selectedVals.includes(SPACE_GROUP_MENU)) {
                selectedVals.push(SPACE_GROUP_MENU);
            } else if (selectedVals.includes(SPACE_GROUP_MENU) && !selectedVals.includes(SPACE_MENU)) {
                selectedVals.push(SPACE_MENU);
            }
            
            checkboxes.forEach(chk => {
                if (selectedVals.includes(chk.dataset.val)) {
                    chk.checked = true;
                }
            });
            
            // 동기화된 값으로 hidden input 업데이트
            inputBox.value = selectedVals.join(',');
        }

        // 체크박스 변경 시 input 값 업데이트
        function updateInput() {
            const checkedVals = Array.from(checkboxes)
                .filter(chk => chk.checked)
                .map(chk => chk.dataset.val);
            inputBox.value = checkedVals.join(',');
        }

        checkboxes.forEach(chk => {
            chk.addEventListener('change', function() {
                const currentVal = this.dataset.val;
                const isChecked = this.checked;
                
                // 공간관리 또는 공간그룹관리가 변경되면 상대방도 동기화
                if (currentVal === SPACE_MENU || currentVal === SPACE_GROUP_MENU) {
                    const targetVal = (currentVal === SPACE_MENU) ? SPACE_GROUP_MENU : SPACE_MENU;
                    const targetCheckbox = Array.from(checkboxes).find(cb => cb.dataset.val === targetVal);
                    
                    if (targetCheckbox) {
                        targetCheckbox.checked = isChecked;
                    }
                }
                
                updateInput();
            });
        });
    }

    // ############ 검색 키워드 관리 ############
    const keywordInput = document.getElementById('input_keyword');
    const keywordHidden = document.getElementById('shop_keywords');
    const keywordBox = document.getElementById('keyword_box');
    const addKeywordBtn = document.getElementById('add_keyword');

    if (keywordInput && keywordHidden && keywordBox && addKeywordBtn) {
        let keywords = [];

        const normalizeKeyword = (kw) => kw.trim();

        const loadInitialKeywords = () => {
            const initVal = keywordHidden.value || '';
            keywords = initVal
                .split(',')
                .map(kw => normalizeKeyword(kw))
                .filter(kw => kw.length);
            renderKeywordChips();
        };

        const renderKeywordChips = () => {
            keywordBox.innerHTML = '';
            keywords.forEach((kw, index) => {
                const chip = document.createElement('span');
                chip.className = 'keyword-chip inline-flex items-center bg-gray-200 rounded px-2 py-1 mr-2 mb-2';
                chip.dataset.index = String(index);

                const label = document.createElement('span');
                label.className = 'sp_cont';
                label.textContent = kw;
                chip.appendChild(label);

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'ml-2 text-red-500';
                removeBtn.setAttribute('aria-label', '키워드 삭제');
                removeBtn.textContent = '×';
                chip.appendChild(removeBtn);

                keywordBox.appendChild(chip);
            });
            keywordHidden.value = keywords.join(', ');
        };

        const validateKeyword = (raw) => {
            if (!raw.length) {
                alert('키워드를 입력해 주세요.');
                return false;
            }
            if (raw.includes(',')) {
                alert('키워드에는 쉼표를 포함할 수 없습니다.');
                return false;
            }
            const spacePattern = /^[^\s]+(?:\s[^\s]+)?$/;
            if (!spacePattern.test(raw)) {
                alert('키워드는 공백을 최대 1개까지만 사용할 수 있습니다.');
                return false;
            }
            const allowedPattern = /^[A-Za-z0-9가-힣](?:[A-Za-z0-9가-힣'\-]*[A-Za-z0-9가-힣])?(?:\s[A-Za-z0-9가-힣](?:[A-Za-z0-9가-힣'\-]*[A-Za-z0-9가-힣])?)?$/;
            if (!allowedPattern.test(raw)) {
                alert("키워드는 영문/숫자/한글과 내부의 ' 또는 - 만 사용할 수 있으며, 특수문자는 단어의 처음과 끝에 올 수 없습니다.");
                return false;
            }
            return true;
        };

        addKeywordBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const rawValue = keywordInput.value.trim();
            if (!validateKeyword(rawValue)) {
                keywordInput.focus();
                return;
            }

            if (keywords.length >= 10) {
                alert('키워드는 최대 10개까지 등록할 수 있습니다.');
                keywordInput.focus();
                return;
            }

            const normalized = normalizeKeyword(rawValue);
            if (keywords.includes(normalized)) {
                alert('이미 등록된 키워드입니다.');
                keywordInput.value = '';
                keywordInput.focus();
                return;
            }

            keywords.push(normalized);
            renderKeywordChips();
            keywordInput.value = '';
            keywordInput.focus();
        });

        keywordBox.addEventListener('click', function(e) {
            const target = e.target;
            if (target.tagName === 'BUTTON' && target.parentElement && target.parentElement.dataset.index) {
                const idx = parseInt(target.parentElement.dataset.index, 10);
                if (!Number.isNaN(idx)) {
                    keywords.splice(idx, 1);
                    renderKeywordChips();
                }
            }
        });

        loadInitialKeywords();
    }

    // ############ 편의시설 버튼 토글 관리 ############
    const amenitiesHidden = document.getElementById('amenities_id_list');
    const amenityButtons = document.querySelectorAll('.amenity-btn');

    if (amenitiesHidden && amenityButtons.length > 0) {
        // 초기 선택된 편의시설 ID 배열
        let selectedAmenityIds = [];
        const initVal = amenitiesHidden.value.trim();
        if (initVal) {
            selectedAmenityIds = initVal.split(',').map(id => id.trim()).filter(id => id.length > 0);
        }

        // 버튼 클릭 이벤트 처리
        amenityButtons.forEach(btn => {
            const amenityId = btn.getAttribute('data-amenity-id');
            const amenityName = btn.getAttribute('data-amenity-name');
            const isInitiallyActive = selectedAmenityIds.includes(amenityId);

            // 초기 상태 설정
            if (isInitiallyActive) {
                btn.classList.remove('bg-gray-200', 'text-gray-700');
                btn.classList.add('bg-blue-500', 'text-white');
            }

            // 버튼 클릭 이벤트
            btn.addEventListener('click', function() {
                const currentAmenityId = this.getAttribute('data-amenity-id');
                const isActive = this.classList.contains('bg-blue-500');
                const iconEnabled = this.getAttribute('data-icon-enabled');
                const iconDisabled = this.getAttribute('data-icon-disabled');
                const iconImg = this.querySelector('.amenity-icon');

                if (isActive) {
                    // 비활성화: 선택된 목록에서 제거
                    selectedAmenityIds = selectedAmenityIds.filter(id => id !== currentAmenityId);
                    this.classList.remove('bg-blue-500', 'text-white');
                    this.classList.add('bg-gray-200', 'text-gray-700');
                    // 아이콘 변경
                    if (iconImg && iconDisabled) {
                        iconImg.src = iconDisabled;
                        iconImg.style.display = 'inline-block';
                    } else if (iconImg && !iconDisabled) {
                        iconImg.style.display = 'none';
                    }
                } else {
                    // 활성화: 선택된 목록에 추가
                    if (!selectedAmenityIds.includes(currentAmenityId)) {
                        selectedAmenityIds.push(currentAmenityId);
                    }
                    this.classList.remove('bg-gray-200', 'text-gray-700');
                    this.classList.add('bg-blue-500', 'text-white');
                    // 아이콘 변경
                    if (iconImg && iconEnabled) {
                        iconImg.src = iconEnabled;
                        iconImg.style.display = 'inline-block';
                    } else if (iconImg && !iconEnabled) {
                        iconImg.style.display = 'none';
                    }
                }

                // hidden input 값 갱신
                amenitiesHidden.value = selectedAmenityIds.join(',');
            });
        });
    }
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
    const categoryInput = document.querySelector('input[name="category_ids"]');
    if(categoryInput) categoryInput.value = cate_ids;
}
// sortable .branch_li 요소를 순회하면서 .sp_sort안에 순서번호를 업데이트하는 함수
async function updateSortNumbersBranch() {
    const categoryItems = document.querySelectorAll('.branch_li');
    let branch_ids = '';
    categoryItems.forEach((li, index) => {
        // li의 속성중에 data-id값을 cate_ids에 콤마로 구분해서 추가
        const dataId = li.getAttribute('data-id');
        if (dataId) {
            branch_ids += (branch_ids ? ',' : '') + dataId;
        }
        const sortSpan = li.querySelector('.sp_sort');
        if (sortSpan) {
            sortSpan.textContent = index + 1; // 순서대로 1, 2, 3, ...
        }
    });
    const branchInput = document.querySelector('input[name="branch_ids"]');
    if(branchInput) branchInput.value = branch_ids;
    //fle_sort 업데이트
    const url = '<?=G5_Z_URL?>/ajax/fle_sort_change.php';
    try{
        const data = {
            brc_ids: branch_ids,
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

// 
function updateSortKeywords() {
    const keywordItems = document.querySelectorAll('.keyword-chip');
    let keywords = [];
    keywordItems.forEach((chip, index) => {
        const label = chip.querySelector('span.sp_cont');
        if (label) {
            keywords.push(label.textContent);
        }
        const sortSpan = chip.querySelector('.sp_sort');
        if (sortSpan) {
            sortSpan.textContent = index + 1; // 순서대로 1, 2, 3, ...
        }
    });
    const shopKeywordsInput = document.getElementById('shop_keywords');
    if(shopKeywordsInput) shopKeywordsInput.value = keywords.join(', ');
}

function form01_submit(f) {
    if (f.name.value.trim() === '') {
        alert('업체명을 입력해 주십시오.');
        f.name.focus();
        return false;
    }

    // 업체명 길이 검증 (30자 제한)
    if (f.name.value.length > 30) {
        alert('업체명은 최대 30자까지 입력 가능합니다.');
        f.name.focus();
        return false;
    }

    if (f.business_no.value.trim() === '') {
        alert('사업자등록번호를 입력해 주십시오.');
        f.business_no.focus();
        return false;
    }

    if (f.shop_name.value.trim() === '') {
        alert('가맹점명을 입력해 주십시오.');
        f.shop_name.focus();
        return false;
    }

    // 가맹점명 길이 검증 (50자 제한)
    if (f.shop_name.value.length > 50) {
        alert('가맹점명은 최대 50자까지 입력 가능합니다.');
        f.shop_name.focus();
        return false;
    }

    if (f.branch.value.trim() === '') {
        alert('지점명을 입력해 주십시오.');
        f.branch.focus();
        return false;
    }

    // 지점명 길이 검증 (30자 제한)
    if (f.branch.value.length > 30) {
        alert('지점명은 최대 30자까지 입력 가능합니다.');
        f.branch.focus();
        return false;
    }

    if (f.contact_email.value == '') {
        alert('대표이메일을 입력해 주십시오.');
        f.contact_email.focus();
        return false;
    }
    // 이메일 검증에 사용할 정규식 (이메일정규식)
    var emailRegExp = /^[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*@[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*.[a-zA-Z]{2,3}$/i;
    if (f.contact_email.value.match(emailRegExp) == null) {
        alert("올바른 이메일 주소가 아닙니다.");
        f.contact_email.focus();
        return false; 
    }


    if (f.owner_name.value == '') {
        alert('대표자명을 입력해 주십시오.');
        f.owner_name.focus();
        return false;
    }

    if (f.contact_phone.value == '') {
        alert('업체전화번호를 입력해 주십시오.');
        f.contact_phone.focus();
        return false;
    }

    // 사업자번호에 해당하는 정보가 있으면 사업자번호 검증을 함(사업자번호정규식)
    if(f.business_no.value.trim() !== ''){
        var bizNoRegExp = /^(\d{3}-\d{2}-\d{5}|\d{10})$/;
        if(f.business_no.value.match(bizNoRegExp) == null){
            alert("올바른 사업자번호가 아닙니다.");
            f.business_no.focus();
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

    // URL 필드 길이 검증 (500자 제한)
    if(f.blog_url && f.blog_url.value.length > 500){
        alert("블로그 URL은 최대 500자까지 입력 가능합니다.");
        f.blog_url.focus();
        return false;
    }

    if(f.instagram_url && f.instagram_url.value.length > 500){
        alert("인스타그램 URL은 최대 500자까지 입력 가능합니다.");
        f.instagram_url.focus();
        return false;
    }

    if(f.kakaotalk_url && f.kakaotalk_url.value.length > 500){
        alert("카카오톡 채널 URL은 최대 500자까지 입력 가능합니다.");
        f.kakaotalk_url.focus();
        return false;
    }

    return true;
}

// 가맹점 탈퇴 확인
function confirm_close(f) {
    var msg = '정말 탈퇴하시겠습니까?\n\n';
    msg += '탈퇴 시 가맹점 정보 및 관리자 계정이 비활성화되며,\n';
    msg += '모든 서비스가 즉시 중단됩니다.';

    return confirm(msg);
}
</script>

