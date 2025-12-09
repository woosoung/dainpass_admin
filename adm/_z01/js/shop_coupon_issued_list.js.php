<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
var shopId = <?php echo $shop_id; ?>;
var userIdCheckTimer = null;
var isValidUserId = false;

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
    
    if (!confirm('선택한 ' + checked_count + '개의 발급 쿠폰을 삭제하시겠습니까?')) {
        return false;
    }
    
    // 토큰 확인
    if (!f.token || !f.token.value) {
        alert('토큰 정보가 올바르지 않습니다. 페이지를 새로고침해주세요.');
        return false;
    }
    
    // AJAX로 삭제 처리
    var formData = new FormData();
    formData.append('token', f.token.value);
    formData.append('act', 'delete');
    
    var chkElements = f.querySelectorAll('input[name="chk[]"]:checked');
    chkElements.forEach(function(el) {
        formData.append('chk[]', el.value);
    });
    
    fetch('./ajax/shop_coupon_issued_del.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message || '선택한 항목이 삭제되었습니다.');
            location.reload();
        } else {
            // 메시지의 \n을 실제 줄바꿈으로 변환
            var message = (data.message || '삭제 중 오류가 발생했습니다.').replace(/\\n/g, '\n');
            alert(message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('삭제 중 오류가 발생했습니다. (오류: ' + error.message + ')');
    });
    
    return false;
}

function check_all(f) {
    var chk = document.getElementsByName("chk[]");
    
    for (var i=0; i<chk.length; i++) {
        chk[i].checked = f.checked;
    }
}

function addCoupon() {
    var actionEl = document.getElementById('action');
    var modalTitleEl = document.getElementById('modalTitle');
    var couponModalEl = document.getElementById('couponModal');
    
    if (!actionEl || !modalTitleEl || !couponModalEl) {
        alert('모달 요소를 찾을 수 없습니다. 페이지를 새로고침해주세요.');
        return;
    }
    
    // 먼저 발급 가능한 쿠폰 목록 확인
    fetch('./ajax/shop_coupon_issued_get.php?action=get_available_coupons&shop_id=' + shopId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.coupons && data.coupons.length > 0) {
                // 쿠폰이 있으면 모달 열기
                openCouponModal();
            } else {
                // 쿠폰이 없으면 경고창 표시
                alert('발급 가능한 쿠폰이 없습니다.\n\n먼저 쿠폰관리 메뉴에서 쿠폰을 생성해주세요.');
                return;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('쿠폰 목록을 불러오는 중 오류가 발생했습니다.');
        });
}

function openCouponModal() {
    var actionEl = document.getElementById('action');
    var modalTitleEl = document.getElementById('modalTitle');
    var couponModalEl = document.getElementById('couponModal');
    
    actionEl.value = 'add';
    modalTitleEl.innerText = '쿠폰발급';
    document.getElementById('modal_customer_coupon_id').value = '';
    
    // 폼 초기화
    var frmCoupon = document.getElementById('frmCoupon');
    if (frmCoupon) {
        frmCoupon.reset();
    }
    
    // 신규 발급 모드로 전환
    document.getElementById('tr_coupon_select').style.display = '';
    document.getElementById('tr_user_id').style.display = '';
    document.getElementById('tr_coupon_info').style.display = 'none';
    document.getElementById('tr_customer_info').style.display = 'none';
    document.getElementById('tr_issued_at').style.display = 'none';
    document.getElementById('tr_status').style.display = 'none'; // 신규 발급 시 상태 필드 숨김
    var statusSelect = document.getElementById('modal_status');
    statusSelect.value = 'ISSUED'; // 자동으로 ISSUED 설정
    statusSelect.removeAttribute('required'); // 신규 등록 시 required 제거
    
    isValidUserId = false;
    document.getElementById('user_id_check').innerHTML = '';
    
    // 발급 가능한 쿠폰 목록 로드
    loadAvailableCoupons();
    
    couponModalEl.style.display = 'block';
}

