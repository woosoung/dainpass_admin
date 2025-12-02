<?php
if (!defined('_GNUBOARD_')) exit;
?>
<script>
// 스케줄 삭제
function deleteSchedule(scheduleId) {
    if (!confirm('해당 근무일정을 삭제하시겠습니까?')) {
        return false;
    }
    
    $.ajax({
        url: './ajax/staff_schedule_del.php',
        type: 'POST',
        data: {
            schedule_id: scheduleId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('삭제되었습니다.');
                location.reload();
            } else {
                alert(response.message || '삭제에 실패했습니다.');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            alert('삭제 중 오류가 발생했습니다.');
        }
    });
}

$(document).ready(function() {
    // 페이지 로드 완료
    console.log('Staff Schedule List loaded');
});
</script>

