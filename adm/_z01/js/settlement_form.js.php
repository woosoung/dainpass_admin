<script>
// 가맹점 선택 팝업 열기
function open_shop_popup() {
    const winShop = window.open(
        './_win_shop_select.php',
        'winShop',
        'width=900, height=700, left=100, top=100, scrollbars=yes'
    );
    if (winShop) {
        winShop.focus();
    }
}

// 팝업에서 가맹점 선택 시 호출될 함수
function set_selected_shop(shop_id, shop_name) {
    document.getElementById('shop_id').value = shop_id;
    document.getElementById('shop_name_display').value = shop_name || '';
    
    // 신규 등록 모드에서만 결제 목록 불러오기
    const paymentSelect = document.getElementById('payment_id');
    if (paymentSelect && paymentSelect.tagName === 'SELECT') {
        loadPaymentsByShop(shop_id);
    }
}

// 가맹점별 결제 목록 불러오기
function loadPaymentsByShop(shop_id) {
    const paymentSelect = document.getElementById('payment_id');
    if (!paymentSelect || !shop_id) {
        return;
    }
    
    // 로딩 표시
    paymentSelect.disabled = true;
    paymentSelect.innerHTML = '<option value="">결제 정보를 불러오는 중...</option>';
    
    fetch('./ajax/settlement_form_data.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_payments_by_shop&shop_id=' + encodeURIComponent(shop_id)
    })
    .then(response => {
        // 응답이 JSON인지 확인
        const contentType = response.headers.get('content-type');
        
        if (!contentType || !contentType.includes('application/json')) {
            // JSON이 아닌 경우 전체 응답 텍스트를 로그로 출력
            return response.text().then(text => {
                console.error('=== Non-JSON Response Start ===');
                console.error('Status:', response.status);
                console.error('Status Text:', response.statusText);
                console.error('Response Body:', text);
                console.error('Response Body Length:', text.length);
                console.error('=== Non-JSON Response End ===');
                
                // HTML 오류 페이지인 경우 일부 추출
                if (text.includes('<html') || text.includes('<!DOCTYPE')) {
                    const titleMatch = text.match(/<title>(.*?)<\/title>/i);
                    const bodyMatch = text.match(/<body[^>]*>(.*?)<\/body>/is);
                    console.error('HTML Title:', titleMatch ? titleMatch[1] : 'N/A');
                    if (bodyMatch) {
                        // body 내용의 일부만 추출 (너무 길면 잘라냄)
                        const bodyText = bodyMatch[1].replace(/<[^>]+>/g, ' ').substring(0, 500);
                        console.error('HTML Body (first 500 chars):', bodyText);
                    }
                }
                
                throw new Error('서버 응답이 올바르지 않습니다. (상세 내용은 콘솔 확인)');
            });
        }
        
        // JSON 응답 파싱
        return response.json().catch(err => {
            console.error('JSON Parse Error:', err);
            return response.text().then(text => {
                console.error('Failed to parse JSON. Response text:', text);
                throw new Error('JSON 파싱 실패: ' + err.message);
            });
        });
    })
    .then(data => {
        if (data.success) {
            if (data.data && data.data.length > 0) {
                paymentSelect.innerHTML = '<option value="">결제 정보를 선택하세요</option>';
                data.data.forEach(function(payment) {
                    const option = document.createElement('option');
                    option.value = payment.payment_id;
                    const displayText = `[${payment.payment_id}] ${payment.customer_name || '고객명 없음'} - ${payment.paid_at ? new Date(payment.paid_at).toLocaleString('ko-KR') : ''}`;
                    option.textContent = displayText;
                option.dataset.payFlag = payment.pay_flag || '';
                option.dataset.appointmentId = payment.appointment_id || '';
                option.dataset.shopdetailId = payment.shopdetail_id || '';
                option.dataset.personalId = payment.personal_id || '';
                option.dataset.customerId = payment.customer_id || '';
                option.dataset.appointmentDatetime = payment.appointment_datetime || '';
                option.dataset.amount = payment.amount || '';
                paymentSelect.appendChild(option);
                });
            } else {
                paymentSelect.innerHTML = '<option value="">결제 정보가 없습니다</option>';
            }
        } else {
            // 서버에서 오류 메시지 반환
            const errorMsg = data.message || '결제 정보를 불러오는 중 오류가 발생했습니다';
            paymentSelect.innerHTML = '<option value="">' + errorMsg + '</option>';
            
            // 상세 오류 정보 로그 출력
            console.error('=== Server Error Details ===');
            console.error('Error Message:', data.message);
            console.error('Error Object:', data.error);
            if (data.error) {
                console.error('Error Type:', data.error.type);
                console.error('Error Message:', data.error.message);
                if (data.error.file) {
                    console.error('Error File:', data.error.file);
                    console.error('Error Line:', data.error.line);
                }
                if (data.error.pg_error) {
                    console.error('PostgreSQL Error:', data.error.pg_error);
                }
                if (data.error.sql) {
                    console.error('SQL Query:', data.error.sql);
                }
                if (data.error.trace) {
                    console.error('Stack Trace:', data.error.trace);
                }
            }
            console.error('Full Response:', JSON.stringify(data, null, 2));
            console.error('=== Server Error Details End ===');
        }
        paymentSelect.disabled = false;
    })
    .catch(error => {
        console.error('Error:', error);
        paymentSelect.innerHTML = '<option value="">결제 정보를 불러오는 중 오류가 발생했습니다</option>';
        paymentSelect.disabled = false;
    });
}

