<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
// 월 변경
function changeMonth(delta) {
    var year = parseInt($('#year-select').val());
    var month = parseInt($('#month-select').val());

    // year/month 유효성 검증
    if (isNaN(year) || isNaN(month)) {
        alert('잘못된 날짜 정보입니다.');
        return;
    }

    month += delta;

    if (month < 1) {
        month = 12;
        year--;
    } else if (month > 12) {
        month = 1;
        year++;
    }

    // year 범위 검증 (1900-2100)
    if (year < 1900 || year > 2100) {
        alert('유효하지 않은 연도입니다. (1900-2100)');
        return;
    }

    // month 범위 검증 (1-12)
    if (month < 1 || month > 12) {
        alert('유효하지 않은 월입니다. (1-12)');
        return;
    }

    location.href = './shop_business_exceptions_calendar.php?year=' + year + '&month=' + month;
}

// 년월 선택 변경
function changeYearMonth() {
    var year = parseInt($('#year-select').val());
    var month = parseInt($('#month-select').val());

    // year/month 유효성 검증
    if (isNaN(year) || isNaN(month)) {
        alert('잘못된 날짜 정보입니다.');
        return;
    }

    // year 범위 검증 (1900-2100)
    if (year < 1900 || year > 2100) {
        alert('유효하지 않은 연도입니다. (1900-2100)');
        return;
    }

    // month 범위 검증 (1-12)
    if (month < 1 || month > 12) {
        alert('유효하지 않은 월입니다. (1-12)');
        return;
    }

    location.href = './shop_business_exceptions_calendar.php?year=' + year + '&month=' + month;
}

// 신규등록 (날짜 미지정)
function addException() {
    try {
        var actionEl = document.getElementById('action');
        var modalTitleEl = document.getElementById('modalTitle');
        var exceptionModalEl = document.getElementById('exceptionModal');
        
        if (!actionEl || !modalTitleEl || !exceptionModalEl) {
            alert('모달 요소를 찾을 수 없습니다. 페이지를 새로고침해주세요.');
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
        document.getElementById('reason_length').innerText = '0';

        // 영업시간 필드 초기 상태
        toggleBusinessHours();
        
        exceptionModalEl.style.display = 'block';
    } catch (e) {
        alert('오류가 발생했습니다: ' + e.message);
        console.error('addException error:', e);
    }
}

// 특정 날짜에 예외일 추가
function addExceptionForDate(date) {
    try {
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

        var actionEl = document.getElementById('action');
        var modalTitleEl = document.getElementById('modalTitle');
        var exceptionModalEl = document.getElementById('exceptionModal');

        if (!actionEl || !modalTitleEl || !exceptionModalEl) {
            alert('모달 요소를 찾을 수 없습니다. 페이지를 새로고침해주세요.');
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
        document.getElementById('modal_date').value = date;
        document.getElementById('modal_is_open').value = '';
        document.getElementById('modal_open_time').value = '';
        document.getElementById('modal_close_time').value = '';
        document.getElementById('modal_reason').value = '';

        // 사유 글자 수 초기화
        document.getElementById('reason_length').innerText = '0';

        // 영업시간 필드 초기 상태
        toggleBusinessHours();
        
        exceptionModalEl.style.display = 'block';
    } catch (e) {
        alert('오류가 발생했습니다: ' + e.message);
        console.error('addExceptionForDate error:', e);
    }
}

// 예외일 수정
function editException(date) {
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

    // AJAX로 해당 날짜의 데이터 가져오기
    $.ajax({
        url: './ajax/shop_business_exceptions_get.php',
        type: 'POST',
        data: {
            date: date
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                var data = response.data;
                document.getElementById('action').value = 'edit';
                document.getElementById('modalTitle').innerText = '특별휴무/영업일 수정';
                document.getElementById('modal_original_date').value = date;
                
                // 기존 값 설정
                document.getElementById('modal_date').value = date;
                document.getElementById('modal_is_open').value = data.is_open ? 'true' : 'false';
                document.getElementById('modal_open_time').value = data.open_time || '';
                document.getElementById('modal_close_time').value = data.close_time || '';

                var reasonValue = data.reason || '';
                document.getElementById('modal_reason').value = reasonValue;

                // 사유 글자 수 업데이트
                document.getElementById('reason_length').innerText = reasonValue.length;

                // 영업여부에 따라 영업시간 필드 활성화/비활성화
                toggleBusinessHours();
                
                document.getElementById('exceptionModal').style.display = 'block';
            } else {
                alert(response.message || '데이터를 불러올 수 없습니다.');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            console.error('Status Code:', xhr.status);
            
            // 응답이 JSON 형식인 경우 파싱 시도
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.message) {
                    alert(response.message);
                    return;
                }
            } catch (e) {
                // JSON 파싱 실패 시 기본 메시지
            }
            
            alert('데이터를 불러오는 중 오류가 발생했습니다. (상태: ' + xhr.status + ')');
        }
    });
}

