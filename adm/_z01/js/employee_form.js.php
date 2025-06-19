<script>
//사원관련 멀티파일
$('#multi_file_emp').MultiFile();

const reg_mb_id = document.querySelector('#reg_mb_id');
let mb_id_flag = true;
let mb_email_flag = true;
let mb_nick_flag = true;
let mb_hp_flag = true;

<?php if($w=='') { //등록모드일때 ################################### ?>
const url = '<?=G5_Z_URL?>/ajax/mb_id_check.php';
const s_id_info = document.querySelector('.s_id_info');
const mb_id_pattern = /^[a-zA-Z][a-zA-Z0-9]{4,19}$/;
let timer;
reg_mb_id.addEventListener('keydown', function(){
    clearTimeout(timer);
    timer = setTimeout(mbIdCheck, 500);
});

async function mbIdCheck(){
    const mb_id = reg_mb_id.value.trim();

    // 입력값 유효성 검사
    if(mb_id.length === 0){
        return;
    }

    // 최소5글자이상 최대 20글자 미만
    if(mb_id.length < 5 || mb_id.length > 20){
        s_id_info.textContent = '아이디는 5글자 이상, 20글자 미만이어야 합니다.';
        s_id_info.style.color = 'red';
        mb_id_flag = false;
        return;
    }

    // 최대20글자이하
    if(!mb_id_pattern.test(mb_id)){
        s_id_info.textContent = '아이디는 영문 또는 영문숫자 조합으로 해서 5글자 이상, 20글자 미만이어야 합니다.';
        s_id_info.style.color = 'red';
        mb_id_flag = false;
        return;
    }

    try{
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'mb_id=' + encodeURIComponent(mb_id),
        });

        if(!res.ok){
            throw new Error('네트워크 상태가 불안정합니다.');
        }

        const rst = await res.text();
        
        if(rst == '1'){
            s_id_info.textContent = '사용가능한 아이디입니다.';
            s_id_info.style.color = 'blue';
            mb_id_flag = true;
        }
        else if(rst == '0'){
            s_id_info.textContent = '이미 사용중인 아이디입니다.';
            s_id_info.style.color = 'red';
            mb_id_flag = false;
        }
        else{
            throw new Error('서버 오류가 발생했습니다.');
            s_id_info.textContent = '';
            s_id_info.style.color = 'black';
            mb_id_flag = false;
        }
    }
    catch(err){
        console.error('Error:',err);
        s_id_info.textContent = err;
        s_id_info.style.color = 'red';
        mb_id_flag = false;
    }
}
<?php } ?>
const url2 = '<?=G5_Z_URL?>/ajax/email_check.php';
const reg_mb_email = document.querySelector('#reg_mb_email');
const s_email_info = document.querySelector('.s_email_info');
const mb_email_pattern = /^[^ ]+@[^ ]+\.[a-z]{2,4}$/;
let timer2;
reg_mb_email.addEventListener('keydown', function(){
    clearTimeout(timer2);
    timer2 = setTimeout(mbEmailCheck, 500);
});

async function mbEmailCheck(){
    const mb_id = reg_mb_id.value.trim();
    const mb_email = reg_mb_email.value.trim();
    const w = '<?=$w?>';
    
    // 입력값 유효성 검사
    if(mb_id.length === 0){
        s_email_info.textContent = '아이디 입력이 안되어 있습니다.';
        s_email_info.style.color = 'red';
        mb_email_flag = false;
        return;
    }

    // 이메일 형식 검사
    if(!mb_email_pattern.test(mb_email)){
        s_email_info.textContent = '이메일 형식에 맞지 않습니다.';
        s_email_info.style.color = 'red';
        mb_email_flag = false;
        return;
    }

    try{
        const data = {
            mb_id: mb_id,
            mb_email: mb_email,
            w: w,
        };
        
        const res = await fetch(url2, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            // body: 'mb_id=' + encodeURIComponent(mb_id) + '&mb_email=' + encodeURIComponent(mb_email) + '&w=' + encodeURIComponent(w),
            body: JSON.stringify(data),
        });

        if(!res.ok){
            throw new Error('네트워크 상태가 불안정합니다.');
        }

        const rst = await res.text();
        // console.log(rst);return;
        if(rst == '1'){
            s_email_info.textContent = '사용가능한 이메일입니다.';
            s_email_info.style.color = 'blue';
            mb_email_flag = true;
        }
        else if(rst == '0'){
            s_email_info.textContent = '이미 다른 사용자가 사용중인 이메일입니다.';
            s_email_info.style.color = 'red';
            mb_email_flag = false;
        }
        else{
            throw new Error('서버 오류가 발생했습니다.');
            s_email_info.textContent = '';
            s_email_info.style.color = 'black';
            mb_email_flag = false;
        }
    }
    catch(err){
        console.error('Error:',err);
        s_email_info.textContent = err;
        s_email_info.style.color = 'red';
        mb_email_flag = false;
    }
}

