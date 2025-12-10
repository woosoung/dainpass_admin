<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
function toggleServiceField() {
    var discountScope = document.getElementById('discount_scope').value;
    var serviceIdField = document.getElementById('service_id');
    
    if (discountScope === 'SERVICE') {
        // 서비스별 할인인 경우 서비스 선택 필수
        if (serviceIdField) {
            serviceIdField.classList.add('required');
            serviceIdField.setAttribute('required', 'required');
        }
    } else {
        // 가맹점 전체 할인인 경우 서비스 선택 불필요
        if (serviceIdField) {
            serviceIdField.classList.remove('required');
            serviceIdField.removeAttribute('required');
            serviceIdField.value = '';
        }
    }
}

function toggleDiscountFields() {
    var discountType = document.getElementById('discount_type').value;
    var discountUnit = document.getElementById('discount_unit');
    
    if (discountType === 'PERCENT') {
        if (discountUnit) {
            discountUnit.textContent = '%';
        }
    } else if (discountType === 'AMOUNT') {
        if (discountUnit) {
            discountUnit.textContent = '원';
        }
    } else {
        if (discountUnit) {
            discountUnit.textContent = '원';
        }
    }
}

function form01_submit(f) {
    // 이벤트제목 검증
    if (!f.discount_title.value.trim()) {
        alert('이벤트제목을 입력하세요.');
        f.discount_title.focus();
        return false;
    }
    
    // 할인범위 검증
    if (!f.discount_scope.value) {
        alert('할인범위를 선택하세요.');
        f.discount_scope.focus();
        return false;
    }
    
    // 서비스별 할인인 경우 서비스 선택 검증
    if (f.discount_scope.value === 'SERVICE' && !f.service_id.value) {
        alert('서비스별 할인인 경우 서비스를 선택하세요.');
        f.service_id.focus();
        return false;
    }
    
    // 할인유형 검증
    if (!f.discount_type.value) {
        alert('할인유형을 선택하세요.');
        f.discount_type.focus();
        return false;
    }
    
    // 할인값 검증
    var discountValue = parseInt(f.discount_value.value);
    if (!discountValue || discountValue < 1) {
        alert('할인값을 올바르게 입력하세요.');
        f.discount_value.focus();
        return false;
    }
    
    // 백분율 할인인 경우 할인값이 100 이하여야 함
    if (f.discount_type.value === 'PERCENT' && discountValue > 100) {
        alert('백분율 할인은 100%를 초과할 수 없습니다.');
        f.discount_value.focus();
        return false;
    }
    
    // 시작일시 검증
    if (!f.start_datetime.value) {
        alert('시작일시를 선택하세요.');
        f.start_datetime.focus();
        return false;
    }
    
    // 종료일시 검증
    if (f.end_datetime.value) {
        var startDate = new Date(f.start_datetime.value);
        var endDate = new Date(f.end_datetime.value);
        
        if (endDate < startDate) {
            alert('종료일시는 시작일시보다 늦어야 합니다.');
            f.end_datetime.focus();
            return false;
        }
    }
    
    return true;
}

// 페이지 로드 시 초기 설정
document.addEventListener('DOMContentLoaded', function() {
    toggleServiceField();
    toggleDiscountFields();
    
    // 할인유형 변경 시 이벤트 리스너
    var discountTypeField = document.getElementById('discount_type');
    if (discountTypeField) {
        discountTypeField.addEventListener('change', toggleDiscountFields);
    }
});
</script>
