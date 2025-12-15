<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
var currentAppointmentId = 0;
var currentShopdetailId = 0;
var appointmentDetails = {};

function openPartialCancelModal(appointmentId, shopdetailId) {
    currentAppointmentId = appointmentId;
    currentShopdetailId = shopdetailId;
    
    // AJAX로 예약 상세 정보 로드
    $.ajax({
        url: './ajax/shop_appointment_detail_get.php',
        type: 'GET',
        data: {
            appointment_id: appointmentId,
            shopdetail_id: shopdetailId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                appointmentDetails = response.data;
                renderPartialCancelForm();
                $('#partialCancelModal').show();
            } else {
                alert(response.message || '예약 상세 정보를 불러올 수 없습니다.');
            }
        },
        error: function(xhr, status, error) {
            var errorMsg = '예약 상세 정보를 불러오는 중 오류가 발생했습니다.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMsg = response.message;
                    }
                } catch(e) {
                    console.error('AJAX Error:', xhr.status, xhr.responseText);
                }
            }
            console.error('AJAX Error Details:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });
            alert(errorMsg);
        }
    });
}

function renderPartialCancelForm() {
    var html = '<div class="tbl_frm01 tbl_wrap">';
    html += '<table>';
    html += '<caption>서비스별 취소 수량 입력</caption>';
    html += '<colgroup><col style="width:200px;"><col style="width:100px;"><col style="width:100px;"><col style="width:100px;"><col style="width:150px;"><col style="width:150px;"></colgroup>';
    html += '<thead><tr>';
    html += '<th scope="col">서비스명</th>';
    html += '<th scope="col">원본수량</th>';
    html += '<th scope="col">현재수량</th>';
    html += '<th scope="col">취소수량</th>';
    html += '<th scope="col">취소후수량</th>';
    html += '<th scope="col">단가</th>';
    html += '</tr></thead>';
    html += '<tbody>';
    
    var hasCancelable = false;
    
    for (var i = 0; i < appointmentDetails.length; i++) {
        var detail = appointmentDetails[i];
        var detailId = detail.detail_id;
        var serviceId = detail.service_id;
        var serviceName = detail.service_name;
        var orgQuantity = parseInt(detail.org_quantity);
        var quantity = parseInt(detail.quantity);
        var price = parseInt(detail.price);
        
        // 취소 가능한 서비스만 표시 (현재 수량이 0보다 큰 경우)
        if (quantity > 0) {
            hasCancelable = true;
            html += '<tr>';
            html += '<td>' + escapeHtml(serviceName) + '</td>';
            html += '<td class="td_num">' + orgQuantity + '</td>';
            html += '<td class="td_num">' + quantity + '</td>';
            html += '<td class="td_num">';
            html += '<input type="number" name="cancel_quantity[' + detailId + ']" ';
            html += 'id="cancel_quantity_' + detailId + '" ';
            html += 'class="frm_input cancel_quantity_input" ';
            html += 'min="0" max="' + quantity + '" ';
            html += 'value="0" ';
            html += 'data-detail-id="' + detailId + '" ';
            html += 'data-service-id="' + serviceId + '" ';
            html += 'data-current-quantity="' + quantity + '" ';
            html += 'data-price="' + price + '" ';
            html += 'onchange="updateRemainingQuantity(' + detailId + ');" ';
            html += 'style="width:80px; text-align:right;">';
            html += '</td>';
            html += '<td class="td_num"><span id="remaining_quantity_' + detailId + '">' + quantity + '</span></td>';
            html += '<td class="td_num">' + number_format(price) + '원</td>';
            html += '</tr>';
        }
    }
    
    html += '</tbody>';
    html += '</table>';
    html += '</div>';
    
    if (!hasCancelable) {
        html = '<p style="text-align:center; padding:20px;">취소 가능한 서비스가 없습니다.</p>';
    }
    
    $('#partialCancelContent').html(html);
}

function updateRemainingQuantity(detailId) {
    var input = $('#cancel_quantity_' + detailId);
    var currentQuantity = parseInt(input.data('current-quantity'));
    var cancelQuantity = parseInt(input.val()) || 0;
    
    if (cancelQuantity < 0) {
        input.val(0);
        cancelQuantity = 0;
    }
    
    if (cancelQuantity > currentQuantity) {
        alert('취소 수량은 현재 수량(' + currentQuantity + ')을 초과할 수 없습니다.');
        input.val(currentQuantity);
        cancelQuantity = currentQuantity;
    }
    
    var remainingQuantity = currentQuantity - cancelQuantity;
    $('#remaining_quantity_' + detailId).text(remainingQuantity);
}

