<script>
// 관리페이지권한
const auth_div = document.querySelectorAll('.auth_div');

auth_div.forEach((v) => {
    v.addEventListener('click', authClick);
});

function authClick(e){
    if(!e.target.closest('.auth_h3') && !e.target.closest('.auths')) return;

    let pel = (e.target.classList.contains('auth_h3')) ? e.target.parentNode : e.target.parentNode.parentNode;
    let auth_h3 = (e.target.classList.contains('auth_h3')) ? e.target : pel.querySelector('.auth_h3');
    let auth_li = (e.target.classList.contains('auths')) ? e.target : null;
    let auth_list = pel.querySelectorAll('.auths');

    if(e.target.classList.contains('auth_h3')){
        auth_h3.classList.toggle('unact');
        if(auth_h3.classList.contains('unact')){ // 만약 메인메뉴가 비활성화되면 하위메뉴는
            auth_list.forEach((v) => {
                v.classList.remove('unact');
            });
        }
    }
    else if(e.target.classList.contains('auths')){
        auth_li.classList.toggle('unact');
        if(auth_li.classList.contains('unact')){ // 만약 하위메뉴가 비활성화되면 메인메뉴도
            auth_h3.classList.remove('unact');
            let unact_cnt = 0;
            auth_list.forEach((v) => {
                if(v.classList.contains('unact')) unact_cnt++;
            });
            
            if(unact_cnt == auth_list.length){
                auth_h3.classList.add('unact');
                auth_list.forEach((v) => {
                    v.classList.remove('unact');
                });
            }
        }
    }
    allAuthInputUpdate();
}

function allAuthInputUpdate(){
    const set_hide_mainmenus = document.querySelector('#set_hide_mainmenus');
    const set_hide_submenus = document.querySelector('#set_hide_submenus');
    const auth_h3s = document.querySelectorAll('.auth_h3');
    const auths = document.querySelectorAll('.auths');
    let mainmenus = '';
    let submenus = '';

    let i = 0;
    auth_h3s.forEach((v) => {
        if(v.classList.contains('unact')){
            mainmenus += (i == 0)?v.getAttribute('data-code') : ',' + v.getAttribute('data-code');
            i++;
        }
    });
    set_hide_mainmenus.value = mainmenus;
    
    let n = 0;
    auths.forEach((v) => {
        if(v.classList.contains('unact')){
            submenus += (n == 0)?v.getAttribute('data-code') : ',' + v.getAttribute('data-code');
            n++;
        }
    });
    set_hide_submenus.value = submenus;  
}

// 전체비활성해제
const all_hide_clear = document.querySelector('#all_hide_clear');
all_hide_clear.addEventListener('click', allAuthInputClear);

function allAuthInputClear(){
    const main = document.querySelector('input[name="set_hide_mainmenus"]');
    const sub = document.querySelector('input[name="set_hide_submenus"]');
    const auth_h3s = document.querySelectorAll('.auth_h3.unact');
    const auths = document.querySelectorAll('.auths.unact');
    if(auth_h3s.length) {
        auth_h3s.forEach((v) => {
            v.classList.remove('unact');
        });
        main.value = '';
    }
    if(auths.length) {
        auths.forEach((v) => {
            v.classList.remove('unact');
        });
        sub.value = '';
    }
}
</script>