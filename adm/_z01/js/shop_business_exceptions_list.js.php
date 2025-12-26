<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
function addException() {
    try {
        var actionEl = document.getElementById('action');
        var modalTitleEl = document.getElementById('modalTitle');
        var exceptionModalEl = document.getElementById('exceptionModal');
        
        if (!actionEl || !modalTitleEl || !exceptionModalEl) {
            alert('모달 요소를 찾을 수 없습니다. 페이지를 새로고침해주세요.');
            console.error('Missing modal elements:', {
                action: !!actionEl,
                modalTitle: !!modalTitleEl,
                exceptionModal: !!exceptionModalEl
            });
            return;
        }
        
        actionEl.value = 'add';
        modalTitleEl.innerText = '특별휴무/영업일 등록';
        document.getElementById('modal_original_date').value = '';
        
        // 폼 초기화
        var frmException = document.getElementById('frmException');
        if (frmException) {
            frmException.reset();
        }
        document.getElementById('modal_date').value = '';
        document.getElementById('modal_is_open').value = '';
        document.getElementById('modal_open_time').value = '';
        document.getElementById('modal_close_time').value = '';
        document.getElementById('modal_reason').value = '';

        // 사유 글자 수 초기화
        var reasonLengthEl = document.getElementById('reason_length');
        if (reasonLengthEl) {
            reasonLengthEl.innerText = '0';
        }

        // 영업시간 필드 초기 상태
        toggleBusinessHours();
        
        exceptionModalEl.style.display = 'block';
    } catch (e) {
        alert('오류가 발생했습니다: ' + e.message);
        console.error('addException error:', e);
    }
}

function editException(date, is_open, open_time, close_time, reason) {
    // date 유효성 검증
    if (!date || date === '') {
        alert('날짜 정보가 없습니다.');
        return;
    }

    // date 형식 검증 (YYYY-MM-DD)
    var dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (!dateRegex.test(date)) {
        alert('올바른 날짜 형식이 아닙니다.');
        return;
    }

    document.getElementById('action').value = 'edit';
    document.getElementById('modalTitle').innerText = '특별휴무/영업일 수정';
    document.getElementById('modal_original_date').value = date;

    // 기존 값 설정
    document.getElementById('modal_date').value = date;
    document.getElementById('modal_is_open').value = is_open ? 'true' : 'false';
    document.getElementById('modal_open_time').value = open_time || '';
    document.getElementById('modal_close_time').value = close_time || '';

    // reason 값 설정 및 글자 수 업데이트
    var reasonValue = reason || '';
    document.getElementById('modal_reason').value = reasonValue;

    // 사유 글자 수 업데이트
    var reasonLengthEl = document.getElementById('reason_length');
    if (reasonLengthEl) {
        reasonLengthEl.innerText = reasonValue.length;
    }

    // 영업여부에 따라 영업시간 필드 활성화/비활성화
    toggleBusinessHours();

    document.getElementById('exceptionModal').style.display = 'block';
}

function toggleBusinessHours() {
    var isOpen = document.getElementById('modal_is_open').value;
    var openTimeRow = document.getElementById('tr_open_time');
    var closeTimeRow = document.getElementById('tr_close_time');
    var openTimeInput = document.getElementById('modal_open_time');
    var closeTimeInput = document.getElementById('modal_close_time');
    
    if (isOpen === 'true') {
        // 영업인 경우 영업시간 필드 표시
        if (openTimeRow) openTimeRow.style.display = '';
        if (closeTimeRow) closeTimeRow.style.display = '';
        if (openTimeInput) openTimeInput.removeAttribute('disabled');
        if (closeTimeInput) closeTimeInput.removeAttribute('disabled');
    } else {
        // 휴무인 경우 영업시간 필드 숨김 및 값 초기화
        if (openTimeRow) openTimeRow.style.display = 'none';
        if (closeTimeRow) closeTimeRow.style.display = 'none';
        if (openTimeInput) {
            openTimeInput.value = '';
            openTimeInput.setAttribute('disabled', 'disabled');
        }
        if (closeTimeInput) {
            closeTimeInput.value = '';
            closeTimeInput.setAttribute('disabled', 'disabled');
        }
    }
}

