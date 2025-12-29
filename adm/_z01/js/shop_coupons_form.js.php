<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
// 쿠폰코드 재생성 함수
function regenerateCouponCode() {
    var couponCodeInput = document.getElementById('coupon_code');
    if (!couponCodeInput) {
        return;
    }

    // 12자리 영문숫자 조합 난수 생성 (혼동되는 문자 제외: I, O, 0, 1)
    var characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    var randomString = '';
    for (var i = 0; i < 12; i++) {
        randomString += characters.charAt(Math.floor(Math.random() * characters.length));
    }

    // 4-4-4 형식으로 표시 (예: ABCD-EFGH-1234)
    var formattedCode = randomString.substring(0, 4) + '-' + randomString.substring(4, 8) + '-' + randomString.substring(8, 12);
    couponCodeInput.value = formattedCode;
}

function toggleDiscountFields() {
    var discountType = document.getElementById('discount_type').value;
    var maxDiscountAmtRow = document.getElementById('tr_max_discount_amt');
    var discountUnit = document.getElementById('discount_unit');
    var discountValueInput = document.getElementById('discount_value');

    if (discountType === 'PERCENT') {
        // 백분율 할인인 경우 최대할인금액 필드 표시
        if (maxDiscountAmtRow) {
            maxDiscountAmtRow.style.display = '';
        }
        if (discountUnit) {
            discountUnit.textContent = '%';
        }
        // 할인값 최대값을 100으로 설정
        if (discountValueInput) {
            discountValueInput.max = 100;
        }
    } else if (discountType === 'AMOUNT') {
        // 정액 할인인 경우 최대할인금액 필드 숨김
        if (maxDiscountAmtRow) {
            maxDiscountAmtRow.style.display = 'none';
        }
        if (discountUnit) {
            discountUnit.textContent = '원';
        }
        // 할인값 최대값을 1억원으로 설정
        if (discountValueInput) {
            discountValueInput.max = 100000000;
        }
    } else {
        // 선택 안된 경우
        if (maxDiscountAmtRow) {
            maxDiscountAmtRow.style.display = 'none';
        }
        if (discountUnit) {
            discountUnit.textContent = '원';
        }
        // 기본값은 1억원
        if (discountValueInput) {
            discountValueInput.max = 100000000;
        }
    }
}

