<script>
//업체관련 멀티파일
$('#multi_file_com').MultiFile();

const com_select = document.querySelector('.com_select');
com_select.addEventListener('click', () => {
    const url = com_select.getAttribute('data-url') + '?file_name=' + file_name;
    const win_com_select = window.open(url, "win_com_select", "width=500,height=540,scrollbars=yes");
    win_com_select.focus();
    return false;
});

const key_renewal = document.querySelector('#key_renewal');
const key_clear = document.querySelector('#key_clear');
key_renewal.addEventListener('change', (e) => {
    if (e.target.checked) {
        key_clear.checked = false;
    }
});

key_clear.addEventListener('change', (e) => {
    if (e.target.checked) {
        key_renewal.checked = false;
    }
});

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