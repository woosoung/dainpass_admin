<script>
document.addEventListener('DOMContentLoaded', function(){
    const serviceSelect = document.getElementById('service_select');
    const btnAddService = document.getElementById('btn_add_service');
    const selectedServicesList = document.getElementById('selected_services_list');
    let serviceCounter = <?php echo $registered_count ?? 0; ?>;
    
    // 서비스 추가 버튼 클릭 이벤트
    btnAddService.addEventListener('click', function() {
        const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            alert('서비스를 선택해 주세요.');
            return;
        }
        
        const serviceId = selectedOption.value;
        const serviceName = selectedOption.getAttribute('data-service-name');
        const serviceDesc = selectedOption.getAttribute('data-service-desc') || '-';
        const servicePrice = selectedOption.getAttribute('data-service-price') || '0';
        const defaultServiceTime = selectedOption.getAttribute('data-service-time') || '0';
        
        // 이미 추가된 서비스인지 확인
        const existingRows = selectedServicesList.querySelectorAll('tr[data-service-id="' + serviceId + '"]');
        if (existingRows.length > 0) {
            alert('이미 추가된 서비스입니다.');
            return;
        }
        
        // 새 행 추가
        serviceCounter++;
        const newRow = document.createElement('tr');
        newRow.setAttribute('data-service-id', serviceId);
        newRow.innerHTML = 
            '<td class="td_num">' + serviceCounter + '</td>' +
            '<td class="td_left">' + serviceName + '</td>' +
            '<td class="td_left">' + serviceDesc + '</td>' +
            '<td class="td_right">' + servicePrice + '원</td>' +
            '<td class="td_center">' +
                '<input type="hidden" name="service_id[]" value="' + serviceId + '">' +
                '<input type="number" name="service_time[]" value="' + defaultServiceTime + '" class="frm_input text-center" style="width:80px;" min="0" max="1440" required>' +
            '</td>' +
            '<td class="td_center">' +
                '<input type="number" name="slot_max_persons_cnt[]" value="1" class="frm_input text-center" style="width:80px;" min="1" max="100" required>' +
            '</td>' +
            '<td class="td_center">' +
                '<select name="status[]" class="frm_input">' +
                    '<option value="ok">정상</option>' +
                    '<option value="pending">대기</option>' +
                '</select>' +
            '</td>' +
            '<td class="td_center">' +
                '<button type="button" class="btn-remove-service btn btn_02" data-service-id="' + serviceId + '">취소</button>' +
            '</td>';
        
        selectedServicesList.appendChild(newRow);
        
        // 선택박스 초기화
        serviceSelect.value = '';
        
        // 번호 재정렬
        updateRowNumbers();
    });
    
    // 서비스 삭제 버튼 클릭 이벤트 (이벤트 위임)
    selectedServicesList.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-remove-service')) {
            const serviceId = e.target.getAttribute('data-service-id');
            const row = e.target.closest('tr[data-service-id="' + serviceId + '"]');
            
            if (confirm('이 서비스를 목록에서 제거하시겠습니까?')) {
                // 기존 등록된 서비스인지 확인 (staff_service_id가 있는지)
                const staffServiceIdInput = row.querySelector('input[name="staff_service_id[]"]');
                if (staffServiceIdInput && staffServiceIdInput.value) {
                    // 기존 등록된 서비스는 삭제 마커 추가
                    const deleteInput = document.createElement('input');
                    deleteInput.type = 'hidden';
                    deleteInput.name = 'delete_staff_service_id[]';
                    deleteInput.value = staffServiceIdInput.value;
                    document.getElementById('form01').appendChild(deleteInput);
                }
                
                row.remove();
                updateRowNumbers();
            }
        }
    });
    
    // 행 번호 업데이트 함수
    function updateRowNumbers() {
        const rows = selectedServicesList.querySelectorAll('tr');
        rows.forEach(function(row, index) {
            const numCell = row.querySelector('.td_num');
            if (numCell) {
                numCell.textContent = index + 1;
            }
        });
    }
    
    // 폼 제출 검증
    window.form01_submit = function(f) {
        const serviceRows = selectedServicesList.querySelectorAll('tr');
        if (serviceRows.length === 0) {
            alert('최소 하나 이상의 서비스를 선택해 주세요.');
            return false;
        }
        
        // 서비스시간과 슬롯당 고객수 검증
        const serviceTimeInputs = f.querySelectorAll('input[name="service_time[]"]');
        const slotMaxInputs = f.querySelectorAll('input[name="slot_max_persons_cnt[]"]');

        for (let i = 0; i < serviceTimeInputs.length; i++) {
            const serviceTime = parseInt(serviceTimeInputs[i].value);
            const slotMax = parseInt(slotMaxInputs[i].value);

            if (isNaN(serviceTime) || serviceTime < 0) {
                alert('서비스시간은 0 이상의 숫자여야 합니다.');
                serviceTimeInputs[i].focus();
                return false;
            }

            if (serviceTime > 1440) {
                alert('서비스시간은 최대 1440분(24시간)까지 입력 가능합니다.');
                serviceTimeInputs[i].focus();
                return false;
            }

            if (isNaN(slotMax) || slotMax < 1) {
                alert('슬롯당 고객수는 1 이상의 숫자여야 합니다.');
                slotMaxInputs[i].focus();
                return false;
            }

            if (slotMax > 100) {
                alert('슬롯당 고객수는 최대 100명까지 입력 가능합니다.');
                slotMaxInputs[i].focus();
                return false;
            }
        }
        
        return true;
    };
});
</script>

