<script>
document.addEventListener("DOMContentLoaded", function() {
    const btnGnb = document.querySelector("#btn_gnb"); //최상단 좌측 아이콘
    const logoA = document.querySelector("#logo a");
    const logoImg = document.querySelector("#logo a img");
    const shopBtn = document.querySelector('#tnb .tnb_shop');
    const homeBtn = document.querySelector('#tnb .tnb_community');
    // 기존 '관리자'로 고정되어 있는 것을 로그인한 회원 이름으로 변경
    const tnb_mb_btn = document.querySelector('.tnb_mb_btn');
    if(tnb_mb_btn){
        let tnb_mb_btn_txt = tnb_mb_btn.childNodes[0];
        tnb_mb_btn_txt.nodeValue = mb_name;
    }

    if (btnGnb) {
        // 이전 텍스트를 제거합니다.
        btnGnb.textContent = '';

        // <i> 태그를 생성하고 클래스를 추가합니다.
        var icon1 = document.createElement("i");
        icon1.className = "bi bi-arrow-left-square";
        var icon2 = document.createElement("i");
        icon2.className = "bi bi-arrow-right-square";
        
        // <i> 태그를 #btn_gnb에 삽입합니다.
        btnGnb.appendChild(icon1);
        btnGnb.appendChild(icon2);
    }

    // 최상단 ADMINISTRATOR 로고이미지를 제거하고 텍스트로 변경합니다.
    if (logoImg) {
        logoImg.remove();
        logoA.textContent = 'CONFIG SET';
    }

    // 최상산 shop버튼 대체
    if (shopBtn) {
        // 이전 텍스트를 제거합니다.
        shopBtn.textContent = '';
        // <i> 태그를 생성하고 클래스를 추가합니다.
        var icon1 = document.createElement("i");
        icon1.className = "bi bi-bag";
        // <i> 태그를 shopBtn에 삽입합니다.
        shopBtn.appendChild(icon1);
    }

    // 최상산 home버튼 대체
    if (homeBtn) {
        // 이전 텍스트를 제거합니다.
        homeBtn.textContent = '';
        // <i> 태그를 생성하고 클래스를 추가합니다.
        var icon1 = document.createElement("i");
        icon1.className = "bi bi-house";
        // <i> 태그를 shopBtn에 삽입합니다.
        homeBtn.appendChild(icon1);
    }


    // 필수라는 의미의 *를 붙이기 위한 조건에 맞는 요소들을 찾습니다.
    // const elements = Array.from(document.getElementsByClassName('sound_only')).filter(el => el.textContent.includes('필수'));
    const elements = Array.from(document.getElementsByClassName('sound_only')).filter(el => {
        const nextEl = el.nextElementSibling;
        return el.textContent.includes('필수') && (!nextEl || nextEl.id !== 'stx');
    });

    // elements 배열의 길이가 0보다 큰 경우에만 코드를 실행합니다.
    if (elements.length > 0) {
        // 각 요소에 대해 새로운 span을 생성하고 추가합니다.
        elements.forEach(element => {
            // 새로운 span 요소를 생성합니다.
            const newSpan = document.createElement('span');
            newSpan.className = 'require';
    
            // 새로운 span을 현재 요소의 바로 다음에 삽입합니다.
            element.parentNode.insertBefore(newSpan, element.nextSibling);
        });
    }

    // 불필요한 require 클래스를 가진 요소들을 찾아 제거합니다.
    if(file_name == 'auth_list'
        || file_name == 'personalpaylist'
        || file_name == 'itemqalist'
        || file_name == 'itemuselist'
        || file_name == 'itemstocklist'
        || file_name == 'itemtypelist'
        || file_name == 'optionstocklist'
        || file_name == 'couponlist'
        || file_name == 'couponzonelist'
        || file_name == 'inorderlist'
        || file_name == 'itemstocksms'
        || file_name == 'itemeventlist'
        || file_name == 'history_list'
        || file_name == 'history_num'
        || file_name == 'form_group'
        || file_name == 'form_list'
        || file_name == 'num_group'
        || file_name == 'num_book'
        || file_name == '_win_company_select'
    ){
        const span_require = document.querySelectorAll('.require');
        span_require.forEach((el) => {
            el.remove();
        });
    }
    
    // 관리자단에서 favicon 파비콘을 title태그 다음에 추가
    var title = document.querySelector('title');
    var faviEle = document.createElement('link');
    faviEle.rel = 'icon';
    faviEle.type = 'image/png';
    faviEle.href = '<?=$set_com['favicon_url']?>'; // PHP 변수가 적절히 처리되었다고 가정
    if(title) title.insertAdjacentElement('afterend', faviEle);

});
</script>