// 예외일 삭제
function deleteException(event, date) {
    event.stopPropagation();

    // date 유효성 검증
    if (!date || date === '') {
        alert('날짜 정보가 없습니다.');
        return false;
    }

    // date 형식 검증 (YYYY-MM-DD)
    var dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (!dateRegex.test(date)) {
        alert('올바른 날짜 형식이 아닙니다.');
        return false;
    }

    if (!confirm('해당 특별휴무/영업일을 삭제하시겠습니까?')) {
        return false;
    }

    $.ajax({
        url: './ajax/shop_business_exceptions_del.php',
        type: 'POST',
        data: {
            date: date
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // 해당 라벨 삭제
                $('[data-date="' + date + '"].exception-label').fadeOut(300, function() {
                    $(this).remove();
                });
                alert('삭제되었습니다.');
            } else {
                alert(response.message || '삭제에 실패했습니다.');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            console.error('Status Code:', xhr.status);
            
            // 응답이 JSON 형식인 경우 파싱 시도
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.message) {
                    alert(response.message);
                    return;
                }
            } catch (e) {
                // JSON 파싱 실패 시 기본 메시지
            }
            
            alert('삭제 중 오류가 발생했습니다. (상태: ' + xhr.status + ')');
        }
    });
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
        alert('유효하지 않은 날짜입니다.');
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
    
    // 휴무인 경우 영업시간을 명시적으로 빈 문자열로 설정
    if (is_open !== 'true') {
        open_time = '';
        close_time = '';
    }
    
    // 토큰 가져오기
    var tokenInput = document.querySelector('input[name="token"]');
    var token = tokenInput ? tokenInput.value : '';
    
    if (!token) {
        alert('토큰을 찾을 수 없습니다. 페이지를 새로고침해주세요.');
        return;
    }
    
    // AJAX로 저장
    $.ajax({
        url: './shop_business_exceptions_list_update.php',
        type: 'POST',
        data: {
            token: token,
            action: action,
            shop_id: shop_id_int,  // 정수형으로 전송
            date: date,
            is_open: is_open === 'true' ? '1' : '0',
            open_time: open_time || '',
            close_time: close_time || '',
            reason: reason,
            original_date: original_date || ''
        },
        dataType: 'html',
        success: function(response) {
            // 성공 시 페이지 새로고침
            alert(action === 'add' ? '등록되었습니다.' : '수정되었습니다.');
            location.reload();
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            // 응답에서 에러 메시지 확인
            if (xhr.responseText) {
                console.error('Response:', xhr.responseText);
            }
            alert('저장 중 오류가 발생했습니다.');
        }
    });
}

function closeModal() {
    document.getElementById('exceptionModal').style.display = 'none';
}

// 모달 외부 클릭 시 닫기
window.onclick = function(event) {
    var modal = document.getElementById('exceptionModal');
    if (event.target == modal) {
        closeModal();
    }
}

$(document).ready(function() {
    // 페이지 로드 완료
    console.log('Shop Business Exceptions Calendar loaded');

    // 사유 입력 실시간 글자 수 카운터
    $('#modal_reason').on('input', function() {
        var length = $(this).val().length;
        $('#reason_length').text(length);

        // 200자 초과 방지 (maxlength로도 막히지만 이중 체크)
        if (length > 200) {
            $(this).val($(this).val().substring(0, 200));
            $('#reason_length').text(200);
        }
    });
});
</script>

