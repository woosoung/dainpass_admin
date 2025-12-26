<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
function addSlot() {
    try {
        var actionEl = document.getElementById('action');
        var modalTitleEl = document.getElementById('modalTitle');
        var slotModalEl = document.getElementById('slotModal');

        if (!actionEl || !modalTitleEl || !slotModalEl) {
            alert('모달 요소를 찾을 수 없습니다. 페이지를 새로고침해주세요.');
            console.error('Missing modal elements:', {
                action: !!actionEl,
                modalTitle: !!modalTitleEl,
                slotModal: !!slotModalEl
            });
            return;
        }

        actionEl.value = 'add';
        modalTitleEl.innerText = '시간대 추가';
        document.getElementById('old_weekday').value = '';
        document.getElementById('old_slot_seq').value = '';

        // 폼 초기화
        var frmSlot = document.getElementById('frmSlot');
        if (frmSlot) {
            frmSlot.reset();
        }
        document.getElementById('modal_weekday').value = '';
        document.getElementById('modal_slot_seq').value = '';
        document.getElementById('modal_open_time').value = '';
        document.getElementById('modal_close_time').value = '';
        var isOpenY = document.getElementById('modal_is_open_y');
        if (isOpenY) {
            isOpenY.checked = true;
        }

        slotModalEl.style.display = 'block';
    } catch (e) {
        alert('오류가 발생했습니다: ' + e.message);
        console.error('addSlot error:', e);
    }
}

function editSlot(shop_id, weekday, slot_seq, open_time, close_time, is_open) {
    document.getElementById('action').value = 'edit';
    document.getElementById('modalTitle').innerText = '시간대 수정';
    document.getElementById('old_weekday').value = weekday;
    document.getElementById('old_slot_seq').value = slot_seq;

    // 기존 값 설정
    document.getElementById('modal_weekday').value = weekday;
    document.getElementById('modal_slot_seq').value = slot_seq;

    // 시간 형식 변환 (HH:MM:SS -> HH:MM)
    var open_time_str = open_time.substring(0, 5);
    var close_time_str = close_time.substring(0, 5);
    document.getElementById('modal_open_time').value = open_time_str;
    document.getElementById('modal_close_time').value = close_time_str;

    if (is_open === true || is_open === 'true' || is_open === 't' || is_open === '1') {
        document.getElementById('modal_is_open_y').checked = true;
    } else {
        document.getElementById('modal_is_open_n').checked = true;
    }

    document.getElementById('slotModal').style.display = 'block';
}

function deleteSlot(shop_id, weekday, slot_seq) {
    if (!confirm('정말 삭제하시겠습니까?')) {
        return;
    }

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = './shop_weeks_slot_list_update.php';

    var token = document.querySelector('input[name="token"]');
    if (!token) {
        alert('토큰을 찾을 수 없습니다. 페이지를 새로고침해주세요.');
        return;
    }

    var fields = {
        'token': token.value,
        'action': 'delete',
        'weekday': weekday,
        'slot_seq': slot_seq
    };

    for (var key in fields) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = fields[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
}

function saveSlot() {
    var form = document.getElementById('frmSlot');
    var weekday = document.getElementById('modal_weekday').value;
    var slot_seq = document.getElementById('modal_slot_seq').value;
    var open_time = document.getElementById('modal_open_time').value;
    var close_time = document.getElementById('modal_close_time').value;

    // 유효성 검사
    if (!weekday || weekday === '') {
        alert('요일을 선택해 주세요.');
        document.getElementById('modal_weekday').focus();
        return;
    }

    // 요일 범위 검증 (0-6)
    var weekdayNum = parseInt(weekday);
    if (isNaN(weekdayNum) || weekdayNum < 0 || weekdayNum > 6) {
        alert('올바른 요일을 선택해 주세요.');
        document.getElementById('modal_weekday').focus();
        return;
    }

    if (!slot_seq || slot_seq === '') {
        alert('순서를 입력해 주세요.');
        document.getElementById('modal_slot_seq').focus();
        return;
    }

    // 순서 범위 검증 (1-99)
    var slotSeqNum = parseInt(slot_seq);
    if (isNaN(slotSeqNum) || slotSeqNum < 1 || slotSeqNum > 99) {
        alert('순서는 1 이상 99 이하로 입력해 주세요.');
        document.getElementById('modal_slot_seq').focus();
        return;
    }

    if (!open_time || open_time === '') {
        alert('시작시간을 입력해 주세요.');
        document.getElementById('modal_open_time').focus();
        return;
    }

    // 시작시간 형식 검증 (HH:MM)
    if (!/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/.test(open_time)) {
        alert('시작시간 형식이 올바르지 않습니다. (예: 09:00)');
        document.getElementById('modal_open_time').focus();
        return;
    }

    if (!close_time || close_time === '') {
        alert('종료시간을 입력해 주세요.');
        document.getElementById('modal_close_time').focus();
        return;
    }

    // 종료시간 형식 검증 (HH:MM)
    if (!/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/.test(close_time)) {
        alert('종료시간 형식이 올바르지 않습니다. (예: 18:00)');
        document.getElementById('modal_close_time').focus();
        return;
    }

    // 영업여부 확인
    var is_open_checked = document.querySelector('input[name="is_open"]:checked');
    if (!is_open_checked) {
        alert('영업여부를 선택해 주세요.');
        return;
    }

    // is_open 값 검증 (0 또는 1만 허용)
    if (is_open_checked.value !== '0' && is_open_checked.value !== '1') {
        alert('올바른 영업여부 값이 아닙니다.');
        return;
    }

    // 폼 생성하여 제출
    var submitForm = document.createElement('form');
    submitForm.method = 'POST';
    submitForm.action = './shop_weeks_slot_list_update.php';

    var token = document.querySelector('input[name="token"]');
    if (!token) {
        alert('토큰을 찾을 수 없습니다. 페이지를 새로고침해주세요.');
        return;
    }

    var fields = {
        'token': token.value,
        'action': document.getElementById('action').value,
        'weekday': weekday,
        'slot_seq': slot_seq,
        'open_time': open_time,
        'close_time': close_time,
        'is_open': is_open_checked.value
    };

    // 수정 모드인 경우 기존 값 추가
    if (document.getElementById('action').value == 'edit') {
        fields['old_weekday'] = document.getElementById('old_weekday').value;
        fields['old_slot_seq'] = document.getElementById('old_slot_seq').value;
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
    document.getElementById('slotModal').style.display = 'none';
}

function frm_check(f) {
    // 기본 폼 검증은 모달에서 처리
    return true;
}

// 모달 외부 클릭 시 닫기
window.onclick = function(event) {
    var modal = document.getElementById('slotModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

