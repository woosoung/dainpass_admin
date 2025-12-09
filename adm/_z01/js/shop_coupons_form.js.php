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
    
    // shop_id 가져오기 (JavaScript 변수 또는 현재 코드에서 추출)
    var shopId = null;
    if (typeof shopIdForCouponCode !== 'undefined') {
        shopId = shopIdForCouponCode;
    } else {
        // 현재 쿠폰코드에서 shop_id 부분 추출
        var currentCode = couponCodeInput.value;
        var match = currentCode.match(/^SHOP(\d+)-/);
        if (match && match[1]) {
            shopId = match[1];
        }
    }
    
    if (shopId) {
        // 8자리 영문숫자 조합 난수 생성
        var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        var randomString = '';
        for (var i = 0; i < 8; i++) {
            randomString += characters.charAt(Math.floor(Math.random() * characters.length));
        }
        var newCode = 'SHOP' + shopId + '-' + randomString;
        couponCodeInput.value = newCode;
    } else {
        // shop_id를 찾을 수 없으면 경고
        alert('가맹점 정보를 찾을 수 없습니다. 페이지를 새로고침해주세요.');
    }
}

function toggleDiscountFields() {
    var discountType = document.getElementById('discount_type').value;
    var maxDiscountAmtRow = document.getElementById('tr_max_discount_amt');
    var discountUnit = document.getElementById('discount_unit');
    
    if (discountType === 'PERCENT') {
        // 백분율 할인인 경우 최대할인금액 필드 표시
        if (maxDiscountAmtRow) {
            maxDiscountAmtRow.style.display = '';
        }
        if (discountUnit) {
            discountUnit.textContent = '%';
        }
    } else if (discountType === 'AMOUNT') {
        // 정액 할인인 경우 최대할인금액 필드 숨김
        if (maxDiscountAmtRow) {
            maxDiscountAmtRow.style.display = 'none';
        }
        if (discountUnit) {
            discountUnit.textContent = '원';
        }
    } else {
        // 선택 안된 경우
        if (maxDiscountAmtRow) {
            maxDiscountAmtRow.style.display = 'none';
        }
        if (discountUnit) {
            discountUnit.textContent = '원';
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
    
    if (!isEditMode) {
        // 신규 등록 모드: SHOP{shop_id}-{8자리영문숫자} 형식 검증
        var shopId = typeof shopIdForCouponCode !== 'undefined' ? shopIdForCouponCode : '';
        if (shopId) {
            var expectedPattern = new RegExp('^SHOP' + shopId + '-[A-Z0-9]{8}$');
            if (!expectedPattern.test(couponCode)) {
                alert('쿠폰코드 형식이 올바르지 않습니다. (형식: SHOP' + shopId + '-{8자리영문숫자})');
                f.coupon_code.focus();
                return false;
            }
        } else {
            // shop_id를 찾을 수 없는 경우 기본 형식 검증
            if (!/^SHOP\d+-[A-Z0-9]{8}$/.test(couponCode)) {
                alert('쿠폰코드 형식이 올바르지 않습니다.');
                f.coupon_code.focus();
                return false;
            }
        }
    } else {
        // 수정 모드: 기본 형식 검증만
        if (!/^[A-Za-z0-9_-]+$/.test(couponCode)) {
            alert('쿠폰코드는 영문, 숫자, 하이픈(-), 언더스코어(_)만 사용할 수 있습니다.');
            f.coupon_code.focus();
            return false;
        }
    }
    
    // 쿠폰명 검증
    if (!f.coupon_name.value.trim()) {
        alert('쿠폰명을 입력하세요.');
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
    
    // 백분율 할인인 경우 할인값이 100 이하여야 함
    if (f.discount_type.value === 'PERCENT' && discountValue > 100) {
        alert('백분율 할인은 100%를 초과할 수 없습니다.');
        f.discount_value.focus();
        return false;
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
    
    // 전체 발급한도 검증
    var totalLimit = f.total_limit.value.trim();
    if (totalLimit) {
        totalLimit = parseInt(totalLimit);
        if (totalLimit < 1) {
            alert('전체 발급한도는 1 이상이어야 합니다.');
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
    }
    
    return true;
}

// 페이지 로드 시 초기 설정
document.addEventListener('DOMContentLoaded', function() {
    toggleDiscountFields();
});
</script>
