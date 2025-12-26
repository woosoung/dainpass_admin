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
        alert('요일을 선택하세요.');
        document.getElementById('modal_weekday').focus();
        return;
    }

    if (!slot_seq || slot_seq < 1) {
        alert('순서는 1 이상이어야 합니다.');
        document.getElementById('modal_slot_seq').focus();
        return;
    }

    if (!open_time) {
        alert('시작시간을 입력하세요.');
        document.getElementById('modal_open_time').focus();
        return;
    }

    if (!close_time) {
        alert('종료시간을 입력하세요.');
        document.getElementById('modal_close_time').focus();
        return;
    }

    // 시간 비교
    if (open_time >= close_time) {
        alert('시작시간은 종료시간보다 빨라야 합니다.');
        document.getElementById('modal_open_time').focus();
        return;
    }

    // 영업여부 확인
    var is_open_checked = document.querySelector('input[name="is_open"]:checked');
    if (!is_open_checked) {
        alert('영업여부를 선택하세요.');
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

