<script>
document.addEventListener('DOMContentLoaded', function() {
    // 멀티파일 업로드 설정
    if (document.getElementById('group_images')) {
        $('#group_images').MultiFile({
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
});

function fgroup_submit(f) {
    if (!f.group_type.value) {
        alert('그룹 타입을 선택해 주세요.');
        f.group_type.focus();
        return false;
    }
    
    if (!f.name.value.trim()) {
        alert('그룹명을 입력해 주세요.');
        f.name.focus();
        return false;
    }
    
    // 캔버스 크기 검증 (둘 다 입력하거나 둘 다 비워야 함)
    var canvas_width = f.canvas_width.value.trim();
    var canvas_height = f.canvas_height.value.trim();
    
    if ((canvas_width && !canvas_height) || (!canvas_width && canvas_height)) {
        alert('캔버스 크기는 가로와 세로를 모두 입력해 주세요.');
        if (!canvas_width) {
            f.canvas_width.focus();
        } else {
            f.canvas_height.focus();
        }
        return false;
    }
    
    if (canvas_width && canvas_height) {
        if (parseInt(canvas_width) < 1 || parseInt(canvas_height) < 1) {
            alert('캔버스 크기는 1 이상의 값을 입력해 주세요.');
            return false;
        }
    }
    
    return true;
}
</script>

