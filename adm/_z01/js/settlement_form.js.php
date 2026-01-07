<script>
// 가맹점 선택 팝업 열기
function open_shop_popup() {
    const winShop = window.open(
        './_win_shop_select.php',
        'winShop',
        'width=900, height=700, left=100, top=100, scrollbars=yes'
    );
    winShop.focus();
}

// 팝업에서 가맹점 선택 시 호출될 함수
function set_selected_shop(shop_id, shop_name) {
    document.getElementById('shop_id').value = shop_id;
    document.getElementById('shop_name_display').value = shop_name || '';
    
    // 신규 등록 모드에서만 결제 목록 로드
    var w = document.querySelector('input[name="w"]').value;
    if (w == '') {
        loadPaymentsByShop(shop_id);
    }
}

// 가맹점별 결제 목록 로드
function loadPaymentsByShop(shop_id) {
    if (!shop_id || shop_id <= 0) {
        return;
    }
    
    var paymentSelect = document.getElementById('payment_id');
    if (!paymentSelect) {
        return;
    }
    
    // 로딩 상태
    paymentSelect.disabled = true;
    paymentSelect.innerHTML = '<option value="">로딩 중...</option>';
    
    fetch('./ajax/settlement_form_data.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_payments_by_shop&shop_id=' + encodeURIComponent(shop_id)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.payments) {
            paymentSelect.innerHTML = '<option value="">결제를 선택하세요</option>';
            data.payments.forEach(function(payment) {
                var option = document.createElement('option');
                option.value = payment.paymentId;
                option.textContent = payment.displayText;
                option.setAttribute('data-payment', JSON.stringify(payment));
                paymentSelect.appendChild(option);
            });
            paymentSelect.disabled = false;
        } else {
            paymentSelect.innerHTML = '<option value="">결제 정보가 없습니다</option>';
            paymentSelect.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        paymentSelect.innerHTML = '<option value="">결제 정보를 불러오는 중 오류가 발생했습니다.</option>';
        paymentSelect.disabled = false;
    });
}

// 결제 선택 시 상세 정보 로드
function loadPaymentDetails(paymentId, paymentDataFromSelect) {
    if (!paymentId || paymentId <= 0) {
        return;
    }
    
    var paymentData = null;
    if (paymentDataFromSelect) {
        try {
            paymentData = JSON.parse(paymentDataFromSelect);
        } catch (e) {
            console.error('Error parsing payment data:', e);
        }
    }
    
    if (paymentData) {
        fillPaymentFields(paymentData);
    } else {
        // 서버에서 상세 정보 가져오기
        fetch('./ajax/settlement_form_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_payment&payment_id=' + encodeURIComponent(paymentId)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.payment) {
                fillPaymentFields(data.payment);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
}

// 결제 정보로 폼 필드 채우기
function fillPaymentFields(payment) {
    if (payment.payFlag) {
        document.getElementById('pay_flag').value = payment.payFlag;
    }
    
    if (payment.shopdetailId) {
        document.getElementById('shopdetail_id').value = payment.shopdetailId;
        document.getElementById('shopdetail_id').readOnly = true;
    }
    
    if (payment.personalId) {
        document.getElementById('personal_id').value = payment.personalId;
        document.getElementById('personal_id').readOnly = true;
    }
    
    if (payment.appointmentDatetime) {
        document.getElementById('appointment_datetime').value = payment.appointmentDatetime;
    }
    
    if (payment.amount) {
        document.getElementById('total_payment_amount').value = payment.amount;
        calculateNetAmount();
    }
}

// 결제 필드 초기화
function clearPaymentFields() {
    document.getElementById('pay_flag').value = '';
    document.getElementById('shopdetail_id').value = '';
    document.getElementById('shopdetail_id').readOnly = false;
    document.getElementById('personal_id').value = '';
    document.getElementById('personal_id').readOnly = false;
    document.getElementById('appointment_datetime').value = '';
    document.getElementById('total_payment_amount').value = '';
    document.getElementById('cancel_amount').value = '0';
    document.getElementById('net_settlement_amount').value = '';
}

// 정산금액 자동 계산
function calculateNetAmount() {
    var totalPayment = parseFloat(document.getElementById('total_payment_amount').value) || 0;
    var cancelAmount = parseFloat(document.getElementById('cancel_amount').value) || 0;
    var netSettlementAmount = Math.floor(totalPayment - cancelAmount);
    
    if (netSettlementAmount < 0) {
        netSettlementAmount = 0;
    }
    
    document.getElementById('net_settlement_amount').value = netSettlementAmount;
}

// 페이지 로드 시 이벤트 바인딩
document.addEventListener('DOMContentLoaded', function() {
    var paymentSelect = document.getElementById('payment_id');
    if (paymentSelect && paymentSelect.tagName === 'SELECT') {
        paymentSelect.addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                var paymentData = selectedOption.getAttribute('data-payment');
                loadPaymentDetails(selectedOption.value, paymentData);
            } else {
                clearPaymentFields();
            }
        });
    }
    
    var totalPaymentInput = document.getElementById('total_payment_amount');
    var cancelAmountInput = document.getElementById('cancel_amount');
    
    if (totalPaymentInput) {
        totalPaymentInput.addEventListener('input', calculateNetAmount);
        totalPaymentInput.addEventListener('change', calculateNetAmount);
    }
    
    if (cancelAmountInput) {
        cancelAmountInput.addEventListener('input', calculateNetAmount);
        cancelAmountInput.addEventListener('change', calculateNetAmount);
    }
});

// 폼 제출 검증
function form01_submit(f) {
    var shop_id = f.shop_id.value;
    if (!shop_id || shop_id <= 0) {
        alert('가맹점을 선택해 주세요.');
        return false;
    }
    
    var pay_flag = f.pay_flag.value;
    if (!pay_flag) {
        // 수정 모드에서 disabled인 경우 hidden input 확인
        var pay_flag_hidden = document.querySelector('input[name="pay_flag"][type="hidden"]');
        if (pay_flag_hidden) {
            pay_flag = pay_flag_hidden.value;
        }
        if (!pay_flag) {
            alert('결제 유형을 선택해 주세요.');
            return false;
        }
    }
    
    var total_payment_amount = parseFloat(f.total_payment_amount.value) || 0;
    if (total_payment_amount <= 0) {
        alert('결제금액을 입력해 주세요.');
        return false;
    }
    
    var net_settlement_amount = parseFloat(f.net_settlement_amount.value) || 0;
    if (net_settlement_amount < 0) {
        alert('정산금액이 올바르지 않습니다.');
        return false;
    }
    
    return true;
}
</script>