// 닉네임 검사
const url3 = '<?=G5_Z_URL?>/ajax/nick_check.php';
const reg_mb_nick = document.querySelector('#reg_mb_nick');
const s_nick_info = document.querySelector('.s_nick_info');
const mb_nick_pattern = /^[a-zA-Z가-힣]+[a-zA-Z가-힣0-9]*$/;
let timer3;
reg_mb_nick.addEventListener('keydown', function(){
    clearTimeout(timer3);
    timer3 = setTimeout(mbNickCheck, 500);
});

async function mbNickCheck(){
    const mb_id = reg_mb_id.value.trim();
    const mb_nick = reg_mb_nick.value.trim();
    const w = '<?=$w?>';
    
    // 입력값 유효성 검사
    if(mb_id.length === 0){
        s_nick_info.textContent = '아이디 입력이 안되어 있습니다.';
        s_nick_info.style.color = 'red';
        mb_nick_flag = false;
        return;
    }

    // 닉네임 형식 검사
    if(!mb_nick_pattern.test(mb_nick)){
        s_nick_info.textContent = '닉네임 형식에 맞지 않습니다.';
        s_nick_info.style.color = 'red';
        mb_nick_flag = false;
        return;
    }

    try{
        const data = {
            mb_id: mb_id,
            mb_nick: mb_nick,
            w: w,
        };
        
        const res = await fetch(url3, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            // body: 'mb_id=' + encodeURIComponent(mb_id) + '&mb_nick=' + encodeURIComponent(mb_nick) + '&w=' + encodeURIComponent(w),
            body: JSON.stringify(data),
        });

        if(!res.ok){
            throw new Error('네트워크 상태가 불안정합니다.');
        }

        const rst = await res.text();
        // console.log(rst);return;
        if(rst == '1'){
            s_nick_info.textContent = '사용가능한 닉네임입니다.';
            s_nick_info.style.color = 'blue';
            mb_nick_flag = true;
        }
        else if(rst == '0'){
            s_nick_info.textContent = '이미 다른 사용자가 사용중인 닉네임입니다.';
            s_nick_info.style.color = 'red';
            mb_nick_flag = false;
        }
        else{
            throw new Error('서버 오류가 발생했습니다.');
            s_nick_info.textContent = '';
            s_nick_info.style.color = 'black';
            mb_nick_flag = false;
        }
    }
    catch(err){
        console.error('Error:',err);
        s_nick_info.textContent = err;
        s_nick_info.style.color = 'red';
        mb_nick_flag = false;
    }
}


// 휴대폰 검사
const url4 = '<?=G5_Z_URL?>/ajax/hp_check.php';
const reg_mb_hp = document.querySelector('#reg_mb_hp');
const s_hp_info = document.querySelector('.s_hp_info');
const mb_hp_pattern = /^01([0|1|6|7|8|9])-?([0-9]{3,4})-?([0-9]{4})$/;
let timer4;
reg_mb_hp.addEventListener('keydown', function(){
    clearTimeout(timer4);
    timer4 = setTimeout(mbHpCheck, 500);
});

