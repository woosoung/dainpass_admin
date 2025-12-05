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
    if (!f.group_id.value) {
        alert('공간 그룹을 선택해 주세요.');
        f.group_id.focus();
        return false;
    }
    
    if (!f.unit_type.value) {
        alert('유닛 타입을 선택해 주세요.');
        f.unit_type.focus();
        return false;
    }
    
    if (!f.name.value.trim()) {
        alert('유닛명을 입력해 주세요.');
        f.name.focus();
        return false;
    }
    
    if (!f.capacity.value || parseInt(f.capacity.value) < 1) {
        alert('수용 인원은 1 이상이어야 합니다.');
        f.capacity.focus();
        return false;
    }
    
    return true;
}
</script>