document.addEventListener('DOMContentLoaded', function(){
    const paymentIdInput = document.getElementById('payment_id');
    const btnLoadPayment = document.getElementById('btn_load_payment');
    const paymentInfoDiv = document.getElementById('payment_info');
    const totalPaymentAmount = document.getElementById('total_payment_amount');
    const cancelAmount = document.getElementById('cancel_amount');
    const netSettlementAmount = document.getElementById('net_settlement_amount');
    const payFlagSelect = document.getElementById('pay_flag');
    const shopIdSelect = document.getElementById('shop_id');

    // 정산금액 자동 계산
    function calculateNetAmount() {
        const total = parseFloat(totalPaymentAmount.value) || 0;
        const cancel = parseFloat(cancelAmount.value) || 0;
        const net = total - cancel;
        netSettlementAmount.value = Math.max(0, Math.floor(net));
    }

    if (totalPaymentAmount && cancelAmount && netSettlementAmount) {
        totalPaymentAmount.addEventListener('input', calculateNetAmount);
        cancelAmount.addEventListener('input', calculateNetAmount);
    }

    // 결제 정보 불러오기
    if (btnLoadPayment && paymentIdInput) {
        btnLoadPayment.addEventListener('click', function() {
            const paymentId = paymentIdInput.value.trim();
            if (!paymentId) {
                alert('결제 ID를 입력해 주세요.');
                paymentIdInput.focus();
                return;
            }

            // 로딩 표시
            paymentInfoDiv.style.display = 'block';
            paymentInfoDiv.innerHTML = '<p>결제 정보를 불러오는 중...</p>';

            fetch('./ajax/settlement_form_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_payment&payment_id=' + encodeURIComponent(paymentId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const payment = data.data;
                    
                    // 필드 자동 채우기
                    if (payment.pay_flag) {
                        payFlagSelect.value = payment.pay_flag;
                    }
                    if (payment.shopdetail_id) {
                        document.getElementById('shopdetail_id').value = payment.shopdetail_id;
                    }
                    if (payment.personal_id) {
                        document.getElementById('personal_id').value = payment.personal_id;
                    }
                    if (payment.appointment_datetime) {
                        const dt = new Date(payment.appointment_datetime);
                        const dtLocal = dt.toISOString().slice(0, 16);
                        document.getElementById('appointment_datetime').value = dtLocal;
                    }
                    if (payment.amount || payment.total_payment_amount) {
                        totalPaymentAmount.value = payment.amount || payment.total_payment_amount;
                    }
                    if (payment.cancel_amount) {
                        cancelAmount.value = payment.cancel_amount;
                    }
                    
                    // 정산금액 계산
                    calculateNetAmount();

                    // 결제 정보 표시
                    let infoHtml = '<h4>결제 정보</h4>';
                    infoHtml += '<p><strong>결제 ID:</strong> ' + payment.payment_id + '</p>';
                    infoHtml += '<p><strong>결제 유형:</strong> ' + (payment.pay_flag || '-') + '</p>';
                    infoHtml += '<p><strong>금액:</strong> ' + (payment.amount || payment.total_payment_amount || 0).toLocaleString() + '원</p>';
                    if (payment.shop_id) {
                        infoHtml += '<p><strong>가맹점 ID:</strong> ' + payment.shop_id + '</p>';
                        // 가맹점 선택도 업데이트
                        if (shopIdSelect) {
                            shopIdSelect.value = payment.shop_id;
                            // 가맹점명도 업데이트 (가능한 경우)
                            if (payment.shop_name) {
                                const shopNameDisplay = document.getElementById('shop_name_display');
                                if (shopNameDisplay) {
                                    shopNameDisplay.value = payment.shop_name;
                                }
                            }
                        }
                    }
                    paymentInfoDiv.innerHTML = infoHtml;
                } else {
                    paymentInfoDiv.innerHTML = '<p class="text-red-600">' + (data.message || '결제 정보를 찾을 수 없습니다.') + '</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                paymentInfoDiv.innerHTML = '<p class="text-red-600">결제 정보를 불러오는 중 오류가 발생했습니다.</p>';
            });
        });
    }

    // 결제 선택박스에서 결제 선택 시 정보 자동 채우기 (신규 등록 모드)
    const paymentSelect = document.getElementById('payment_id');
    if (paymentSelect && paymentSelect.tagName === 'SELECT') {
        paymentSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.value) {
                // 선택박스의 dataset에서 정보 가져오기
                const paymentData = {
                    payment_id: selectedOption.value,
                    pay_flag: selectedOption.dataset.payFlag || '',
                    shopdetail_id: selectedOption.dataset.shopdetailId || '',
                    personal_id: selectedOption.dataset.personalId || '',
                    appointment_datetime: selectedOption.dataset.appointmentDatetime || '',
                    amount: selectedOption.dataset.amount || ''
                };
                
                // 선택된 결제 ID로 상세 정보 불러오기 (더 많은 정보를 위해)
                loadPaymentDetails(selectedOption.value, paymentData);
            } else {
                // 선택 해제 시 필드 초기화
                clearPaymentFields();
            }
        });
    }
    
    // 결제 필드 초기화 함수
    function clearPaymentFields() {
        if (payFlagSelect) payFlagSelect.value = '';
        const shopdetailInput = document.getElementById('shopdetail_id');
        if (shopdetailInput) {
            shopdetailInput.value = '';
            shopdetailInput.removeAttribute('readonly');
        }
        const personalInput = document.getElementById('personal_id');
        if (personalInput) {
            personalInput.value = '';
            personalInput.removeAttribute('readonly');
        }
        const appointmentInput = document.getElementById('appointment_datetime');
        if (appointmentInput) {
            appointmentInput.value = '';
            appointmentInput.removeAttribute('required');
        }
        if (totalPaymentAmount) totalPaymentAmount.value = '';
        if (cancelAmount) cancelAmount.value = '';
        if (netSettlementAmount) netSettlementAmount.value = '';
    }
    
    // 결제 상세 정보 불러오기 함수
    function loadPaymentDetails(paymentId, paymentDataFromSelect) {
        if (!paymentId) return;
        
        // 먼저 선택박스의 데이터로 필드 채우기
        if (paymentDataFromSelect) {
            fillPaymentFields(paymentDataFromSelect);
        }
        
        // AJAX로 더 상세한 정보 가져오기
        fetch('./ajax/settlement_form_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_payment&payment_id=' + encodeURIComponent(paymentId)
        })
        .then(response => {
            // 응답 상태 확인
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('HTTP Error:', response.status, response.statusText);
                    console.error('Response Body:', text);
                    throw new Error('서버 오류: ' + response.status);
                });
            }
            
            // 응답이 JSON인지 확인
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    console.error('Non-JSON response:', text);
                    throw new Error('서버 응답이 올바르지 않습니다.');
                });
            }
            
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const payment = data.data;
                // AJAX로 가져온 데이터로 필드 업데이트 (더 정확한 정보)
                fillPaymentFields(payment);
            } else {
                console.warn('Payment details not loaded:', data.message || '알 수 없는 오류');
            }
        })
        .catch(error => {
            console.error('Error loading payment details:', error);
            // 오류가 발생해도 선택박스의 데이터로 이미 필드를 채웠으므로 계속 진행
        });
    }
    
    // 결제 정보로 필드 채우기 함수
    function fillPaymentFields(payment) {
        // 1. 결제 유형 자동 선택
        if (payment.pay_flag && payFlagSelect) {
            payFlagSelect.value = payment.pay_flag;
        }
        
        // 2. shopdetail_id가 있으면 '예약 내역 ID'에 자동입력(수동입력불가)
        const shopdetailInput = document.getElementById('shopdetail_id');
        if (shopdetailInput) {
            if (payment.shopdetail_id) {
                shopdetailInput.value = payment.shopdetail_id;
                shopdetailInput.setAttribute('readonly', 'readonly');
                shopdetailInput.style.backgroundColor = '#f5f5f5';
            } else {
                shopdetailInput.removeAttribute('readonly');
                shopdetailInput.style.backgroundColor = '';
            }
        }
        
        // 3. personal_id가 있으면 '개인 결제 ID'에 자동입력(수동입력불가)
        const personalInput = document.getElementById('personal_id');
        if (personalInput) {
            if (payment.personal_id) {
                personalInput.value = payment.personal_id;
                personalInput.setAttribute('readonly', 'readonly');
                personalInput.style.backgroundColor = '#f5f5f5';
            } else {
                personalInput.removeAttribute('readonly');
                personalInput.style.backgroundColor = '';
            }
        }
        
        // 4. appointment_datetime이 있으면 '예약일시'에 자동설정(필수입력항목 아님)
        const appointmentInput = document.getElementById('appointment_datetime');
        if (appointmentInput) {
            if (payment.appointment_datetime) {
                const dt = new Date(payment.appointment_datetime);
                const dtLocal = dt.toISOString().slice(0, 16);
                appointmentInput.value = dtLocal;
            }
            // required 속성은 항상 제거 (필수입력 아님)
            appointmentInput.removeAttribute('required');
        }
        
        // 5. amount가 있으면 '결제금액'에 자동입력(수동입력가능)
        if (totalPaymentAmount) {
            if (payment.amount || payment.total_payment_amount) {
                totalPaymentAmount.value = payment.amount || payment.total_payment_amount;
            }
        }
        
        if (payment.cancel_amount && cancelAmount) {
            cancelAmount.value = payment.cancel_amount;
        }
        
        // 정산금액 계산
        if (totalPaymentAmount && cancelAmount && netSettlementAmount) {
            const total = parseFloat(totalPaymentAmount.value) || 0;
            const cancel = parseFloat(cancelAmount.value) || 0;
            const net = total - cancel;
            netSettlementAmount.value = Math.max(0, Math.floor(net));
        }
    }
});

