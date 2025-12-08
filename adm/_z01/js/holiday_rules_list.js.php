<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
function addRule() {
    try {
        var actionEl = document.getElementById('action');
        var modalTitleEl = document.getElementById('modalTitle');
        var ruleModalEl = document.getElementById('ruleModal');
        
        if (!actionEl || !modalTitleEl || !ruleModalEl) {
            alert('모달 요소를 찾을 수 없습니다. 페이지를 새로고침해주세요.');
            console.error('Missing modal elements:', {
                action: !!actionEl,
                modalTitle: !!modalTitleEl,
                ruleModal: !!ruleModalEl
            });
            return;
        }
        
        actionEl.value = 'add';
        modalTitleEl.innerText = '정기휴무 규칙 등록';
        document.getElementById('modal_holiday_rule_id').value = '';
        
        // 폼 초기화
        var frmRule = document.getElementById('frmRule');
        if (frmRule) {
            frmRule.reset();
        }
        document.getElementById('modal_holiday_type').value = '';
        document.getElementById('modal_weekday').value = '';
        document.getElementById('modal_week_of_month').value = '';
        document.getElementById('modal_week_of_month').disabled = true;
        document.getElementById('modal_description').value = '';
        
        ruleModalEl.style.display = 'block';
    } catch (e) {
        alert('오류가 발생했습니다: ' + e.message);
        console.error('addRule error:', e);
    }
}

function editRule(holiday_rule_id, holiday_type, weekday, week_of_month, description) {
    document.getElementById('action').value = 'edit';
    document.getElementById('modalTitle').innerText = '정기휴무 규칙 수정';
    document.getElementById('modal_holiday_rule_id').value = holiday_rule_id;
    
    // 기존 값 설정
    document.getElementById('modal_holiday_type').value = holiday_type;
    document.getElementById('modal_weekday').value = weekday !== null ? weekday : '';
    document.getElementById('modal_week_of_month').value = week_of_month !== null ? week_of_month : '';
    document.getElementById('modal_description').value = description || '';
    
    // 휴무유형에 따라 주차 선택박스 활성화/비활성화
    toggleWeekOfMonth();
    
    document.getElementById('ruleModal').style.display = 'block';
}

function toggleWeekOfMonth() {
    var holidayType = document.getElementById('modal_holiday_type').value;
    var weekOfMonthSelect = document.getElementById('modal_week_of_month');
    
    if (holidayType === 'monthly') {
        // 매월인 경우 주차 선택 가능
        weekOfMonthSelect.disabled = false;
    } else {
        // 매주이거나 선택 안 된 경우 주차 선택 불가
        weekOfMonthSelect.disabled = true;
        weekOfMonthSelect.value = '';  // 값 초기화
    }
}

function saveRule() {
    var form = document.getElementById('frmRule');
    var shop_id = document.getElementById('modal_shop_id') ? document.getElementById('modal_shop_id').value : '';
    var holiday_type = document.getElementById('modal_holiday_type').value;
    var weekday = document.getElementById('modal_weekday').value;
    var week_of_month = document.getElementById('modal_week_of_month').value;
    var description = document.getElementById('modal_description').value;
    var action = document.getElementById('action').value;
    var holiday_rule_id = document.getElementById('modal_holiday_rule_id').value;
    
    // 유효성 검사
    if (!shop_id || shop_id === '') {
        alert('가맹점 정보를 찾을 수 없습니다. 페이지를 새로고침해주세요.');
        return;
    }
    
    if (!holiday_type || holiday_type === '') {
        alert('휴무유형을 선택하세요.');
        document.getElementById('modal_holiday_type').focus();
        return;
    }
    
    if (!weekday || weekday === '') {
        alert('요일을 선택하세요.');
        document.getElementById('modal_weekday').focus();
        return;
    }
    
    // 매주인 경우 week_of_month는 빈 문자열로 강제 설정
    if (holiday_type === 'weekly') {
        week_of_month = '';
    }
    
    // 폼 생성하여 제출
    var submitForm = document.createElement('form');
    submitForm.method = 'POST';
    submitForm.action = './holiday_rules_list_update.php';
    
    var token = document.querySelector('input[name="token"]');
    if (!token) {
        alert('토큰을 찾을 수 없습니다. 페이지를 새로고침해주세요.');
        return;
    }
    
    var fields = {
        'token': token.value,
        'action': action,
        'shop_id': shop_id,
        'holiday_type': holiday_type,
        'weekday': weekday,
        'week_of_month': week_of_month || '',  // 빈 문자열이면 NULL로 처리됨
        'description': description
    };
    
    // 수정 모드인 경우 holiday_rule_id 추가
    if (action == 'edit') {
        fields['holiday_rule_id'] = holiday_rule_id;
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
    document.getElementById('ruleModal').style.display = 'none';
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
    
    if (!confirm('선택한 ' + checked_count + '개의 정기휴무 규칙을 삭제하시겠습니까?')) {
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
    var modal = document.getElementById('ruleModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