function submitPartialCancel() {
    // 유효성 검증
    var hasCancel = false;
    var cancelData = {
        appointmentId: currentAppointmentId,
        appointmentShops: [{
            shopdetailId: currentShopdetailId,
            shopAppointmentDetails: []
        }]
    };
    
    $('.cancel_quantity_input').each(function() {
        var $input = $(this);
        var detailId = parseInt($input.data('detail-id'));
        var serviceId = parseInt($input.data('service-id'));
        var currentQuantity = parseInt($input.data('current-quantity'));
        var cancelQuantity = parseInt($input.val()) || 0;
        var remainingQuantity = currentQuantity - cancelQuantity;
        
        if (cancelQuantity > 0) {
            hasCancel = true;
            
            if (cancelQuantity > currentQuantity) {
                alert('취소 수량이 현재 수량을 초과할 수 없습니다.');
                $input.focus();
                return false;
            }
            
            if (remainingQuantity < 0) {
                alert('취소 후 수량이 0 미만이 될 수 없습니다.');
                $input.focus();
                return false;
            }
            
            cancelData.appointmentShops[0].shopAppointmentDetails.push({
                detailId: detailId,
                serviceId: serviceId,
                quantity: remainingQuantity  // 취소 후 남을 수량
            });
        } else {
            // 취소하지 않는 서비스도 포함 (quantity는 현재 수량 유지)
            cancelData.appointmentShops[0].shopAppointmentDetails.push({
                detailId: detailId,
                serviceId: serviceId,
                quantity: currentQuantity
            });
        }
    });
    
    if (!hasCancel) {
        alert('취소할 서비스를 선택하세요.');
        return false;
    }
    
    if (!confirm('부분 취소를 진행하시겠습니까?')) {
        return false;
    }
    
    // API 호출
    $.ajax({
        url: './ajax/shop_appointment_partial_cancel.php',
        type: 'POST',
        data: JSON.stringify(cancelData),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            // 디버그 모드 체크
            if (response.debug === true) {
                // 콘솔에 전체 디버그 정보 출력
                console.group('🔍 디버그 모드: API 요청 데이터');
                console.log('API URL:', response.api_url);
                console.log('원본 요청 데이터:', response.original_request_data);
                console.log('API 요청 데이터 (규격서 준수):', response.api_request_data);
                console.log('요청 데이터 (JSON):', response.request_json);
                console.log('요청 데이터 (포맷팅):', response.request_json_pretty);
                console.log('키워드:', response.keyword);
                console.log('SecretKey:', response.secretKey);
                console.log('가맹점 ID:', response.shop_id);
                console.log('예약 ID:', response.appointment_id);
                console.log('주문 ID (orderId):', response.order_id);
                console.log('전체 응답:', response);
                console.groupEnd();
                
                // 경고창에 요약 정보 표시
                var debugInfo = '디버그 모드: API 요청 데이터 확인\n\n';
                debugInfo += 'API URL: ' + response.api_url + '\n';
                debugInfo += '예약 ID: ' + response.appointment_id + '\n';
                debugInfo += '주문 ID (orderId): ' + response.order_id + '\n';
                debugInfo += '가맹점 ID: ' + response.shop_id + '\n';
                debugInfo += '키워드: ' + (response.keyword || 'null') + '\n';
                debugInfo += 'SecretKey: ' + (response.secretKey ? response.secretKey.substring(0, 20) + '...' : 'null') + '\n\n';
                debugInfo += '자세한 정보는 브라우저 콘솔(F12)을 확인하세요.';
                alert(debugInfo);
                return;
            }
            
            if (response.success) {
                alert('부분 취소가 완료되었습니다.');
                location.reload();
            } else {
                alert(response.message || '부분 취소 처리 중 오류가 발생했습니다.');
            }
        },
        error: function(xhr, status, error) {
            // 에러 응답도 디버그 모드일 수 있음
            if (xhr.responseJSON && xhr.responseJSON.debug === true) {
                var debugResponse = xhr.responseJSON;
                console.group('🔍 디버그 모드: API 요청 데이터 (에러 응답)');
                console.log('API URL:', debugResponse.api_url);
                console.log('원본 요청 데이터:', debugResponse.original_request_data);
                console.log('API 요청 데이터 (규격서 준수):', debugResponse.api_request_data);
                console.log('요청 데이터 (JSON):', debugResponse.request_json);
                console.log('요청 데이터 (포맷팅):', debugResponse.request_json_pretty);
                console.log('키워드:', debugResponse.keyword);
                console.log('SecretKey:', debugResponse.secretKey);
                console.log('가맹점 ID:', debugResponse.shop_id);
                console.log('예약 ID:', debugResponse.appointment_id);
                console.log('주문 ID (orderId):', debugResponse.order_id);
                console.log('전체 응답:', debugResponse);
                console.groupEnd();
                
                var debugInfo = '디버그 모드: API 요청 데이터 확인\n\n';
                debugInfo += 'API URL: ' + debugResponse.api_url + '\n';
                debugInfo += '예약 ID: ' + debugResponse.appointment_id + '\n';
                debugInfo += '주문 ID (orderId): ' + debugResponse.order_id + '\n';
                debugInfo += '가맹점 ID: ' + debugResponse.shop_id + '\n';
                debugInfo += '키워드: ' + (debugResponse.keyword || 'null') + '\n';
                debugInfo += 'SecretKey: ' + (debugResponse.secretKey ? debugResponse.secretKey.substring(0, 20) + '...' : 'null') + '\n\n';
                debugInfo += '자세한 정보는 브라우저 콘솔(F12)을 확인하세요.';
                alert(debugInfo);
                return;
            }
            
            var errorMsg = '부분 취소 처리 중 오류가 발생했습니다.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            console.error('AJAX Error:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });
            alert(errorMsg);
        }
    });
}

function closePartialCancelModal() {
    $('#partialCancelModal').hide();
    currentAppointmentId = 0;
    currentShopdetailId = 0;
    appointmentDetails = {};
}

function escapeHtml(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function number_format(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// 모달 외부 클릭 시 닫기
$(document).on('click', '#partialCancelModal', function(e) {
    if ($(e.target).attr('id') === 'partialCancelModal') {
        closePartialCancelModal();
    }
});
</script>