async function mbHpCheck(){
    const mb_id = reg_mb_id.value.trim();
    const mb_hp = reg_mb_hp.value.trim();
    const w = '<?=$w?>';
    
    // 입력값 유효성 검사
    if(mb_id.length === 0){
        s_hp_info.textContent = '아이디 입력이 안되어 있습니다.';
        s_hp_info.style.color = 'red';
        mb_hp_flag = false;
        return;
    }

    // 휴대폰 형식 검사
    if(!mb_hp_pattern.test(mb_hp)){
        s_hp_info.textContent = '휴대폰번호 형식에 맞지 않습니다.';
        s_hp_info.style.color = 'red';
        mb_hp_flag = false;
        return;
    }

    try{
        const data = {
            mb_id: mb_id,
            mb_hp: mb_hp,
            w: w,
        };
        
        const res = await fetch(url4, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            // body: 'mb_id=' + encodeURIComponent(mb_id) + '&mb_hp=' + encodeURIComponent(mb_hp) + '&w=' + encodeURIComponent(w),
            body: JSON.stringify(data),
        });

        if(!res.ok){
            throw new Error('네트워크 상태가 불안정합니다.');
        }

        const rst = await res.text();
        // console.log(rst);return;
        if(rst == '1'){
            s_hp_info.textContent = '사용가능한 휴대폰번호입니다.';
            s_hp_info.style.color = 'blue';
            mb_hp_flag = true;
        }
        else if(rst == '0'){
            s_hp_info.textContent = '이미 다른 사용자가 사용중인 휴대폰번호입니다.';
            s_hp_info.style.color = 'red';
            mb_hp_flag = false;
        }
        else{
            throw new Error('서버 오류가 발생했습니다.');
            s_hp_info.textContent = '';
            s_hp_info.style.color = 'black';
            mb_hp_flag = false;
        }
    }
    catch(err){
        console.error('Error:',err);
        s_hp_info.textContent = err;
        s_hp_info.style.color = 'red';
        mb_hp_flag = false;
    }
}


// 관리페이지권한
const auth_ul = document.querySelectorAll('.auth_ul');

auth_ul.forEach((v) => {
    v.addEventListener('click', authClick);
});

function authClick(e){
    if(!e.target.closest('.auth_r') && !e.target.closest('.auth_w') && !e.target.closest('.auth_d')) return;
    
    const pl = e.target.parentNode;
    const el = e.target;
    let el_r = pl.querySelector('.auth_r').classList.contains('act');
    let el_w = pl.querySelector('.auth_w').classList.contains('act');
    let el_d = pl.querySelector('.auth_d').classList.contains('act');
    
    // console.log(el_r);return;
    // console.log(pl.getAttribute('data-code'));
    if(el.classList.contains('act')){
        el.classList.remove('act');
        if(el.classList.contains('auth_r')){
            el_r = false;
        }
        if(el.classList.contains('auth_w')){
            el_w = false;
        }
        if(el.classList.contains('auth_d')){
            el_d = false;
        }

        if(!el_r && !el_w && !el_d){
            pl.classList.remove('act');
        }
    }
    else{
        el.classList.add('act');
        if(el.classList.contains('auth_r')){
            el_r = true;
        }
        if(el.classList.contains('auth_w')){
            el_w = true;
        }
        if(el.classList.contains('auth_d')){
            el_d = true;
        }

        if(!pl.classList.contains('act')){
            pl.classList.add('act');
        }
    }

    allAuthInputUpdate();
}

function allAuthInputUpdate(){
    const inp = document.querySelector('input[name="auths"]');
    const auths = document.querySelectorAll('.auths.act');
    if(!auths.length){
        inp.value = '';
        return;
    }
    inp_str = '';
    let n = 0;
    auths.forEach((v) => {
        const code = v.getAttribute('data-code');
        const r = v.querySelector('.auth_r').classList.contains('act');
        const w = v.querySelector('.auth_w').classList.contains('act');
        const d = v.querySelector('.auth_d').classList.contains('act');
        const str = code + (r ? '_r' : '') + (w ? '_w' : '') + (d ? '_d' : '');
        inp_str += (n == 0) ? str : ',' + str;
        n++;
    });
    inp.value = inp_str;
}

