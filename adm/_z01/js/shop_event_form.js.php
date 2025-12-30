<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
function toggleServiceField() {
    var discountScope = document.getElementById('discount_scope').value;
    var serviceHeader = document.getElementById('service_header');
    var serviceCell = document.getElementById('service_cell');
    var serviceIdField = document.getElementById('service_id');

    if (discountScope === 'SERVICE') {
        // 서비스별 할인인 경우 서비스 선택 필수
        if (serviceHeader) {
            serviceHeader.style.display = 'table-cell';
        }
        if (serviceCell) {
            serviceCell.style.display = 'table-cell';
            // 부드러운 표시 효과
            setTimeout(function() {
                serviceCell.style.backgroundColor = '#fff9e6';
                setTimeout(function() {
                    serviceCell.style.backgroundColor = '';
                }, 1000);
            }, 50);
        }
        if (serviceIdField) {
            serviceIdField.classList.add('required');
            serviceIdField.setAttribute('required', 'required');
            // 포커스 이동
            setTimeout(function() {
                serviceIdField.focus();
            }, 300);
        }
    } else {
        // 가맹점 전체 할인인 경우 서비스 선택 불필요
        if (serviceHeader) {
            serviceHeader.style.display = 'none';
        }
        if (serviceCell) {
            serviceCell.style.display = 'none';
        }
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
    var discountTitle = f.discount_title.value.trim();
    if (!discountTitle) {
        alert('이벤트제목을 입력하세요.');
        f.discount_title.focus();
        return false;
    }

    // 이벤트제목 길이 제한
    if (discountTitle.length > 100) {
        alert('이벤트제목은 최대 100자까지 입력할 수 있습니다.');
        f.discount_title.focus();
        return false;
    }

    // 할인범위 검증
    if (!f.discount_scope.value) {
        alert('할인범위를 선택하세요.');
        f.discount_scope.focus();
        return false;
    }

    // 할인범위 화이트리스트 검증
    var allowedScope = ['SHOP', 'SERVICE'];
    if (allowedScope.indexOf(f.discount_scope.value) === -1) {
        alert('유효하지 않은 할인범위입니다.');
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

    // 할인유형 화이트리스트 검증
    var allowedType = ['PERCENT', 'AMOUNT'];
    if (allowedType.indexOf(f.discount_type.value) === -1) {
        alert('유효하지 않은 할인유형입니다.');
        f.discount_type.focus();
        return false;
    }

    // 할인값 검증
    var discountValue = parseInt(f.discount_value.value);
    if (isNaN(discountValue) || discountValue < 1) {
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

    // 정액 할인인 경우 최대값 제한
    if (f.discount_type.value === 'AMOUNT' && discountValue > 100000000) {
        alert('정액 할인은 최대 1억원까지 설정할 수 있습니다.');
        f.discount_value.focus();
        return false;
    }

    // 시작일시 검증
    if (!f.start_datetime.value) {
        alert('시작일시를 선택하세요.');
        f.start_datetime.focus();
        return false;
    }

    // 시작일시 형식 검증
    var datetimePattern = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/;
    if (!datetimePattern.test(f.start_datetime.value)) {
        alert('시작일시 형식이 올바르지 않습니다.');
        f.start_datetime.focus();
        return false;
    }

    // 종료일시 검증
    if (f.end_datetime.value) {
        // 종료일시 형식 검증
        if (!datetimePattern.test(f.end_datetime.value)) {
            alert('종료일시 형식이 올바르지 않습니다.');
            f.end_datetime.focus();
            return false;
        }

        var startDate = new Date(f.start_datetime.value);
        var endDate = new Date(f.end_datetime.value);

        // 날짜 유효성 검증
        if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
            alert('날짜 형식이 올바르지 않습니다.');
            return false;
        }

        if (endDate <= startDate) {
            alert('종료일시는 시작일시보다 늦어야 합니다.');
            f.end_datetime.focus();
            return false;
        }
    }

    // 토큰 확인
    if (!f.token || !f.token.value) {
        alert('토큰 정보가 올바르지 않습니다. 페이지를 새로고침해주세요.');
        return false;
    }

    // 중복 제출 방지
    var submitButtons = document.querySelectorAll('input[type="submit"]');
    for (var i = 0; i < submitButtons.length; i++) {
        submitButtons[i].disabled = true;
    }

    // 3초 후 버튼 다시 활성화 (에러 발생 시 재시도 가능하도록)
    setTimeout(function() {
        for (var i = 0; i < submitButtons.length; i++) {
            submitButtons[i].disabled = false;
        }
    }, 3000);

    return true;
}

// 페이지 로드 시 초기 설정
document.addEventListener('DOMContentLoaded', function() {
    // 초기 상태 설정 (애니메이션 없이)
    var discountScope = document.getElementById('discount_scope');
    var serviceHeader = document.getElementById('service_header');
    var serviceCell = document.getElementById('service_cell');

    if (discountScope && discountScope.value === 'SERVICE') {
        if (serviceHeader) {
            serviceHeader.style.display = 'table-cell';
        }
        if (serviceCell) {
            serviceCell.style.display = 'table-cell';
        }
        var serviceIdField = document.getElementById('service_id');
        if (serviceIdField) {
            serviceIdField.classList.add('required');
            serviceIdField.setAttribute('required', 'required');
        }
    }

    // 할인유형 초기값 설정
    toggleDiscountFields();

    // 할인유형 변경 시 이벤트 리스너
    var discountTypeField = document.getElementById('discount_type');
    if (discountTypeField) {
        discountTypeField.addEventListener('change', toggleDiscountFields);
    }
});
</script>