function editCoupon(customer_coupon_id) {
    var actionEl = document.getElementById('action');
    var modalTitleEl = document.getElementById('modalTitle');
    var couponModalEl = document.getElementById('couponModal');
    
    if (!actionEl || !modalTitleEl || !couponModalEl) {
        alert('모달 요소를 찾을 수 없습니다. 페이지를 새로고침해주세요.');
        return;
    }
    
    actionEl.value = 'edit';
    modalTitleEl.innerText = '쿠폰발급 수정';
    document.getElementById('modal_customer_coupon_id').value = customer_coupon_id;
    
    // 수정 모드로 전환
    document.getElementById('tr_coupon_select').style.display = 'none';
    document.getElementById('tr_user_id').style.display = 'none';
    document.getElementById('tr_coupon_info').style.display = '';
    document.getElementById('tr_customer_info').style.display = '';
    document.getElementById('tr_issued_at').style.display = '';
    document.getElementById('tr_status').style.display = ''; // 수정 시 상태 필드 표시
    var statusSelect = document.getElementById('modal_status');
    statusSelect.disabled = false;
    statusSelect.setAttribute('required', 'required'); // 수정 시 required 추가
    document.getElementById('status_help_text').style.display = '';
    
    // 데이터 로드
    loadCouponData(customer_coupon_id);
    
    couponModalEl.style.display = 'block';
}

function loadAvailableCoupons() {
    fetch('./ajax/shop_coupon_issued_get.php?action=get_available_coupons&shop_id=' + shopId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.coupons) {
                var select = document.getElementById('modal_coupon_id');
                select.innerHTML = '<option value="">쿠폰을 선택하세요</option>';
                data.coupons.forEach(function(coupon) {
                    var option = document.createElement('option');
                    option.value = coupon.coupon_id;
                    option.textContent = coupon.coupon_code + ' - ' + coupon.coupon_name;
                    select.appendChild(option);
                });
            } else {
                alert('발급 가능한 쿠폰을 불러올 수 없습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('쿠폰 목록을 불러오는 중 오류가 발생했습니다.');
        });
}

function loadCouponData(customer_coupon_id) {
    fetch('./ajax/shop_coupon_issued_get.php?action=get_coupon&customer_coupon_id=' + customer_coupon_id)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.coupon) {
                var coupon = data.coupon;
                
                // 쿠폰 정보 표시
                document.getElementById('modal_coupon_info').innerHTML = 
                    '<strong>' + (coupon.coupon_code || '') + '</strong> - ' + (coupon.coupon_name || '');
                
                // 회원 정보 표시
                document.getElementById('modal_customer_info').innerHTML = 
                    'ID: ' + (coupon.user_id || '') + '<br>' + 
                    '이름: ' + (coupon.customer_name || '');
                
                // 발급일시 표시
                document.getElementById('modal_issued_at').innerHTML = coupon.issued_at || '-';
                
                // 상태 설정 (수정 가능한 상태만)
                var statusSelect = document.getElementById('modal_status');
                statusSelect.innerHTML = '';
                
                var currentStatus = coupon.status || 'ISSUED';
                var options = [
                    {value: 'ISSUED', text: '발급됨'},
                    {value: 'USED', text: '사용됨'},
                    {value: 'EXPIRED', text: '만료됨'}
                ];
                
                options.forEach(function(opt) {
                    var option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.text;
                    
                    // 현재 상태에 따라 옵션 설정
                    if (currentStatus === 'USED') {
                        // USED 상태에서는 ISSUED로만 변경 가능
                        if (opt.value === 'ISSUED') {
                            option.selected = true;
                        } else if (opt.value === currentStatus) {
                            option.selected = true;
                        } else {
                            option.disabled = true;
                        }
                    } else if (currentStatus === 'EXPIRED') {
                        // EXPIRED 상태에서는 변경 불가 (현재 상태만 선택 가능)
                        if (opt.value === currentStatus) {
                            option.selected = true;
                        } else {
                            option.disabled = true;
                        }
                    } else if (currentStatus === 'ISSUED') {
                        // ISSUED 상태에서는 다른 상태로 변경 불가
                        if (opt.value === currentStatus) {
                            option.selected = true;
                        } else {
                            option.disabled = true;
                        }
                    }
                    
                    statusSelect.appendChild(option);
                });
            } else {
                alert(data.message || '쿠폰 정보를 불러올 수 없습니다.');
                closeModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('쿠폰 정보를 불러오는 중 오류가 발생했습니다.');
            closeModal();
        });
}