// 전체권한삭제
const all_auth_del = document.querySelector('#all_auth_del');
all_auth_del.addEventListener('click', allAuthInputDelete);

function allAuthInputDelete(){
    const inp = document.querySelector('input[name="auths"]');
    const auths = document.querySelectorAll('.auths.act');
    if(!auths.length) return;
    auths.forEach((v) => {
        const code = v.classList.remove('act');
        const r = v.querySelector('.auth_r').classList.remove('act');
        const w = v.querySelector('.auth_w').classList.remove('act');
        const d = v.querySelector('.auth_d').classList.remove('act');
    });
    inp.value = '';
}

// 그룹권한버튼
const auth_h3 = document.querySelectorAll('.auth_h3');
auth_h3.forEach((v) => {
    v.addEventListener('click', authH3Click);
});

function authH3Click(e){
    if(!e.target.closest('.group_y') && !e.target.closest('.group_n')) return;
    const pl = e.target.closest('div');
    const ul = pl.querySelector('ul');
    const el = e.target;
    if(el.classList.contains('group_y')){
        groupAuthY(ul);
    }
    else if(el.classList.contains('group_n')){
        groupAuthN(ul);
    }
}

function groupAuthY(el){
    const auths = el.querySelectorAll('.auths');
    auths.forEach((v) => {
        v.classList.add('act');
        v.querySelector('.auth_r').classList.add('act');
        v.querySelector('.auth_w').classList.add('act');
        v.querySelector('.auth_d').classList.add('act');
    });
    allAuthInputUpdate();
}

function groupAuthN(el){
    const auths = el.querySelectorAll('.auths');
    auths.forEach((v) => {
        v.classList.remove('act');
        v.querySelector('.auth_r').classList.remove('act');
        v.querySelector('.auth_w').classList.remove('act');
        v.querySelector('.auth_d').classList.remove('act');
    });
    allAuthInputUpdate();
}

// 마지막 유효성검사후 DB에 데이터 전송
function fmember_submit(f){
    // 아이디검사
    if (!mb_id_flag || !f.mb_id.value){ 
        alert('올바른 아이디를 입력해 주십시오.');
        f.mb_id.focus();
        return false;
    }
    <?php if($w=='') { ?>
    // 비밀번호검사
    if (!f.mb_password.value){
        alert('비밀번호를 입력해 주십시오.');
        f.mb_password.focus();
        return false;
    }
    <?php } ?>
    // 이름검사
    if (!f.mb_name.value){
        alert('이름을 입력해 주십시오.');
        f.mb_name.focus();
        return false;
    }
    //닉네임검사
    if (!mb_nick_flag || !f.mb_nick.value){
        alert('올바른 닉네임을 입력해 주십시오.');
        f.mb_nick.focus();
        return false;
    }
    // 부서검사
    if(!f.mb_department.value){
        alert('부서를 선택해 주십시오.');
        f.mb_department.focus();
        return false;
    }
    // 직급검사
    if(!f.mb_rank.value){
        alert('직급을 선택해 주십시오.');
        f.mb_rank.focus();
        return false;
    }
    //직책검사
    if(!f.mb_role.value){
        alert('직책을 선택해 주십시오.');
        f.mb_role.focus();
        return false;
    }
    // 이메일검사
    if (!mb_email_flag || !f.mb_email.value){
        alert('올바른 이메일을 입력해 주십시오.');
        f.mb_email.focus();
        return false;
    }
    // 휴대폰번호검사
    if (!mb_hp_flag || !f.mb_hp.value){
        alert('올바른 휴대폰번호를 입력해 주십시오.');
        f.mb_hp.focus();
        return false;
    }
    // 입사일검사
    if(!f.mb_datetime.value){
        alert('입사일을 선택해 주십시오.');
        f.mb_datetime.focus();
        return false;
    }

    return true;
}
</script>