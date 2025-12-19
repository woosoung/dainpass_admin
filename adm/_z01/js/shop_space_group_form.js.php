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
    // 그룹 타입 검증
    if (!f.group_type.value) {
        alert('그룹 타입을 선택해 주세요.');
        f.group_type.focus();
        return false;
    }

    // 그룹 타입 화이트리스트 검증
    var group_type_whitelist = ['FLOOR', 'HALL', 'ZONE'];
    if (group_type_whitelist.indexOf(f.group_type.value) === -1) {
        alert('올바른 그룹 타입이 아닙니다.');
        f.group_type.focus();
        return false;
    }

    // 그룹명 검증
    if (!f.name.value.trim()) {
        alert('그룹명을 입력해 주세요.');
        f.name.focus();
        return false;
    }

    // 그룹명 길이 검증
    if (f.name.value.trim().length > 100) {
        alert('그룹명은 최대 100자까지 입력 가능합니다.');
        f.name.focus();
        return false;
    }

    // 층 번호 범위 검증
    if (f.level_no.value.trim() !== '') {
        var level_no = parseInt(f.level_no.value);
        if (isNaN(level_no) || level_no < -999 || level_no > 999) {
            alert('층 번호는 -999 이상 999 이하로 입력해 주세요.');
            f.level_no.focus();
            return false;
        }
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

    // 캔버스 크기 범위 검증
    if (canvas_width && canvas_height) {
        var width = parseInt(canvas_width);
        var height = parseInt(canvas_height);

        if (isNaN(width) || width < 100 || width > 10000) {
            alert('캔버스 가로 크기는 100 이상 10000 이하로 입력해 주세요.');
            f.canvas_width.focus();
            return false;
        }

        if (isNaN(height) || height < 100 || height > 10000) {
            alert('캔버스 세로 크기는 100 이상 10000 이하로 입력해 주세요.');
            f.canvas_height.focus();
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