function checkUserId() {
    var userIdInput = document.getElementById('modal_user_id');
    var userIdCheckDiv = document.getElementById('user_id_check');
    
    if (!userIdInput || !userIdCheckDiv) {
        return;
    }
    
    var userId = userIdInput.value.trim();
    
    if (!userId) {
        userIdCheckDiv.innerHTML = '';
        isValidUserId = false;
        return;
    }
    
    // 타이머로 중복 요청 방지
    if (userIdCheckTimer) {
        clearTimeout(userIdCheckTimer);
    }
    
    userIdCheckTimer = setTimeout(function() {
        var formData = new FormData();
        formData.append('user_id', userId);
        
        fetch('./ajax/customer_id_check.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data === '1') {
                userIdCheckDiv.innerHTML = '<span class="valid">✓ 사용 가능한 회원입니다.</span>';
                userIdCheckDiv.className = 'user_id_check valid';
                isValidUserId = true;
            } else {
                userIdCheckDiv.innerHTML = '<span class="invalid">✗ 존재하지 않거나 비활성화된 회원입니다.</span>';
                userIdCheckDiv.className = 'user_id_check invalid';
                isValidUserId = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            userIdCheckDiv.innerHTML = '<span class="invalid">회원 확인 중 오류가 발생했습니다.</span>';
            userIdCheckDiv.className = 'user_id_check invalid';
            isValidUserId = false;
        });
    }, 500);
}

function saveCoupon() {
    var form = document.getElementById('frmCoupon');
    var action = document.getElementById('action').value;
    
    if (action === 'add') {
        // 신규 발급 검증
        var couponId = document.getElementById('modal_coupon_id').value;
        var userId = document.getElementById('modal_user_id').value.trim();
        
        if (!couponId) {
            alert('쿠폰을 선택하세요.');
            document.getElementById('modal_coupon_id').focus();
            return;
        }
        
        if (!userId) {
            alert('회원ID를 입력하세요.');
            document.getElementById('modal_user_id').focus();
            return;
        }
        
        if (!isValidUserId) {
            alert('유효한 회원ID를 입력하세요.');
            document.getElementById('modal_user_id').focus();
            return;
        }
    } else {
        // 수정 검증
        var customerCouponId = document.getElementById('modal_customer_coupon_id').value;
        var status = document.getElementById('modal_status').value;
        
        if (!customerCouponId) {
            alert('쿠폰 정보가 올바르지 않습니다.');
            return;
        }
        
        if (!status) {
            alert('상태를 선택하세요.');
            document.getElementById('modal_status').focus();
            return;
        }
    }
    
    // 폼 데이터 수집
    var formData = new FormData(form);
    formData.append('token', '<?php echo get_admin_token(); ?>');
    
    // AJAX로 저장
    fetch('./shop_coupon_issued_list_updaate.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message || '저장되었습니다.');
            location.reload();
        } else {
            alert(data.message || '저장 중 오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('저장 중 오류가 발생했습니다.');
    });
}

function closeModal() {
    var couponModalEl = document.getElementById('couponModal');
    if (couponModalEl) {
        couponModalEl.style.display = 'none';
    }
    
    // 폼 초기화
    var frmCoupon = document.getElementById('frmCoupon');
    if (frmCoupon) {
        frmCoupon.reset();
    }
    
    isValidUserId = false;
    document.getElementById('user_id_check').innerHTML = '';
    
    if (userIdCheckTimer) {
        clearTimeout(userIdCheckTimer);
    }
}

// 회원ID 입력 시 실시간 검증
document.addEventListener('DOMContentLoaded', function() {
    var userIdInput = document.getElementById('modal_user_id');
    if (userIdInput) {
        userIdInput.addEventListener('input', checkUserId);
        userIdInput.addEventListener('blur', checkUserId);
    }
    
    // 모달 배경 클릭 시 닫기
    var modalBg = document.querySelector('#couponModal .modal_bg');
    if (modalBg) {
        modalBg.addEventListener('click', closeModal);
    }
});
</script>