function form01_submit(f) {
    // 쿠폰코드 검증
    var couponCode = f.coupon_code.value.trim();
    if (!couponCode) {
        alert('쿠폰코드를 입력하세요.');
        f.coupon_code.focus();
        return false;
    }

    // 수정 모드인지 확인 (w 파라미터 확인)
    var isEditMode = f.w && f.w.value === 'u';

    // 하이픈 제거 (DB 저장용)
    var couponCodeWithoutHyphen = couponCode.replace(/-/g, '');

    if (!isEditMode) {
        // 신규 등록 모드: 4-4-4 형식 또는 12자리 형식 허용
        // 형식 검증: XXXX-XXXX-XXXX 또는 XXXXXXXXXXXX
        if (!/^[ABCDEFGHJKLMNPQRSTUVWXYZ23456789]{4}-[ABCDEFGHJKLMNPQRSTUVWXYZ23456789]{4}-[ABCDEFGHJKLMNPQRSTUVWXYZ23456789]{4}$/.test(couponCode) &&
            !/^[ABCDEFGHJKLMNPQRSTUVWXYZ23456789]{12}$/.test(couponCode)) {
            alert('쿠폰코드 형식이 올바르지 않습니다. (형식: XXXX-XXXX-XXXX 또는 12자리 영문숫자)');
            f.coupon_code.focus();
            return false;
        }

        // 하이픈 제거 후 12자리 검증
        if (couponCodeWithoutHyphen.length !== 12) {
            alert('쿠폰코드는 12자리여야 합니다.');
            f.coupon_code.focus();
            return false;
        }

        // DB에 저장될 값으로 변경 (하이픈 제거)
        f.coupon_code.value = couponCodeWithoutHyphen;
    } else {
        // 수정 모드: 기본 형식 검증만
        if (!/^[A-Za-z0-9_-]+$/.test(couponCode)) {
            alert('쿠폰코드는 영문, 숫자, 하이픈(-), 언더스코어(_)만 사용할 수 있습니다.');
            f.coupon_code.focus();
            return false;
        }
    }
    
    // 쿠폰명 검증
    var couponName = f.coupon_name.value.trim();
    if (!couponName) {
        alert('쿠폰명을 입력하세요.');
        f.coupon_name.focus();
        return false;
    }

    // 쿠폰명 길이 검증 (최대 50자)
    if (couponName.length > 50) {
        alert('쿠폰명은 최대 50자까지 입력 가능합니다.');
        f.coupon_name.focus();
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

    // 백분율 할인인 경우 할인값이 1~100 범위여야 함
    if (f.discount_type.value === 'PERCENT') {
        if (discountValue < 1 || discountValue > 100) {
            alert('백분율 할인은 1%에서 100% 사이여야 합니다.');
            f.discount_value.focus();
            return false;
        }
    }

    // 정액 할인인 경우 합리적인 범위 검증 (최대 1억원)
    if (f.discount_type.value === 'AMOUNT') {
        if (discountValue > 100000000) {
            alert('할인금액은 1억원을 초과할 수 없습니다.');
            f.discount_value.focus();
            return false;
        }
    }
    
    // 유효기간 검증
    var validFrom = f.valid_from.value;
    var validUntil = f.valid_until.value;
    
    if (!validFrom) {
        alert('유효기간 시작일을 선택하세요.');
        f.valid_from.focus();
        return false;
    }
    
    if (validUntil && validUntil < validFrom) {
        alert('유효기간 종료일은 시작일보다 늦어야 합니다.');
        f.valid_until.focus();
        return false;
    }
    
    // 1인당 발급한도 검증
    var issuedLimit = parseInt(f.issued_limit.value);
    if (!issuedLimit || issuedLimit < 1) {
        alert('1인당 발급한도를 올바르게 입력하세요.');
        f.issued_limit.focus();
        return false;
    }

    // 1인당 발급한도 최대값 검증 (최대 1000장)
    if (issuedLimit > 1000) {
        alert('1인당 발급한도는 최대 1,000장까지 가능합니다.');
        f.issued_limit.focus();
        return false;
    }

    // 전체 발급한도 검증
    var totalLimit = f.total_limit.value.trim();
    if (totalLimit) {
        totalLimit = parseInt(totalLimit);
        if (totalLimit < 1) {
            alert('전체 발급한도는 1 이상이어야 합니다.');
            f.total_limit.focus();
            return false;
        }

        // 전체 발급한도 최대값 검증 (최대 100만장)
        if (totalLimit > 1000000) {
            alert('전체 발급한도는 최대 1,000,000장까지 가능합니다.');
            f.total_limit.focus();
            return false;
        }
    }

    // 최대할인금액 검증 (백분율 할인인 경우)
    if (f.discount_type.value === 'PERCENT') {
        var maxDiscountAmt = f.max_discount_amt.value.trim();
        if (maxDiscountAmt) {
            maxDiscountAmt = parseInt(maxDiscountAmt);
            if (maxDiscountAmt < 0) {
                alert('최대할인금액은 0 이상이어야 합니다.');
                f.max_discount_amt.focus();
                return false;
            }

            // 최대할인금액 최대값 검증 (최대 1억원)
            if (maxDiscountAmt > 100000000) {
                alert('최대할인금액은 1억원을 초과할 수 없습니다.');
                f.max_discount_amt.focus();
                return false;
            }
        }
    }

    // 최소결제금액 검증
    var minPurchaseAmt = f.min_purchase_amt.value.trim();
    if (minPurchaseAmt) {
        minPurchaseAmt = parseInt(minPurchaseAmt);
        if (minPurchaseAmt < 0) {
            alert('최소결제금액은 0 이상이어야 합니다.');
            f.min_purchase_amt.focus();
            return false;
        }

        // 최소결제금액 최대값 검증 (최대 1억원)
        if (minPurchaseAmt > 100000000) {
            alert('최소결제금액은 1억원을 초과할 수 없습니다.');
            f.min_purchase_amt.focus();
            return false;
        }
    }

    // 상세설명 길이 검증 (최대 1000자)
    if (f.description && f.description.value.length > 1000) {
        alert('상세설명은 최대 1000자까지 입력 가능합니다.');
        f.description.focus();
        return false;
    }

    return true;
}

// 페이지 로드 시 초기 설정
document.addEventListener('DOMContentLoaded', function() {
    toggleDiscountFields();
});
</script>