function form01_submit(f) {
    const shopIdInput = document.getElementById('shop_id');
    if (!shopIdInput || !shopIdInput.value || shopIdInput.value == '') {
        alert('가맹점을 선택해 주세요.');
        if (typeof open_shop_popup === 'function') {
            if (confirm('가맹점을 선택하시겠습니까?')) {
                open_shop_popup();
            }
        }
        return false;
    }

    // pay_flag 검증 (disabled된 경우 hidden 필드 확인)
    const payFlagSelect = document.getElementById('pay_flag');
    const payFlagValue = payFlagSelect && payFlagSelect.disabled 
        ? (document.querySelector('input[name="pay_flag"][type="hidden"]')?.value || '')
        : (f.pay_flag?.value || '');
    
    if (!payFlagValue || payFlagValue == '') {
        alert('결제 유형을 선택해 주세요.');
        if (payFlagSelect && !payFlagSelect.disabled) {
            payFlagSelect.focus();
        }
        return false;
    }

    const totalAmount = parseFloat(f.total_payment_amount.value) || 0;
    if (totalAmount <= 0) {
        alert('결제금액을 입력해 주세요.');
        f.total_payment_amount.focus();
        return false;
    }

    const netAmount = parseFloat(f.net_settlement_amount.value) || 0;
    if (netAmount < 0) {
        alert('정산금액이 올바르지 않습니다.');
        f.net_settlement_amount.focus();
        return false;
    }

    return true;
}
</script>

