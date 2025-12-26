<script>
document.addEventListener('DOMContentLoaded', function() {
    // 멀티파일 업로드 설정
    if (document.getElementById('unit_images')) {
        $('#unit_images').MultiFile({
            max: 5,
            accept: 'jpg|jpeg|png|gif|webp',
            STRING: {
                remove: '삭제',
                selected: '$file',
                denied: '$ext 파일은 업로드할 수 없습니다.',
                duplicate: '$file 파일은 이미 선택되었습니다.'
            }
        });
    }
    
    // unit_type 변경 시 좌석 정보 필드 표시/숨김
    var unitTypeSelect = document.getElementById('unit_type');
    var trSeatInfo = document.getElementById('tr_seat_info');
    
    function toggleSeatInfo() {
        if (unitTypeSelect.value === 'SEAT') {
            trSeatInfo.style.display = '';
        } else {
            trSeatInfo.style.display = 'none';
        }
    }
    
    if (unitTypeSelect) {
        unitTypeSelect.addEventListener('change', toggleSeatInfo);
        toggleSeatInfo(); // 초기 상태 설정
    }
});

function funit_submit(f) {
    // 공간 그룹 검증
    if (!f.group_id.value) {
        alert('공간 그룹을 선택해 주세요.');
        f.group_id.focus();
        return false;
    }

    // 유닛 타입 검증
    if (!f.unit_type.value) {
        alert('유닛 타입을 선택해 주세요.');
        f.unit_type.focus();
        return false;
    }

    // 유닛 타입 화이트리스트 검증
    var unit_type_whitelist = ['ROOM', 'TABLE', 'SEAT', 'VIRTUAL'];
    if (unit_type_whitelist.indexOf(f.unit_type.value) === -1) {
        alert('올바른 유닛 타입이 아닙니다.');
        f.unit_type.focus();
        return false;
    }

    // 유닛명 검증
    if (!f.name.value.trim()) {
        alert('유닛명을 입력해 주세요.');
        f.name.focus();
        return false;
    }

    // 유닛명 길이 검증
    if (f.name.value.trim().length > 100) {
        alert('유닛명은 최대 100자까지 입력 가능합니다.');
        f.name.focus();
        return false;
    }

    // 유닛 코드 길이 검증
    if (f.code.value.trim().length > 50) {
        alert('유닛 코드는 최대 50자까지 입력 가능합니다.');
        f.code.focus();
        return false;
    }

    // 수용 인원 검증
    if (!f.capacity.value || parseInt(f.capacity.value) < 1) {
        alert('수용 인원은 1 이상이어야 합니다.');
        f.capacity.focus();
        return false;
    }

    // 수용 인원 범위 검증
    var capacity = parseInt(f.capacity.value);
    if (isNaN(capacity) || capacity < 1 || capacity > 9999) {
        alert('수용 인원은 1 이상 9999 이하로 입력해 주세요.');
        f.capacity.focus();
        return false;
    }

    // 좌표 범위 검증
    if (f.pos_x.value.trim() !== '') {
        var pos_x = parseFloat(f.pos_x.value);
        if (isNaN(pos_x) || pos_x < -10000 || pos_x > 10000) {
            alert('X 좌표는 -10000 이상 10000 이하로 입력해 주세요.');
            f.pos_x.focus();
            return false;
        }
    }

    if (f.pos_y.value.trim() !== '') {
        var pos_y = parseFloat(f.pos_y.value);
        if (isNaN(pos_y) || pos_y < -10000 || pos_y > 10000) {
            alert('Y 좌표는 -10000 이상 10000 이하로 입력해 주세요.');
            f.pos_y.focus();
            return false;
        }
    }

    // 크기 범위 검증
    if (f.width.value.trim() !== '') {
        var width = parseFloat(f.width.value);
        if (isNaN(width) || width < 20 || width > 10000) {
            alert('가로 크기는 20 이상 10000 이하로 입력해 주세요.');
            f.width.focus();
            return false;
        }
    }

    if (f.height.value.trim() !== '') {
        var height = parseFloat(f.height.value);
        if (isNaN(height) || height < 20 || height > 10000) {
            alert('세로 크기는 20 이상 10000 이하로 입력해 주세요.');
            f.height.focus();
            return false;
        }
    }

    // 회전 각도 범위 검증
    if (f.rotation_deg.value.trim() !== '') {
        var rotation_deg = parseFloat(f.rotation_deg.value);
        if (isNaN(rotation_deg) || rotation_deg < -360 || rotation_deg > 360) {
            alert('회전 각도는 -360 이상 360 이하로 입력해 주세요.');
            f.rotation_deg.focus();
            return false;
        }
    }

    // 정렬순서 범위 검증
    if (f.sort_order.value.trim() !== '') {
        var sort_order = parseInt(f.sort_order.value);
        if (isNaN(sort_order) || sort_order < 0 || sort_order > 9999) {
            alert('정렬순서는 0 이상 9999 이하로 입력해 주세요.');
            f.sort_order.focus();
            return false;
        }
    }

    // 좌석 정보 길이 검증
    if (f.seat_row.value.trim().length > 10) {
        alert('좌석 열은 최대 10자까지 입력 가능합니다.');
        f.seat_row.focus();
        return false;
    }

    if (f.seat_number.value.trim().length > 10) {
        alert('좌석 번호는 최대 10자까지 입력 가능합니다.');
        f.seat_number.focus();
        return false;
    }

    // 설명 길이 검증
    if (f.description.value.trim().length > 1000) {
        alert('설명은 최대 1000자까지 입력 가능합니다.');
        f.description.focus();
        return false;
    }

    // 토큰 가져오기
    var token = get_ajax_token();
    if (!token) {
        alert('토큰 정보가 올바르지 않습니다.');
        return false;
    }
    f.token.value = token;

    return true;
}
</script>