function saveException() {
    var form = document.getElementById('frmException');
    var shop_id = document.getElementById('modal_shop_id') ? document.getElementById('modal_shop_id').value : '';
    var date = document.getElementById('modal_date').value;
    var is_open = document.getElementById('modal_is_open').value;
    var open_time = document.getElementById('modal_open_time').value;
    var close_time = document.getElementById('modal_close_time').value;
    var reason = document.getElementById('modal_reason').value;
    var action = document.getElementById('action').value;
    var original_date = document.getElementById('modal_original_date').value;

    // shop_id 유효성 검사
    if (!shop_id || shop_id === '') {
        alert('가맹점 정보를 찾을 수 없습니다. 페이지를 새로고침해주세요.');
        return;
    }

    var shop_id_int = parseInt(shop_id);
    if (isNaN(shop_id_int) || shop_id_int <= 0) {
        alert('유효하지 않은 가맹점 정보입니다. 페이지를 새로고침해주세요.');
        return;
    }

    // date 유효성 검사
    if (!date || date === '') {
        alert('날짜를 선택하세요.');
        document.getElementById('modal_date').focus();
        return;
    }

    // date 형식 검증 (YYYY-MM-DD)
    var dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (!dateRegex.test(date)) {
        alert('올바른 날짜 형식이 아닙니다. (YYYY-MM-DD)');
        document.getElementById('modal_date').focus();
        return;
    }

    // 날짜 유효성 검증 (실제 존재하는 날짜인지)
    var dateObj = new Date(date);
    if (isNaN(dateObj.getTime())) {
        alert('유효하지 않은 날짜입니다.');
        document.getElementById('modal_date').focus();
        return;
    }

    // 년도 범위 검증 (1900-2100)
    var year = dateObj.getFullYear();
    if (year < 1900 || year > 2100) {
        alert('날짜의 년도는 1900년에서 2100년 사이여야 합니다.');
        document.getElementById('modal_date').focus();
        return;
    }
    
    if (!is_open || is_open === '') {
        alert('영업여부를 선택하세요.');
        document.getElementById('modal_is_open').focus();
        return;
    }
    
    // 영업인 경우 영업시간 검증
    if (is_open === 'true') {
        if (!open_time || open_time === '') {
            alert('영업시작시간을 입력하세요.');
            document.getElementById('modal_open_time').focus();
            return;
        }
        if (!close_time || close_time === '') {
            alert('영업종료시간을 입력하세요.');
            document.getElementById('modal_close_time').focus();
            return;
        }
        // 시작시간이 종료시간보다 늦으면 안됨
        if (open_time >= close_time) {
            alert('영업시작시간은 영업종료시간보다 빨라야 합니다.');
            document.getElementById('modal_open_time').focus();
            return;
        }
    } else {
        // 휴무인 경우 영업시간은 빈 값으로 설정
        open_time = '';
        close_time = '';
    }

    // reason 길이 제한 (200자)
    if (reason && reason.length > 200) {
        alert('사유는 최대 200자까지 입력 가능합니다.');
        document.getElementById('modal_reason').focus();
        return;
    }

    // disabled된 필드가 있으면 제거하고 값을 명시적으로 설정하여 전송되도록 함
    var openTimeInput = document.getElementById('modal_open_time');
    var closeTimeInput = document.getElementById('modal_close_time');
    if (openTimeInput) {
        if (openTimeInput.disabled) {
            openTimeInput.removeAttribute('disabled');
        }
        // 휴무인 경우 명시적으로 빈 값 설정
        if (is_open !== 'true') {
            openTimeInput.value = '';
        }
    }
    if (closeTimeInput) {
        if (closeTimeInput.disabled) {
            closeTimeInput.removeAttribute('disabled');
        }
        // 휴무인 경우 명시적으로 빈 값 설정
        if (is_open !== 'true') {
            closeTimeInput.value = '';
        }
    }
    
    // 폼 생성하여 제출
    var submitForm = document.createElement('form');
    submitForm.method = 'POST';
    submitForm.action = './shop_business_exceptions_list_update.php';
    
    var token = document.querySelector('input[name="token"]');
    if (!token) {
        alert('토큰을 찾을 수 없습니다. 페이지를 새로고침해주세요.');
        return;
    }
    
    // 휴무인 경우 영업시간을 명시적으로 빈 문자열로 설정
    if (is_open !== 'true') {
        open_time = '';
        close_time = '';
    }
    
    var fields = {
        'token': token.value,
        'action': action,
        'shop_id': shop_id_int,  // 정수형으로 전송
        'date': date,
        'is_open': is_open === 'true' ? '1' : '0',
        'open_time': open_time || '',
        'close_time': close_time || '',
        'reason': reason
    };
    
    // 수정 모드인 경우 original_date 추가
    if (action == 'edit') {
        fields['original_date'] = original_date;
    }
    
    for (var key in fields) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = fields[key];
        submitForm.appendChild(input);
    }
    
    document.body.appendChild(submitForm);
    submitForm.submit();
}

function closeModal() {
    document.getElementById('exceptionModal').style.display = 'none';
}

function flist_submit(f) {
    return true;
}

function flist_delete_submit() {
    var f = document.flist;
    var checked_count = 0;
    
    for (var i=0; i<f.elements.length; i++) {
        if (f.elements[i].name == "chk[]" && f.elements[i].checked) {
            checked_count++;
        }
    }
    
    if (checked_count == 0) {
        alert('선택된 항목이 없습니다.');
        return false;
    }
    
    if (!confirm('선택한 ' + checked_count + '개의 특별휴무/영업일을 삭제하시겠습니까?')) {
        return false;
    }
    
    // 토큰 확인
    if (!f.token || !f.token.value) {
        alert('토큰 정보가 올바르지 않습니다. 페이지를 새로고침해주세요.');
        return false;
    }
    
    f.act.value = 'delete';
    f.submit();
}

function check_all(f) {
    var chk = document.getElementsByName("chk[]");
    
    for (var i=0; i<chk.length; i++) {
        chk[i].checked = f.checked;
    }
}

// 모달 외부 클릭 시 닫기
window.onclick = function(event) {
    var modal = document.getElementById('exceptionModal');
    if (event.target == modal) {
        closeModal();
    }
}

// 페이지 로드 시 URL 파라미터 확인하여 모달 자동 열기
$(document).ready(function() {
    // 사유 입력 실시간 글자 수 카운터
    $('#modal_reason').on('input', function() {
        var length = $(this).val().length;
        $('#reason_length').text(length);

        // 200자 초과 방지
        if (length > 200) {
            $(this).val($(this).val().substring(0, 200));
            $('#reason_length').text(200);
        }
    });

    if (typeof addDateParam !== 'undefined' && addDateParam) {
        // 날짜가 전달된 경우 신규등록 모달 열기
        setTimeout(function() {
            document.getElementById('modal_date').value = addDateParam;
            addException();
        }, 100);
    } else if (typeof editExceptionData !== 'undefined' && editExceptionData) {
        // 수정할 날짜 데이터가 전달된 경우 모달 열기
        setTimeout(function() {
            editException(
                editExceptionData.date,
                editExceptionData.is_open,
                editExceptionData.open_time,
                editExceptionData.close_time,
                editExceptionData.reason
            );
        }, 100);
    }
});
</script>

